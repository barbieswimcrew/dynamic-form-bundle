<?php
/**
 * @author Anton Zoffmann
 * @copyright dasistweb GmbH (http://www.dasistweb.de)
 * Date: 15.09.16
 * Time: 10:33
 */

namespace Barbieswimcrew\Bundle\SymfonyFormRuleSetBundle\Exceptions\Rules;


class NoRuleDefinedException extends \Exception
{
    public function __construct($value, $code = 0, \Exception $previous=null)
    {
        $message = sprintf("There is no rule defined for the value %s", $value);
        parent::__construct($message, $code, $previous);
    }
}