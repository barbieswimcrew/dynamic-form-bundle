<?php

namespace Barbieswimcrew\Bundle\DynamicFormBundle\Tests\Service\OptionsMerger;

use Barbieswimcrew\Bundle\DynamicFormBundle\Service\OptionsMerger\Base\OptionsMergerInterface;
use Barbieswimcrew\Bundle\DynamicFormBundle\Service\OptionsMerger\ResponsibilityChecker;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormTypeInterface;

/**
 * Class ResponsibilityCheckerTest
 * @package Barbieswimcrew\Bundle\DynamicFormBundle\Tests\Service\OptionsMerger
 */
class ResponsibilityCheckerTest extends \PHPUnit_Framework_TestCase
{

    /** @var ResponsibilityChecker */
    private $service;

    protected function setUp()
    {
        $this->service = new ResponsibilityChecker();
    }

    /**
     * @dataProvider getClassTestData
     * @test
     * @param $expectedResult
     * @param $merger
     * @param $formType
     */
    public function testIsResponsibleForClass($expectedResult, $merger, $formType)
    {
        /** @var bool $actualResult */
        $actualResult = $this->service->isResponsibleForClass($merger, $formType);

        $this->assertEquals($expectedResult, $actualResult);
    }

    /**
     * @dataProvider getExceptionalClassData
     * @test
     * @param $merger
     * @param $formType
     */
    public function testIsResponsibleForNotExistingClass($merger, $formType)
    {
        $this->expectException(\Exception::class);

        $this->service->isResponsibleForClass($merger, $formType);

    }

    /**
     * @dataProvider getInterfaceTestData
     * @test
     */
    public function testIsResponsibleForInterface($expectedResult, $merger, $formType)
    {
        /** @var bool $actualResult */
        $actualResult = $this->service->isResponsibleForInterface($merger, $formType);

        $this->assertEquals($expectedResult, $actualResult);
    }

    /**
     * @return array
     */
    public function getClassTestData()
    {

        $mock = $this->createMock(OptionsMergerInterface::class);

        $mock->method("getApplicableClasses")->willReturn(array(RepeatedType::class));

        return array(
            "merger is responsible" => array(
                "expectedResult" => true,
                "merger" => $mock,
                "formType" => new RepeatedType()
            ),
            "merger is not responsible" => array(
                "expectedResult" => false,
                "merger" => $mock,
                "formType" => new FormType()
            )
        );
    }

    /**
     * @return array
     */
    public function getExceptionalClassData()
    {
        $mock = $this->createMock(OptionsMergerInterface::class);

        $mock->method("getApplicableClasses")->willReturn(array("Some\\NonExisting\\Class"));

        return array(
            "throws exception because class not found" => array(
                "merger" => $mock,
                "formType" => new RepeatedType()
            ),
        );
    }

    /**
     * @return array
     */
    public function getInterfaceTestData()
    {

        $mock = $this->createMock(OptionsMergerInterface::class);

        $mock->method("getApplicableInterface")->willReturn(FormTypeInterface::class);

        return array(
            "implements FormInterface 1" => array(
                "expectedResult" => true,
                "merger" => $mock,
                "formType" => new RepeatedType()
            ),
            "implements FormInterface 2" => array(
                "expectedResult" => true,
                "merger" => $mock,
                "formType" => new FormType()
            )
        );
    }
}
