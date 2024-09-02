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

namespace Webkul\AttrBaseSplitcart\Plugin\Checkout\CustomerData;

use Magento\Framework\Exception\LocalizedException;

/**
 * Cart source
 */
class Cart
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @param \Magento\Checkout\Model\Session $checkoutSession
     */
    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * Function afterGetSectionData updates the result from checkout session
     *
     * @param \Magento\Checkout\CustomerData\Cart $subject
     * @param object $result
     * @return object
     */
    public function afterGetSectionData(
        \Magento\Checkout\CustomerData\Cart $subject,
        $result
    ) {
        if ($this->checkoutSession->getWkCustomQuote()) {
            return $this->checkoutSession->getWkCustomQuote();
        }
        return $result;
    }
}
