<?php
/**
 * @author Martin Schindler
 * @copyright dasistweb GmbH (http://www.dasistweb.de)
 * Date: 11.09.16
 * Time: 11:55
 */

namespace Barbieswimcrew\Bundle\SymfonyFormRuleSetBundle\Form\Extension;


use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RelatedChoiceTypeExtension extends AbstractRelatedExtension
{

    const OPTION_NAME_CHOICE_RELATED = "choice_related";

    /**
     * Returns the name of the form field type being extended
     * @author Martin Schindler
     * @return string
     */
    public function getExtendedType()
    {
        return ChoiceType::class;
    }

    /**
     * Registering the new form field options
     * @param OptionsResolver $resolver
     * @author Martin Schindler
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $this->defineNewFieldOption($resolver, self::OPTION_NAME_CHOICE_RELATED, array(), array('array'));
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

        foreach ($options[self::OPTION_NAME_CHOICE_RELATED] as $key => $row) {
            $fields = $this->getValidFields($form, $row);

//            var_dump($form->getName());
//            var_dump($key);

//            $view->vars['choices'][$key]['attr']['hans'] = "done";

//            die();

            #@todo: Target Felder an Choices binden!!
            if (count($fields) > 0) {
                # add target fields data
                $view->vars['choices'][$key]->attr['aha'] = 'zefix';
//                die(var_dump($view->vars['choices']));
//                var_dump($view->vars['choices']);
                $view->vars['attr'][self::ATTR_NAME_RELATED_TARGET] = implode(',', $fields);
            }

//            echo '<br><br>';
        }

    }

}