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

namespace Speedinfo\Opensi\Block\Adminhtml;

class Validation extends \Magento\Framework\View\Element\Template
{
  protected $_productCollectionFactory;
  protected $_request;
  protected $_backendHelper;
  protected $_productAttributeRepository;
  protected $_taxModelConfig;
  protected $_countryFactory;
  protected $_regionFactory;
  protected $_productMetadata;
  protected $_moduleList;
  protected $_scopeConfig;
  protected $_storeManager;

  /**
	 * Constructor
   *
   * @param \Magento\Framework\View\Element\Template\Context $context
   * @param \Magento\Framework\App\Request\Http $request
   * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
   * @param \Magento\Backend\Helper\Data $backendHelper
   * @param \Magento\Catalog\Model\Product\Attribute\Repository $productAttributeRepository
   * @param \Magento\Tax\Model\Calculation\Rate $taxModelConfig
   * @param \Magento\Directory\Model\CountryFactory $countryFactory
   * @param \Magento\Directory\Model\RegionFactory $regionFactory
   * @param \Magento\Framework\App\ProductMetadataInterface $productMetadata
   * @param \Magento\Framework\Module\ModuleListInterface $moduleList
	 */
	public function __construct(
    \Magento\Framework\View\Element\Template\Context $context,
    \Magento\Framework\App\Request\Http $request,
    \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
    \Magento\Backend\Helper\Data $backendHelper,
    \Magento\Catalog\Model\Product\Attribute\Repository $productAttributeRepository,
    \Magento\Tax\Model\Calculation\Rate $taxModelConfig,
    \Magento\Directory\Model\CountryFactory $countryFactory,
    \Magento\Directory\Model\RegionFactory $regionFactory,
    \Magento\Framework\App\ProductMetadataInterface $productMetadata,
    \Magento\Framework\Module\ModuleListInterface $moduleList
  )	{
		parent::__construct($context);

    $this->_request = $request;
    $this->_productCollectionFactory = $productCollectionFactory;
    $this->_backendHelper = $backendHelper;
    $this->_productAttributeRepository = $productAttributeRepository;
    $this->_taxModelConfig = $taxModelConfig;
    $this->_countryFactory = $countryFactory;
    $this->_regionFactory = $regionFactory;
    $this->_productMetadata = $productMetadata;
    $this->_moduleList = $moduleList;
    $this->_scopeConfig = $context->getScopeConfig();
    $this->_storeManager = $context->getStoreManager();
  }


