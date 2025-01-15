<?php

namespace App\Providers;

use App\Models\PetShop\PetShopToken;
use App\Models\RedemptionToken;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Http;
use Laravel\Sanctum\Sanctum;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        if ($this->app->environment('local')) {
            $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
            $this->app->register(TelescopeServiceProvider::class);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // define petapi macro to serve a single point of 
        Http::macro('petapi', function () {
            return Http::withHeaders([
                'SECURITYTOKEN' => config('app.petapikey'),
                'RESELLERID' => config('app.petid'),
            ])->baseUrl(config('app.peturl'))->timeout(300);
        });
        Sanctum::usePersonalAccessTokenModel(PetShopToken::class);
        Sanctum::usePersonalAccessTokenModel(RedemptionToken::class);

        $migrationsPath = database_path('migrations');
        $directories = glob($migrationsPath . '/*', GLOB_ONLYDIR);
        $paths = array_merge([$migrationsPath], $directories);

        $this->loadMigrationsFrom($paths);

        Http::macro('googleplaceapi', function () {
            return Http::withHeaders([
                'X-Goog-Api-Key' => config('app.google_place_api'),
                'X-Goog-FieldMask' => 'id,displayName,location,viewport,rating,googleMapsUri',
            ])->baseUrl(config('app.google_place_url'));
        });
    }
}
