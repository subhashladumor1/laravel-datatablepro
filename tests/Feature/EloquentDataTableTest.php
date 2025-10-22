<?php

declare(strict_types=1);

namespace SubhashLadumor\DataTablePro\Tests\Feature;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\TestCase;
use SubhashLadumor\DataTablePro\DataTable\Builder;
use SubhashLadumor\DataTablePro\DataTable\Column;
use SubhashLadumor\DataTablePro\Providers\DataTableServiceProvider;

class EloquentDataTableTest extends TestCase
{
    use RefreshDatabase;

    protected function getPackageProviders($app): array
    {
        return [DataTableServiceProvider::class];
    }

    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('status');
            $table->timestamps();
        });

        TestUser::create(['name' => 'John Doe', 'email' => 'john@example.com', 'status' => 'active']);
        TestUser::create(['name' => 'Jane Smith', 'email' => 'jane@example.com', 'status' => 'inactive']);
        TestUser::create(['name' => 'Bob Johnson', 'email' => 'bob@example.com', 'status' => 'active']);
    }

    /** @test */
    public function it_can_create_datatable_from_eloquent_builder(): void
    {
        $datatable = Builder::make()
            ->eloquent(TestUser::query())
            ->columns([
                Column::make('name', 'Name'),
                Column::make('email', 'Email'),
                Column::make('status', 'Status'),
            ]);

        $this->assertInstanceOf(Builder::class, $datatable);
        $this->assertCount(3, $datatable->getColumns()->all());
    }

    /** @test */
    public function it_can_fetch_paginated_data(): void
    {
        $datatable = Builder::make()
            ->eloquent(TestUser::query())
            ->columns([
                Column::make('name', 'Name'),
                Column::make('email', 'Email'),
            ])
            ->pageLength(2);

        $request = new \Illuminate\Http\Request([
            'draw' => 1,
            'start' => 0,
            'length' => 2,
        ]);

        $result = $datatable->toArray($request);

        $this->assertEquals(1, $result['draw']);
        $this->assertEquals(3, $result['recordsTotal']);
        $this->assertEquals(3, $result['recordsFiltered']);
        $this->assertCount(2, $result['data']);
    }

    /** @test */
    public function it_can_search_data(): void
    {
        $datatable = Builder::make()
            ->eloquent(TestUser::query())
            ->columns([
                Column::make('name', 'Name')->searchable(),
                Column::make('email', 'Email')->searchable(),
            ]);

        $request = new \Illuminate\Http\Request([
            'draw' => 1,
            'start' => 0,
            'length' => 10,
            'search' => ['value' => 'john'],
        ]);

        $result = $datatable->toArray($request);

        $this->assertEquals(2, $result['recordsFiltered']); // John Doe and Bob Johnson
    }

    /** @test */
    public function it_can_order_data(): void
    {
        $datatable = Builder::make()
            ->eloquent(TestUser::query())
            ->columns([
                Column::make('name', 'Name')->orderable(),
                Column::make('email', 'Email'),
            ]);

        $request = new \Illuminate\Http\Request([
            'draw' => 1,
            'start' => 0,
            'length' => 10,
            'order' => [['column' => 0, 'dir' => 'desc']],
        ]);

        $result = $datatable->toArray($request);

        $this->assertEquals('John Doe', $result['data'][0]['name']);
    }

    /** @test */
    public function it_validates_whitelisted_columns(): void
    {
        $this->expectException(\SubhashLadumor\DataTablePro\Exceptions\DataTableException::class);

        $datatable = Builder::make()
            ->eloquent(TestUser::query())
            ->columns([
                Column::make('name', 'Name'),
            ]);

        $request = new \Illuminate\Http\Request([
            'draw' => 1,
            'start' => 0,
            'length' => 10,
            'order' => [['column' => 1, 'dir' => 'asc']], // Column 1 doesn't exist
        ]);

        $datatable->toArray($request);
    }
}

class TestUser extends Model
{
    protected $table = 'users';
    protected $fillable = ['name', 'email', 'status'];
}
