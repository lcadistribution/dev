<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_Simpledetailconfigurable
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2024 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\Simpledetailconfigurable\ViewModel\Framework\Pricing;
use Magento\Store\Model\StoreManagerInterface;

class Render implements \Magento\Framework\View\Element\Block\ArgumentInterface
{
    /**
     * @var \Bss\Simpledetailconfigurable\Model\Product
     */
    protected $product;

    /**
     * @param \Bss\Simpledetailconfigurable\Model\Product $product
     */
    public function __construct(
        \Bss\Simpledetailconfigurable\Model\Product $product
    ) {
        $this->product = $product;
    }

    /**
     * Get child product
     *
     * @param \Magento\Catalog\Api\Data\ProductInterface $saleableItem
     * @return \Magento\Catalog\Api\Data\ProductInterface|\Magento\Catalog\Model\Product|mixed|null
     */
    public function getProduct($saleableItem)
    {
        return $this->product->getProduct($saleableItem);
    }
}
