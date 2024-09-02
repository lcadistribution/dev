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

namespace Mageplaza\ConfigureGridView\Test\Unit\Block\Product;

use Magento\Catalog\Model\Product;
use Magento\Framework\Registry;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\ConfigureGridView\Block\Product\View;
use Mageplaza\ConfigureGridView\Helper\Data;
use Mageplaza\Core\Helper\AbstractData;
use PHPUnit\Framework\TestCase;
use ReflectionException;

/**
 * Class ViewTest
 * @package Magento\ConfigureGridView\Test\Unit\Block\Product
 */
class ViewTest extends TestCase
{
    /**
     * @var View
     */
    protected $view;

    /**
     * @var Registry/\PHPUnit_Framework_MockObject_MockObject
     */
    protected $registryMock;

    /**
     * @var StoreInterface/\PHPUnit\Framework\MockObject\MockObject
     */
    protected $storeInterfaceMock;

    /**
     * @var StoreManagerInterface/\PHPUnit\Framework\MockObject\MockObject
     */
    protected $storeManagerMock;

    /**
     * @var Data/\PHPUnit\Framework\MockObject\MockObject
     */
    protected $helperDataMock;

    /**
     * @var AbstractData/\PHPUnit\Framework\MockObject\MockObject
     */
    protected $abstractDataMock;

    /**
     * @throws ReflectionException
     */
    public function testIsEnable()
    {
        $store = $this->createMock(StoreInterface::class);
        $this->storeManagerMock->method('getStore')->willReturn($store);
        $storeId = '1';
        $this->storeInterfaceMock->method('getId')->willReturn($storeId);
        $productMock = $this->createMock(Product::class);
        $this->registryMock->method('registry')->with('product')->willReturn($productMock);
        $this->helperDataMock->method('checkEnableModule')->with($productMock, $storeId)->willReturn(true);

        $this->assertEquals(null, $this->view->isEnable());
    }

    /**
     * @throws ReflectionException
     */
    protected function setUp()
    {
        $helper                   = new ObjectManager($this);
        $this->registryMock       = $this->createMock(Registry::class);
        $this->storeInterfaceMock = $this->createMock(StoreInterface::class);
        $this->storeManagerMock   = $this->createMock(StoreManagerInterface::class);
        $this->helperDataMock     = $this->createMock(Data::class);
        $this->abstractDataMock   = $this->createMock(AbstractData::class);
        $this->view               = $helper->getObject(
            View::class,
            [
                'registry'       => $this->registryMock,
                'storeInterface' => $this->storeInterfaceMock,
                'storeManager'   => $this->storeManagerMock,
                'helperData'     => $this->helperDataMock,
                'abstractData'   => $this->abstractDataMock
            ]
        );
    }
}
