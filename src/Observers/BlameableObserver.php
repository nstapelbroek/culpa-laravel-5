<?php

/**
 * Blameable auditing support for Laravel's Eloquent ORM.
 *
 * @author Ross Masters <ross@rossmasters.com>
 * @copyright Ross Masters 2013
 * @license MIT
 */

namespace Culpa\Observers;

use Culpa\Contracts\CreatorAware;
use Culpa\Contracts\EraserAware;
use Culpa\Contracts\UpdaterAware;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use ReflectionClass;

class BlameableObserver
{
    /** @var array Mapping of events to fields */
    private $fields;

    /**
     * Creating event.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     */
    public function creating(Model $model)
    {
        $this->updateBlameables($model);
    }

    /**
     * Updating event.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     */
    public function updating(Model $model)
    {
        $this->updateBlameables($model);
    }

    /**
     * Deleting event.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     */
    public function deleting(Model $model)
    {
        $this->updateDeleteBlameable($model);
    }

    /**
     * Update the blameable fields.
     *
     * @param Model $model
     * @throws \Exception
     */
    protected function updateBlameables(Model $model)
    {
        $user = $this->getActiveUser();

        if (is_null($user)) {
            return; // Todo: might be wise to implement loggin here
        }

        // Set updated-by if it has not been touched on this model
        if ($this->isBlameable($model, 'updated') && ! $model->isDirty($this->getColumn($model, 'updated'))) {
            $this->setUpdatedBy($model, $user);
        }

        // Determine if we need to touch the created stamp
        if ($model->exists) {
            return;
        }

        // Set created-by if the model does not exist
        if ($this->isBlameable($model, 'created') && ! $model->isDirty($this->getColumn($model, 'created'))) {
            $this->setCreatedBy($model, $user);
        }
    }

    /**
     * Update the deletedBy blameable field.
     * @param Model $model
     * @throws \Exception
     */
    public function updateDeleteBlameable(Model $model)
    {
        $user = $this->getActiveUser();

        if (is_null($user)) {
            return;
        }

        // Set deleted-at if it has not been touched
        if ($this->isBlameable($model, 'deleted') && ! $model->isDirty($this->getColumn($model, 'deleted'))) {
            $this->setDeletedBy($model, $user);
            $model->save();
        }
    }

    /**
     * Get the active user.
     *
     * @return User
     * @throws \Exception
     */
    protected function getActiveUser()
    {
        if (! Config::has('culpa.users.active_user')) {
            return Auth::check() ? Auth::user() : null;
        }

        $fn = Config::get('culpa.users.active_user');
        if (! is_callable($fn)) {
            throw new \Exception('culpa.users.active_user should be a closure');
        }

        return $fn();
    }

    /**
     * Get the id of the active user.
     *
     * @return int User ID
     * @throws \Exception
     */
    protected function getActiveUserIdentifier()
    {
        return $this->getActiveUser()->id;
    }

    /**
     * Set the created-by field of the model.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param User $user
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    protected function setCreatedBy(Model $model, $user)
    {
        $model->{$this->getColumn($model, 'created')} = $user->id;

        if ($model instanceof CreatorAware) {
            $model->setRelation('creator', $user);
        }

        return $model;
    }

    /**
     * Set the updated-by field of the model.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param User $user
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    protected function setUpdatedBy(Model $model, $user)
    {
        $model->{$this->getColumn($model, 'updated')} = $user->id;

        if ($model instanceof UpdaterAware) {
            $model->setRelation('updater', $user);
        }

        return $model;
    }

    /**
     * Set the deleted-by field of the model.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param User $user
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    protected function setDeletedBy(Model $model, $user)
    {
        $model->{$this->getColumn($model, 'deleted')} = $user->id;

        if ($model instanceof EraserAware) {
            $model->setRelation('eraser', $user);
        }

        return $model;
    }

    /**
     * Get the created/updated/deleted-by column, or null if it is not used.
     *
     * @param string $event One of (created|updated|deleted)
     *
     * @return string|null
     */
    public function getColumn(Model $model, $event)
    {
        if (! array_key_exists($event, $this->getBlameableFields($model))) {
            return;
        }

        $fields = $this->getBlameableFields($model);

        return $fields[$event];
    }

    /**
     * Does the model use blameable fields for an event?
     *
     * @param string $event One of (created|updated|deleted), or omitted for any
     *
     * @return bool
     */
    public function isBlameable(Model $model, $event = null)
    {
        return $event ?
            array_key_exists($event, $this->getBlameableFields($model)) :
            count($this->getBlameableFields($model)) > 0;
    }

    /**
     * Evaluate the blameable fields to use, using reflection to find a protected $blameable property.
     *
     * If keys in $blameable exist for any of [created, updated, deleted], the
     * values are taken as the column names
     *
     * Examples:
     *   private $blameable = ['created', 'updated'];
     *   private $blameable = ['created' => 'author_id'];
     *   private $blameable = ['created', 'updated', 'deleted' => 'killedBy'];
     *
     * @param Model $model
     * @param array|null $blameable Optionally, the $blameable array can be given rather than using reflection
     * @return array array of blameable fields
     */
    public static function findBlameableFields(Model $model, $blameable = [])
    {
        if (empty($blameable)) {
            $reflectedModel = new ReflectionClass($model);
            if (! $reflectedModel->hasProperty('blameable')) {
                return [];
            }

            $blameableProp = $reflectedModel->getProperty('blameable');
            $blameableProp->setAccessible(true);
            $blameable = $blameableProp->getValue($model);
        }

        if (is_array($blameable)) {
            return self::extractBlamableFields($blameable);
        }

        return [];
    }

    /**
     * Internal method that matches the extracted blamable property values with eloquent fields.
     *
     * @param array $blameableValue
     *
     * @return array
     */
    protected static function extractBlamableFields(array $blameableValue)
    {
        $fields = [];
        $checkedFields = ['created', 'updated', 'deleted'];

        foreach ($checkedFields as $possibleField) {
            if (array_key_exists($possibleField, $blameableValue)) {
                $fields[$possibleField] = $blameableValue[$possibleField];
                continue;
            }

            if (in_array($possibleField, $blameableValue)) {
                $defaultValue = $possibleField.'_by';
                $configKey = 'culpa.default_fields.'.$possibleField;
                $fields[$possibleField] = Config::get($configKey, $defaultValue);
            }
        }

        return $fields;
    }

    /**
     * Get the blameable fields.
     *
     * @param Model $model
     * @return array
     */
    protected function getBlameableFields(Model $model)
    {
        if (isset($this->fields)) {
            return $this->fields;
        }

        $this->fields = self::findBlameableFields($model);

        return $this->fields;
    }
}
