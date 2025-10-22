<?php

declare(strict_types=1);

namespace SubhashLadumor\DataTablePro\Tests\Feature;

use Illuminate\Support\Facades\View;
use Orchestra\Testbench\TestCase;
use SubhashLadumor\DataTablePro\Providers\DataTableServiceProvider;

class BladeComponentTest extends TestCase
{
    protected function getPackageProviders($app): array
    {
        return [DataTableServiceProvider::class];
    }

    /** @test */
    public function it_can_render_blade_component(): void
    {
        $this->assertTrue(View::exists('datatable::components.table'));
    }

    /** @test */
    public function it_registers_blade_component(): void
    {
        $this->assertTrue(array_key_exists(
            'dtable-table',
            app('blade.compiler')->getClassComponentAliases()
        ) || class_exists(\SubhashLadumor\DataTablePro\View\Components\Table::class));
    }
}
