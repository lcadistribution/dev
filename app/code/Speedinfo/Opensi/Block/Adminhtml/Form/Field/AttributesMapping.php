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

 class AttributesMapping extends \Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray
 {
   /**
    * @var $_attributesRenderer \Magently\Tutorial\Block\Adminhtml\Form\Field\Activation
    */
   protected $_attributesMagento;

   /**
    * Get Magento attributes options
    *
    * @return \Speedinfo\Opensi\Model\Config\Source\AttributeOptions
    */
   protected function _getAttributesMagentoRenderer()
   {
     if (!$this->_attributesMagento) {
       $this->_attributesMagento = $this->getLayout()->createBlock(
         '\Speedinfo\Opensi\Block\Adminhtml\Form\Field\AttributesMagento',
         '',
         ['data' => ['is_render_to_js_template' => true]]
       );
     }

     return $this->_attributesMagento;
   }

   /**
    * Prepare to render.
    *
    * @return void
    */
   protected function _prepareToRender()
   {
     $this->addColumn('opensi_attribute', ['label' => __('OpenSi attribute')]);
     $this->addColumn('magento_attribute', ['label' => __('Corresponding Magento attribute'), 'renderer' => $this->_getAttributesMagentoRenderer()]);

     $this->_addAfter = false;
     $this->_addButtonLabel = __('Add');
   }

   /**
    * Prepare existing row data object.
    *
    * @param \Magento\Framework\DataObject $row
    * @return void
    */
   protected function _prepareArrayRow(\Magento\Framework\DataObject $row)
   {
       $options = [];

       $attributesMagento = $row->getData('magento_attribute');
       $key = 'option_' . $this->_getAttributesMagentoRenderer()->calcOptionHash($attributesMagento);
       $options[$key] = 'selected="selected"';
       $row->setData('option_extra_attrs', $options);
   }


   /**
    * Render array cell for prototypeJS template
    *
    * @param string $columnName
    * @return string
    * @throws \Exception
    */
   public function renderCellTemplate($columnName)
   {
     if ($columnName == "opensi_attribute") {
       $this->_columns[$columnName]['class'] = 'input-text required-entry validate-length minimum-length-1 maximum-length-30';
       $this->_columns[$columnName]['style'] = 'width:100%';
     }

     return parent::renderCellTemplate($columnName);
   }
 }
