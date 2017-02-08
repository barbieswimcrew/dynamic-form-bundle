<?php

namespace Barbieswimcrew\Bundle\DynamicFormBundle\Exceptions\Rules;

/**
 * Class DuplicateRuleValueException
 * @author Martin Schindler
 * @package Barbieswimcrew\Bundle\DynamicFormBundle\Exceptions\Rules
 */
class DuplicateRuleValueException extends \Exception
{
    /**
     * DuplicateRuleValueException constructor.
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