<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $sessionsPath = sys_get_temp_dir().'/larakube-sessions-'.getmypid().'-'.uniqid();
        config()->set('larakube.sessions_path', $sessionsPath);
    }

    protected function tearDown(): void
    {
        $sessionsPath = config('larakube.sessions_path');

        if (is_dir($sessionsPath)) {
            $files = glob($sessionsPath.'/*.json');
            if (is_array($files)) {
                array_map(unlink(...), $files);
            }
            rmdir($sessionsPath);
        }

        parent::tearDown();
    }
}
