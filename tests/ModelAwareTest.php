<?php

namespace Culpa\Tests;

use Culpa\Contracts\EraserAware;
use Culpa\Contracts\CreatorAware;
use Culpa\Contracts\UpdaterAware;
use Culpa\Tests\Models\FullyBlameableAwareModel;

class ModelAwareTest extends FullyBlameableTest
{
    private $model;

    public function setUp()
    {
        parent::setUp();
        $this->model = new FullyBlameableAwareModel();
    }

    public function testHasInterfaces()
    {
        $this->assertTrue($this->model instanceof CreatorAware);
        $this->assertTrue($this->model instanceof UpdaterAware);
        $this->assertTrue($this->model instanceof EraserAware);
    }
}
