<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Auth;
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     * * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     * * @return void
     */
    public function boot()
    {
        Paginator::useBootstrapFive();

        // @role('admin', 'seguridad') ... @endrole
        // Muestra el bloque solo si el usuario autenticado tiene alguno de los roles indicados.
        Blade::directive('role', function ($expression) {
            $roles = array_map(
                fn($r) => trim(trim($r), "\"'"),
                explode(',', $expression)
            );
            $json = json_encode($roles);
            return "<?php if(Auth::check() && in_array(Auth::user()->role, {$json})): ?>";
        });

        Blade::directive('endrole', function () {
            return '<?php endif; ?>';
        });
    }
}
