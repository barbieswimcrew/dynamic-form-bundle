<?php
/**
 * @author Anton Zoffmann
 * @copyright dasistweb GmbH (http://www.dasistweb.de)
 * Date: 16.09.16
 * Time: 14:24
 */

namespace Barbieswimcrew\Bundle\SymfonyFormRuleSetBundle\Exceptions\OptionsMerger;


class OptionsMergerResponsibilityException extends \Exception
{
    public function __construct($className, $code = 0, $previous = null)
    {
        $message = sprintf("There is no OptionsMerger responsible for given class %s", $className);
        parent::__construct($message, $code, $previous);
    }
}