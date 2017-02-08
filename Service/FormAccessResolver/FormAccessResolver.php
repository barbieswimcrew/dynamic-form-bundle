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
        $path = explode(self::PATH_DIVIDER, $ruleFieldAccessor);

        foreach ($path as $name) {
            if ($form->has($name)) {
                $form = $form->get($name);
            } else {
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
        $parent = $this->getFormById($ruleFieldAccessor, $form->getParent());
        $fullName = $ruleFieldAccessor;

        while (!$parent->isRoot()) {
            $parent = $parent->getParent();
            $fullName = $parent->getName() . '_' . $fullName;
        }

        return $fullName;
    }

}