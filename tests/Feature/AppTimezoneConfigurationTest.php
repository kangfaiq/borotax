<?php

namespace Tests\Feature;

use Carbon\Carbon;
use Tests\TestCase;

class AppTimezoneConfigurationTest extends TestCase
{
    public function test_application_uses_asia_jakarta_timezone_by_default(): void
    {
        $this->assertSame('Asia/Jakarta', config('app.timezone'));
        $this->assertSame('Asia/Jakarta', Carbon::now()->timezone->getName());
    }
}