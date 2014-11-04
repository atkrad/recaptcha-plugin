<?php

namespace ReCaptcha\Model\Validation;

use Cake\Validation\Validator;
use ReCaptcha\Lib\ReCaptcha;

class ReCaptchaValidator
{
    /**
     * ReCaptcha Validation
     *
     * @param mixed $value   The value of column to be checked for validation
     * @param array $context The validation context as provided by the validation routine
     *
     * @return bool true if the value is valid
     */
    public function reCaptchaValidation($value, array $context)
    {
        return ReCaptcha::isValid($context['data']['recaptcha_challenge_field'], $value);
    }
}
