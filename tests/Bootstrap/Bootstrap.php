<?php

$loader = require __DIR__.'/../../vendor/autoload.php';

use Culpa\Tests\Bootstrap\CulpaTest;
use Culpa\Tests\Bootstrap\AppFactory;

CulpaTest::$app = AppFactory::create();
