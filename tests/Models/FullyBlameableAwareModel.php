<?php

namespace Culpa\Tests\Models;

use Culpa\Traits\Blameable;
use Culpa\Traits\CreatedBy;
use Culpa\Traits\DeletedBy;
use Culpa\Traits\UpdatedBy;
use Culpa\Contracts\EraserAware;
use Culpa\Contracts\CreatorAware;
use Culpa\Contracts\UpdaterAware;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * A model with all 3 fields, with the default values and the aware interfaces.
 */
class FullyBlameableAwareModel extends Model implements CreatorAware, UpdaterAware, EraserAware
{
    use CreatedBy, UpdatedBy, DeletedBy, Blameable, SoftDeletes;
    protected $table = 'posts';
    protected $blameable = ['created', 'updated', 'deleted'];
}
