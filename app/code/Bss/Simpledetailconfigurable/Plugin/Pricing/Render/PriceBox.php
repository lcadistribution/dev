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
namespace Bss\Simpledetailconfigurable\Plugin\Pricing\Render;

class PriceBox
{
    /**
     * @var \Bss\Simpledetailconfigurable\CustomerData\ConfigurableItem
     */
    protected $configurableItem;

    /**
     * @param \Bss\Simpledetailconfigurable\CustomerData\ConfigurableItem $configurableItem
     */
    public function __construct(\Bss\Simpledetailconfigurable\CustomerData\ConfigurableItem $configurableItem)
    {
        $this->configurableItem = $configurableItem;
    }

    public function afterGetPriceType(\Magento\Framework\Pricing\Render\PriceBox $subject, $result, $priceCode)
    {
        $schema = ($subject->getZone() == 'item_view') ? true : false;
        $childByPreselect = $this->configurableItem->getChildByPreselect();

        if ($childByPreselect && $childByPreselect->getPriceInfo() && $schema) {
            return $childByPreselect->getPriceInfo()->getPrice($priceCode);
        }

        return $result;
    }

    /**
     * After get configuration item
     *
     * @param \Bss\Simpledetailconfigurable\Pricing\Render\FinalPrice $subject
     * @param \Bss\Simpledetailconfigurable\CustomerData\ConfigurableItem $result
     * @return \Bss\Simpledetailconfigurable\CustomerData\ConfigurableItem
     */
    public function afterGetConfigurableItem($subject, $result)
    {
        return $this->configurableItem;
    }
}
