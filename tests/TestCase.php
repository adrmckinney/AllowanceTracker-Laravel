<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Notification as FacadesNotification;
use Tests\Helpers\WithUserHelpers;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication, WithUserHelpers;

    protected function setUp(): void
    {
        parent::setUp();

        FacadesNotification::fake();
    }
}
