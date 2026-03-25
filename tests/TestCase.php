<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('larakube.session_path', sys_get_temp_dir().'/larakube-'.getmypid().'-'.uniqid().'.json');
    }

    protected function tearDown(): void
    {
        $path = config('larakube.session_path');

        if (file_exists($path)) {
            unlink($path);
        }

        parent::tearDown();
    }
}
