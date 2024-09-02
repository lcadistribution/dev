<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_ConfigureGridView
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\TableCategoryView\Test\Unit\Block;

use Magento\Catalog\Model\Product;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Mageplaza\TableCategoryView\Block\CatalogProduct\ListProduct;
use Mageplaza\TableCategoryView\Helper\Data;
use PHPUnit\Framework\TestCase;
use ReflectionException;

/**
 * Class ViewTest
 * @package Magento\TableCategoryView\Test\Unit\Block
 */
class ListproductTest extends TestCase
{
    /**
     * @var Data/\PHPUnit\Framework\MockObject\MockObject
     */
    protected $helperDataMock;

    /**
     * @var ListProduct
     */
    protected $listProduct;

    /**
     * @throws ReflectionException
     */
    public function testIsPopup()
    {
        $productMock = $this->createMock(Product::class);
        $productMock->method('getTypeId')->willReturn('simple');

        $this->helperDataMock->method('getPopupOption')->willReturn([0 => 'simple', 1 => 'bundle']);

        $this->assertEquals(true, $this->listProduct->isPopup($productMock));
    }

    /**
     * test function getButtonInf
     */
    public function testGetButtonInf()
    {
        $typeMock = 'text';

        $this->helperDataMock->method('getAddButtonText')->willReturn('Add To Cart');
        $this->assertEquals('Add To Cart', $this->listProduct->getButtonInf($typeMock));
    }

    /**
     * @throws ReflectionException
     */
    protected function setUp()
    {
        $helper               = new ObjectManager($this);
        $this->helperDataMock = $this->createMock(Data::class);
        $this->listProduct    = $helper->getObject(
            ListProduct::class,
            [
                'helperData' => $this->helperDataMock,
            ]
        );
    }
}
