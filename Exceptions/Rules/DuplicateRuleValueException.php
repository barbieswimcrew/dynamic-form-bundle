<?php


namespace Barbieswimcrew\Bundle\DynamicFormBundle\Exceptions\Rules;

use Exception;

class DuplicateRuleValueException extends \Exception
{
    public function __construct($value, $code = 0, Exception $previous = null)
    {
        $message = sprintf("You may not set more than one Rule for a given value. Affected value: %s", $value);
        parent::__construct($message, $code, $previous);
    }

}