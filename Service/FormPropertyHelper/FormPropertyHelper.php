<?php

namespace Barbieswimcrew\Bundle\DynamicFormBundle\Service\FormPropertyHelper;

use Symfony\Component\Debug\Exception\ClassNotFoundException;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\Form\ResolvedFormTypeInterface;

/**
 * Class FormPropertyHelper
 * @author Martin Schindler
 * @package Barbieswimcrew\Bundle\DynamicFormBundle\Service\FormPropertyHelper
 */
class FormPropertyHelper
{
    /**
     * @param FormInterface $form
     * @author Anton Zoffmann
     * @return FormTypeInterface
     * @throws ClassNotFoundException
     */
    public function getConfiguredFormTypeByForm(FormInterface $form)
    {

        $resolvedType = $form->getConfig()->getType();
        $resolvedTypeClass = get_class($resolvedType);

        if ($resolvedType instanceof ResolvedFormTypeInterface) {
            $resolvedTypeClass = get_class($resolvedType->getInnerType());
        }

        if (!class_exists($resolvedTypeClass)) {
            throw new ClassNotFoundException(sprintf('Class "%s" not found', $resolvedTypeClass), null);
        }

        $formType = new $resolvedTypeClass();

        return $formType;
    }
}