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

class StocksOptions implements \Magento\Framework\Option\ArrayInterface
{
	/**
   * Options getter
   *
   * @return array
   */
  public function toOptionArray()
  {
  	return [['value' => 1, 'label' => __('Available stocks')], ['value' => 4, 'label' => __('Real stocks')], ['value' => 2, 'label' => __('Supplier availability')], ['value' => '7', 'label' => __('Virtual stocks')], ['value' => 3, 'label' => __('Combination of the available stocks and the supplier availability')], ['value' => 5, 'label' => __('Combination of the real stocks and the supplier availability')], ['value' => 6, 'label' => __('A percentage of the stock available')], ['value' => 8, 'label' => __('Available stocks in the Magento quantity and the supplier quantity in a custom attribute')]];
  }
}
