<?php


namespace Barbieswimcrew\Bundle\DynamicFormBundle\Exceptions\Rules;


class NoRuleDefinedException extends \Exception
{
    public function __construct($value, $code = 0, \Exception $previous=null)
    {
        $message = sprintf("There is no rule defined for the value %s", $value);
        parent::__construct($message, $code, $previous);
    }
}