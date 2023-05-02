<?php

namespace App\Validator;

use Symfony\Component\Validator\Attribute\HasNamedArguments;
use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 *
 * @Target({"PROPERTY", "METHOD", "ANNOTATION"})
 */
#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::IS_REPEATABLE)]
class UniqueField extends Constraint
{
    #[HasNamedArguments]
    public function __construct(public string $entityClass, public string $field, $options = null, array $groups = null, mixed $payload = null)
    {
        parent::__construct($options, $groups, $payload);
    }

    public string $message = 'The value "{{ value }}" is already used.';

    public function getTargets(): array|string
    {
        return self::CLASS_CONSTRAINT;
    }
}
