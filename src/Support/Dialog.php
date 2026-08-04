<?php

namespace MityDigital\FuseUtilities\Support;

use MityDigital\FuseUtilities\Exceptions\DialogException;

class Dialog
{
    private const string DIALOG_IDS = 'fuse-utilities.dialog.ids';

    public static function registerId(mixed $id): string
    {
        if (! is_string($id) || blank($id)) {
            throw DialogException::invalidId($id);
        }

        $registeredIds = request()->attributes->get(self::DIALOG_IDS, []);

        if (in_array($id, $registeredIds, true)) {
            throw DialogException::duplicateId($id);
        }

        $registeredIds[] = $id;
        request()->attributes->set(self::DIALOG_IDS, $registeredIds);

        return $id;
    }
}
