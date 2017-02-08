<?php

namespace Barbieswimcrew\Bundle\DynamicFormBundle\Exceptions\Rules;

/**
 * Class UndefinedFormAccessorException
 * @author Martin Schindler
 * @package Barbieswimcrew\Bundle\DynamicFormBundle\Exceptions\Rules
 */
class UndefinedFormAccessorException extends \Exception
{
    /**
     * UndefinedFormAccessorException constructor.
     * @param string $cssId
     * @param int $code
     * @param \Exception|null $previous
     */
    public function __construct($cssId, $code = 0, \Exception $previous = null)
    {
        $message = sprintf("You defined a form accessor within a RuleSetInterface Object which could not be resolved to the given Form structure. ID = %s", $cssId);
        parent::__construct($message, $code, $previous);
    }
}