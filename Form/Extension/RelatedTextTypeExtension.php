<?php
/**
 * @author Martin Schindler
 * @copyright dasistweb GmbH (http://www.dasistweb.de)
 * Date: 11.09.16
 * Time: 11:55
 */

namespace Barbieswimcrew\Bundle\SymfonyFormRuleSetBundle\Form\Extension;


use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RelatedTextTypeExtension extends AbstractRelatedExtension
{

    const OPTION_NAME_RELATED = "related";

    /**
     * Returns the name of the form field type being extended
     * @author Martin Schindler
     * @return string
     */
    public function getExtendedType()
    {
        return TextType::class;
    }

    /**
     * Registering the new form field options
     * @param OptionsResolver $resolver
     * @author Martin Schindler
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $this->defineNewFieldOption($resolver, self::OPTION_NAME_RELATED, array(), array('array'));
    }

    /**
     * Adding data properties to the current form fields view html attributes
     * @param FormView $view
     * @param FormInterface $form
     * @param array $options
     * @author Martin Schindler
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        # check if any field with $fieldName exists
        $fields = $this->getValidFields($form, $options[self::OPTION_NAME_RELATED]);

        # if there are any existing fields then set the html data attribute with all related fields names
        if (count($fields) > 0) {
            # add target fields data
            $view->vars['attr'][self::ATTR_NAME_RELATED_TARGET] = implode(',', $fields);
        }
    }

}