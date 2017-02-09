<?php

namespace Barbieswimcrew\Bundle\DynamicFormBundle\Service\FormAccessResolver;

use Barbieswimcrew\Bundle\DynamicFormBundle\Exceptions\Rules\UndefinedFormAccessorException;
use Symfony\Component\Form\FormInterface;

/**
 * Class FormAccessResolver
 * @author Martin Schindler
 * @package Barbieswimcrew\Bundle\DynamicFormBundle\Service\FormAccessResolver
 */
class FormAccessResolver
{

    /** This divider is being used to define the path>to>field accessor string */
    const PATH_DIVIDER = '>';

    /** This divider is being used to define the fully_qualified_namespace_of_field */
    const NAMESPACE_DIVIDER = '_';

    /**
     * checks if the rule field accessor string is valid for the given form
     * @param $ruleFieldAccessor
     * @param FormInterface $form
     * @author Martin Schindler
     * @return FormInterface
     * @throws UndefinedFormAccessorException
     */
    public function getFormById($ruleFieldAccessor, FormInterface $form)
    {
        $path = array_filter(explode(self::PATH_DIVIDER, $ruleFieldAccessor));

        foreach ($path as $name) {
            try {
                $form = $form->get($name);
            } catch (\OutOfBoundsException $e) {
                throw new UndefinedFormAccessorException($ruleFieldAccessor, 500);
            }
        }

        return $form;
    }

    /**
     * Method returns the full qualified accessor name of the given form field
     * @param $ruleFieldAccessor
     * @param FormInterface $form
     * @author Martin Schindler
     * @return string
     */
    public function getFullName($ruleFieldAccessor, FormInterface $form)
    {

        # if given form doesn't have a parent use the form itself instead
        $parentForm = (is_null($form->getParent())) ? $form : $form->getParent();

        $parent = $this->getFormById($ruleFieldAccessor, $parentForm);
        $fullName = $parent->getName();

        while (!$parent->isRoot()) {
            $parent = $parent->getParent();
            $fullName = $parent->getName() . self::NAMESPACE_DIVIDER . $fullName;
        }

        return $fullName;
    }

}