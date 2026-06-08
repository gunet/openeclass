<?php

/*
 *  ========================================================================
 *  * Open eClass
 *  * E-learning and Course Management System
 *  * ========================================================================
 *  * Copyright 2003-2024, Greek Universities Network - GUnet
 *  *
 *  * Open eClass is an open platform distributed in the hope that it will
 *  * be useful (without any warranty), under the terms of the GNU (General
 *  * Public License) as published by the Free Software Foundation.
 *  * The full license can be read in "/info/license/license_gpl.txt".
 *  *
 *  * Contact address: GUnet Asynchronous eLearning Group
 *  *                  e-mail: info@openeclass.org
 *  * ========================================================================
 *
 */

require_once __DIR__ . '/AbstractExternalRepo.php';

/**
 * IslandoraRepository
 *
 * Implementation for Drupal/Islandora repositories exposed via JSON:API Search API
 * (search_api + jsonapi + jsonapi_search_api).
 *
 * Reference endpoint shape:
 *   {base_url}/{lang}/jsonapi/index/{index_machine_name}?filter[fulltext]=...
 *
 * The provider takes per-instance config (admin form -> stored in
 * external_repository.config JSON):
 *   - index_name        machine name of the Search API index (default repository_items_index)
 *   - lang_code         Drupal language path prefix (default 'el')
 *   - url_pattern       Item URL template, tokens {base} {lang} {uuid} {pid}
 *                       (default '{base}/{lang}/node/{uuid}')
 *   - description_field Optional attributes.* field shown as item description
 */
class IslandoraRepository extends AbstractExternalRepo
{
    const DEFAULT_INDEX = 'repository_items_index';
    const DEFAULT_LANG = 'el';
    const DEFAULT_URL_PATTERN = '{base}/{lang}/node/{uuid}';

    /** @var array<string,string> request-scoped tid-uuid -> term name */
    private array $termCache = [];

    public function getType(): string
    {
        return 'islandora';
    }

    public function search(string $query, array $filters = [], int $page = 1, int $perPage = 20): array
    {
        if (!$this->isConfigured()) {
            return $this->buildErrorResponse(
                $GLOBALS['langRepoNotConfigured'] ?? 'Repository is not properly configured'
            );
        }

        $perPage = max(1, min(50, $perPage));
        $page = max(1, $page);

        $params = [
            'page[limit]' => $perPage,
            'page[offset]' => ($page - 1) * $perPage,
            'include' => 'field_representative_image.field_media_image,field_model',
        ];
        if (trim($query) !== '') {
            $params['filter[fulltext]'] = $query;
        }

        try {
            $resp = $this->httpGet($this->indexPath(), $params);
            if (!$resp['success']) {
                $err = $resp['error'] ?? ($GLOBALS['langSearchError'] ?? 'Search failed');
                if (!empty($resp['http_code'])) {
                    $err .= ' (HTTP ' . $resp['http_code'] . ')';
                }
                error_log("Islandora search failed: $err");
                return $this->buildErrorResponse($err, $resp['http_code'] ?? 0);
            }
            return $this->parseSearchResults($resp['data'] ?? [], $page, $perPage);
        } catch (Exception $e) {
            error_log('Islandora search exception: ' . $e->getMessage());
            return $this->buildErrorResponse($e->getMessage());
        }
    }

    public function getItem(string $itemId): ?array
    {
        if (!$this->isConfigured() || $itemId === '') {
            return null;
        }
        $lang = $this->langCode();
        $url = "/$lang/jsonapi/node/islandora_object/" . rawurlencode($itemId);
        $params = ['include' => 'field_representative_image.field_media_image,field_model'];
        try {
            $resp = $this->httpGet($url, $params);
            if (!$resp['success'] || !is_array($resp['data']) || empty($resp['data']['data'])) {
                return null;
            }
            $included = $this->buildIncludedIndex($resp['data']['included'] ?? []);
            return $this->parseItem($resp['data']['data'], $included);
        } catch (Exception $e) {
            error_log('Islandora getItem exception: ' . $e->getMessage());
            return null;
        }
    }

