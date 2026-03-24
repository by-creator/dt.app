<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Laravel\Fortify\Features;

abstract class TestCase extends BaseTestCase
{
    protected function skipUnlessFortifyFeature(string $feature, ?string $message = null): void
    {
        if (! Features::enabled($feature)) {
            $this->markTestSkipped($message ?? "Fortify feature [{$feature}] is not enabled.");
        }
    }

    protected function afterRefreshingDatabase()
    {
        parent::afterRefreshingDatabase();

        // Reload routes after database refresh to fix RefreshDatabase route caching issue
        // This hook is called after the database has been refreshed
        $this->reloadRoutes();
    }

    private function reloadRoutes(): void
    {
        $router = $this->app['router'];

        // Get the current routes to determine which are custom routes
        $allRoutes = $router->getRoutes()->getRoutes();

        // Clear all routes except those from Fortify (which should be preserved)
        // Fortify routes are registered first, so we can identify them by counting

        // Re-load the web routes using the router's built-in group mechanism
        $router->group(['middleware' => 'web'], function ($router) {
            require __DIR__.'/../routes/web.php';
        });
    }
}
