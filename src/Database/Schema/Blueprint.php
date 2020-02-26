<?php

namespace Culpa\Database\Schema;

use Illuminate\Database\Schema\Blueprint as IlluminateBlueprint;
use Illuminate\Support\Facades\Config;

class Blueprint extends IlluminateBlueprint
{
    /**
     * Single method to configure all blameable fields in the table.
     *
     * @param array $fields
     * @param bool $nullable determine if the column can be NULL
     * @throws \Exception
     * @see Blueprint::createdBy()
     * @see Blueprint::updatedBy()
     * @see Blueprint::deletedBy()
     */
    public function blameable($fields = ['created', 'updated', 'deleted'], $nullable = false)
    {
        if (in_array('created', $fields)) {
            $this->createdBy($nullable);
        }

        if (in_array('updated', $fields)) {
            $this->updatedBy($nullable);
        }

        if (in_array('deleted', $fields)) {
            $this->deletedBy($nullable);
        }
    }

    /**
     * Add the blameable creator field.
     *
     * @see Illuminate\Database\Schema\Blueprint::integer()
     * @param bool $nullable determine if the column can be NULL
     * @return \Illuminate\Support\Fluent
     * @throws \Exception
     */
    public function createdBy($nullable = false)
    {
        $columnName = Config::get('culpa.default_fields.created');
        if (! $columnName) {
            throw new \Exception('No column for the created field is configured, did you publish the Culpa config?');
        }

        $field = $this->integer($columnName)->unsigned();

        if (true === $nullable) {
            $field->nullable();
        }

        $this->addCulpaForeign($columnName);

        return $field;
    }

    /**
     * Add the blameable updater field.
     *
     * @see Illuminate\Database\Schema\Blueprint::integer()
     * @param bool $nullable determine if the column can be NULL
     * @return \Illuminate\Support\Fluent
     * @throws \Exception
     */
    public function updatedBy($nullable = false)
    {
        $columnName = Config::get('culpa.default_fields.updated');
        if (! $columnName) {
            throw new \Exception('No column for the updated field is configured, did you publish the Culpa config?');
        }

        $field = $this->integer($columnName)->unsigned();

        if (true === $nullable) {
            $field->nullable();
        }

        $this->addCulpaForeign($columnName);

        return $field;
    }

    /**
     * Add the blameable eraser field.
     *
     * @see Illuminate\Database\Schema\Blueprint::integer()
     * @param bool $nullable determine if the column can be NULL
     * @return \Illuminate\Support\Fluent
     * @throws \Exception
     */
    public function deletedBy($nullable = false)
    {
        $columnName = Config::get('culpa.default_fields.deleted');
        if (! $columnName) {
            throw new \Exception('No column for the deleted field is configured, did you publish the Culpa config?');
        }

        $field = $this->integer($columnName)->unsigned();

        if (true === $nullable) {
            $field->nullable();
        }

        $this->addCulpaForeign($columnName);

        return $field;
    }

    /**
     * Add a foreign key constraint to the users table.
     *
     * Failing to configure a users table in the configuration does not break this method, although you
     * should never neglect the foreign keys, the schema blueprint can function without them.
     * @param $columnName
     * @return void
     */
    protected function addCulpaForeign($columnName)
    {
        $foreignTable = Config::get('culpa.users.table');
        if ($foreignTable) {
            $this->foreign($columnName)->references('id')->on($foreignTable);
        }
    }
}
