<?php
namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class NoBadWords extends Constraint
{
    public $message = 'The text contains inappropriate language.';
}