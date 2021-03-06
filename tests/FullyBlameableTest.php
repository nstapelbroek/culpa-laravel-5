<?php

namespace Culpa\Tests;

use Culpa\Tests\Bootstrap\CulpaTest;
use Culpa\Tests\Models\FullyBlameableModel;
use Illuminate\Support\Facades\Auth;

class FullyBlameableTest extends CulpaTest
{
    private $model;

    public function setUp()
    {
        parent::setUp();
        $this->model = new FullyBlameableModel();
    }

    public function testBlameables()
    {
        $this->assertTrue($this->model->isBlameable('created'), 'Created should be blameable');
        $this->assertTrue($this->model->isBlameable('updated'), 'Updated should be blameable');
        $this->assertTrue($this->model->isBlameable('deleted'), 'Deleted should be blameable');
    }

    public function testCreate()
    {
        $this->model->title = 'Hello, world!';
        $this->assertTrue($this->model->save());

        $this->model = $this->model->fresh();

        // Check datetimes are being set properly for sanity's sake
        $this->assertNotNull($this->model->created_at);
        $this->assertEquals($this->model->created_at, $this->model->updated_at);
        $this->assertNull($this->model->deleted_at);

        // Check id references are set
        $this->assertEquals(1, $this->model->created_by);
        $this->assertEquals(1, $this->model->updated_by);
        $this->assertNull(null, $this->model->deleted_by);

        $this->assertEquals(Auth::user()->name, $this->model->creator->name);
        $this->assertEquals(Auth::user()->name, $this->model->updater->name);
    }

    public function testUpdate()
    {
        $this->model->title = 'Hello, world!';
        $this->assertTrue($this->model->save());

        // Make sure updated_at > created_at by at least 1 second
        usleep(1.5 * 1000000); // 1.5 seconds

        $this->model = $this->model->fresh();
        $this->model->title = 'Test Post, please ignore';
        $this->assertTrue($this->model->save());

        // Check datetimes are being set properly for sanity's sake
        $this->assertNotNull($this->model->created_at);
        $this->assertGreaterThan($this->model->created_at, $this->model->updated_at);
        $this->assertNull($this->model->deleted_at);

        $this->assertEquals(1, $this->model->created_by);
        $this->assertEquals(1, $this->model->updated_by);
        $this->assertEquals(null, $this->model->deleted_by);

        $this->assertEquals(Auth::user()->name, $this->model->creator->name);
        $this->assertEquals(Auth::user()->name, $this->model->updater->name);
    }

    public function testDelete()
    {
        $this->model->title = 'Hello, world!';
        $this->assertTrue($this->model->save());
        usleep(1.5 * 1000000); // 1.5 seconds
        $this->assertTrue($this->model->delete());

        // Reload the model
        $this->model = $this->model->withTrashed()->find($this->model->id);

        // Check datetimes are being set properly for sanity's sake
        $this->assertNotNull($this->model->created_at);
        $this->assertNotNull($this->model->updated_at);
        $this->assertNotNull($this->model->deleted_at);
        $this->assertGreaterThan($this->model->created_at, $this->model->deleted_at);

        $this->assertEquals(1, $this->model->created_by);
        $this->assertEquals(1, $this->model->updated_by);
        $this->assertEquals(1, $this->model->deleted_by);

        $this->assertEquals(Auth::user()->name, $this->model->creator->name);
        $this->assertEquals(Auth::user()->name, $this->model->updater->name);
        $this->assertEquals(Auth::user()->name, $this->model->eraser->name);
    }
}