    public function testConnection(): array
    {
        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'message' => $GLOBALS['langRepoNotConfigured'] ?? 'Repository is not properly configured',
            ];
        }
        try {
            $resp = $this->httpGet($this->indexPath(), ['page[limit]' => 1]);
            if ($resp['success'] && is_array($resp['data']) && array_key_exists('data', $resp['data'])) {
                return [
                    'success' => true,
                    'message' => $GLOBALS['langConnectionSuccess'] ?? 'Connection successful',
                ];
            }
            return [
                'success' => false,
                'message' => ($GLOBALS['langConnectionFailed'] ?? 'Connection failed')
                    . ' (HTTP ' . ($resp['http_code'] ?? 0) . ')',
            ];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    // ------------------------------------------------------------------
    // helpers

    private function indexName(): string
    {
        $n = $this->additionalConfig['index_name'] ?? '';
        return trim($n) !== '' ? trim($n) : self::DEFAULT_INDEX;
    }

    private function langCode(): string
    {
        $l = $this->additionalConfig['lang_code'] ?? '';
        return preg_match('/^[a-z]{2,3}(-[A-Za-z0-9]+)?$/', $l) === 1 ? $l : self::DEFAULT_LANG;
    }

    private function urlPattern(): ?string
    {
        $p = $this->additionalConfig['url_pattern'] ?? '';
        $p = trim($p);
        return $p !== '' ? $p : null;
    }

    private function descriptionField(): ?string
    {
        $f = $this->additionalConfig['description_field'] ?? '';
        return trim($f) !== '' ? trim($f) : null;
    }

    private function indexPath(): string
    {
        return '/' . $this->langCode() . '/jsonapi/index/' . $this->indexName();
    }

    /**
     * Build "type:id" -> resource index over the `included` array.
     */
    private function buildIncludedIndex(array $included): array
    {
        $idx = [];
        foreach ($included as $resource) {
            if (isset($resource['type'], $resource['id'])) {
                $idx[$resource['type'] . ':' . $resource['id']] = $resource;
            }
            if (isset($resource['type']) && strpos($resource['type'], 'taxonomy_term--islandora_models') === 0
                && isset($resource['id'], $resource['attributes']['name'])) {
                $this->termCache[$resource['id']] = (string)$resource['attributes']['name'];
            }
        }
        return $idx;
    }

    private function parseSearchResults(array $payload, int $page, int $perPage): array
    {
        $data = $payload['data'] ?? [];
        $included = $this->buildIncludedIndex($payload['included'] ?? []);

        // `meta.count` includes omitted (forbidden) items; subtract them so paging math is honest.
        $count = $payload['meta']['count'] ?? count($data);
        $omitted = $payload['meta']['omitted']['links'] ?? $payload['meta']['omitted'] ?? [];
        $omittedCount = is_array($omitted) ? max(0, count($omitted) - (isset($omitted['detail']) ? 1 : 0)) : 0;
        $total = max(0, ((int)$count) - $omittedCount);
        if ($total === 0 && count($data) > 0) {
            $total = count($data);
        }

        $items = [];
        foreach ($data as $node) {
            $item = $this->parseItem($node, $included);
            if ($item !== null) {
                $items[] = $item;
            }
        }

        return $this->buildSearchResults($items, $total, $page, $perPage);
    }

    private function parseItem(array $node, array $included): ?array
    {
        $uuid = $node['id'] ?? null;
        if (!$uuid) {
            return null;
        }
        $attr = $node['attributes'] ?? [];
        $rels = $node['relationships'] ?? [];

        $title = $attr['field_full_title']
            ?? $attr['title']
            ?? ($GLOBALS['langUntitled'] ?? 'Untitled');

        $descField = $this->descriptionField();
        $description = null;
        if ($descField !== null && isset($attr[$descField])) {
            $val = $attr[$descField];
            if (is_string($val)) {
                $description = $val;
            } elseif (is_array($val) && isset($val['value']) && is_string($val['value'])) {
                $description = $val['value'];
            }
        }

        $pid = is_string($attr['field_pid'] ?? null) ? $attr['field_pid'] : null;
        $url = $this->buildItemUrl((string)$uuid, $pid, $attr);

        $resourceType = $this->resolveResourceType($rels, $included);
        $thumbnail = $this->resolveThumbnail($rels, $included);

        return $this->buildResultItem(
            (string)$uuid,
            (string)$title,
            $description,
            $url,
            $resourceType,
            $thumbnail,
            [
                'pid' => $pid,
                'model' => $this->modelNameFromRels($rels),
                'langcode' => $attr['langcode'] ?? null,
                'created' => $attr['created'] ?? null,
                'changed' => $attr['changed'] ?? null,
                'moderation_state' => $attr['moderation_state'] ?? null,
                'field_accessibility' => $attr['field_accessibility'] ?? null,
            ]
        );
    }

    private function modelNameFromRels(array $rels): ?string
    {
        $modelId = $rels['field_model']['data']['id'] ?? null;
        if (!$modelId) {
            return null;
        }
        return $this->termCache[$modelId] ?? null;
    }

    private function resolveResourceType(array $rels, array $included): string
    {
        $modelName = $this->modelNameFromRels($rels);
        if ($modelName === null) {
            // Try to look up the model node directly through included
            $modelId = $rels['field_model']['data']['id'] ?? null;
            if ($modelId) {
                foreach ($included as $key => $res) {
                    if ($res['id'] === $modelId
                        && isset($res['type'])
                        && strpos($res['type'], 'taxonomy_term') === 0
                        && isset($res['attributes']['name'])) {
                        $modelName = (string)$res['attributes']['name'];
                        $this->termCache[$modelId] = $modelName;
                        break;
                    }
                }
            }
        }
        if ($modelName === null) {
            return 'document';
        }
        $map = [
            'image' => 'image',
            'photograph' => 'image',
            'document' => 'document',
            'digital document' => 'document',
            'publication' => 'document',
            'page' => 'document',
            'paged content' => 'document',
            'binary' => 'document',
            'video' => 'video',
            'audio' => 'audio',
            'collection' => 'learning_object',
            'compound object' => 'learning_object',
        ];
        $key = strtolower(trim($modelName));
        if (isset($map[$key])) {
            return $map[$key];
        }
        error_log("IslandoraRepository: unmapped field_model term: $modelName");
        return 'document';
    }

    private function resolveThumbnail(array $rels, array $included): ?string
    {
        $mediaId = $rels['field_representative_image']['data']['id'] ?? null;
        if (!$mediaId) {
            return null;
        }
        // representative image is a media--image; find it in included
        $mediaResource = null;
        foreach ($included as $res) {
            if (($res['id'] ?? null) === $mediaId
                && isset($res['type'])
                && strpos($res['type'], 'media--') === 0) {
                $mediaResource = $res;
                break;
            }
        }
        if (!$mediaResource) {
            return null;
        }
        // The media references a file--file via field_media_image
        $fileId = $mediaResource['relationships']['field_media_image']['data']['id'] ?? null;
        if (!$fileId) {
            return null;
        }
        foreach ($included as $res) {
            if (($res['id'] ?? null) === $fileId
                && isset($res['type'])
                && strpos($res['type'], 'file--') === 0) {
                $u = $res['attributes']['uri']['url'] ?? $res['attributes']['uri']['value'] ?? null;
                if (is_string($u) && $u !== '') {
                    return $this->absoluteUrl($u);
                }
            }
        }
        return null;
    }

    private function absoluteUrl(string $u): string
    {
        if (preg_match('#^https?://#i', $u)) {
            return $u;
        }
        return rtrim($this->baseUrl, '/') . '/' . ltrim($u, '/');
    }

    private function buildItemUrl(string $uuid, ?string $pid, array $attr = []): string
    {
        $pattern = $this->urlPattern();
        if ($pattern === null) {
            // Default: prefer the JSON:API path alias for a human-readable URL,
            // fall back to /{lang}/node/{uuid} when no alias is exposed.
            $alias = $attr['path']['alias'] ?? null;
            if (is_string($alias) && $alias !== '') {
                return rtrim($this->baseUrl, '/') . '/' . $this->langCode() . '/' . ltrim($alias, '/');
            }
            $pattern = self::DEFAULT_URL_PATTERN;
        }
        $rendered = strtr($pattern, [
            '{base}' => rtrim($this->baseUrl, '/'),
            '{lang}' => $this->langCode(),
            '{uuid}' => $uuid,
            '{pid}' => $pid ?? '',
        ]);
        if (!preg_match('#^https?://#i', $rendered)) {
            $rendered = rtrim($this->baseUrl, '/') . '/' . ltrim($rendered, '/');
        }
        return $rendered;
    }
}
