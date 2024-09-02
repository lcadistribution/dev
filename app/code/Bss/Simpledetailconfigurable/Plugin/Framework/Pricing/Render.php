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
namespace Bss\Simpledetailconfigurable\Plugin\Framework\Pricing;

use Magento\Framework\Pricing\SaleableInterface;
use Magento\Store\Model\StoreManagerInterface;
class Render
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
     * Set child product
     *
     * @param \Magento\Framework\Pricing\Render $subject $subject
     * @param string $priceCode
     * * @param SaleableInterface $saleableItem
     * * @param array $arguments
     * @return array
     */
    public function beforeRender($subject, $priceCode, $saleableItem, $arguments = [])
    {
        $saleableItem = $this->product->getProduct($saleableItem);
        return [$priceCode, $saleableItem, $arguments];
    }
}
