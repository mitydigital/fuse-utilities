<?php

namespace MityDigital\FuseUtilities\Support;

use AryehRaber\Captcha\Contracts\CustomShouldVerify;
use MityDigital\FuseUtilities\Facades\FuseUtilities;
use Statamic\Events\FormSubmitted;

class CaptchaShouldVerify implements CustomShouldVerify
{
    public function __invoke($event): ?bool
    {
        //
        // FORM SUBMITTED
        //
        if ($event instanceof FormSubmitted) {
            return FuseUtilities::isCaptchaEnabled(
                form: $event->submission->form()->handle()
            );
        }

        return true;
    }
}
