<?php

namespace Barbieswimcrew\Bundle\DynamicFormBundle\Exceptions\Reconfiguration;

/**
 * Class ReconfigurationNotAllowedException
 * @author Martin Schindler
 * @package Barbieswimcrew\Bundle\DynamicFormBundle\Exceptions\Reconfiguration
 */
class ReconfigurationNotAllowedException extends \Exception
{
    /**
     * ReconfigurationNotAllowedException constructor.
     * @param string $message
     * @param int $code
     * @param \Exception|null $previous
     */
    public function __construct($message = "", $code = 0, \Exception $previous = null)
    {
        $message = "Form was already reconfigured, multiple reconfigurations are not allowed! First come, first serve.";
        parent::__construct($message, $code, $previous);
    }

}