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

class CustomerLogInObserver implements ObserverInterface
{
    /**
     * @var \Webkul\AttrBaseSplitcart\Helper\Data
     */
    protected $helper;

    /**
     * @param \Webkul\AttrBaseSplitcart\Helper\Data $helper
     */
    public function __construct(
        \Webkul\AttrBaseSplitcart\Helper\Data $helper
    ) {
        $this->helper = $helper;
    }
    
    /**
     * [executes on controller_action_predispatch_customer_account_logoutSuccess event]
     *
     * @param \Magento\Framework\Event\Observer $observer
     *
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            $session = $this->helper->unsetCheckoutRemoveSession();
        } catch (\Exception $e) {
            $this->helper->logDataInLogger("CustomerLogInObserver execute : ".$e->getMessage());
        }
    }
}
