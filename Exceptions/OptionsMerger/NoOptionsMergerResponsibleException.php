<?php

namespace Barbieswimcrew\Bundle\DynamicFormBundle\Exceptions\OptionsMerger;

/**
 * Class NoOptionsMergerResponsibleException
 * @author Martin Schindler
 * @package Barbieswimcrew\Bundle\DynamicFormBundle\Exceptions\OptionsMerger
 */
class NoOptionsMergerResponsibleException extends \Exception
{
    /**
     * NoOptionsMergerResponsibleException constructor.
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