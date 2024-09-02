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
use Magento\Framework\View\Result\Page as ResultPage;

/**
 * Webkul AttrBaseSplitcart ShoppingCartPost Observer
 */
class ShoppingCartPost implements ObserverInterface
{
    /**
     * @var \Webkul\AttrBaseSplitcart\Helper\Data
     */
    protected $helper;

    /**
     * @var ResultPage
     */
    protected $resultPage;

    /**
     * @param \Webkul\AttrBaseSplitcart\Helper\Data $helper
     * @param ResultPage $resultPage
     */
    public function __construct(
        \Webkul\AttrBaseSplitcart\Helper\Data $helper,
        ResultPage $resultPage
    ) {
        $this->helper = $helper;
        $this->resultPage = $resultPage;
    }

    /**
     * Function execute
     *
     * Executes on controller_action_predispatch_checkout_cart_index event
     * and used to add virtual cart items into quote
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            if ($this->helper->checkAttributesplitcartStatus()) {
                $this->resultPage->getLayout()->unsetElement('cart.summary');
                $this->resultPage->getLayout()->unsetElement('checkout.cart.coupon');
            }
            
        } catch (\Exception $e) {
            $this->helper->logDataInLogger("Observer_ShoppingCartPost execute : ".$e->getMessage());
        }
    }
}
