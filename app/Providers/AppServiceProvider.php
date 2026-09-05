<?php

namespace App\Providers;

use App\Application\Catalog\Ports\EventBusPort;
use App\Domain\Catalog\Repositories\CategoryRepositoryInterface;
use App\Domain\Catalog\Repositories\ProductRepositoryInterface;
use App\Infrastructure\Catalog\Adapters\LaravelEventBusAdapter;
use App\Infrastructure\Catalog\Repositories\EloquentCategoryRepository;
use App\Infrastructure\Catalog\Repositories\EloquentProductRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(ProductRepositoryInterface::class, EloquentProductRepository::class);
        $this->app->bind(CategoryRepositoryInterface::class, EloquentCategoryRepository::class);
        $this->app->bind(EventBusPort::class, LaravelEventBusAdapter::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::shouldBeStrict(! $this->app->isProduction());

        // Verhindert destructive Befehle wie migrate:fresh in Produktionsumgebungen
        DB::prohibitDestructiveCommands($this->app->isProduction());
    }
}
