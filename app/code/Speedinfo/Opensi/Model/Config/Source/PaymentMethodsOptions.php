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

class PaymentMethodsOptions implements \Magento\Framework\Option\ArrayInterface
{
	protected $_paymentHelper;

  /**
	 * Constructor
   *
   * @param \Magento\Payment\Helper\Data $paymentHelper
	 */
  public function __construct(\Magento\Payment\Helper\Data $paymentHelper)
  {
    $this->_paymentHelper = $paymentHelper;
  }

  /**
   * Options getter
   *
   * @return array
   */
  public function toOptionArray()
  {
    $labels = array();
    $paymentMethods = array();
    $paymentMethods[] = array('value' => '', 'label' => __('-- Please Select --'));

    // Get payment methods
    if (null !== $this->_paymentHelper->getPaymentMethods())
    {
      foreach ($this->_paymentHelper->getPaymentMethods() as $key => $paymentMethod)
      {
        $paymentMethods[] = array(
          'value' => $key,
          'label' => (isset($paymentMethod['title'])?$paymentMethod['title']:$key)
        );
      }
    }

    // Sort payment methods label
    foreach ($paymentMethods as $paymentMethod) {
      $labels[] = $paymentMethod['label'];
    }

    array_multisort($labels, SORT_ASC, $paymentMethods);

    // Return
    return $paymentMethods;
  }
}
