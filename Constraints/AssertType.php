<?php

namespace Symfony\Component\Validator\Constraints;

class AssertType extends \Symfony\Component\Validator\Constraint
{
    public $message = 'This value should be of type %type%';
    public $type;

    /**
     * {@inheritDoc}
     */
    public function defaultOption()
    {
        return 'type';
    }

    /**
     * {@inheritDoc}
     */
    public function requiredOptions()
    {
        return array('type');
    }
}
