<?php
// src/Validator/Constraints/NoBadWordsValidator.php
namespace App\Validator\Constraints;

use App\Service\WebPurifyService;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class NoBadWordsValidator extends ConstraintValidator
{
    private WebPurifyService $webPurifyService;

    public function __construct(WebPurifyService $webPurifyService)
    {
        $this->webPurifyService = $webPurifyService;
    }

    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof NoBadWords) {
            throw new UnexpectedTypeException($constraint, NoBadWords::class);
        }

        // Skip validation if the value is empty or not a string
        if (null === $value || '' === $value || !is_string($value)) {
            return;
        }

        // Check for bad words using the WebPurify API
        if ($this->webPurifyService->containsBadWords($value)) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}