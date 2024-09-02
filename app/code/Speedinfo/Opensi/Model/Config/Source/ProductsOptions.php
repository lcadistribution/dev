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

class ProductsOptions implements \Magento\Framework\Option\ArrayInterface
{
	protected $_productCollectionFactory;
	protected $_request;

	/**
	 * Constructor
   *
   * @param \Magento\Framework\App\Request\Http $request
   * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
	 */
	public function __construct(
		\Magento\Framework\App\Request\Http $request,
    \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
  )	{
		$this->_request = $request;
    $this->_productCollectionFactory = $productCollectionFactory;
  }


	/**
   * Get products collection (depending on the store)
   */
	public function getProductsCollection()
	{
		$collection = $this->_productCollectionFactory->create();
		$collection->addAttributeToSelect('*');
		$collection->addStoreFilter($this->_request->getParam('store'));

		return $collection;
	}


	/**
   * Options getter
   *
   * @return array
   */
  public function toOptionArray()
  {
    return [
			['value' => 1, 'label' => __('All products').' ('.$this->getProductsCollection()->getSize().')'],
			['value' => 2, 'label' => __('Only enabled products').' ('.$this->getProductsCollection()->addAttributeToFilter('status', \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)->getSize().')'],
			['value' => 3, 'label' => __('Only disabled products').' ('.$this->getProductsCollection()->addAttributeToFilter('status', \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED)->getSize().')']
		];
  }
}
