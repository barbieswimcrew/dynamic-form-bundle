<?php
/**
 * @author Anton Zoffmann
 * @copyright dasistweb GmbH (http://www.dasistweb.de)
 * Date: 21.09.16
 * Time: 18:13
 */

namespace Barbieswimcrew\Bundle\SymfonyFormRuleSetBundle\Exceptions\Reconfiguration;


use Exception;

class ReconfigurationNotAllowedException extends \Exception
{
    public function __construct($message="", $code=0, Exception $previous=null)
    {
        $message = "Form was already reconfigured, multiple reconfigurations are not allowed! First come, first serve.";
        parent::__construct($message, $code, $previous);
    }

}