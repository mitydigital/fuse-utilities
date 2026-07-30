<?php

namespace MityDigital\FuseUtilities\Support;

use AryehRaber\Captcha\Contracts\CustomShouldVerify;
use MityDigital\FuseUtilities\Facades\Forms;
use Statamic\Events\FormSubmitted;

class CaptchaShouldVerify implements CustomShouldVerify
{
    public function __invoke($event): ?bool
    {
        //
        // FORM SUBMITTED
        //
        if ($event instanceof FormSubmitted) {
            return Forms::isCaptchaEnabled(
                form: $event->submission->form()->handle()
            );
        }

        return true;
    }
}
