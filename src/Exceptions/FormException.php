<?php

namespace MityDigital\FuseUtilities\Exceptions;

use InvalidArgumentException;

class FormException extends InvalidArgumentException
{
    public static function notSet(): self
    {
        return new self('Form not set.');
    }

    public static function missingHandle(): self
    {
        return new self('Field handle is missing.');
    }

    public static function fieldNotFound(string $handle, string $form): self
    {
        return new self("Field \"{$handle}\" could not be found on the form \"{$form}\".");
    }
}
