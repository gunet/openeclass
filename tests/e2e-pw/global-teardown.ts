import type { FullConfig } from '@playwright/test';
import { Harness } from './utils/harness';

/**
 * Undo the harness's config overrides (mail included) after the run, so the e2e site behaves like a
 * normal install between runs. Skipped when the harness isn't reachable (e.g. the install-only project
 * wiped the site).
 */
export default async function globalTeardown(config: FullConfig): Promise<void> {
  const baseURL = config.projects[0]?.use.baseURL;
  if (!baseURL) {
    return;
  }
  const { harness, dispose } = await Harness.create(baseURL);
  try {
    await harness.deactivate();
  } catch (error) {
    console.warn(`global teardown: harness not reachable, nothing to undo (${(error as Error).message})`);
  } finally {
    await dispose();
  }
}
