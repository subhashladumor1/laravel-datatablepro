<?php

declare(strict_types=1);

namespace SubhashLadumor1\DataTablePro\Tests\Unit;

use Orchestra\Testbench\TestCase;
use SubhashLadumor1\DataTablePro\DataTable\Builder;
use SubhashLadumor1\DataTablePro\DataTable\Column;
use SubhashLadumor1\DataTablePro\DataTable\Filter;
use SubhashLadumor1\DataTablePro\Providers\DataTableServiceProvider;

class BuilderTest extends TestCase
{
    protected function getPackageProviders($app): array
    {
        return [DataTableServiceProvider::class];
    }

    /** @test */
    public function it_can_instantiate_builder(): void
    {
        $builder = new Builder();
        $this->assertInstanceOf(Builder::class, $builder);
    }

    /** @test */
    public function it_can_add_columns(): void
    {
        $builder = Builder::make()
            ->columns([
                Column::make('name', 'Name'),
                Column::make('email', 'Email'),
            ]);

        $this->assertCount(2, $builder->getColumns()->all());
    }

    /** @test */
    public function it_can_add_filters(): void
    {
        $builder = Builder::make()
            ->filters([
                Filter::text('name', 'Name'),
                Filter::select('status', 'Status', ['active' => 'Active', 'inactive' => 'Inactive']),
            ]);

        $this->assertCount(2, $builder->getFilters());
    }

    /** @test */
    public function it_can_set_page_length(): void
    {
        $builder = Builder::make()->pageLength(25);
        $this->assertEquals(25, $builder->getPageLength());
    }

    /** @test */
    public function it_can_enable_responsive(): void
    {
        $builder = Builder::make()->responsive();
        $this->assertTrue($builder->isResponsive());
    }

    /** @test */
    public function it_can_enable_virtual_scroll(): void
    {
        $builder = Builder::make()->virtualScroll();
        $this->assertTrue($builder->isVirtualScroll());
    }

    /** @test */
    public function it_can_enable_realtime(): void
    {
        $builder = Builder::make()->realtime();
        $this->assertTrue($builder->isRealtime());
    }

    /** @test */
    public function it_whitelists_columns_automatically(): void
    {
        $builder = Builder::make()
            ->columns([
                Column::make('name', 'Name'),
                Column::make('email', 'Email'),
            ]);

        $this->assertContains('name', $builder->getWhitelistedColumns());
        $this->assertContains('email', $builder->getWhitelistedColumns());
    }

    /** @test */
    public function it_whitelists_relationships_automatically(): void
    {
        $builder = Builder::make()
            ->columns([
                Column::make('name', 'Name')->relationship('user'),
            ])
            ->with(['posts', 'comments']);

        $this->assertContains('user', $builder->getWhitelistedRelationships());
        $this->assertContains('posts', $builder->getWhitelistedRelationships());
        $this->assertContains('comments', $builder->getWhitelistedRelationships());
    }
}
