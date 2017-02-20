<?php

namespace Nord\Lumen\Cors\Tests;

use Laravel\Lumen\Application;

class MockApplication extends Application
{
    /**
     * @inheritdoc
     */
    public function __construct($basePath = null)
    {
        parent::__construct(realpath(__DIR__ . '/../'));
    }
}
