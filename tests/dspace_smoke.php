<?php
/*
 * Smoke test for DSpaceRepository — metadata-profile support.
 *
 * Covers the dublin_core (default) and lom profiles:
 *   1. Unit — dublin_core profile parses dc.* fields.
 *   2. Unit — lom profile parses lom.* fields, MIME-based type,
 *      handle-based URL (ignoring the stale dc.identifier.uri).
 *   3. Unit — missing/invalid metadata_profile falls back to dublin_core.
 *   4. Live — optional, gated on env vars:
 *        DSPACE_TEST_BASE      conventional DSpace (dublin_core)
 *        DSPACE_LOM_TEST_BASE  LOM repository (lom)
 *   5. Registry — `dspace` is a supported type.
 *
 * Run from inside the dev container:
 *   docker compose -f docker-compose.dev.yaml exec -T eclass php tests/dspace_smoke.php
 *
 * Exit code 0 = all assertions passed, 1 = at least one failure.
 */

declare(strict_types=1);

define('DSPACE_TEST_BASE', (string)getenv('DSPACE_TEST_BASE'));
define('DSPACE_LOM_TEST_BASE', (string)getenv('DSPACE_LOM_TEST_BASE'));

chdir(__DIR__ . '/..');

$failures = [];
$passes = 0;

function tassert(bool $cond, string $name, ?string $detail = null): void
{
    global $failures, $passes;
    if ($cond) {
        $passes++;
        echo "  \033[32mPASS\033[0m $name\n";
    } else {
        $failures[] = $name . ($detail ? " — $detail" : '');
        echo "  \033[31mFAIL\033[0m $name" . ($detail ? " — $detail" : '') . "\n";
    }
}

function section(string $title): void
{
    echo "\n\033[1m== $title ==\033[0m\n";
}

require_once 'config/config.php';
require_once 'include/baseTheme.php';
require_once 'include/lib/externalrepos/ExternalRepoInterface.php';
require_once 'include/lib/externalrepos/AbstractExternalRepo.php';
require_once 'include/lib/externalrepos/DSpaceRepository.php';
require_once 'include/lib/externalrepos/ExternalRepoFactory.php';
require_once 'modules/admin/extconfig/externals.php';
require_once 'modules/admin/extconfig/externalreposapp.php';

/** Build a DSpaceRepository with the given metadata_profile (null = no config). */
function dspaceRepo(?string $profile): DSpaceRepository
{
    $cfg = (object)[
        'id' => 0,
        'name' => 'DSpace smoke',
        'type' => 'dspace',
        'base_url' => 'https://repo.example',
        'api_key' => null,
        'auth_type' => 'none',
        'enabled' => 1,
        'config' => $profile === null ? null : json_encode(['metadata_profile' => $profile]),
    ];
    return new DSpaceRepository($cfg);
}

/** Wrap a metadata assoc-array into DSpace's {key: [{value: ...}]} shape. */
function md(array $pairs): array
{
    $out = [];
    foreach ($pairs as $k => $v) {
        $out[$k] = [['value' => $v]];
    }
    return $out;
}

$parseItem = new ReflectionMethod(DSpaceRepository::class, 'parseItem');
$parseItem->setAccessible(true);

// Synthetic Dublin Core item
$dcItem = [
    'uuid' => 'dc-uuid-1',
    'handle' => '123/45',
    'metadata' => md([
        'dc.title' => 'A Dublin Core Title',
        'dc.type' => 'Article',
        'dc.description.abstract' => 'An abstract in Dublin Core.',
        'dc.contributor.author' => 'Doe, Jane',
        'dc.date.issued' => '2024-01-01',
        'dc.identifier.uri' => 'https://repo.example/handle/123/45',
    ]),
];

// Synthetic LOM item (mirrors a real Photodentro record)
$lomItem = [
    'uuid' => 'lom-uuid-1',
    'handle' => '20.500.14863/13544',
    'metadata' => md([
        'lom.general-title' => 'Το νερό (Α΄ μέρος)',
        'lom.general-description' => 'Εκπαιδευτικό βίντεο.',
        'lom.technical-format' => 'video/mp4',
        'lom.lifecycle-contribute-entity' => 'Εκπαιδευτική Ραδιοτηλεόραση',
        'dc.date.issued' => '2023-10-26',
        // deliberately a stale host — the lom profile must ignore this
        'dc.identifier.uri' => 'https://udev8.photodentro.edu.gr/handle/20.500.14863/13544',
        // deliberately stale — must be ignored in favour of the embedded thumbnail
        'lom.relation-hasThumbnail' => '/retrieve/5378/thumb.jpg',
    ]),
    // ?embed=thumbnail inlines the thumbnail bitstream
    '_embedded' => [
        'thumbnail' => [
            '_links' => ['content' => ['href' => 'https://repo.example/server/api/core/bitstreams/abc-123/content']],
        ],
    ],
];

section('1 · Unit — dublin_core profile');

$repoDc = dspaceRepo('dublin_core');
$dc = $parseItem->invoke($repoDc, $dcItem);
tassert(is_array($dc), 'parseItem() returns an array');
tassert(($dc['title'] ?? null) === 'A Dublin Core Title', 'title from dc.title', $dc['title'] ?? 'null');
tassert(($dc['type'] ?? null) === 'article', 'type from dc.type (Article -> article)', $dc['type'] ?? 'null');
tassert(($dc['description'] ?? null) === 'An abstract in Dublin Core.', 'description from dc.description.abstract');
tassert(($dc['url'] ?? null) === 'https://repo.example/handle/123/45', 'url from dc.identifier.uri', $dc['url'] ?? 'null');
tassert(in_array('Doe, Jane', $dc['metadata']['authors'] ?? [], true), 'author from dc.contributor.author');
tassert(($dc['metadata']['metadata_profile'] ?? null) === 'dublin_core', 'metadata.metadata_profile stamped');

