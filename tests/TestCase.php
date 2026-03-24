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

    protected function setUp(): void
    {
        parent::setUp();

        // Re-load routes at the start of each test to ensure they're available
        // This works around RefreshDatabase clearing routes
        $this->ensureRoutesLoaded();
    }

    private function ensureRoutesLoaded(): void
    {
        // Make sure routes are loaded by making a dummy request
        try {
            $this->get('/');
        } catch (\Exception) {
            // Ignore any exceptions from the dummy request
        }
    }
}
