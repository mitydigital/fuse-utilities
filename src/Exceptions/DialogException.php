<?php

namespace MityDigital\FuseUtilities\Exceptions;

use InvalidArgumentException;

class DialogException extends InvalidArgumentException
{
    public static function duplicateId(string $id): self
    {
        return new self("The dialog id [{$id}] has already been used in this request.");
    }

    public static function invalidId(mixed $id): self
    {
        $id = is_scalar($id) ? (string) $id : get_debug_type($id);

        return new self("The dialog id [{$id}] is invalid. It must be a non-empty string.");
    }
}
