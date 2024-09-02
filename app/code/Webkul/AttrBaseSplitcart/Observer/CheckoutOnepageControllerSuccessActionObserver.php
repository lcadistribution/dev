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

class CheckoutOnepageControllerSuccessActionObserver implements ObserverInterface
{
    /**
     * @var \Magento\Sales\Model\Order
     */
    protected $orderFactory;
    
    /**
     * @var \Webkul\AttrBaseSplitcart\Helper\Data
     */
    protected $helper;
    
    /**
     * @param \Magento\Sales\Model\Order $orderFactory
     * @param \Webkul\AttrBaseSplitcart\Helper\Data $helper
     */
    public function __construct(
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Webkul\AttrBaseSplitcart\Helper\Data $helper
    ) {
        $this->orderFactory = $orderFactory;
        $this->helper = $helper;
    }

    /**
     * Function execute
     *
     * Executes when checkout_onepage_controller_success_action event hit,
     * and used to update virtual cart after successfully placed an order
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            $itemIds = [];
            if ($this->helper->checkAttributesplitcartStatus()) {
                $this->helper->removeCustomQuote();
                $orderIds = $observer->getOrderIds();
                $attribute = $this->helper->getSelectedAttribute();
                foreach ($orderIds as $orderId) {
                    $order = $this->getOrderInfo($orderId);
                    foreach ($order->getAllItems() as $item) {
                        $itemIds[] = $item->getId();
                        $product = $item->getProduct();
                        $attributeCode = $product->getData($attribute);
                        if ($attributeCode=="") {
                            $attributeCode = -1;
                        }
                    }
                }
                $this->helper->logDataInLogger(
                    "attributeCode : ".$attributeCode
                );
                $this->helper->updateCart();
                $this->helper->updateVirtualCart($attributeCode);
                $this->helper->addVirtualCartToQuote();
            }
        } catch (\Exception $e) {
            $this->helper->logDataInLogger(
                "CheckoutOnepageControllerSuccessActionObserver execute : ".$e->getMessage()
            );
        }
    }
    /**
     * GetOrderInfo function
     *
     * @param int $orderId
     * @return void
     */
    public function getOrderInfo($orderId)
    {
          $orderInformation = null;
        try {
            $orderInformation = $this->orderFactory->create()->load($orderId);
        } catch (\Exception $e) {
            $this->helper->logDataInLogger(
                "CheckoutOnepageControllerSuccessActionObserver getOrderInfo : ".$e->getMessage()
            );
        }
        return $orderInformation;
    }
}
