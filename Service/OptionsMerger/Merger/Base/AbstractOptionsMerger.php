<?php
/**
 * @author Anton Zoffmann
 * @copyright dasistweb GmbH (http://www.dasistweb.de)
 * Date: 16.09.16
 * Time: 13:17
 */

namespace Barbieswimcrew\Bundle\SymfonyFormRuleSetBundle\Service\OptionsMerger\Merger\Base;


use Barbieswimcrew\Bundle\SymfonyFormRuleSetBundle\Service\OptionsMerger\Base\OptionsMergerInterface;
use Symfony\Component\Debug\Exception\ClassNotFoundException;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\ResolvedFormTypeInterface;

abstract class AbstractOptionsMerger implements OptionsMergerInterface, ResponsibilityInterface
{

    /**
     * @inheritdoc
     * @author Anton Zoffmann
     * @return array
     */
    abstract public function getMergedOptions(FormInterface $form, array $overrideOptions, $hidden);

    /**
     * @inheritdoc
     * @param FormInterface $form
     * @author Anton Zoffmann
     */
    public function isResponsibleForFormTypeClass(FormInterface $form)
    {

        foreach ($this->getApplicableClasses() as $applicableClass) {

            if(!class_exists($applicableClass)){
                throw new ClassNotFoundException(sprintf("Class %s not found", $applicableClass), null);
            }

            $formTypeClassName = $this->getConfiguredFormTypeByForm($form);

            if(!class_exists($applicableClass)){
                throw new ClassNotFoundException(sprintf("Class %s not found", $formTypeClassName), null);
            }

            $formType = new $formTypeClassName();

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
    public function isResponsibleForFormTypeInterface(FormInterface $form)
    {
        $type = $this->getConfiguredFormTypeByForm($form);

        $applicableInterface = $this->getApplicableInterface();
        if ($type instanceof $applicableInterface) {
            return true;
        }

        return false;
    }

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
     * @param FormInterface $form
     * @author Anton Zoffmann
     * @return string
     */
    protected function getConfiguredFormTypeByForm(FormInterface $form)
    {
        if (($resolvedType = $form->getConfig()->getType()) instanceof ResolvedFormTypeInterface) {
            return get_class($resolvedType->getInnerType());
        } else {
            return get_class($form->getConfig()->getType());
        }
    }
}