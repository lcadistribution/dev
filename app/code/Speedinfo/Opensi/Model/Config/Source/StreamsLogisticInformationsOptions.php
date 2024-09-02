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

class StreamsLogisticInformationsOptions implements \Magento\Framework\Option\ArrayInterface
{
	/**
	 * Options getter
   *
   * @return array
   */
  public function toOptionArray()
  {
    return [
      ['value' => '', 'label' => __('--')],
      ['value' => 'barcode', 'label' => __('Barcode')],
      ['value' => 'volume', 'label' => __('Volume')],
      ['value' => 'height', 'label' => __('Height')],
      ['value' => 'length', 'label' => __('Length')],
      ['value' => 'width', 'label' => __('Width')],
      ['value' => 'weight', 'label' => __('Weight')],
      ['value' => 'manufacturer_reference', 'label' => __('Manufacturer reference')],
      ['value' => 'restocking_time', 'label' => __('Restocking time')],
      ['value' => 'direct_supplier', 'label' => __('Direct supplier')],
      ['value' => 'abc_class', 'label' => __('ABC Class')],
      ['value' => 'conditioning', 'label' => __('Conditioning')],
      ['value' => 'nc8_code', 'label' => __('NC8 code')],
      ['value' => 'country_code_manufacture', 'label' => __('Country code of manufacture')],
      ['value' => 'net_weight', 'label' => __('Net weight')]
    ];
  }
}
