<?php

declare(strict_types=1);

namespace SubhashLadumor\DataTablePro\Tests\Feature;

use Orchestra\Testbench\TestCase;
use SubhashLadumor\DataTablePro\DataTable\Builder;
use SubhashLadumor\DataTablePro\DataTable\Column;
use SubhashLadumor\DataTablePro\Providers\DataTableServiceProvider;

class ExportTest extends TestCase
{
    protected function getPackageProviders($app): array
    {
        return [DataTableServiceProvider::class];
    }

    /** @test */
    public function it_can_enable_export(): void
    {
        $datatable = Builder::make()
            ->collection(collect([
                ['name' => 'John', 'email' => 'john@example.com'],
                ['name' => 'Jane', 'email' => 'jane@example.com'],
            ]))
            ->columns([
                Column::make('name', 'Name'),
                Column::make('email', 'Email'),
            ])
            ->exportable();

        $this->assertTrue($datatable->isExportable());
    }

    /** @test */
    public function it_throws_exception_when_export_disabled(): void
    {
        $this->expectException(\SubhashLadumor\DataTablePro\Exceptions\DataTableException::class);

        $datatable = Builder::make()
            ->collection(collect([]))
            ->columns([])
            ->exportable(false);

        $request = new \Illuminate\Http\Request();
        $datatable->toExport('csv', $request);
    }

    /** @test */
    public function it_can_export_to_csv(): void
    {
        $datatable = Builder::make()
            ->collection(collect([
                ['name' => 'John', 'email' => 'john@example.com'],
                ['name' => 'Jane', 'email' => 'jane@example.com'],
            ]))
            ->columns([
                Column::make('name', 'Name')->exportable(),
                Column::make('email', 'Email')->exportable(),
            ])
            ->exportable();

        $request = new \Illuminate\Http\Request();
        $response = $datatable->toExport('csv', $request);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString('text/csv', $response->headers->get('Content-Type'));
    }

    /** @test */
    public function it_filters_non_exportable_columns(): void
    {
        $datatable = Builder::make()
            ->collection(collect([
                ['name' => 'John', 'email' => 'john@example.com', 'password' => 'secret'],
            ]))
            ->columns([
                Column::make('name', 'Name')->exportable(),
                Column::make('email', 'Email')->exportable(),
                Column::make('password', 'Password')->exportable(false),
            ])
            ->exportable();

        $exportableColumns = $datatable->getColumns()->exportable();
        
        $this->assertCount(2, $exportableColumns);
    }
}
