<?php
/**
 * @package Magedelight_Customerprice for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */
namespace Magedelight\Customerprice\Pricing\Render\Configurable;

use Magento\Catalog\Pricing\Price\TierPrice;

class TierPriceBox extends \Magento\ConfigurableProduct\Pricing\Render\FinalPriceBox
{

	/**
     * @inheritdoc
     */
    public function toHtml()
    {
        // Hide tier price block in case of MSRP or in case when no options with tier price.
        if (!$this->isMsrpPriceApplicable() && $this->isTierPriceApplicable()) {
            return parent::toHtml();
        }
        return '';
    }

    /**
     * Check if at least one of simple products has tier price.
     *
     * @return bool
     */
    private function isTierPriceApplicable()
    {
        $product = $this->getSaleableItem();
        foreach ($product->getTypeInstance()->getUsedProducts($product) as $simpleProduct) {
            if ($simpleProduct->isSalable() &&
                !empty($simpleProduct->getPriceInfo()->getPrice(TierPrice::PRICE_CODE)->getTierPriceList())
            ) {
                return true;
            }
        }
        return true;
    }
}
