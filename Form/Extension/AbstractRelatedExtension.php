<?php


namespace Barbieswimcrew\Bundle\SymfonyFormRuleSetBundle\Form\Extension;


use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class AbstractRelatedExtension extends AbstractTypeExtension
{

    /** @var array $attr */
    protected $attr;

    /**
     * AbstractRelatedExtension constructor.
     * @param ContainerInterface $container
     * @param array $config
     */
    public function __construct(ContainerInterface $container, array $config = array())
    {
        # if at least one config parameter exists in container
        if ($container->hasParameter('barbieswimcrew_symfony_form_rule_set.strict_mode')) {
            $this->attr['strictMode'] = $container->getParameter('barbieswimcrew_symfony_form_rule_set.strict_mode');
            $this->attr['id'] = $container->getParameter('barbieswimcrew_symfony_form_rule_set.data_attr_id');
            $this->attr['isRequired'] = $container->getParameter('barbieswimcrew_symfony_form_rule_set.data_attr_is_required');
            $this->attr['targetsShow'] = $container->getParameter('barbieswimcrew_symfony_form_rule_set.data_attr_targets_show');
            $this->attr['targetsHide'] = $container->getParameter('barbieswimcrew_symfony_form_rule_set.data_attr_targets_hide');
        }

        # override attributes if custom config has been injected
        foreach ($config as $key => $value) {
            $this->attr[$key] = $value;
        }
    }

    /**
     * This method validates if a field with $fieldName exists in the form
     * and returns only existing fields
     * @param FormInterface $form
     * @param array $relatedFields
     * @author Martin Schindler
     * @return array
     * @deprecated evaluate if we need this function because of handling in ReconfigurationSubscriber
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