  /**
   * Get store informations
   */
  public function getStore()
  {
    return $this->_storeManager->getStore($this->_request->getParam('store'));
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
   * Get products collection which can be sync with OpenSi (depending on the store)
   */
  public function getProductsToSyncCollection()
  {
    $collection = $this->_productCollectionFactory->create();
    $collection->addAttributeToSelect('*');
    $collection->addStoreFilter($this->_request->getParam('store'));
    $collection->addFieldToFilter('type_id', array('nin' => array('configurable', 'grouped', 'bundle')));

    switch ($this->_scopeConfig->getValue('opensi_preferences/manage_products/products', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
			case 2:
        $collection->addAttributeToFilter('status',\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED);
        $html = __('(only enabled products - see module configuration)');
				break;
			case 3:
        $collection->addAttributeToFilter('status',\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED);
        $html = __('(only disabled products - see module configuration)');
				break;
		}

    $collection->getSelect()->where('LENGTH(sku) != 0 AND sku NOT REGEXP "^ | $" AND LENGTH(sku) <= 40');

    if(isset($html)) {
      return $collection->getSize().' '.$html;
    } else {
      return $collection->getSize();
    }
  }


  /**
   * Get products url (admin/backend)
   */
  public function getProductUrl($productId)
  {
    return $this->_backendHelper->getUrl(
      'catalog/product/edit',
      ['id' => $productId]
    );
  }


  /**
   * Get number of products (depending on the store)
   */
  public function getTotalProducts()
	{
		return $this->getProductsCollection()->getSize();
	}


  /**
	 * Get products without sku (depending on the store)
	 */
	public function getProductsWithoutSku()
	{
		$products = $this->getProductsCollection();
		$products->addAttributeToSelect(['entity_id', 'name', 'type_id', 'visibility', 'status']);
		$products->addAttributeToFilter('sku', '');

		return $products;
	}


  /**
	 * Get products with long sku (depending on the store)
	 */
	public function getProductsWithLongSku()
	{
		$products = $this->getProductsCollection();
		$products->addAttributeToSelect(['entity_id', 'sku', 'name', 'type_id', 'visibility', 'status']);
		$products->getSelect()->where('LENGTH(sku) > 40');

		return $products;
	}


  /**
	 * Get products with a space before or after the sku (depending on the store)
	 */
	public function getProductsWithSpace()
	{
		$products = $this->getProductsCollection();
		$products->addAttributeToSelect(['entity_id', 'sku', 'name', 'type_id', 'visibility', 'status']);
		$products->getSelect()->where('sku REGEXP "^ | $"');

		return $products;
	}


  /**
	 * Get products which sku is duplicated (depending on the store)
	 */
  public function getProductsWithDuplicateSkuCount()
 	{
 		$products = $this->getProductsCollection();
 		$products->getSelect()->columns(array('count(*) as duplicate'))->group('sku')->having('duplicate > 1');

 		return $products->count();
 	}

	public function getProductsWithDuplicateSku()
	{
    $products = $this->getProductsCollection();
		$products->getSelect()
			->columns(array('count(*) as duplicate'))
	    ->columns('GROUP_CONCAT(CONCAT(e.entity_id, "|", ev.value) SEPARATOR "||") as products_list')
	    ->joinLeft(['ev' => $products->getTable('catalog_product_entity_varchar')], 'ev.entity_id = e.entity_id')
	    ->joinLeft(['a' => $products->getTable('eav_attribute')], 'a.attribute_id = ev.attribute_id AND ev.store_id = '.(!$this->_request->getParam('store')?0:$this->_request->getParam('store')))
	    ->where('a.attribute_code = "name"')
	    ->group('e.sku')
	    ->having('duplicate > 1');

    return $products;
	}


  /**
	 * Get products without wholesale price (depending on the store)
	 */
	public function getProductsWithoutWholesalePrice()
	{
    try {
      $attributeCost = $this->_productAttributeRepository->get('cost');
    } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
      return 0;
    }

    $products = $this->getProductsCollection();
		$products->addAttributeToFilter('cost', array('null' => true), 'left')
			->addAttributeToSelect(array('entity_id', 'sku', 'name', 'type_id', 'visibility', 'status'))
			->getSelect()
			->limit(100);

		return $products;
  }


  /**
	 * Get products without tax
	 */
	public function getProductsWithoutTax()
	{
		$products = $this->getProductsCollection();
		$products->addAttributeToFilter('tax_class_id', array('null' => true), 'left')
			->addAttributeToSelect(array('entity_id', 'sku', 'name', 'type_id', 'visibility', 'status'))
			->getSelect()
			->limit(100);

		return $products;
	}


  /**
	 * Get taxes
	 */
	public function getTaxRateList()
	{
      $taxes = $this->_taxModelConfig->getCollection();
      $taxes->getSelect()
			->columns('class.class_name')
			->columns('count(cpei.value) AS numProducts')
			->join(['tc' => $taxes->getTable('tax_calculation')], 'tc.tax_calculation_rate_id = main_table.tax_calculation_rate_id', array())
			->join(['class' => $taxes->getTable('tax_class')], 'class.class_id = tc.product_tax_class_id', array())
			->join(['eav' => $taxes->getTable('eav_attribute')], 'eav.attribute_code = "tax_class_id"', array())
			->joinLeft(['cpei' => $taxes->getTable('catalog_product_entity_int')], 'cpei.value = tc.product_tax_class_id AND cpei.attribute_id = eav.attribute_id AND store_id = '.(!$this->_request->getParam('store')?0:$this->_request->getParam('store')), array())
			->group('main_table.rate')
      ->order('main_table.tax_calculation_rate_id ASC');

		return $taxes;
  }


  /**
   * Get country name by country code
   *
   * @param $countryCode
   */
  public function getCountryName($countryCode)
  {
    return $this->_countryFactory->create()->loadByCode($countryCode)->getName();
  }


  /**
   * Get region name by id
   *
   * @param $regionId
   */
  public function getRegionName($regionId)
  {
    return ($this->_regionFactory->create()->load($regionId)->getName()?$this->_regionFactory->create()->load($regionId)->getName():'*');
  }


  /**
   * Get Magento version
   */
  public function getMagentoVersion()
  {
    return $this->_productMetadata->getVersion();
  }


  /**
   * Get OpenSi Connect extension version
   */
  public function getExtensionVersion()
  {
    $module = $this->_moduleList->getOne('Speedinfo_Opensi');

    return $module['setup_version'];
  }


  /**
	 * Check if Soap is installed
	 */
	public function checkSoap()
	{
		return class_exists('SOAPClient');
	}


  /**
   * Check if there is more than one stock source
   */
  public function checkStocksSources()
  {
    if (class_exists('\Magento\InventoryCatalog\Model\IsSingleSourceMode')) {
      return \Magento\Framework\App\ObjectManager::getInstance()->get('\Magento\InventoryCatalog\Model\IsSingleSourceMode')->execute();
    }

    return true;
  }


  /**
	 * Get the shipping tax (depending on the store)
	 */
	public function getShippingTax()
	{
		return $this->_scopeConfig->getValue('tax/classes/shipping_tax_class', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
	}


  /**
	 * Check if barcode / EAN is mapped
	 */
	public function checkBarcode()
	{
    if (!$this->_scopeConfig->getValue('opensi_configuration/attributes/barcode', \Magento\Store\Model\ScopeInterface::SCOPE_STORE))
    {
			return true;
		}

		return false;
	}


  /**
	 * Check price scope (global vs website)
	 */
	public function checkPriceConfiguration()
	{
    if (count($this->_storeManager->getStores()) > 1 && !$this->_scopeConfig->getValue('catalog/price/scope', \Magento\Store\Model\ScopeInterface::SCOPE_STORE))
    {
			return true;
		}

		return false;
	}


  /**
	 * Get tax rates to create
	 */
  public function getTaxRateToCreate()
	{
      $taxes = $this->_taxModelConfig->getCollection();
      $taxes->getSelect()
			->columns('class.class_name')
			->columns('count(cpei.value) AS numProducts')
			->join(['tc' => $taxes->getTable('tax_calculation')], 'tc.tax_calculation_rate_id = main_table.tax_calculation_rate_id', array())
			->join(['class' => $taxes->getTable('tax_class')], 'class.class_id = tc.product_tax_class_id', array())
			->join(['eav' => $taxes->getTable('eav_attribute')], 'eav.attribute_code = "tax_class_id"', array())
			->joinLeft(['cpei' => $taxes->getTable('catalog_product_entity_int')], 'cpei.value = tc.product_tax_class_id AND cpei.attribute_id = eav.attribute_id AND store_id = '.(!$this->_request->getParam('store')?0:$this->_request->getParam('store')), array())
			->group('main_table.rate')
      ->having('count(cpei.value) > 0');

		return $taxes;
  }


  /**
   * Check ecotax configuration
   */
  public function checkEcotax()
  {
    if ($this->_scopeConfig->getValue('tax/weee/enable', \Magento\Store\Model\ScopeInterface::SCOPE_STORE))
    {
      $html = __('Ecotax enabled');
    } else {
      $html = __('Ecotax disabled');
    }

    return $html;
  }

  public function ecotaxConfiguration()
  {
    if ($this->_scopeConfig->getValue('tax/weee/enable', \Magento\Store\Model\ScopeInterface::SCOPE_STORE))
    {
      $html = __('FPT are enabled.');

      if (!$this->_scopeConfig->getValue('tax/weee/apply_vat', \Magento\Store\Model\ScopeInterface::SCOPE_STORE))
      {
        $html .= '<br /><br /><div class="opensi-error">'.__('Warning! the configuration "Apply Tax To FPT" is not enabled (cf. System/Configuration/Tax).').'</div>';
      }
    } else {
      $html = __('FPT are disabled.');
    }

    return $html;
  }


  /**
   * Get tax rules url (admin/backend)
   */
  public function getTaxRulesUrl()
  {
    return $this->_backendHelper->getUrl('tax/rule');
  }


  /**
   * Get ecotax url (admin/backend)
   */
  public function getEcotaxUrl()
  {
    return $this->_backendHelper->getUrl('adminhtml/system_config/edit/section/tax');
  }
}
