<?php

namespace Barbieswimcrew\Bundle\DynamicFormBundle\Tests\Service\OptionsMerger;
use Barbieswimcrew\Bundle\DynamicFormBundle\Service\FormPropertyHelper\FormPropertyHelper;
use Barbieswimcrew\Bundle\DynamicFormBundle\Service\OptionsMerger\Base\ResponsibilityInterface;
use Barbieswimcrew\Bundle\DynamicFormBundle\Service\OptionsMerger\Merger\RepeatedTypeOptionsMerger;
use Barbieswimcrew\Bundle\DynamicFormBundle\Service\OptionsMerger\Merger\ScalarFormTypeOptionsMerger;
use Barbieswimcrew\Bundle\DynamicFormBundle\Service\OptionsMerger\OptionsMergerService;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;


/**
 * Class OptionsMergerServiceTest
 * @package Barbieswimcrew\Bundle\DynamicFormBundle\Tests\Service\OptionsMerger
 */
class OptionsMergerServiceTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @test
     */
    public function testGetMergerByClass()
    {
        $propertyHelperMock = $this->createMock(FormPropertyHelper::class);
        $propertyHelperMock->method("getConfiguredFormTypeByForm")
            ->willReturn(new RepeatedType(), new TextType());

        $responsibilityMock = $this->createMock(ResponsibilityInterface::class);

        $responsibilityMock->method("isResponsibleForClass")->willReturn(true);
        $responsibilityMock->method("isResponsibleForInterface")->willReturn(true);

        $expectedResult = $this->createMock(RepeatedTypeOptionsMerger::class);

        $unexpectedResult = $this->createMock(ScalarFormTypeOptionsMerger::class);

        $merger = new OptionsMergerService($propertyHelperMock, $responsibilityMock, $expectedResult, $unexpectedResult);

        $formMock = $this->createMock(FormInterface::class);

        $actualResult = $merger->getOptionsMerger($formMock);

        $this->assertEquals($expectedResult, $actualResult);
    }

    /**
     * @test
     */
    public function testGetMergerByInterface()
    {
        $propertyHelperMock = $this->createMock(FormPropertyHelper::class);
        $propertyHelperMock->method("getConfiguredFormTypeByForm")
            ->willReturn(new RepeatedType(), new TextType());

        $responsibilityMock = $this->createMock(ResponsibilityInterface::class);

        $responsibilityMock->method("isResponsibleForClass")->willReturn(false, false);
        $responsibilityMock->method("isResponsibleForInterface")->willReturn(false, true);

        $unexpectedResult = $this->createMock(RepeatedTypeOptionsMerger::class);

        $expectedResult = $this->createMock(ScalarFormTypeOptionsMerger::class);

        $merger = new OptionsMergerService($propertyHelperMock, $responsibilityMock, $unexpectedResult, $expectedResult);

        $formMock = $this->createMock(FormInterface::class);

        $actualResult = $merger->getOptionsMerger($formMock);

        $this->assertEquals($expectedResult, $actualResult);
    }

    /**
     * @test
     */
    public function testNoMergerFound()
    {
        $propertyHelperMock = $this->createMock(FormPropertyHelper::class);
        $propertyHelperMock->method("getConfiguredFormTypeByForm")
            ->willReturn(new RepeatedType(), new TextType());

        $responsibilityMock = $this->createMock(ResponsibilityInterface::class);

        $responsibilityMock->method("isResponsibleForClass")->willReturn(false);
        $responsibilityMock->method("isResponsibleForInterface")->willReturn(false);

        $unexpectedResult = $this->createMock(RepeatedTypeOptionsMerger::class);

        $merger = new OptionsMergerService($propertyHelperMock, $responsibilityMock, $unexpectedResult);

        $formMock = $this->createMock(FormInterface::class);

        $this->expectException(\Exception::class);

        $merger->getOptionsMerger($formMock);

    }
}
