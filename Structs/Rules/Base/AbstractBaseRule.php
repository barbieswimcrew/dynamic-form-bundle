<?php


namespace Barbieswimcrew\Bundle\SymfonyFormRuleSetBundle\Structs\Rules\Base;

abstract class AbstractBaseRule implements RuleInterface
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