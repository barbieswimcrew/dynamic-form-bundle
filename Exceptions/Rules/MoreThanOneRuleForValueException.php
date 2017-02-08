<?php

namespace Barbieswimcrew\Bundle\DynamicFormBundle\Exceptions\Rules;

/**
 * Class MoreThanOneRuleForValueException
 * @author Martin Schindler
 * @package Barbieswimcrew\Bundle\DynamicFormBundle\Exceptions\Rules
 */
class MoreThanOneRuleForValueException extends \Exception
{
    /**
     * MoreThanOneRuleForValueException constructor.
     * @param string $value
     * @param int $code
     * @param \Exception|null $previous
     */
    public function __construct($value, $code = 0, \Exception $previous = null)
    {
        $message = sprintf("You may not set more than one Rule for a given value. Affected value: %s", $value);
        parent::__construct($message, $code, $previous);
    }

}