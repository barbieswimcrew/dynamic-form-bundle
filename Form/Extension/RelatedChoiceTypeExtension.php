<?php
/**
 * @author Martin Schindler
 * @copyright dasistweb GmbH (http://www.dasistweb.de)
 * Date: 11.09.16
 * Time: 11:55
 */

namespace Barbieswimcrew\Bundle\SymfonyFormRuleSetBundle\Form\Extension;


use Barbieswimcrew\Bundle\SymfonyFormRuleSetBundle\Structs\Rules\Base\RuleSetInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RelatedChoiceTypeExtension extends AbstractRelatedExtension
{

    const OPTION_NAME_RULES = "rules";

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
        $this->defineNewFieldOption($resolver, self::OPTION_NAME_RULES, null, array(RuleSetInterface::class));
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
        //@todo: Über die gesetzten Rules müssen hier noch die Targets an die View gebunden werden

//        foreach ($options[self::OPTION_NAME_RULES] as $key => $row) {
//            $fields = $this->getValidFields($form, $row);
//
//            if (count($fields) > 0) {
//                # add target fields data
//                $view->vars['attr'][self::ATTR_NAME_RELATED_TARGETS] = implode(',', $fields);
//            }
//        }

    }


}