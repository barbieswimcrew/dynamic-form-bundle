<?php
/**
 * @author Anton Zoffmann
 * @copyright dasistweb GmbH (http://www.dasistweb.de)
 * Date: 13.09.16
 * Time: 15:04
 */

namespace barbieswimcrew\DynamicFormsBundle\Structs\Rules;


use barbieswimcrew\DynamicFormsBundle\Structs\Rules\Base\RuleInterface;

class BaseRule implements RuleInterface
{

    /** @var string $value */
    private $value;
    /** @var array $showFields */
    private $showFields;
    /** @var array $hideFields */
    private $hideFields;

    public function __construct($value, array $showFields, array $hideFields)
    {
        $this->value = $value;
        $this->showFields = $showFields;
        $this->hideFields = $hideFields;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return array
     */
    public function getShowFields()
    {
        return $this->showFields;
    }

    /**
     * @return array
     */
    public function getHideFields()
    {
        return $this->hideFields;
    }

}