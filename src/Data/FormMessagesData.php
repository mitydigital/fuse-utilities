<?php

namespace MityDigital\FuseUtilities\Data;

use Spatie\LaravelData\Data;

class FormMessagesData extends Data
{
    public function __construct(
        public FormMessageData $error,
        public FormMessageData $success,
        public FormMessageData $validation
    ) {}
}
