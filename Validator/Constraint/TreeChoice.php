<?php

namespace CyberApp\TreeChoiceBundle\Validator\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class TreeChoice extends Constraint
{
    /**
     * @var string
     */
    public $message = 'Entity cannot be parent of yourself.';

    /**
     * {@inheritdoc}
     */
    public function validatedBy()
    {
        return 'tree_choice';
    }

    /**
     * {@inheritdoc}
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
