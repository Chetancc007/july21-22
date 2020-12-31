<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ShopbyBrand
 */


namespace Amasty\ShopbyBrand\Test\Unit\Block\Widget;

use Amasty\ShopbyBase\Model\ResourceModel\OptionSetting\CollectionFactory;
use Amasty\ShopbyBrand\Block\Widget\BrandSlider;
use Amasty\ShopbyBrand\Test\Unit\Traits;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Amasty\ShopbyBase\Api\Data\OptionSettingInterface;
use Magento\Eav\Model\Entity\Attribute\Option;

/**
 * Class BrandSliderTest
 *
 * @see BrandSlider
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * phpcs:ignoreFile
 */
class BrandSliderTest extends \PHPUnit\Framework\TestCase
{
    use Traits\ObjectManagerTrait;
    use Traits\ReflectionTrait;

    /**
     * @var \Amasty\ShopbyBrand\Helper\Data
     */
    private $helper;

    /**
     * @var \Amasty\ShopbyBrand\Block\Widget\BrandSlider
     */
    private $brandSlider;

    public function setup(): void
    {
        $this->helper = $this->createMock(\Amasty\ShopbyBrand\Helper\Data::class);

        $this->brandSlider = $this->getObjectManager()->getObject(
            BrandSlider::class,
            [
                'helper' => $this->helper,
            ]
        );
    }

    /**
     * @covers       BrandSlider::getItemData
     *
     * @dataProvider getTestDatabase
     *
     * @param bool $value
     * @param bool $isShowInSlider
     * @param int $brandId
     * @param null $label
     * @param null $img
     * @param null $position
     * @param null $alt
     * @param null $url
     *
     * @throws \ReflectionException
     */
    public function testGetItemData(
        $value,
        $isShowInSlider,
        $brandId = 0,
        $label = null,
        $img = null,
        $position = null,
        $alt = null,
        $url = null
    ) {
        if ($value) {
            $result = [
                'label' => $label,
                'url' => $url,
                'img' => $img,
                'position' => $position,
                'alt' => $alt,
                'brandId' => $brandId
            ];
        } else {
            $result = [];
        }

        $optionSetting = $this->createMock(\Amasty\ShopbyBase\Api\Data\OptionSettingInterface::class);
        $option = $this->createMock(\Magento\Eav\Model\Entity\Attribute\Option::class);
        $option->expects($this->any())->method('getLabel')->will($this->returnValue($label));
        $option->expects($this->any())->method('getValue')->will($this->returnValue($brandId));

        $optionSetting->expects($this->any())->method('getValue')->will($this->returnValue($value));
        $optionSetting->expects($this->any())->method('getSliderImageUrl')->will($this->returnValue($img));
        $optionSetting->expects($this->any())->method('getSliderPosition')->will($this->returnValue($position));
        $optionSetting->expects($this->any())->method('getSmallImageAlt')->will($this->returnValue($alt));
        $optionSetting->expects($this->any())->method('getIsShowInSlider')->will($this->returnValue($isShowInSlider));
        $this->helper->expects($this->any())->method('isDisplayZero')->will($this->returnValue(true));

        $resultOrigMethod = $this->invokeMethod($this->brandSlider, 'getItemData', [$option, $optionSetting]);

        $this->assertEquals($result, $resultOrigMethod);
    }

    /**
     * @return array
     */
    public function getTestDatabase()
    {
        return [
            [false, false],
            [true, true, 1, 'label', 'label', 'label', 'label'],
        ];
    }
}
