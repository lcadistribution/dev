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

namespace Webkul\AttrBaseSplitcart\Plugin\Checkout\Model;

use Webkul\AttrBaseSplitcart\Helper\Data;
use Webkul\AttrBaseSplitcart\Logger\AttrBaseLogger;
use Magento\Framework\Exception\CouldNotSaveException;

class PaymentInformationManagement
{
    /**
     * @var \Webkul\AttrBaseSplitcart\Helper\Data
     */
    protected $helper;
    
    /**
     * @var \Webkul\AttrBaseSplitcart\Logger\AttrBaseLogger
     */
    protected $logger;
    
    /**
     * @param Data $helper
     * @param AttrBaseLogger $logger
     */
    public function __construct(
        Data $helper,
        AttrBaseLogger $logger
    ) {
        $this->helper = $helper;
        $this->logger = $logger;
    }

    /**
     * Function afterGetSectionData updates the result from checkout session
     *
     * @param \Magento\Checkout\Model\PaymentInformationManagement $subject
     * @param \Closure $proceed
     * @param integer $cartId
     * @param \Magento\Quote\Api\Data\PaymentInterface $paymentMethod
     * @param \Magento\Quote\Api\Data\AddressInterface $billingAddress
     * @return void
     */
    public function aroundSavePaymentInformationAndPlaceOrder(
        \Magento\Checkout\Model\PaymentInformationManagement $subject,
        \Closure $proceed,
        $cartId,
        \Magento\Quote\Api\Data\PaymentInterface $paymentMethod,
        \Magento\Quote\Api\Data\AddressInterface $billingAddress
    ) {
        try {
            $result = $this->helper->checkSplitCart();
            $session = $this->helper->getCheckoutRemoveSession();

            if (count($result)>1
                && $this->helper->checkAttributesplitcartStatus()
                && (!$session || $session!==1 || $session==null)
            ) {
                throw new CouldNotSaveException(
                    __('Invalid checkout')
                );
            } else {
                return $proceed($cartId, $paymentMethod, $billingAddress);
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            throw new CouldNotSaveException(
                __($e->getMessage()),
                $e
            );
        } catch (\Exception $e) {
            $this->logger->critical($e);
            throw new CouldNotSaveException(
                __($e->getMessage()),
                $e
            );
        }
    }
}
