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

class AttributeOptions implements \Magento\Framework\Option\ArrayInterface
{
  protected $_attributeOptionsCollection;

  /**
   * Construct
   */
  public function __construct(\Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection $attributeOptionsCollection)
  {
    $this->_attributeOptionsCollection = $attributeOptionsCollection;
  }

  /**
   * Options getter
   *
   * @return array
   */
  public function toOptionArray()
  {
    // Get all attributes
    $attributes = $this->_attributeOptionsCollection->getSelect()->reset(\Zend_Db_Select::COLUMNS)->columns('attribute_code'); // Select the field `attribute_code` only
    $attributes = $this->_attributeOptionsCollection->load();

    $attributeArray = array();
    $attributeArray[] = array('value' => '', 'label' => __('-- Please Select --'));

    foreach ($attributes as $attribute)
    {
      if (null !== $attribute->getAttributeCode())
      {
        $attributeArray[] = array(
          'value' => $attribute->getAttributeCode(),
          'label' => $attribute->getAttributeCode()
        );
      }
    }

    // Return
    return $attributeArray;
  }
}
