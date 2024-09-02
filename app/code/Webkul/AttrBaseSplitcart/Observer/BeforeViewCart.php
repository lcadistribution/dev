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
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\UrlInterface;
use Webkul\AttrBaseSplitcart\Helper\Data;

class BeforeViewCart implements ObserverInterface
{
    /**
     * @var \Magento\Framework\Event\ObserverInterface
     */
    protected $messageManager;
    
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $urlInterface;
    
    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $helper;
    
    /**
     * @param ManagerInterface $messageManager
     * @param UrlInterface $urlInterface
     * @param Data $helper
     */
    public function __construct(
        ManagerInterface $messageManager,
        UrlInterface $urlInterface,
        Data $helper
    ) {
        $this->messageManager = $messageManager;
        $this->urlInterface = $urlInterface;
        $this->helper = $helper;
    }

    /**
     * [executes on controller_action_predispatch_checkout_index_index event]
     *
     * @param \Magento\Framework\Event\Observer $observer
     *
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            $result = $this->helper->checkSplitCart();
            $session = $this->helper->getCheckoutRemoveSession();

            if (count($result)>1
                && $this->helper->checkAttributesplitcartStatus()
                && (!$session || $session!==1 || $session==null)
            ) {
                $message = __("At a time you can checkout with products single attribute value type. ");
                $message .= __("Remaining other products will be saved into your cart.");
                $this->messageManager->addError($message);
                $url = $this->urlInterface->getUrl('checkout/cart');
                $observer->getControllerAction()
                    ->getResponse()
                    ->setRedirect($url);
            }
        } catch (\Exception $e) {
            $this->helper->logDataInLogger(
                "Observer_BeforeViewCart_execute Exception : ".$e->getMessage()
            );
        }
    }
}
