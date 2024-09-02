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

class ShippingMethods extends \Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray
{
  /**
   * @var $_shippingMethodsOpensi \Speedinfo\Opensi\Block\Adminhtml\Form\Field\ShippingMethodsOpensi
   * @var $_shippingMethodsMagento \Speedinfo\Opensi\Block\Adminhtml\Form\Field\ShippingMethodsMagento
   */
  protected $_shippingMethodsOpensi;
  protected $_shippingMethodsMagento;

  /**
   * Get OpenSi shipping methods options
   *
   * @return \Speedinfo\Opensi\Block\Adminhtml\Form\Field\ShippingMethodsOpensi
   */
  protected function _getShippingMethodsOpensiRenderer()
  {
    if (!$this->_shippingMethodsOpensi) {
      $this->_shippingMethodsOpensi = $this->getLayout()->createBlock(
        '\Speedinfo\Opensi\Block\Adminhtml\Form\Field\ShippingMethodsOpensi',
        '',
        ['data' => ['is_render_to_js_template' => true]]
      );
    }

    return $this->_shippingMethodsOpensi;
  }


  /**
   * Get Magento shipping methods options
   *
   * @return \Speedinfo\Opensi\Block\Adminhtml\Form\Field\ShippingMethodsMagento
   */
  protected function _getShippingMethodsMagentoRenderer()
  {
    if (!$this->_shippingMethodsMagento) {
      $this->_shippingMethodsMagento = $this->getLayout()->createBlock(
        '\Speedinfo\Opensi\Block\Adminhtml\Form\Field\ShippingMethodsMagento',
        '',
        ['data' => ['is_render_to_js_template' => true]]
      );
    }

    return $this->_shippingMethodsMagento;
  }


  /**
   * Prepare to render
   *
   * @return void
   */
  protected function _prepareToRender()
  {
    $this->addColumn('opensi_shipping_method', ['label' => __('OpenSi Shipping Method'), 'renderer' => $this->_getShippingMethodsOpensiRenderer()]);
    $this->addColumn('magento_shipping_method', ['label' => __('Magento Shipping Method'), 'renderer' => $this->_getShippingMethodsMagentoRenderer()]);

    $this->_addAfter = false;
    $this->_addButtonLabel = __('Add');
  }


  /**
   * Prepare existing row data object
   *
   * @param \Magento\Framework\DataObject $row
   * @return void
   */
  protected function _prepareArrayRow(\Magento\Framework\DataObject $row)
  {
    $options = [];

    $shippingMethodsOpensi = $row->getData('opensi_shipping_method');
    $shippingMethodsMagento = $row->getData('magento_shipping_method');

    $key = 'option_' . $this->_getShippingMethodsOpensiRenderer()->calcOptionHash($shippingMethodsOpensi);
    $key2 = 'option_' . $this->_getShippingMethodsMagentoRenderer()->calcOptionHash($shippingMethodsMagento);

    $options[$key] = 'selected="selected"';
    $options[$key2] = 'selected="selected"';

    $row->setData('option_extra_attrs', $options);
  }
}
