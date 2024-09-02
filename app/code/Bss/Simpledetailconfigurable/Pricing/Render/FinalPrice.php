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
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\Simpledetailconfigurable\Pricing\Render;

use Bss\Simpledetailconfigurable\CustomerData\ConfigurableItem;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Pricing\Renderer\SalableResolverInterface;
use Magento\Catalog\Pricing\Price\MinimalPriceCalculatorInterface;
use Magento\ConfigurableProduct\Pricing\Price\ConfigurableOptionsProviderInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Pricing\Price\PriceInterface;
use Magento\Framework\Pricing\Render\RendererPool;
use Magento\Framework\Pricing\SaleableInterface;
use Magento\Framework\View\Element\Template\Context;

class FinalPrice extends \Magento\ConfigurableProduct\Pricing\Render\FinalPriceBox
{
    /**
     * @var ConfigurableItem
     */
    protected $configurableItem;

    /**
     * Get child product by selection
     *
     * @return ProductInterface|Product|null
     * @throws NoSuchEntityException
     */
    public function getChildByPreselect()
    {
        return $this->configurableItem->getChildByPreselect();
    }

    /**
     * To html
     *
     * @return string
     */
    public function toHtml()
    {
        if ($this->getRequest()->getParam("load_html") == 1) {
            $this->getRequest()->setParam("load_html", 0);
            return $this->_toHtml();
        }
        return parent::toHtml();
    }

    public function getConfigurableItem()
    {
        return $this->configurableItem;
    }
}
