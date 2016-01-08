<?php

namespace Culpa\Contracts;

interface EraserAware
{
    /**
     * Get the user that soft-deleted the model.
     *
     * @return \Illuminate\Database\Eloquent\Model User instance
     */
    public function eraser();
}
