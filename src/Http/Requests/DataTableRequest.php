<?php

declare(strict_types=1);

namespace SubhashLadumor1\DataTablePro\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * DataTableRequest
 *
 * Validates and sanitizes DataTable AJAX requests.
 */
class DataTableRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'draw' => 'nullable|integer|min:1',
            'start' => 'nullable|integer|min:0',
            'length' => 'nullable|integer|min:1|max:' . config('datatable.max_page_length', 100),
            'search.value' => 'nullable|string|max:255',
            'order' => 'nullable|array',
            'order.*.column' => 'required_with:order|integer|min:0',
            'order.*.dir' => 'required_with:order|in:asc,desc',
            'filters' => 'nullable|array',
        ];
    }

    /**
     * Get validated draw number.
     */
    public function getDraw(): int
    {
        return (int) $this->validated()['draw'] ?? 1;
    }

    /**
     * Get validated start offset.
     */
    public function getStart(): int
    {
        return (int) $this->validated()['start'] ?? 0;
    }

    /**
     * Get validated page length.
     */
    public function getLength(): int
    {
        return (int) $this->validated()['length'] ?? config('datatable.page_length', 10);
    }

    /**
     * Get validated search value.
     */
    public function getSearch(): string
    {
        return $this->validated()['search']['value'] ?? '';
    }

    /**
     * Get validated order array.
     *
     * @return array<int, array{column: int, dir: string}>
     */
    public function getOrder(): array
    {
        return $this->validated()['order'] ?? [];
    }

    /**
     * Get validated filters array.
     *
     * @return array<string, mixed>
     */
    public function getFilters(): array
    {
        return $this->validated()['filters'] ?? [];
    }
}
