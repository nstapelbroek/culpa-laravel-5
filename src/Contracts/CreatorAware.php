<?php

namespace Culpa\Contracts;

interface CreatorAware
{
    /**
     * Get the user that created the model.
     *
     * @return \Illuminate\Database\Eloquent\Model User instance
     */
    public function creator();
}
