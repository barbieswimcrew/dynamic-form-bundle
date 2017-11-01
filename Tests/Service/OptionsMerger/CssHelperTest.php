<?php
/**
 * Created by PhpStorm.
 * User: anton
 * Date: 31.10.17
 * Time: 13:38
 */

namespace Barbieswimcrew\Bundle\DynamicFormBundle\Tests\Service\OptionsMerger;

use Barbieswimcrew\Bundle\DynamicFormBundle\Service\OptionsMerger\Merger\CssHelper;

class CssHelperTest extends \PHPUnit_Framework_TestCase
{
    const TEST_CSS_HIDDENCLASS = "hidden";

    /** @var CssHelper */
    private $service;

    protected function setUp()
    {
        $this->service = new CssHelper(self::TEST_CSS_HIDDENCLASS);
    }

    /**
     * @dataProvider getHandleCssClassesData
     * @test
     * @param $expectedResult
     * @param $originAttr
     * @param $overrideAttr
     * @param $hidden
     */
    public function test_handleCssClasses($expectedResult, $originAttr, $overrideAttr, $hidden)
    {
        $result = $this->service->handleCssClasses($originAttr, $overrideAttr, $hidden);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @dataProvider getHandleHiddenClassData
     * @test
     * @param $expectedResult
     * @param $testValue
     * @param $hidden
     */
    public function test_handleHiddenClass($expectedResult, $testValue, $hidden)
    {
        $result = $this->service->handleHiddenClass($testValue, $hidden);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @test
     * @dataProvider getAppendHiddenClassData
     * @param $expectedResult
     * @param $testValue
     */
    public function test_appendHiddenClass($expectedResult, $testValue)
    {
        $result = $this->service->appendHiddenClass($testValue);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @dataProvider getRemoveHiddenClassData
     * @test
     * @param $expectedResult
     * @param $testValue
     */
    public function test_removeHiddenClass($expectedResult, $testValue)
    {
        $result = $this->service->removeHiddenClass($testValue);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @dataProvider getMergeClassesData
     * @test
     * @param array $expectedResult
     * @param array $originClasses
     * @param array $overrideClasses
     */
    public function test_mergeClasses(array $expectedResult, array $originClasses, array $overrideClasses)
    {
        $result = $this->service->mergeClasses($originClasses, $overrideClasses);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @dataProvider getExplodeClassesData
     * @test
     * @param $testValue
     * @param $expectedResult
     */
    public function test_explodeClasses($testValue, $expectedResult)
    {
        $result = $this->service->explodeClasses($testValue);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @dataProvider getImplodeClassesData
     * @test
     * @param $testValue
     * @param $expectedResult
     */
    public function test_implodeClasses($testValue, $expectedResult)
    {
        $result = $this->service->implodeClasses($testValue);

        $this->assertEquals($expectedResult, $result);
    }

    # # #
    # # # DATA PROVIDERS
    # # #

    public function getImplodeClassesData()
    {
        return array(
            "test 1" => array(
                "testValue" => array("form-group", "row"),
                "expectedResult" => "form-group row",
            ),
            "test 2" => array(
                "testValue" => array(),
                "expectedResult" => "",
            ),
            "test 3" => array(
                "testValue" => array(""),
                "expectedResult" => "",
            ),
        );
    }

    public function getExplodeClassesData()
    {
        return array(
            "test 1" => array(
                "testValue" => "form-group row",
                "expectedResult" => array("form-group", "row"),
            ),
            "test 2" => array(
                "testValue" => "form-group",
                "expectedResult" => array("form-group"),
            ),
            "test 3" => array(
                "testValue" => "",
                "expectedResult" => array(""),
            ),
        );
    }

    public function getMergeClassesData()
    {
        return array(
            "test 1" => array(
                "expectedResult" => array("form-group", "row"),
                "originClasses" => array("form-group"),
                "overrideClasses" => array("row"),
            ),
            "test 2" => array(
                "expectedResult" => array("form-group", "row", "hidden"),
                "originClasses" => array("form-group"),
                "overrideClasses" => array("row", "hidden"),
            ),
            "test 3" => array(
                "expectedResult" => array("hidden"),
                "originClasses" => array(),
                "overrideClasses" => array("hidden"),
            ),
            "test 4" => array(
                "expectedResult" => array(),
                "originClasses" => array(),
                "overrideClasses" => array(),
            ),
        );
    }

    public function getRemoveHiddenClassData()
    {
        return array(
            "test 1" => array(
                "expectedResult" => array(),
                "testValue" => array(self::TEST_CSS_HIDDENCLASS),
            ),
            "test 2" => array(
                "expectedResult" => array(),
                "testValue" => array(),
            ),
            "test 3" => array(
                "expectedResult" => array("form-group"),
                "testValue" => array("form-group"),
            ),
            "test 4" => array(
                "expectedResult" => array("form-group"),
                "testValue" => array("form-group", self::TEST_CSS_HIDDENCLASS),
            ),
        );
    }

    public function getAppendHiddenClassData()
    {
        return array(
            "test 1" => array(
                "expectedResult" => array("form-group", self::TEST_CSS_HIDDENCLASS),
                "testValue" => array("form-group"),
            ),
            "test 2" => array(
                "expectedResult" => array(self::TEST_CSS_HIDDENCLASS),
                "testValue" => array(),
            ),
        );
    }

    public function getHandleHiddenClassData()
    {
        return array(
            "remove hidden class 1" => array(
                "expectedResult" => array("form-group"),
                "testValue" => array("form-group"),
                "hidden" => false
            ),
            "remove hidden class 2" => array(
                "expectedResult" => array("form-group"),
                "testValue" => array("form-group", self::TEST_CSS_HIDDENCLASS),
                "hidden" => false
            ),
            "add hidden class 1" => array(
                "expectedResult" => array("form-group", self::TEST_CSS_HIDDENCLASS),
                "testValue" => array("form-group"),
                "hidden" => true
            ),
            "add hidden class 2" => array(
                "expectedResult" => array("form-group", self::TEST_CSS_HIDDENCLASS),
                "testValue" => array("form-group", self::TEST_CSS_HIDDENCLASS),
                "hidden" => true
            ),
        );
    }

    public function getHandleCssClassesData()
    {
        return array(
            "test 1" => array(
                "expectedResult" => self::TEST_CSS_HIDDENCLASS,
                "originClasses" => array(),
                "overrideClasses" => array(),
                "hidden" => true
            ),
            "test 2" => array(
                "expectedResult" => "form-row " . self::TEST_CSS_HIDDENCLASS,
                "originClasses" => array(),
                "overrideClasses" => array(
                    "class" => "form-row"
                ),
                "hidden" => true
            ),
            "test 3" => array(
                "expectedResult" => "test form-row " . self::TEST_CSS_HIDDENCLASS,
                "originClasses" => array(
                    "class" => "test"
                ),
                "overrideClasses" => array(
                    "class" => "form-row"
                ),
                "hidden" => true
            ),
            "test 4" => array(
                "expectedResult" => "test form-row",
                "originClasses" => array(
                    "class" => "test"
                ),
                "overrideClasses" => array(
                    "class" => "form-row"
                ),
                "hidden" => false
            ),
        );
    }
}
