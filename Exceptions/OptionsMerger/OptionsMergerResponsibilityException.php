<?php

namespace Barbieswimcrew\Bundle\DynamicFormBundle\Exceptions\OptionsMerger;

/**
 * Class OptionsMergerResponsibilityException
 * @author Martin Schindler
 * @package Barbieswimcrew\Bundle\DynamicFormBundle\Exceptions\OptionsMerger
 */
class OptionsMergerResponsibilityException extends \Exception
{
    /**
     * OptionsMergerResponsibilityException constructor.
     * @param string $className
     * @param int $code
     * @param null $previous
     */
    public function __construct($className, $code = 0, $previous = null)
    {
        $message = sprintf("There is no OptionsMerger responsible for given class %s", $className);
        parent::__construct($message, $code, $previous);
    }
}