section('2 · Unit — lom profile');

$repoLom = dspaceRepo('lom');
$lom = $parseItem->invoke($repoLom, $lomItem);
tassert(($lom['title'] ?? null) === 'Το νερό (Α΄ μέρος)', 'title from lom.general-title', $lom['title'] ?? 'null');
tassert(($lom['type'] ?? null) === 'video', 'type from lom.technical-format (video/mp4 -> video)', $lom['type'] ?? 'null');
tassert(($lom['description'] ?? null) === 'Εκπαιδευτικό βίντεο.', 'description from lom.general-description');
tassert(in_array('Εκπαιδευτική Ραδιοτηλεόραση', $lom['metadata']['authors'] ?? [], true),
    'author from lom.lifecycle-contribute-entity');
tassert(($lom['url'] ?? null) === 'https://repo.example/handle/20.500.14863/13544',
    'url is handle-based on configured host, NOT the stale dc.identifier.uri', $lom['url'] ?? 'null');
tassert(strpos((string)($lom['url'] ?? ''), 'udev8') === false, 'lom url does not leak the stale udev8 host');
tassert(($lom['thumbnail'] ?? null) === 'https://repo.example/server/api/core/bitstreams/abc-123/content',
    'thumbnail from embedded DSpace bitstream, not the stale lom.relation-hasThumbnail', $lom['thumbnail'] ?? 'null');
tassert(($lom['metadata']['metadata_profile'] ?? null) === 'lom', 'metadata.metadata_profile stamped lom');

// lom profile falls back to dc.title when lom.general-title is absent
$lomFallback = $parseItem->invoke($repoLom, [
    'uuid' => 'x', 'handle' => '1/2',
    'metadata' => md(['dc.title' => 'DC Fallback Title', 'lom.technical-format' => 'audio/mpeg']),
]);
tassert(($lomFallback['title'] ?? null) === 'DC Fallback Title', 'lom profile falls back to dc.title');
tassert(($lomFallback['type'] ?? null) === 'audio', 'lom maps audio/mpeg -> audio');

section('3 · Unit — profile defaulting');

$repoNoCfg = dspaceRepo(null);
$def = $parseItem->invoke($repoNoCfg, $dcItem);
tassert(($def['metadata']['metadata_profile'] ?? null) === 'dublin_core',
    'no config -> defaults to dublin_core');
tassert(($def['title'] ?? null) === 'A Dublin Core Title', 'default profile parses dc.* identically');

$repoBad = dspaceRepo('nonsense');
$bad = $parseItem->invoke($repoBad, $dcItem);
tassert(($bad['metadata']['metadata_profile'] ?? null) === 'dublin_core',
    'invalid profile value -> defaults to dublin_core');

section('4 · Live (optional — set DSPACE_TEST_BASE / DSPACE_LOM_TEST_BASE)');

if (DSPACE_TEST_BASE !== '') {
    $cfg = (object)[
        'id' => 0, 'name' => 'live dc', 'type' => 'dspace',
        'base_url' => DSPACE_TEST_BASE, 'api_key' => null, 'auth_type' => 'none',
        'enabled' => 1, 'config' => json_encode(['metadata_profile' => 'dublin_core']),
    ];
    $repo = new DSpaceRepository($cfg);
    $res = $repo->search('test', [], 1, 3);
    tassert(($res['success'] ?? null) === true, 'live dublin_core search succeeds', $res['error'] ?? null);
    if (!empty($res['items'])) {
        $first = $res['items'][0];
        tassert(!empty($first['title']) && $first['title'] !== 'Untitled',
            'live dublin_core item has a real title', $first['title'] ?? 'null');
    }
} else {
    echo "  \033[33mSKIP\033[0m live dublin_core (DSPACE_TEST_BASE not set)\n";
}

if (DSPACE_LOM_TEST_BASE !== '') {
    $cfg = (object)[
        'id' => 0, 'name' => 'live lom', 'type' => 'dspace',
        'base_url' => DSPACE_LOM_TEST_BASE, 'api_key' => null, 'auth_type' => 'none',
        'enabled' => 1, 'config' => json_encode(['metadata_profile' => 'lom']),
    ];
    $repo = new DSpaceRepository($cfg);
    $res = $repo->search('νερό', [], 1, 3);
    tassert(($res['success'] ?? null) === true, 'live lom search succeeds', $res['error'] ?? null);
    if (!empty($res['items'])) {
        $first = $res['items'][0];
        tassert(!empty($first['title']) && $first['title'] !== 'Untitled',
            'live lom item has a real title (not Untitled)', $first['title'] ?? 'null');
    }
} else {
    echo "  \033[33mSKIP\033[0m live lom (DSPACE_LOM_TEST_BASE not set)\n";
}

section('5 · Registry');

$supported = ExternalRepoFactory::getSupportedTypes();
tassert(in_array('dspace', $supported, true), 'ExternalRepoFactory lists dspace');
$types = ExternalReposApp::getRepositoryTypes();
tassert(isset($types['dspace']), 'ExternalReposApp::getRepositoryTypes() includes dspace');

echo "\n";
if ($failures) {
    echo "\033[31m" . count($failures) . " FAILED\033[0m / $passes passed\n";
    foreach ($failures as $f) {
        echo "  - $f\n";
    }
    exit(1);
}
echo "\033[32mAll $passes assertions passed.\033[0m\n";
exit(0);
