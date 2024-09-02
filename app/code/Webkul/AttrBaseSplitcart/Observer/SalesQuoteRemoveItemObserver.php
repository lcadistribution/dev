<?php
/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_AttrBaseSplitcart
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\AttrBaseSplitcart\Observer;

use Magento\Framework\Event\ObserverInterface;
use Webkul\AttrBaseSplitcart\Helper\Data;
use Magento\Framework\Exception\LocalizedException;

class SalesQuoteRemoveItemObserver implements ObserverInterface
{
    /**
     * @var \Webkul\AttrBaseSplitcart\Helper\Data
     */
    protected $helper;

    /**
     * @param Data $helper
     */
    public function __construct(
        Data $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * Function execute
     *
     * Executes when sales_quote_remove_item event hit
     * and used to update virtual cart when any item is removed from sales quote
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            $quoteItem = $observer->getQuoteItem();
            $itemId = $quoteItem->getItemId();

            $virtualCart = $this->helper->getVirtualCart();
            $removeItemCheck = $this->helper->getCheckoutRemoveSession();
            $moduleEnabledCheck = $this->helper->checkAttributesplitcartStatus();

            if ($virtualCart
                && is_array($virtualCart)
                && $virtualCart !== ""
                && $moduleEnabledCheck
                && (!$removeItemCheck
                || $removeItemCheck !== 1
                || $removeItemCheck == null)
            ) {
                $this->manageVirtualCart($virtualCart, $itemId);
            }
        } catch (\Exception $e) {
            $this->helper->logDataInLogger("SalesQuoteRemoveItemObserver execute : ".$e->getMessage());
        }
    }
    
    /**
     * Function manageVirtualCart
     *
     * @param array $virtualCart
     * @param integer $itemId
     * @return mixed
     */
    private function manageVirtualCart($virtualCart, $itemId)
    {
        foreach ($virtualCart as $attributeOptionId => $attributeOptionArray) {
            foreach ($attributeOptionArray as $productId => $productData) {
                if ($productId !== "grouped"
                    && $productData['item_id'] == $itemId
                ) {
                    unset($virtualCart[$attributeOptionId][$productId]);
                } elseif ($productId == "grouped") {
                    foreach ($productData as $groupProId => $groupInner) {
                        if ($groupInner['item_id'] == $itemId
                        ) {
                            unset($virtualCart[$attributeOptionId]['grouped'][$groupProId]);
                        }
                    }
                }
            }
            if (array_key_exists('grouped', $virtualCart[$attributeOptionId])
                && empty($virtualCart[$attributeOptionId]['grouped'])
            ) {
                unset($virtualCart[$attributeOptionId]['grouped']);
            }
            $check = $this->helper->checkEmptyVirtualCart(
                $virtualCart[$attributeOptionId]
            );
            if ($check) {
                unset($virtualCart[$attributeOptionId]);
            }
        }
        $this->helper->setAttributeVirtualCart($virtualCart);
    }
}
