<?php

namespace App\Providers;

use App\Repositories\JsonTicketRepository;
use App\Repositories\TicketRepositoryInterface;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Inversion de dependencias: el controlador pide la interfaz y aqui
        // se decide que implementacion se usa. Cambiar el mock (JSON) por
        // Eloquent/MySQL en produccion es cambiar SOLO esta linea.
        $this->app->bind(TicketRepositoryInterface::class, fn () => new JsonTicketRepository(
            database_path('data/tickets.json')
        ));
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
