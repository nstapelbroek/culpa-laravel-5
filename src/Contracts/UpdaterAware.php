<?php

namespace Culpa\Contracts;

interface UpdaterAware
{
    /**
     * Get the user that updated the model.
     *
     * @return \Illuminate\Database\Eloquent\Model User instance
     */
    public function updater();
}
