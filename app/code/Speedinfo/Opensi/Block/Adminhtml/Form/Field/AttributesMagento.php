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

class AttributesMagento extends \Magento\Framework\View\Element\Html\Select
{
  /**
   * Model Attributes
   *
   * @var \Magento\Config\Model\Config\Source\Allmethods
   */
  protected $_allAttributes;


  /**
   * Shipping Methods constructor (Magento)
   *
   * @param \Magento\Framework\View\Element\Context $context
   * @param \Magento\Config\Model\Config\Source\Allmethods $allMethods
   * @param array $data
   */
  public function __construct (
    \Magento\Framework\View\Element\Context $context,
    \Speedinfo\Opensi\Model\Config\Source\AttributeOptions $allAttributes,
    array $data = []
  ) {
    parent::__construct($context, $data);

    $this->_allAttributes = $allAttributes;
  }

  /**
   * @param string $value
   * @return Speedinfo\Opensi\Block\Adminhtml\Form\Field\AttributesMagento
   */
  public function setInputName($value)
  {
    return $this->setName($value);
  }


  /**
   * Render html
   *
   * @return mixed
   */
  public function _toHtml()
  {
    if (!$this->getOptions()) {
      $attributes = $this->_allAttributes->toOptionArray();

      foreach ($attributes as $attribute) {
        $this->addOption($attribute['value'], $attribute['label']);
      }
    }

    return parent::_toHtml();
  }
}
