<?php
/**
 * 2003-2017 OpenSi Connect
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * @category    Speedinfo
 * @package     Speedinfo_Opensi
 * @copyright   Copyright (c) 2017 Speedinfo SARL (http://www.speedinfo.fr)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Speedinfo\Opensi\Model\Config\Source;

class PaymentMethodsUsedOptions implements \Magento\Framework\Option\ArrayInterface
{
  protected $_orderPaymentCollection;

  /**
   * Constructor
   *
   * @param \Magento\Sales\Model\ResourceModel\Order\Payment\Collection $orderPaymentCollection
   */
  public function __construct(\Magento\Sales\Model\ResourceModel\Order\Payment\Collection $orderPaymentCollection)
  {
    $this->_orderPaymentCollection = $orderPaymentCollection;
  }

  /**
   * Options getter
   *
   * @return array
   */
  public function toOptionArray()
  {
    // Get used payment methods
    $paymentMethods = $this->_orderPaymentCollection->getSelect()->group('method');
    $paymentMethods = $this->_orderPaymentCollection->load();

    $paymentMethodsArray = array();
    $paymentMethodsArray[] = array('value' => '', 'label' => __('-- Please Select --'));

    foreach ($paymentMethods as $paymentMethod)
    {
      if (null !== $paymentMethod->getMethod())
      {
        $paymentMethodsArray[] = array(
          'value' => $paymentMethod->getMethod(),
          'label' => (isset($paymentMethod->getAdditionalInformation()['method_title']) && $paymentMethod->getAdditionalInformation()['method_title']?$paymentMethod->getAdditionalInformation()['method_title']:$paymentMethod->getMethod())
        );
      }
    }

    // Return
    return $paymentMethodsArray;
  }
}
