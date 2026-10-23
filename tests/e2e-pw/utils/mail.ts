import { expect, request } from '@playwright/test';
import type { APIRequestContext } from '@playwright/test';

/**
 * Client for the e2e stack's Mailpit API (https://mailpit.axllent.org/docs/api-v1/), which catches every
 * mail the site sends once `harness.useMailpit()` has run (done in `auth.setup.ts`).
 */

export const MAILPIT_URL = process.env.ECLASS_E2E_MAILPIT_URL
  || `http://localhost:${process.env.ECLASS_E2E_MAILPIT_PORT || '8025'}`;

type Address = { Name: string; Address: string };

export type MailSummary = {
  ID: string;
  From: Address;
  To: Address[];
  Subject: string;
  Created: string;
  Snippet: string;
};

export type Mail = MailSummary & { Text: string; HTML: string };

export class Mailbox {
  constructor(private readonly api: APIRequestContext) {}

  static async create(): Promise<{ mail: Mailbox; dispose: () => Promise<void> }> {
    const api = await request.newContext({ baseURL: MAILPIT_URL });
    return { mail: new Mailbox(api), dispose: () => api.dispose() };
  }

  private async json<T>(path: string): Promise<T> {
    const response = await this.api.get(path);
    if (!response.ok()) {
      throw new Error(`mailpit ${path}: HTTP ${response.status()}`);
    }
    return response.json() as Promise<T>;
  }

  /** Messages sent to `to` (every message when omitted), newest first. */
  async messages(to?: string): Promise<MailSummary[]> {
    const path = to ? `/api/v1/search?query=${encodeURIComponent(`to:"${to}"`)}` : '/api/v1/messages';
    return (await this.json<{ messages: MailSummary[] }>(path)).messages;
  }

  async message(id: string): Promise<Mail> {
    return this.json<Mail>(`/api/v1/message/${id}`);
  }

  /** The newest message to `to`, waiting up to `timeout` ms for one to arrive. */
  async latest(to: string, timeout = 15_000): Promise<Mail> {
    let found: MailSummary | undefined;
    await expect.poll(async () => (found = (await this.messages(to))[0]), {
      message: `a mail to ${to}`,
      timeout,
    }).toBeTruthy();
    return this.message(found!.ID);
  }

  /** The first link in the mail whose URL matches `pattern` (e.g. `/lostpass|mail_verify/`). */
  extractLink(mail: Mail, pattern: RegExp): string {
    const links = [...`${mail.HTML}\n${mail.Text}`.matchAll(/https?:\/\/[^\s"'<>]+/g)]
      .map((match) => match[0].replace(/&amp;/g, '&'));
    const link = links.find((url) => pattern.test(url));
    if (!link) {
      throw new Error(`no link matching ${pattern} in "${mail.Subject}" (links: ${links.join(', ') || 'none'})`);
    }
    return link;
  }

  async clear(): Promise<void> {
    const response = await this.api.delete('/api/v1/messages');
    if (!response.ok()) {
      throw new Error(`mailpit clear: HTTP ${response.status()}`);
    }
  }
}
