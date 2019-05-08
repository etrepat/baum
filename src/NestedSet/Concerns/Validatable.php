<?php

namespace Baum\NestedSet\Concerns;

use Baum\NestedSet\Validator;

trait Validatable
{
    /**
     * Checks wether the underlying Nested Set structure is valid.
     *
     * @return boolean
     */
    public static function isValidNestedSet()
    {
        $validator = new Validator(new static);

        return $validator->passes();
    }
}
