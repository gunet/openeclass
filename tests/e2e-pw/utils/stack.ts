import path from 'node:path';
import { execFileSync } from 'node:child_process';

/**
 * Drives the e2e docker stack (docker-compose.development.yaml + docker-compose.e2e.yaml)
 * from Node: install check, raw SQL, and DB + course-file snapshots. Config values and seed
 * data go through the PHP harness instead (`harness.ts`).
 */

const ROOT = path.resolve(__dirname, '..', '..', '..');
const FILES = ['-f', 'docker-compose.development.yaml', '-f', 'docker-compose.e2e.yaml'];

/** Run `docker compose <args>` for the e2e stack and return stdout. Throws on a non-zero exit. */
export function compose(args: string[], input?: string): string {
  return execFileSync('docker', ['compose', ...FILES, ...args], {
    cwd: ROOT,
    input,
    encoding: 'utf8',
    stdio: [input === undefined ? 'ignore' : 'pipe', 'pipe', 'pipe'],
  });
}

function succeeds(args: string[]): boolean {
  try {
    compose(args);
    return true;
  } catch {
    return false;
  }
}

export const isInstalled = (): boolean => succeeds(['exec', '-T', 'eclass', 'test', '-f', 'config/config.php']);

/** Run SQL against the eClass database and return the tab-separated rows (no header). */
export function sql(query: string): string {
  return compose(
    ['exec', '-T', 'db', 'sh', '-c', 'exec mariadb -uroot -p"$MYSQL_ROOT_PASSWORD" --batch --skip-column-names "$MYSQL_DATABASE"'],
    query,
  );
}

/**
 * Drop the app's FileCache files (include/lib/file_cache.class.php writes them to the
 * container's /tmp). get_config() reads config through that cache for 300s, so it has
 * to go whenever the DB is changed behind the app's back.
 */
export function clearCache(resource = '*'): void {
  compose(['exec', '-T', 'eclass', 'sh', '-c', `rm -f /tmp/*_${resource}.cache`]);
}

const SNAPSHOT_NAME = /^[a-z0-9-]+$/;

function checkName(name: string): void {
  if (!SNAPSHOT_NAME.test(name)) {
    throw new Error(`Snapshot names are lowercase letters, digits and dashes, got "${name}"`);
  }
}

/** Snapshots live in the `e2e_snapshots` volume, so they survive container restarts but not `down -v`. */
export function hasSnapshot(name: string): boolean {
  checkName(name);
  return succeeds(['exec', '-T', 'db', 'test', '-f', `/snapshots/${name}.sql`]);
}

/** Dump the database and tar the uploaded files (`courses/`, `video/`). */
export function snapshot(name: string): void {
  checkName(name);
  compose(['exec', '-T', 'db', 'sh', '-c',
    `mariadb-dump -uroot -p"$MYSQL_ROOT_PASSWORD" --single-transaction --routines --add-drop-database --databases "$MYSQL_DATABASE" > /snapshots/${name}.sql`]);
  compose(['exec', '-T', 'eclass', 'tar', '-C', '/var/www/html', '-cf', `/snapshots/${name}.files.tar`, 'courses', 'video']);
}

/** Put the database and the uploaded files back to how they were at `snapshot(name)`. */
export function restore(name: string): void {
  checkName(name);
  compose(['exec', '-T', 'db', 'sh', '-c', `mariadb -uroot -p"$MYSQL_ROOT_PASSWORD" < /snapshots/${name}.sql`]);
  compose(['exec', '-T', 'eclass', 'sh', '-c',
    'cd /var/www/html && find courses video -mindepth 1 -delete'
    + ` && tar -xf /snapshots/${name}.files.tar && chown -R nginx:nginx courses video`]);
  clearCache();
}
