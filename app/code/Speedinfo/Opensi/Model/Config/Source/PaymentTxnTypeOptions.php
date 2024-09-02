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

class PaymentTxnTypeOptions implements \Magento\Framework\Option\ArrayInterface
{
  protected $_orderTransactionCollection;

  /**
	 * Constructor
   *
   * @param \Magento\Sales\Model\ResourceModel\Order\Payment\Transaction\Collection $orderTransactionCollection
	 */
  public function __construct(\Magento\Sales\Model\ResourceModel\Order\Payment\Transaction\Collection $orderTransactionCollection)
  {
    $this->_orderTransactionCollection = $orderTransactionCollection;
  }

  /**
   * Options getter
   *
   * @return array
   */
  public function toOptionArray()
  {
    // Get used payment methods
    $transactionTypes = $this->_orderTransactionCollection->getSelect()->group('txn_type');
    $transactionTypes = $this->_orderTransactionCollection->load();

    $paymentTxnTypesArray = array();
    $paymentTxnTypesArray[] = array('label' => __('-- Please Select --'), 'value' => '');

    if ($transactionTypes->getSize())
    {
      foreach ($transactionTypes as $key => $value)
      {
        if (null !== $value->getTxnType())
        {
          $paymentTxnTypesArray[] = array(
            'label'   => ucfirst($value->getTxnType()),
            'value' => $value->getTxnType(),
          );
        }
      }
    } else {
      $paymentTxnTypesArray[] = array(
        'label' => 'Capture',
        'value' => 'capture'
      );
    }

    // Return
    return $paymentTxnTypesArray;
  }
}
