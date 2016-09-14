<?php
/**
 * @author Martin Schindler
 * @copyright dasistweb GmbH (http://www.dasistweb.de)
 * Date: 11.09.16
 * Time: 17:16
 */

namespace Barbieswimcrew\Bundle\SymfonyFormRuleSetBundle\Form\Extension;


use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class AbstractRelatedExtension extends AbstractTypeExtension
{

    /** @var boolean $strictMode */
    protected $strictMode;

    /** @var array $attr */
    protected $attr;

    /**
     * AbstractRelatedExtension constructor.
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->strictMode = $container->getParameter('barbieswimcrew_symfony_form_rule_set.strict_mode');
        $this->attr['id'] = $container->getParameter('barbieswimcrew_symfony_form_rule_set.data_attr_id');
        $this->attr['isRequired'] = $container->getParameter('barbieswimcrew_symfony_form_rule_set.data_attr_is_required');
        $this->attr['targetsShow'] = $container->getParameter('barbieswimcrew_symfony_form_rule_set.data_attr_targets_show');
        $this->attr['targetsHide'] = $container->getParameter('barbieswimcrew_symfony_form_rule_set.data_attr_targets_hide');
    }

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