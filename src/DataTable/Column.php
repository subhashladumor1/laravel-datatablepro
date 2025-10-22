<?php

declare(strict_types=1);

namespace SubhashLadumor\DataTablePro\DataTable;

/**
 * Column
 *
 * Represents a single column configuration in the DataTable.
 */
class Column
{
    protected string $key;
    protected string $label;
    protected bool $searchable = false;
    protected bool $orderable = false;
    protected ?string $relationship = null;
    protected mixed $render = null;
    protected bool $raw = false;
    protected mixed $default = null;
    /** @var callable|null */
    protected $formatCallback = null;
    protected bool $visible = true;
    protected bool $exportable = true;
    protected ?string $clientRenderer = null;
    protected array $attributes = [];

    public function __construct(string $key, string $label)
    {
        $this->key = $key;
        $this->label = $label;
    }

    public static function make(string $key, string $label): self
    {
        return new static($key, $label);
    }

    public function searchable(bool $searchable = true): self
    {
        $this->searchable = $searchable;
        return $this;
    }

    public function orderable(bool $orderable = true): self
    {
        $this->orderable = $orderable;
        return $this;
    }

    public function relationship(string $relationship): self
    {
        $this->relationship = $relationship;
        return $this;
    }

    public function render(callable|string $render): self
    {
        if (is_string($render)) {
            $this->clientRenderer = $render;
        } else {
            $this->render = $render;
        }
        return $this;
    }

    public function raw(bool $raw = true): self
    {
        $this->raw = $raw;
        return $this;
    }

    public function default(mixed $default): self
    {
        $this->default = $default;
        return $this;
    }

    public function format(callable $callback): self
    {
        $this->formatCallback = $callback;
        return $this;
    }

    public function visible(bool $visible = true): self
    {
        $this->visible = $visible;
        return $this;
    }

    public function exportable(bool $exportable = true): self
    {
        $this->exportable = $exportable;
        return $this;
    }

    public function attributes(array $attributes): self
    {
        $this->attributes = $attributes;
        return $this;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function isSearchable(): bool
    {
        return $this->searchable;
    }

    public function isOrderable(): bool
    {
        return $this->orderable;
    }

    public function getRelationship(): ?string
    {
        return $this->relationship;
    }

    public function getRender(): mixed
    {
        return $this->render;
    }

    public function isRaw(): bool
    {
        return $this->raw;
    }

    public function getDefault(): mixed
    {
        return $this->default;
    }

    public function getFormatCallback(): ?callable
    {
        return $this->formatCallback;
    }

    public function isVisible(): bool
    {
        return $this->visible;
    }

    public function isExportable(): bool
    {
        return $this->exportable;
    }

    public function getClientRenderer(): ?string
    {
        return $this->clientRenderer;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function toArray(): array
    {
        return [
            'key' => $this->key,
            'label' => $this->label,
            'searchable' => $this->searchable,
            'orderable' => $this->orderable,
            'relationship' => $this->relationship,
            'visible' => $this->visible,
            'exportable' => $this->exportable,
            'clientRenderer' => $this->clientRenderer,
            'attributes' => $this->attributes,
        ];
    }
}
