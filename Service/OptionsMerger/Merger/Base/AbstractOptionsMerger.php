<?php

namespace Barbieswimcrew\Bundle\DynamicFormBundle\Service\OptionsMerger\Merger\Base;

use Barbieswimcrew\Bundle\DynamicFormBundle\Service\FormPropertyHelper\FormPropertyHelper;
use Barbieswimcrew\Bundle\DynamicFormBundle\Service\OptionsMerger\Base\OptionsMergerInterface;
use Symfony\Component\Debug\Exception\ClassNotFoundException;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormTypeInterface;

/**
 * Class AbstractOptionsMerger
 * @author Anton Zoffmann
 * @package Barbieswimcrew\Bundle\DynamicFormBundle\Service\OptionsMerger\Merger\Base
 */
abstract class AbstractOptionsMerger implements OptionsMergerInterface, ResponsibilityInterface
{

    /**
     * @var FormPropertyHelper
     */
    private $propertyHelper;

    /**
     * AbstractOptionsMerger constructor.
     * @param FormPropertyHelper $propertyHelper
     */
    public function __construct(FormPropertyHelper $propertyHelper)
    {
        $this->propertyHelper = $propertyHelper;
    }

    /**
     * @inheritdoc
     * @author Anton Zoffmann
     * @return array
     */
    abstract public function getMergedOptions(FormInterface $form, array $overrideOptions, $hidden);

    /**
     * returns a fully qualified namespace of the interface
     * @author Anton Zoffmann
     * @return string
     */
    abstract protected function getApplicableInterface();

    /**
     * returns an array of strings from the fully qualified namespaces of applicable classes
     * @author Anton Zoffmann
     * @return array
     */
    abstract protected function getApplicableClasses();

    /**
     * @inheritdoc
     * @param FormTypeInterface $formType
     * @author Anton Zoffmann
     * @todo extract the decision whether a OptionsMerger is Responsible or not into another service
     */
    public function isResponsibleForClass(FormTypeInterface $formType)
    {

        $applicableClasses = (is_array($this->getApplicableClasses())) ? $this->getApplicableClasses() : array();

        foreach ($applicableClasses as $applicableClass) {

            if (!class_exists($applicableClass)) {
                throw new ClassNotFoundException(sprintf('Class "%s" not found', $applicableClass), null);
            }

            if ($formType instanceof $applicableClass) {
                return true;
            }
        }

        return false;
    }

    /**
     * @inheritdoc
     * @author Anton Zoffmann
     * @todo extract the decision whether a OptionsMerger is Responsible or not into another service
     */
    public function isResponsibleForInterface(FormTypeInterface $formType)
    {

        $applicableInterface = (string)$this->getApplicableInterface();

        if ($formType instanceof $applicableInterface) {
            return true;
        }

        return false;
    }

}
