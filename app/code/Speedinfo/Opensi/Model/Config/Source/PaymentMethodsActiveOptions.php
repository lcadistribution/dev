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

class PaymentMethodsActiveOptions implements \Magento\Framework\Option\ArrayInterface
{
  protected $_scopeConfigInterface;
  protected $_paymentConfig;

  /**
	 * Constructor
   *
   * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfigInterface
   * @param \Magento\Payment\Model\Config $paymentConfig
	 */
  public function __construct(
    \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfigInterface,
    \Magento\Payment\Model\Config $paymentConfig)
  {
    $this->_scopeConfigInterface = $scopeConfigInterface;
    $this->_paymentConfig = $paymentConfig;
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

    // Get active payment methods
    foreach ($this->_paymentConfig->getActiveMethods() as $key => $paymentMethod)
    {
      $paymentTitle = $this->_scopeConfigInterface->getValue('payment/'.$key.'/title', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

      if (null !== $paymentTitle)
      {
        $paymentMethods[] = array(
          'value' => $key,
          'label' => ($paymentTitle?$paymentTitle:$key)
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
