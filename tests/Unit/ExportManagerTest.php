<?php

declare(strict_types=1);

namespace SubhashLadumor1\DataTablePro\Tests\Unit;

use Orchestra\Testbench\TestCase;
use SubhashLadumor1\DataTablePro\DataTable\ExportManager;
use SubhashLadumor1\DataTablePro\Providers\DataTableServiceProvider;

class ExportManagerTest extends TestCase
{
    protected function getPackageProviders($app): array
    {
        return [DataTableServiceProvider::class];
    }

    /** @test */
    public function it_has_default_exporters(): void
    {
        $manager = new ExportManager($this->app);
        $formats = $manager->getAvailableFormats();

        $this->assertContains('csv', $formats);
        $this->assertContains('xlsx', $formats);
        $this->assertContains('pdf', $formats);
        $this->assertContains('image', $formats);
    }

    /** @test */
    public function it_can_register_custom_exporter(): void
    {
        $manager = new ExportManager($this->app);
        $manager->registerExporter('custom', \stdClass::class);

        $this->assertContains('custom', $manager->getAvailableFormats());
    }

    /** @test */
    public function it_throws_exception_for_unsupported_format(): void
    {
        $this->expectException(\SubhashLadumor1\DataTablePro\Exceptions\DataTableException::class);

        $manager = new ExportManager($this->app);
        $builder = \Mockery::mock(\SubhashLadumor1\DataTablePro\DataTable\Builder::class);
        $request = new \Illuminate\Http\Request();

        $manager->export($builder, 'unsupported', $request);
    }
}
