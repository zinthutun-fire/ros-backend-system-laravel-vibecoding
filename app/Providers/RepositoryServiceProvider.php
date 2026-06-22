<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            \App\Repositories\Contracts\UserRepositoryInterface::class,
            \App\Repositories\UserRepository::class
        );
        $this->app->bind(
            \App\Repositories\Contracts\TableRepositoryInterface::class,
            \App\Repositories\TableRepository::class
        );
        $this->app->bind(
            \App\Repositories\Contracts\TableAreaRepositoryInterface::class,
            \App\Repositories\TableAreaRepository::class
        );
        $this->app->bind(
            \App\Repositories\Contracts\KitchenRepositoryInterface::class,
            \App\Repositories\KitchenRepository::class
        );
        $this->app->bind(
            \App\Repositories\Contracts\CategoryRepositoryInterface::class,
            \App\Repositories\CategoryRepository::class
        );
        $this->app->bind(
            \App\Repositories\Contracts\MenuItemRepositoryInterface::class,
            \App\Repositories\MenuItemRepository::class
        );
        $this->app->bind(
            \App\Repositories\Contracts\MenuItemModifierRepositoryInterface::class,
            \App\Repositories\MenuItemModifierRepository::class
        );
        $this->app->bind(
            \App\Repositories\Contracts\OrderRepositoryInterface::class,
            \App\Repositories\OrderRepository::class
        );
        $this->app->bind(
            \App\Repositories\Contracts\OrderItemRepositoryInterface::class,
            \App\Repositories\OrderItemRepository::class
        );
        $this->app->bind(
            \App\Repositories\Contracts\PaymentRepositoryInterface::class,
            \App\Repositories\PaymentRepository::class
        );
        $this->app->bind(
            \App\Repositories\Contracts\TaxRateRepositoryInterface::class,
            \App\Repositories\TaxRateRepository::class
        );
        $this->app->bind(
            \App\Repositories\Contracts\TableTransferRepositoryInterface::class,
            \App\Repositories\TableTransferRepository::class
        );
        $this->app->bind(
            \App\Repositories\Contracts\TableMergeRepositoryInterface::class,
            \App\Repositories\TableMergeRepository::class
        );
    }

    public function boot(): void
    {
        //
    }
}
