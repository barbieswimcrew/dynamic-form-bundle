<?php
/**
 * @author Martin Schindler
 * @copyright dasistweb GmbH (http://www.dasistweb.de)
 * Date: 11.09.16
 * Time: 17:16
 */

namespace Barbieswimcrew\Bundle\SymfonyFormRuleSetBundle\Form\Extension;


use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class AbstractRelatedExtension extends AbstractTypeExtension
{

    const ATTR_NAME_RELATED_NAME = "data-related-id";
    const ATTR_NAME_RELATED_TARGET = "data-related-target";

    /**
     * This method validates if a field with $fieldName exists in the form
     * and returns only existing fields
     * @param FormInterface $form
     * @param array $relatedFields
     * @author Martin Schindler
     * @return array
     */
    protected function getValidFields(FormInterface $form, array $relatedFields)
    {
        $fields = array();
        foreach ($relatedFields as $fieldName) {
            $root = $form->getRoot();

            if ($this->childExists($root, $fieldName) === true) {
                $fields[] = $root->getName() . '_' . $fieldName;
            }
        }
        return $fields;
    }

    /**
     * Helper method to create new field options
     * @param OptionsResolver $resolver
     * @param $optionName
     * @param $defaultValue
     * @param array $allowedTypes
     * @author Martin Schindler
     */
    protected function defineNewFieldOption(OptionsResolver $resolver, $optionName, $defaultValue, array $allowedTypes)
    {
        $resolver->setDefined($optionName);
        $resolver->setDefault($optionName, $defaultValue);
        $resolver->setAllowedTypes($optionName, $allowedTypes);
    }

    /**
     * Method to determine if a child exists in form
     * @param FormInterface $child
     * @param $fieldName
     * @author Martin Schindler
     * @return bool
     */
    private function childExists(FormInterface $child, $fieldName)
    {
        $path = explode('_', $fieldName);

        foreach ($path as $name) {
            if ($child->has($name)) {
                $child = $child->get($name);
            } else {
                return false;
            }
        }
        return true;
    }
}