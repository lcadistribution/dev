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

class ShippingMethodsOpensi extends \Magento\Framework\View\Element\Html\Select
{
  /**
   * Model ShippingMethodsOpensi
   *
   * @var \Speedinfo\Opensi\Model\ShippingMethodsOpensi
   */
  protected $_shippingMethodsOpensi;
  protected $_shippingMethods;


  /**
   * Shipping Methods constructor
   *
   * @param \Magento\Framework\View\Element\Context $context
   * @param \Speedinfo\Opensi\Model\ShippingMethodsOpensi $shippingMethodsOpensi
   * @param array $data
   */
  public function __construct (
    \Magento\Framework\View\Element\Context $context,
    \Speedinfo\Opensi\Model\ShippingMethodsOpensiFactory $shippingMethodsOpensi,
    array $data = []
  ) {
    parent::__construct($context, $data);

    $this->_shippingMethodsOpensi = $shippingMethodsOpensi;
  }

  /**
   * @param string $value
   * @return Speedinfo\Opensi\Block\Adminhtml\Form\Field\ShippingMethodsOpensi
   */
  public function setInputName($value)
  {
    return $this->setName($value);
  }


  /**
	 * Get OpenSi shipping methods
	 */
	protected function _getShippingMethodsOpenSi()
	{
		if (is_null($this->_shippingMethods))
		{
			$this->_shippingMethods = array();
      $resultPage = $this->_shippingMethodsOpensi->create();
      $collection = $resultPage->getCollection();

			foreach ($collection as $item)
			{
				$this->_shippingMethods[$item->getShippingMethodId()] = $item->getName();
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
			foreach ($this->_getShippingMethodsOpenSi() as $shippingMethod => $label)
      {
				$this->addOption($shippingMethod, $label);
			}
		}

		return parent::_toHtml();
  }
}
