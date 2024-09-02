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

/**
 * Webkul AttrBaseSplitcart ShoppingCart Observer
 */
class ShoppingCart implements ObserverInterface
{
    /**
     * @var \Webkul\AttrBaseSplitcart\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @param \Webkul\AttrBaseSplitcart\Helper\Data $helper
     * @param \Magento\Checkout\Model\Session $checkoutSession
     */
    public function __construct(
        \Webkul\AttrBaseSplitcart\Helper\Data $helper,
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
        $this->helper = $helper;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * Function execute
     *
     * Executes on controller_action_predispatch_checkout_cart_index event
     *  and used to add virtual cart items into quote
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            if ($this->helper->checkAttributesplitcartStatus()) {
                $this->helper->removeCustomQuote();
                if ($this->checkoutSession->getTempFirstCheckoutCheck()==1) {
                    $this->helper->addVirtualCartToQuote();
                    $this->checkoutSession->unsTempFirstCheckoutCheck();
                    $this->checkoutSession->setTempFirstCheckoutCheckForView(1);
                } else {
                    $this->helper->addQuoteToVirtualCart();
                }
                
            }
        } catch (\Exception $e) {
            $this->helper->logDataInLogger(
                "Observer_ShoppingCart_execute Exception : ".$e->getMessage()
            );
        }
    }
}
