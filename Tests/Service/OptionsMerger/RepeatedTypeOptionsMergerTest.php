<?php
/**
 * Created by PhpStorm.
 * User: anton
 * Date: 31.10.17
 * Time: 14:49
 */

namespace Barbieswimcrew\Bundle\DynamicFormBundle\Tests\Service\OptionsMerger;

use Barbieswimcrew\Bundle\DynamicFormBundle\Service\OptionsMerger\Base\OptionsMergerInterface;
use Barbieswimcrew\Bundle\DynamicFormBundle\Service\OptionsMerger\Merger\CssHelper;
use Barbieswimcrew\Bundle\DynamicFormBundle\Service\OptionsMerger\Merger\RepeatedTypeOptionsMerger;
use Barbieswimcrew\Bundle\DynamicFormBundle\Service\OptionsMerger\Merger\ScalarFormTypeOptionsMerger;

class RepeatedTypeOptionsMergerTest extends \PHPUnit_Framework_TestCase
{
    /** @var OptionsMergerInterface */
    private $service;
    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $cssMock;

    protected function setUp()
    {
        $scalarMock = $this->createMock(ScalarFormTypeOptionsMerger::class);
        $this->cssMock = $this->createMock(CssHelper::class);

        $this->service = new RepeatedTypeOptionsMerger($scalarMock, $this->cssMock);
    }

    public function test_mergeOptions()
    {
        $this->cssMock->method("handleHiddenClass")->willReturn(array());
        $this->cssMock->expects($this->exactly(2))->method("handleHiddenClass");

        $this->service->mergeOptions(array(), array(), false);

    }

    public function test_mergeOptions_2()
    {
        $this->cssMock->method("handleHiddenClass")->willReturn(array());
        $this->cssMock->expects($this->exactly(2))->method("handleHiddenClass");

        $this->service->mergeOptions(array("options" => array()), array(), false);

    }

    public function test_mergeOptions_3()
    {
        $this->cssMock->method("handleHiddenClass")->willReturn(array());
        $this->cssMock->expects($this->exactly(2))->method("handleHiddenClass");
        $this->cssMock->expects($this->exactly(1))->method("explodeClasses")->willReturn(array());

        $this->service->mergeOptions(array("options" => array("attr" => array("class" => "test"))), array(), false);

    }

    public function test_mergeOptions_4()
    {
        $this->cssMock->method("handleHiddenClass")->willReturn(array());
        $this->cssMock->expects($this->exactly(2))->method("handleHiddenClass");
        $this->cssMock->expects($this->exactly(2))->method("explodeClasses")->willReturn(array());

        $this->service->mergeOptions(array("options" => array(
            "attr" => array("class" => "test"),
            "label_attr" => array("class" => "test")
        )), array(), false);

    }
}
