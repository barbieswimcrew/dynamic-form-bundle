<?php
/**
 * @author Anton Zoffmann
 * @copyright dasistweb GmbH (http://www.dasistweb.de)
 * Date: 15.09.16
 * Time: 12:13
 */

namespace Barbieswimcrew\Bundle\SymfonyFormRuleSetBundle\Exceptions\Rules;


class WrongIdDefinitionException extends \Exception
{
    public function __construct($cssId , $code = 0, \Exception $previous = null) {
        $message = sprintf("You defined a CSS ID within a RuleSetInterface Object which could not be resolved to the given Form structure. ID = %s", $cssId);
        parent::__construct($message, $code, $previous);
    }
}