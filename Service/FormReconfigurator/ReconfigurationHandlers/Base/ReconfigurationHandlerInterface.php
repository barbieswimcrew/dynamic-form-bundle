<?php
/**
 * Created by PhpStorm.
 * User: anton
 * Date: 03.04.2017
 * Time: 22:54
 */

namespace Barbieswimcrew\Bundle\DynamicFormBundle\Service\FormReconfigurator\ReconfigurationHandlers\Base;


use Symfony\Component\Form\FormInterface;

interface ReconfigurationHandlerInterface
{
    /** @return boolean */
    function isResponsible(FormInterface $toggleForm);

    /** @return void */
    function handle($data, $blockFurtherReconfigurations);
}