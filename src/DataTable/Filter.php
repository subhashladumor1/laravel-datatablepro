<?php

declare(strict_types=1);

namespace SubhashLadumor1\DataTablePro\DataTable;

/**
 * Filter
 *
 * Represents a filter configuration for the DataTable.
 */
class Filter
{
    protected string $key;
    protected string $type;
    protected string $label;
    protected mixed $default = null;
    protected array $options = [];
    /** @var callable|null */
    protected $callback = null;
    protected array $attributes = [];

    public function __construct(string $key, string $type, string $label)
    {
        $this->key = $key;
        $this->type = $type;
        $this->label = $label;
    }

    public static function make(string $key, string $type, string $label): self
    {
        return new static($key, $type, $label);
    }

    public static function text(string $key, string $label): self
    {
        return new static($key, 'text', $label);
    }

    public static function select(string $key, string $label, array $options = []): self
    {
        return (new static($key, 'select', $label))->options($options);
    }

    public static function dateRange(string $key, string $label): self
    {
        return new static($key, 'date-range', $label);
    }

    public static function numericRange(string $key, string $label): self
    {
        return new static($key, 'numeric-range', $label);
    }

    public function default(mixed $default): self
    {
        $this->default = $default;
        return $this;
    }

    public function options(array $options): self
    {
        $this->options = $options;
        return $this;
    }

    public function callback(callable $callback): self
    {
        $this->callback = $callback;
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

    public function getType(): string
    {
        return $this->type;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getDefault(): mixed
    {
        return $this->default;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function getCallback(): ?callable
    {
        return $this->callback;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function toArray(): array
    {
        return [
            'key' => $this->key,
            'type' => $this->type,
            'label' => $this->label,
            'default' => $this->default,
            'options' => $this->options,
            'attributes' => $this->attributes,
        ];
    }
}
