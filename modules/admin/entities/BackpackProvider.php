<?php

/**
 * BackpackProvider Entity
 * 
 * Represents an OpenBadges backpack provider with immutable data
 */
readonly class BackpackProvider
{
    public function __construct(
        public ?int $id,
        public string $name,
        public string $api_url,
        public string $ob_version,
        public bool $active,
        public ?string $created_at,
        public ?string $updated_at
    ) {}

    /**
     * Create a new BackpackProvider instance
     */
    public static function create(
        string $name,
        string $api_url,
        string $ob_version,
        bool $active = true
    ): self {
        return new self(
            id: null,
            name: $name,
            api_url: $api_url,
            ob_version: $ob_version,
            active: $active,
            created_at: null,
            updated_at: null
        );
    }

    /**
     * Create from database row
     */
    public static function fromRow(object $row): self
    {
        return new self(
            id: (int) $row->id,
            name: $row->name,
            api_url: $row->api_url,
            ob_version: $row->ob_version ?? 'OpenBadge v2.0',
            active: (bool) $row->active,
            created_at: $row->created_at,
            updated_at: $row->updated_at
        );
    }

    /**
     * Update provider with new data
     */
    public function update(
        ?string $name = null,
        ?string $api_url = null,
        ?string $ob_version = null,
        ?bool $active = null
    ): self {
        return new self(
            id: $this->id,
            name: $name ?? $this->name,
            api_url: $api_url ?? $this->api_url,
            ob_version: $ob_version ?? $this->ob_version,
            active: $active ?? $this->active,
            created_at: $this->created_at,
            updated_at: date('Y-m-d H:i:s')
        );
    }

    /**
     * Convert to array for database operations
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'api_url' => $this->api_url,
            'ob_version' => $this->ob_version,
            'active' => $this->active ? 1 : 0
        ];
    }

    /**
     * Validate provider data
     */
    public function validate(): array
    {
        $errors = [];

        if (empty(trim($this->name))) {
            $errors[] = 'Provider name is required.';
        }

        if (empty(trim($this->api_url)) || !filter_var($this->api_url, FILTER_VALIDATE_URL)) {
            $errors[] = 'A valid API URL is required.';
        }

        if (empty(trim($this->ob_version))) {
            $errors[] = 'OpenBadge version is required.';
        }

        return $errors;
    }

    /**
     * Check if provider is enabled
     */
    public function isEnabled(): bool
    {
        return $this->active;
    }
} 