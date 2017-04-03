<?php
/**
 * Created by PhpStorm.
 * User: anton
 * Date: 03.04.2017
 * Time: 22:59
 */

namespace Barbieswimcrew\Bundle\DynamicFormBundle\Service\FormReconfigurator\ReconfigurationHandlers;


use Barbieswimcrew\Bundle\DynamicFormBundle\Service\FormReconfigurator\ReconfigurationHandlers\Base\AbstractReconfigurationHandler;
use Barbieswimcrew\Bundle\DynamicFormBundle\Service\FormReconfigurator\ReconfigurationHandlers\Base\ReconfigurationHandlerInterface;
use Symfony\Component\Form\FormInterface;

class DefaultReconfigurationHandler extends AbstractReconfigurationHandler implements ReconfigurationHandlerInterface
{

    /**
     * @param FormInterface $toggleForm
     * @return bool
     */
    public function isResponsible(FormInterface $toggleForm)
    {
        $this->setToggleForm($toggleForm);
        return true;
    }

    public function handle($data, $blockFurtherReconfigurations)
    {
        /** @var FormInterface $parentForm */
        $parentForm = $this->toggleForm->getParent();

        //todo
        $this->disableFields($data, $parentForm, $blockFurtherReconfigurations);

//            if (!false) {     //this was like it was wrapped into the reconfigure method

        $this->enableFields($data, $parentForm);

//            }
        # here it is ok to do reconfiguration with the injected rulesets show/hide fields for each rule
//            $this->reconfigure($data, $parentForm, false, $blockFurtherReconfigurations);   //todo extract desicion whether to enable or disable to this place, remove reconfigure method
    }

}