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

namespace Speedinfo\Opensi\Block\Adminhtml\Form\Field;

class ShippingMethodsMagento extends \Magento\Framework\View\Element\Html\Select
{
  /**
   * Model Allmethods
   *
   * @var \Magento\Config\Model\Config\Source\Allmethods
   */
  protected $_allCarriers;
  protected $_shippingMethods;


  /**
   * Shipping Methods constructor (Magento)
   *
   * @param \Magento\Framework\View\Element\Context $context
   * @param \Magento\Shipping\Model\Config $allCarriers
   * @param array $data
   */
  public function __construct (
    \Magento\Framework\View\Element\Context $context,
    \Magento\Shipping\Model\Config $allCarriers,
    array $data = []
  ) {
    parent::__construct($context, $data);

    $this->_allCarriers = $allCarriers;
  }

  /**
   * @param string $value
   * @return Speedinfo\Opensi\Block\Adminhtml\Form\Field\ShippingMethodsMagento
   */
  public function setInputName($value)
  {
    return $this->setName($value);
  }


  /**
	 * Get Magento shipping methods
	 */
	protected function _getShippingMethodsMagento()
	{
		if (is_null($this->_shippingMethods))
		{
			$this->_shippingMethods = array();
      $collection = $this->_allCarriers->getAllCarriers();

			foreach ($collection as $code => $carrier)
			{
        if ($carrier->getConfigData('title')) {
          $title = $carrier->getConfigData('title');
        } else {
          $title = $code;
        }

        if ($name = $carrier->getConfigData('name')) {
          $this->_shippingMethods[$code] = $title.' - '.$name;
        } else {
          $this->_shippingMethods[$code] = $title;
        }
			}
		}

		return $this->_shippingMethods;
	}


  /**
   * Render html
   *
   * @return mixed
   */
  public function _toHtml()
  {
    if (!$this->getOptions()) {
			foreach ($this->_getShippingMethodsMagento() as $shippingMethod => $label)
      {
				$this->addOption($shippingMethod, $label);
			}
		}

    return parent::_toHtml();
  }
}
