<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Schema;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (app()->environment('testing') && Schema::hasTable('sessions')) {
        return;
    }


            $this->artisan('migrate', ['--path' => 'database/migrations/tenant']);
    }}
