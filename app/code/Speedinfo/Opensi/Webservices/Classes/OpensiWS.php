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

namespace Speedinfo\Opensi\Webservices\Classes;

use \Exception;

class OpensiWS
{
	private $_header;
	private $_server;
	protected $_manager;


	/*=================================================================
	 *
	 *					G L O B A L   F U N C T I O N S
	 *
	 ================================================================*/

	/**
	 * Construtor
	 *
	 * @param $server
	 */
	public function __construct($server, $manager)
	{
		$this->_server = $server;
		$this->_manager = $manager;
	}


	/**
	 * Get header values
	 *
	 * @param $values
	 */
	public function Header($values)
	{
		$this->_header = $values;
	}





	/*=================================================================
	 *
	 *						G E T   D A T E T I M E
	 *
	 ================================================================*/

	/**
	 * Get date time
	 *
	 * @return date/time on the server
	 */
	public function getDateTime()
	{
		/**
		 * Authentification
		 */
		$auth = new Authenticate();

		if (!$auth->login($this->_manager, $this->_header->{'key'})) {
			throw new Exception(OSI_INVALID_AUTH);
		}

		/**
		 * Return
		 */
		return array(
			'return' => $this->_manager->create('\Magento\Framework\Stdlib\DateTime\TimezoneInterface')->date()->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT)
		);
	}





	/*=================================================================
	 *
	 *						G E T   V E R S I O N
	 *
	 ================================================================*/

	/**
	 * Get version
	 *
	 * @return version of the module
	 */
	public function getVersion()
	{
		/**
		 * Authentification
		 */
		$auth = new Authenticate();

		if (!$auth->login($this->_manager, $this->_header->{'key'})) {
			throw new Exception(OSI_INVALID_AUTH);
		}

		/**
		 * Return
		 */
		$storename = $this->_manager->create('\Magento\Store\Model\StoreManagerInterface')->getStore()->getName();
		$version = $this->_manager->create('Magento\Framework\Module\ModuleListInterface')->getOne('Speedinfo_Opensi')['setup_version'];
		$magentoVersion = $this->_manager->create('Magento\Framework\App\ProductMetadataInterface')->getVersion();
		$mysqlVersion = $this->_manager->create('\Magento\Framework\App\ResourceConnection')->getConnection()->fetchOne('SELECT version()');

		return array(
			'return' => 'Nom de la boutique : '.$storename.' | OpenSi Connect : '.$version.' | Magento 2 : '.$magentoVersion.' | PHP : '.phpversion().' | MySQL : '.$mysqlVersion);
	}





	/*=================================================================
	 *
	 *			G E T   P R O D U C T S   C O L L E C T I O N S
	 *
	 ================================================================*/

	/**
 	 * Get products collection
	 *
	 * @param $values
	 * @param $productType
	 *
	 * @return products collection
	 */
	private function getProductsCollection($values, $productType = null)
 	{
		/**
		 * Authentification
		 */
		$auth = new Authenticate();

		if (!$auth->login($this->_manager, $this->_header->{'key'})) {
			throw new Exception(OSI_INVALID_AUTH);
		}

		/**
		 * Check configuration depending on the website code
		 */
		$this->checkConfiguration($values);

		/**
		 * Get products collection
		 */
		$valuesProduct = array();

		if (isset($values->{'Product'})) {
			$valuesProduct = $values->{'Product'};
		} elseif (isset($values->{'ProductImage'})) {
			$valuesProduct = $values->{'ProductImage'};
		}

		if (empty($valuesProduct))
 		{
			$datetimeMin = $this->convertDatetoUTC($values->{'Datetime_Min'});
			$datetimeMax = $this->convertDatetoUTC($values->{'Datetime_Max'});

			$productsCollection = $this->_manager->create('\Speedinfo\Opensi\Model\ResourceModel\Product\Collection'); // Important! Used to disable flat catalog
			$productsCollection->addStoreFilter($this->getCurrentStoreId()); // Important! Filter data by store id

			switch ($productType)
			{
				case 'update':
					$productsCollection->addFieldToFilter('updated_at',
						array('date' => true, 'from' => $datetimeMin, 'to' => $datetimeMax)
					);
					break;

				case 'image':
					if ($values->{'All_Image'}) {
						$productsCollection->addFieldToFilter('created_at',
							array('date' => true, 'from' => $datetimeMin, 'to' => $datetimeMax)
						);
					} else {
						$productsCollection->addFieldToFilter('updated_at',
							array('date' => true, 'from' => $datetimeMin, 'to' => $datetimeMax)
						);
					}
					break;

				default:
					$productsCollection->addFieldToFilter(array(
						array('attribute'=>'created_at', 'from' => $datetimeMin, 'to' => $datetimeMax),
						array('attribute'=>'updated_at', 'from' => $datetimeMin, 'to' => $datetimeMax)
					));
					break;
			}

		} else {

			/**
			 * At least one product reference is specified
			 * Query based on the reference(s)
			 */
			$productReferences = array();

			if (!is_array($valuesProduct)) {
				$valuesProduct = array($valuesProduct);
			}

			foreach ($valuesProduct as $value)
			{
				$productReferences[] = $value->Reference;
			}

			$productsCollection = $this->_manager->create('\Magento\Catalog\Model\ResourceModel\Product\Collection');
			$productsCollection->addAttributeToFilter('sku', array('in' => $productReferences));
		}


		/**
		 * Depending on the configuration, select all or only enabled / disabled products
		 */
		switch ($this->getStoreConfigValue('opensi_preferences/manage_products/products'))
		{
			case 2:
				$productsCollection->addAttributeToFilter('status', array('eq' => \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED));
				break;
			case 3:
				$productsCollection->addAttributeToFilter('status', array('eq' => \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED));
				break;
		}


		/**
		 * Return products collection
		 */
		switch ($productType)
		{
			/**
			 * Components collection
			 */
			case 'bundle': // Get all children products (all products are taken into account, even though there is more than one product per option)
				$productsCollection
					->addFieldToFilter('type_id', array('eq' => array('bundle')))
					->getSelect()
					->reset(\Zend_Db_Select::COLUMNS)
					->columns('cpbs.selection_qty')
					->columns('cpbs.option_id AS optionId')
					->columns('cpbs.product_id AS bundleId')
					->columns('entity_id AS productId')
					->columns('cp.sku AS bundle_sku')
					->columns('sku AS sku')
					->columns('type_id')
					->order('productId')
					->joinLeft(array('cpbs' => $productsCollection->getTable('catalog_product_bundle_selection')), 'e.entity_id = cpbs.parent_product_id', '')
					->joinLeft(array('cp' => $productsCollection->getTable('catalog_product_entity')), 'cp.entity_id = cpbs.product_id', '')
					->where('e.sku NOT REGEXP "^ | $" AND e.sku <> "" AND LENGTH(e.sku) <= 40 AND cpbs.selection_qty IS NOT NULL AND cp.sku IS NOT NULL')
					->limit($values->{'Range_Max'}, $values->{'Range_Min'});
				break;

			/**
			 * Images collection
			 */
			case 'image':
				$productsCollection
					->getSelect()
					->reset(\Zend_Db_Select::COLUMNS)
					->columns('entity_id AS productId')
					->order('productId')
					->group('productId')
					->limit($values->{'Range_Max'}, $values->{'Range_Min'})
					->where('sku NOT REGEXP "^ | $" AND sku <> "" AND LENGTH(sku) <= 40');
				break;

			/**
			 * Products collection
			 */
			default:
				$query = 'SELECT selection_id, parent_product_id FROM '.$productsCollection->getTable('catalog_product_bundle_selection').' GROUP BY parent_product_id, option_id HAVING COUNT(*) > 1';

				$productsCollection
					->addFieldToFilter('type_id', array('nin' => array('configurable', 'grouped')))
					->getSelect()
					->order('entity_id')
					->group('entity_id')
					->joinLeft(new \Zend_Db_Expr('('.$query.')'), 'e.entity_id = t.parent_product_id', array(''))
					->where('sku NOT REGEXP "^ | $" AND sku <> "" AND LENGTH(sku) <= 40 AND t.selection_id is null')
					->limit($values->{'Range_Max'}, $values->{'Range_Min'});
				break;
		}

		/**
		 * Return collection
		 */
		$productsCollection->setFlag('has_stock_status_filter', true); // important! Use to set all products (not only product in stock)

		return $productsCollection;
	}





	/*=================================================================
	 *
	 *						G E T   P R O D U C T S
	 *
	 ================================================================*/

	/**
	 * Get products to create in OpenSi
	 * SWO-P001
	 *
	 * Magento => OpenSi
	 *
	 * @param $values (products)
	 *
	 * @return products
	 */
	public function getProducts($values)
	{
		if (($productsCollection = $this->getProductsCollection($values)) != false)
		{
			$products = array();

			foreach ($productsCollection as $product)
			{
				$productArray = array();

				/**
				 * Get fields to sync depending on the configuration
				 */
				$fieldsToSync = array();
        $customWebservice = $this->getStoreConfigValue('opensi_preferences/manage_flux/products_creation/fields_sync');

				if ($customWebservice)
				{
					if ($this->getStoreConfigValue('opensi_preferences/manage_flux/products_creation/fields_to_sync')) {
            $fieldsToSync = explode(',', $this->getStoreConfigValue('opensi_preferences/manage_flux/products_creation/fields_to_sync'));
          }
				}

				/**
				 * Load current product
				 */
				$productOsi = $this->_manager->create('\Magento\Catalog\Model\Product')->load($product->getId());
				$priceManagement = $this->getStoreConfigValue('tax/calculation/price_includes_tax');

				/**
				 * Set products to create in OpenSi
				 * Construct products array depending on the configuration (fields to sync)
				 */
				$productArray['Reference'] = $productOsi->getSku();
				$productArray['Name'] = $productOsi->getName();
				$productArray['IncludingVATPriceDefinition'] = ($priceManagement)?true:false;

				/**
				 * Product type
				 *
				 * If the product is a bundle and at least one of its child contains more than one product in an option,
				 * the parent product and its child are not sent to OpenSi.
				 */
				if ($productOsi->getTypeId() == 'bundle')
				{
					if ($this->checkIfBundleHasManyProductsInOneOption($productOsi)) {
						continue; // IMPORTANT !, it means that the product contains more than one product in one of its options
					}
					$productArray['ProductType'] = 'F';
				} else {
					$productArray['ProductType'] = 'U';
				}

				/**
				 * Brand
				 */
				if ($this->isSynchronizable('brand', $fieldsToSync, $customWebservice)) {
					if ($this->isProductAttributeExists('manufacturer')) {
						if ($productOsi->getAttributeText('manufacturer')) {
							$productArray['Brand'] = $productOsi->getAttributeText('manufacturer');
						}
					}
				}

				/**
				 * Short description
				 */
				if ($this->isSynchronizable('short_description', $fieldsToSync, $customWebservice)) {
					if ($productOsi->getShortDescription()) {
						$productArray['ShortDescription'] = strip_tags($productOsi->getShortDescription());
					}
				}

				/**
				 * Description
				 */
        $description = '';

				if ($this->isSynchronizable('description', $fieldsToSync, $customWebservice))
				{
					if ($this->getStoreConfigValue('opensi_preferences/manage_products/description'))
					{
						// Custom attribute
						$description = $productOsi->getResource()->getAttribute($this->getStoreConfigValue('opensi_preferences/manage_products/description_custom'))->getFrontend()->getValue($productOsi);

					} else {

						// Default description
						if ($productOsi->getDescription()) {
							$description = $productOsi->getDescription();
						}

					}

					if ($description) {
						$productArray['Description'] = strip_tags($description);
					}
 				}

				/**
				 * OpenSi mapping
				 */
 				if ($this->getStoreConfigValue('opensi_configuration/attributes/enable_attributes'))
				{
					// Attributes mapping
					if ($this->isSynchronizable('attributes_mapping', $fieldsToSync, $customWebservice))
					{
						$attributes = array();
						$attribute = array();

						// Add product ID to mapping
						if ($this->getStoreConfigValue('opensi_preferences/manage_products/product_id'))
						{
							$productId = $productOsi->getId();

							if (is_numeric($productId))
							{
								$productIdLabel = ($this->getStoreConfigValue('opensi_preferences/manage_products/product_id_label') ? $this->getStoreConfigValue('opensi_preferences/manage_products/product_id_label') : __('Product ID OSI'));

								$attribute['Name'] = $productIdLabel;
								$attribute['Value'] = $productId;
								$attributes[] = $attribute;
							}
	 					}

						// Standard mapping
						$attributesMappingStored = $this->_manager->create('\Magento\Framework\Serialize\Serializer\Json')->unserialize($this->getStoreConfigValue('opensi_configuration/attributes/attributes_mapping'));

						if ($attributesMappingStored)
						{
							foreach ($attributesMappingStored as $attributeMappingStored)
							{
								if ($productOsi->getResource()->getAttribute($attributeMappingStored['magento_attribute'])->getFrontendInput() == 'select')
								{
									// getAttributeText() will only work with the attribute type select
									if ($this->isProductAttributeExists($attributeMappingStored['magento_attribute'])) {
										$attributeValue = $productOsi->getAttributeText($attributeMappingStored['magento_attribute']);
									}
								} else {
									// return the text value of the attribute input
									$attributeValue = $productOsi->getResource()->getAttribute($attributeMappingStored['magento_attribute'])->getFrontend()->getValue($productOsi);
								}

								if ($attributeValue)
								{
									$attribute['Name'] = $attributeMappingStored['opensi_attribute'];
									$attribute['Value'] = $attributeValue;

									$attributes[] = $attribute;
								}
							}
						}

						// Return mapping
						if (!empty($attributes)) {
							$productArray['Attributes'] = $attributes;
						}
					}

          /**
           * [Custom] Families
           */
          if ($productOsi->getProductFamily1()) {
            $productArray['Family_1'] = $productOsi->getResource()->getAttribute('product_family_1')->getFrontend()->getValue($productOsi);
          }
          if ($productOsi->getProductFamily2()) {
            $productArray['Family_2'] = $productOsi->getResource()->getAttribute('product_family_2')->getFrontend()->getValue($productOsi);
          }
          if ($productOsi->getProductFamily3()) {
            $productArray['Family_3'] = $productOsi->getResource()->getAttribute('product_family_3')->getFrontend()->getValue($productOsi);
          }

					// Volume mapping
					$volume = $this->getAttributeValue('opensi_configuration/attributes/volume', $productOsi, $fieldsToSync, $customWebservice);

					if ($volume) {
						$productArray['Volume'] = $volume;
					}

					// Height mapping
					$height = $this->getAttributeValue('opensi_configuration/attributes/height', $productOsi, $fieldsToSync, $customWebservice);

					if ($height) {
						$productArray['Height'] = $height;
					}

					// Length mapping
					$length = $this->getAttributeValue('opensi_configuration/attributes/length', $productOsi, $fieldsToSync, $customWebservice);

					if ($length) {
						$productArray['Length'] = $length;
					}

					// Width mapping
					$width = $this->getAttributeValue('opensi_configuration/attributes/width', $productOsi, $fieldsToSync, $customWebservice);

					if ($width) {
						$productArray['Width'] = $width;
					}

					// Weight mapping
					$weight = $this->getAttributeValue('weight', $productOsi, $fieldsToSync, $customWebservice);

					if ($weight) {
						$productArray['Weight'] = $weight;
					}

					// Barcode mapping
					$barcode = $this->getAttributeValue('opensi_configuration/attributes/barcode', $productOsi, $fieldsToSync, $customWebservice);

					if ($barcode) {
						$productArray['Barcode'] = $barcode;
					}

					// Manufacturer reference mapping
					$manufacturerReference = $this->getAttributeValue('opensi_configuration/attributes/manufacturer_reference', $productOsi, $fieldsToSync, $customWebservice);

					if ($manufacturerReference) {
						$productArray['ManufacturerReference'] = $manufacturerReference;
					}

					// NC8 code mapping
					$nc8Code = $this->getAttributeValue('opensi_preferences/manage_customs/nc8_code', $productOsi, $fieldsToSync, $customWebservice);

					if ($nc8Code) {
						$productArray['NC8Code'] = $nc8Code;
					}

					// Country code of manufacture mapping
					$countryCodeManufacture = $this->getAttributeValue('opensi_preferences/manage_customs/country_code_manufacture', $productOsi, $fieldsToSync, $customWebservice);

					if ($countryCodeManufacture) {
						$productArray['CountryCodeManufacture'] = $countryCodeManufacture;
					}

					// Net weight mapping
					$netWeight = $this->getAttributeValue('opensi_preferences/manage_customs/net_weight', $productOsi, $fieldsToSync, $customWebservice);

					if ($netWeight) {
						$productArray['NetWeight'] = $netWeight;
					}

					// Picking zone mapping
					$pickingZone = $this->getAttributeValue('opensi_configuration/attributes/picking_zone', $productOsi, $fieldsToSync, $customWebservice);

					if ($pickingZone) {
						$productArray['PickingZone'] = $pickingZone;
					}
				}

				/**
				 * Model Reference
				 * If simple products have a parent, fill the model reference with the parent sku
				 */
				if ($this->isSynchronizable('model_reference', $fieldsToSync, $customWebservice))
				{
					$modelReferenceId = $this->hasParent($productOsi->getId());

					if ($modelReferenceId) {
						$modelReferenceArray = $this->_manager->create('\Magento\Catalog\Model\Product')->load($modelReferenceId);
						$productArray['ModelReference'] = $modelReferenceArray->getSku();
					}
				}

				/**
				 * Tax rate
				 */
				$taxRate = $this->_manager->create('\Magento\Tax\Model\TaxCalculation')->getCalculatedRate($productOsi->getTaxClassId());
				$productArray['VATRate'] = $taxRate;

				/**
				 * Weee tax - Ecotax
				 *
				 * Important!, the ecotax should always be sent excluding VAT to OpenSi !
				 * Depending on the wee tax configuration (System/Configuration/Tax/Fixed Product Taxes/FPT Tax Configuration),
				 * it is necessary to remove (or not) the tax on the ecotax to sent to OpenSi.
				 *
				 * If the catalog price is managed including tax, the ecotax should be filled including tax
				 * OR excluding tax with "FPT Tax Configuration" as "Taxed".
				 *
				 * On the other hand, if it is managed excluding tax, the ecotax should be excluding tax.
				 */
				$ecotax = 0;
 				$ecotaxForOpenSi = 0;

				if ($this->isSynchronizable('ecotax', $fieldsToSync, $customWebservice))
				{
					if ($this->getStoreConfigValue('tax/weee/enable'))
					{
						$weeTaxCodes = $this->_manager->create('\Magento\Catalog\Model\Resourcemodel\Eav\Attribute')->getAttributeCodesByFrontendType('weee');

						foreach ($weeTaxCodes as $weeTaxCode)
						{
							$ecotaxList = $productOsi->getResource()->getAttribute($weeTaxCode)->getFrontend()->getValue($productOsi);

							if ($ecotaxList && count($ecotaxList) > 0)
							{
								foreach ($ecotaxList as $value)
								{
									switch ($this->getStoreConfigValue('tax/weee/apply_vat'))
									{
										case 0:
										case 2:
											$weeeTaxApplyVatManagement = false;
											break;

										case 1:
											$weeeTaxApplyVatManagement = true;
											break;
									}

									if ($weeeTaxApplyVatManagement)
									{
										$ecotax = $value['value'] * (1 + $taxRate / 100);
										$ecotaxForOpenSi = $value['value'];
									} else {
										$ecotax = $value['value'];
										$ecotaxForOpenSi = $value['value'] / (1 + $taxRate / 100);
									}
								}
							}
						}
						$productArray['Ecotax'] = $ecotaxForOpenSi;
					} else {
						$productArray['Ecotax'] = 0;
					}
				}

				/**
				 * Get product price or special price
				 *
				 * Depending on the preferences, need to get the special price or the normal price
				 * Depending on the configuration, need to send to OpenSi this price excluding or including tax
				 */
				if ($this->getStoreConfigValue('opensi_preferences/manage_prices/prices') == 2)
 				{
 					// Get special price if available
					$now = date('Y-m-d H:i:s');

					if ($productOsi->getSpecialPrice() && $now > $productOsi->getSpecialFromDate() && ($now < $productOsi->getSpecialToDate() || $productOsi->getSpecialToDate() == ''))
					{
						// Special price is defined and now is in the range of dates defined
						if ($priceManagement) {
							$productArray['Price'] = $productOsi->getPriceInfo()->getPrice('special_price')->getAmount()->getValue();
						} else {
							$productArray['Price'] = $productOsi->getPriceInfo()->getPrice('special_price')->getAmount()->getBaseAmount()  + ($ecotax / (1 + $taxRate / 100));
						}

					} else {

						// Special price is defined but now is NOT in the range of dates defined
						if ($priceManagement) {
							$productArray['Price'] = $productOsi->getPriceInfo()->getPrice('regular_price')->getAmount()->getValue();
						} else {
							$productArray['Price'] = $productOsi->getPriceInfo()->getPrice('regular_price')->getAmount()->getBaseAmount()  + ($ecotax / (1 + $taxRate / 100));
						}

					}

				} else {

					// Get regular price
					if ($priceManagement) {
						$productArray['Price'] = $productOsi->getPriceInfo()->getPrice('regular_price')->getAmount()->getValue();
					} else {
						$productArray['Price'] = $productOsi->getPriceInfo()->getPrice('regular_price')->getAmount()->getBaseAmount() + ($ecotax / (1 + $taxRate / 100));
					}
				}

				/**
				 * Wholesale price
				 */
				if ($this->isSynchronizable('wholesale_price', $fieldsToSync, $customWebservice)) {
					if ($this->isProductAttributeExists('cost')) {
						if ($productOsi->getCost()) {
							$productArray['WholesalePrice'] = $productOsi->getCost();
						}
					}
				}

				/**
				 * Retail price (public price - MSRP)
				 */
				if ($this->isSynchronizable('retail_price', $fieldsToSync, $customWebservice))
				{
					if ($this->getStoreConfigValue('opensi_preferences/manage_prices/retail_price'))
					{
						// Custom attribute
						$retailPrice = $productOsi->getResource()->getAttribute($this->getStoreConfigValue('opensi_preferences/manage_prices/retail_price_custom'))->getFrontend()->getValue($productOsi);

					} else {

						// MSRP (default)
						$retailPrice = $productOsi->getMsrp();

					}

					if ($retailPrice && is_numeric($retailPrice)) {
						$productArray['RetailPrice'] = $retailPrice;
					}
				}

				/**
				 * Return product
				 */
				$products[] = $productArray;
			}

			/**
			 * Return products
			 */
			return $products;
		}
	}








	/*=================================================================
	 *
	 * 						G E T   I N V E N T O R Y
	 *
	 ================================================================*/

	/**
	 * Get inventory
	 * Get the stock of the products to create in OpenSi
	 * SWO-P013
	 *
	 * Magento => OpenSi
	 *
	 * @param $values
	 * @return products inventory
	 */
	public function getInventory($values)
	{
		if (($productsCollection = $this->getProductsCollection($values)) != false)
		{
			$productsInventory = array();

			foreach ($productsCollection as $product)
			{
				/**
				 * Products inventory
				 * @return array
				 */
				$productOsi = $this->_manager->create('\Magento\Catalog\Model\Product')->load($product->getId());
				$sourceItems = $this->_manager->create('\Magento\InventoryApi\Api\GetSourceItemsBySkuInterface')->execute($product->getSku());
        $quantity = 0;

        if (!empty($sourceItems)) {
          $quantity = reset($sourceItems)->getQuantity(); // only get the stock of the first source (e.g. basically the default source)
        }

				$productsInventory[] = array(
					'Reference' => $productOsi->getSku(),
					'Quantity' => $quantity,
				);
			}

			/**
			 * Return
			 */
			return $productsInventory;
		}
	}








	/*=================================================================
	 *
	 * 					G E T   P U B L I C A T I O N
	 *
	 ================================================================*/

	/**
	 * Get publication
	 * Get the publication of the products
	 * SWO-P003
	 *
	 * Magento => OpenSi
	 *
	 * @param $values
	 * @return products publication
	 */
	public function getPublication($values)
 	{
		if (($productsCollection = $this->getProductsCollection($values)) != false)
		{
			$productsPublication = array();

			foreach ($productsCollection as $product)
			{
				/**
				 * Products publication
				 * @return array
				 */
				$productOsi = $this->_manager->create('\Magento\Catalog\Model\Product')->load($product->getId());

				$productsPublication[] = array(
					'Reference' => $productOsi->getSku(),
					'Publication' => ($productOsi->getStatus() == 1)?true:false
				);
			}

			/**
			 * Return
			 */
			return $productsPublication;
		}
	}








	/*=================================================================
	 *
	 *			G E T   P R O D U C T S   C O M P O N E N T S
	 *
	 ================================================================*/

	/**
	 * Get components of the bundles
	 * Get the product components to send to OpenSi
	 * SWO-P025
	 *
	 * Magento => OpenSi
	 *
	 * @param $values
	 * @return products components
	 */
	public function getProductComponents($values)
	{
		if (($componentsCollection = $this->getProductsCollection($values, 'bundle')) != false)
		{
			$components = array();

			foreach ($componentsCollection as $component)
			{
				/**
				 * Products components
				 * @return array
				 */
				$productOsi = $this->_manager->create('\Magento\Catalog\Model\Product')->load($component['productId']);

				if ($this->checkIfBundleHasManyProductsInOneOption($productOsi)) {
					continue; // The parent product (bundle) contains more than one product in one of its options => nothing is sent to OpenSi
				}

				$components[] = array(
					'Reference' => $component['sku'],
					'ComponentReference' => $component['bundle_sku'],
					'Quantity' => $component['selection_qty']
				);
			}

			/**
			 * Return
			 */
			return $components;
		}
	}








	/*=================================================================
	 *
	 *				G E T   P R O D U C T S   I M A G E
	 *
	 ================================================================*/

	/**
	 * Get product images collection
	 * Get the products image list to create in OpenSi
	 * SWO-P037
	 *
	 * Magento => OpenSi
	 *
	 * @param $values
	 * @return products image
	 */
	public function getProductImages($values)
	{
		if (($imagesCollection = $this->getProductsCollection($values, 'image')) != false)
		{
			$images = array();

			foreach ($imagesCollection as $image)
			{
				/**
				 * Products image
				 * @return array
				 */
				$imageToReturn = null;
				$productOsi = $this->_manager->create('\Magento\Catalog\Model\Product')->load($image['productId']);

				// The product has an image - Leave the $this->_manager->get in order to avoid the following error : Missing required argument $root of Magento\Framework\App\Filesystem\DirectoryList.
				$imagePath = $this->_manager->get('\Magento\Framework\App\Filesystem\DirectoryList')->getPath('media').'/catalog/product'.$productOsi->getImage();

				if (file_exists($imagePath) && !is_dir($imagePath) && filesize($imagePath) < 5242880) // 512000 => 500Ko // 1024000 => 1Mo // 5242880 => 5Mo
				{
					// The product has an image that exist on the server
					$imageToReturn = file_get_contents($imagePath);
				} else {
					// The product has an image but it doesn't exist on the server
					$imageToReturn = $this->getParentProductImage($productOsi);
				}

				/**
				 * Set image
				 */
				$images[] = array(
					'Reference' => $productOsi->getSku(),
					'Image' => $imageToReturn
				);
			}

			/**
			 * Return
			 */
			return $images;
		}
	}


	/**
	 * Get parent image to return
	 *
	 * @param $product
	 */
	public function getParentProductImage($product)
	{
		$imageToReturn = null;
		$parentId = $this->hasParent($product->getId());

		if ($parentId) {
			$productParent = $this->_manager->create('\Magento\Catalog\Model\Product')->load($parentId);

			$imagePath = $this->_manager->get('\Magento\Framework\App\Filesystem\DirectoryList')->getPath('media').'/catalog/product'.$productParent->getImage();

			if (file_exists($imagePath) && filesize($imagePath) < 5242880) // 512000 => 500Ko // 1024000 => 1Mo // 5242880 => 5Mo
			{
				// The product has an image that exist on the server
				$imageToReturn = file_get_contents($imagePath);
			}
		}

		return $imageToReturn;
	}








	/*=================================================================
	 *
	 *				G E T   P R O D U C T S   U P D A T E
	 *
	 ================================================================*/

	/**
	 * Get products to update
	 * Get the list of the products to update in OpenSi
	 * SWO-P017
	 *
	 * Magento => OpenSi
	 *
	 * @param $values
	 * @return products update
	 */
	public function getProductsUpdate($values)
	{
		if (($productsCollection = $this->getProductsCollection($values)) != false)
		{
			$products = array();

			foreach ($productsCollection as $product)
			{
				$productArray = array();

	      /**
	       * Get fields to sync depending on the configuration
	       */
	      $fieldsToSync = array();
        $customWebservice = $this->getStoreConfigValue('opensi_preferences/manage_flux/products_update/fields_sync');

	      if ($customWebservice)
	      {
	        if ($this->getStoreConfigValue('opensi_preferences/manage_flux/products_update/fields_to_sync')) {
            $fieldsToSync = explode(',', $this->getStoreConfigValue('opensi_preferences/manage_flux/products_update/fields_to_sync'));
          }
	      }

				/**
	       * Load current product
	       */
	      $productOsi = $this->_manager->create('\Magento\Catalog\Model\Product')->load($product->getId());

				/**
	       * Set products to update in OpenSi
	       * Construct products array depending on the configuration (fields to sync)
	       */
	      $productArray['Reference'] = $productOsi->getSku();

				/**
				 * Name
				 */
				if ($this->isSynchronizable('name', $fieldsToSync, $customWebservice)) {
					if ($productOsi->getName()) {
 	          $productArray['Name'] = $productOsi->getName();
 	        }
 	      }

				/**
	       * Brand
	       */
	      if ($this->isSynchronizable('brand', $fieldsToSync, $customWebservice)) {
					if ($this->isProductAttributeExists('manufacturer')) {
		        if ($productOsi->getAttributeText('manufacturer')) {
		          $productArray['Brand'] = $productOsi->getAttributeText('manufacturer');
		        }
					}
	      }

				/**
	       * Short description
	       */
	      if ($this->isSynchronizable('short_description', $fieldsToSync, $customWebservice)) {
	        if ($productOsi->getShortDescription()) {
	          $productArray['ShortDescription'] = strip_tags($productOsi->getShortDescription());
	        }
	      }

	      /**
	       * Description
	       */
        $description = '';

	      if ($this->isSynchronizable('description', $fieldsToSync, $customWebservice))
	      {
	        if ($this->getStoreConfigValue('opensi_preferences/manage_products/description'))
	        {
	          // Custom attribute
	          $description = $productOsi->getResource()->getAttribute($this->getStoreConfigValue('opensi_preferences/manage_products/description_custom'))->getFrontend()->getValue($productOsi);

	        } else {

	          // Default description
	          if ($productOsi->getDescription()) {
	            $description = $productOsi->getDescription();
	          }

	        }

	        if ($description) {
	          $productArray['Description'] = strip_tags($description);
	        }
	      }

				/**
	       * OpenSi mapping
	       */
	      if ($this->getStoreConfigValue('opensi_configuration/attributes/enable_attributes'))
	      {
	        // Attributes mapping
	        if ($this->isSynchronizable('attributes_mapping', $fieldsToSync, $customWebservice))
	        {
	          $attributesMappingStored = $this->_manager->create('\Magento\Framework\Serialize\Serializer\Json')->unserialize($this->getStoreConfigValue('opensi_configuration/attributes/attributes_mapping'));

	          if ($attributesMappingStored)
	          {
	            $productArray['Attributes'] = array();

	            $attributes = array();
	            $attribute = array();

	            foreach ($attributesMappingStored as $attributeMappingStored)
	            {
								if ($productOsi->getResource()->getAttribute($attributeMappingStored['magento_attribute'])->getFrontendInput() == 'select')
								{
									// getAttributeText() will only work with the attribute type select
									if ($this->isProductAttributeExists($attributeMappingStored['magento_attribute'])) {
										$attributeValue = $productOsi->getAttributeText($attributeMappingStored['magento_attribute']);
									}
								} else {
									// return the text value of the attribute input
									$attributeValue = $productOsi->getResource()->getAttribute($attributeMappingStored['magento_attribute'])->getFrontend()->getValue($productOsi);
								}

                $attribute['Name'] = $attributeMappingStored['opensi_attribute'];
                $attribute['Value'] = $attributeValue;

                $attributes[] = $attribute;
	            }

	            $productArray['Attributes'] = $attributes;
	          }
	        }
	      }

				/**
	       * Return product
	       */
	      $products[] = $productArray;
			}

			/**
	     * Return products
	     */
	    return $products;
		}
	}








	/*=================================================================
	 *
	 * 						G E T   O R D E R S
	 *
	 ================================================================*/

	/**
	 * Get orders
	 * Get the orders to create in OpenSi
	 * SWO-P007
	 *
	 * Magento => OpenSi
	 *
	 * @param $values
	 * @return $orders
	 */
	 public function getOrders($values)
 	{
		/**
		 * Authentification
		 */
		$auth = new Authenticate();

		if (!$auth->login($this->_manager, $this->_header->{'key'})) {
			throw new Exception(OSI_INVALID_AUTH);
		}

		/**
		 * Check configuration depending on the website code
		 */
		$this->checkConfiguration($values);

		/**
		 * Get orders collection
		 */
		$orders = array();
		$ordersCollection = $this->_manager->create('Magento\Sales\Model\Order')->getCollection();

		if (empty($values->{'Order'}))
		{
			/**
			 * No order number specified
			 * Query based on datetime range
			 */
			$datetimeMin = $this->convertDatetoUTC($values->{'Datetime_Min'});
 			$datetimeMax = $this->convertDatetoUTC($values->{'Datetime_Max'});

			$ordersCollection->addFieldToFilter('opensi_date', array('date' => true, 'from' => $datetimeMin, 'to' => $datetimeMax));

		} else {

			/**
			 * At least one order number is specified
			 * Query based on the order id(s)
			 */
			$orderNumbers = array();

			if (!is_array($values->{'Order'})) {
				$values->{'Order'} = array($values->{'Order'});
			}

			foreach ($values->{'Order'} as $value)
			{
				$orderNumbers[] = $value->OrderNumber;
			}

			$ordersCollection->addAttributeToFilter('increment_id', array('in' => $orderNumbers));
		}

		$ordersCollection
			->addFieldToFilter('store_id', $this->getCurrentStoreId())
			->addFieldToFilter('base_shipping_amount', array('notnull' => true))
			->getSelect()
			->limit($values->{'Range_Max'}, $values->{'Range_Min'});


		/**
		 * Get order informations
		 */
		foreach ($ordersCollection as $order)
		{
			$orderArray = array();
			$priceManagement = $this->getStoreConfigValue('tax/calculation/price_includes_tax');

			/**
			 * Set order
			 */
			$orderArray['OrderNumber'] = $order->getIncrementId();
			$orderArray['OrderCartNumber'] = $order->getQuoteId();
			$orderArray['OrderDate'] = $this->convertDatetoTimezone($order->getCreatedAt());
			$orderArray['Weight'] = $order->getWeight();
			$orderArray['IncludedTaxEdition'] = ($priceManagement?true:false);
			$orderArray['Shipping'] = ($priceManagement?$order->getBaseShippingInclTax():$order->getBaseShippingAmount());
			$orderArray['Identifier'] = ($order->getCustomerId()?$order->getCustomerId():'Invité');

			/**
			 * Customer group label
			 */
			$customerGroup = $this->_manager->create('\Magento\Customer\Model\Group')->load($order->getCustomerGroupId())->getCustomerGroupCode();
			if ($customerGroup) {
			  $orderArray['CustomerGroup'] = $customerGroup;
			}

			/**
			 * Set OpenSi Login
			 */
			$orderArray['Login'] = ($order->getCustomerId()?$order->getCustomerEmail():'Invité');

			if ($order->getStoreCode()) {
				$orderArray['LoginOpensi'] = $order->getStoreCode();
			}
			elseif ($order->getCodeCustomerService()) {
				$orderArray['LoginOpensi'] = $order->getCodeCustomerService();
			}

      /**
			 * [Custom] Set activity pole + title
			 */
			if ($order->getSalesmanCode()) {
				$orderArray['ActivityPole'] = $order->getSalesmanCode();
			}
			if ($order->getChannelCode()) {
				$orderArray['Title'] = $order->getChannelCode();
			}

			/**
			 * Expected delivery date
			 */
			if ($order->getExpectedDeliveryDate()) {
        $orderArray['ExpectedDeliveryDate'] = $this->_manager->create('\Magento\Framework\Stdlib\DateTime\DateTime')->date(null, strtotime($order->getExpectedDeliveryDate()));
      }

      /**
       * Estimated delivery date
       */
      if ($order->getEstimatedDeliveryDate()) {
        $orderArray['EstimatedDeliveryDate'] = $this->_manager->create('\Magento\Framework\Stdlib\DateTime\DateTime')->date(null, strtotime($order->getEstimatedDeliveryDate()));
      }

			/**
			 * Shipping VAT rate
			 * Use to get the tax of the shipping
			 */
			$percent = 0;
			$taxClassId = $this->getStoreConfigValue('tax/classes/shipping_tax_class');

			if ($taxClassId != 0)
			{
				$taxRequest = $this->_manager->create('\Magento\Tax\Model\Calculation')->getRateRequest($order->getShippingAddress(), $order->getBillingAddress());

				if ($taxRequest->getCustomerClassId()) {
					$percent = $this->_manager->create('\Magento\Tax\Model\Calculation')->getRate($taxRequest->setProductClassId($taxClassId));
				} elseif ($taxRequest->getProductClassId()) {
					$percent = $this->_manager->create('\Magento\Tax\Model\Calculation')->getRate($taxRequest->setCustomerClassId($taxClassId));
				}
			}

			$orderArray['VATRateShipping'] = $percent;

			/**
			 * Payment method
			 */
			$orderArray['PaymentMethod'] = $order->getPayment()->getMethodInstance()->getTitle();

      /**
       * Marketplace number (Shopping Feed extension)
       */
      if ($this->_manager->create('\Magento\Framework\Module\Manager')->isEnabled('ShoppingFeed_Manager') && $this->_manager->create('\Magento\Framework\Module\Manager')->isOutputEnabled('ShoppingFeed_Manager'))
      {
        try {
          $shoppingFeedOrder = $this->_manager->get('ShoppingFeed\Manager\Api\Marketplace\OrderRepositoryInterface')->getBySalesOrderId($order->getId());
          $orderReferenceMarketplace = $this->_manager->get('ShoppingFeed\Manager\Api\Marketplace\OrderRepositoryInterface')->getBySalesOrderId($order->getId())->getMarketplaceOrderNumber();

          if ($orderReferenceMarketplace) {
            $orderArray['OrderReferenceMarketplace'] = $orderReferenceMarketplace;
          }
        } catch(\Magento\Framework\Exception\NoSuchEntityException $e) {
        }
      }

			/**
			 * Discount
			 * Depending on the configuration :
			 * 	- if the prices are managed excluding tax, the magento tax must be apply BEFORE discount
			 *	- if including tax, the magento tax must be apply AFTER discount
			 */
			if (abs($order->getBaseDiscountAmount()) > 0)
			{
				/**
				 * Discount found
				 */
				if (!$priceManagement && !$this->getStoreConfigValue('tax/calculation/apply_after_discount'))
				{
					// In this case, need to remove the tax !
					$taxRate = 1 + (($order->getGrandTotal() - $order->getShippingAmount() - $order->getShippingTaxAmount() - ($order->getSubtotal() + $order->getDiscountAmount())) / $order->getSubtotal());
					$orderArray['DiscountAmount'] = (abs($order->getDiscountAmount()) - $order->getShippingDiscountAmount()) / $taxRate;
					$orderArray['ShippingDiscountAmount'] = $order->getShippingDiscountAmount() / $taxRate;

				} else {

					$orderArray['DiscountAmount'] = abs($order->getDiscountAmount()) - $order->getShippingDiscountAmount();
					$orderArray['ShippingDiscountAmount'] = $order->getShippingDiscountAmount();

				}

			} else {

				/**
				 * No discount
				 */
				$orderArray['DiscountAmount'] = 0;
				$orderArray['ShippingDiscountAmount'] = 0;

			}

			/**
			 * Intracommunity VAT Number
			 */
			if ($order->getBillingAddress()->getVatId()) {
				$orderArray['IntraCommunityVAT'] = $order->getBillingAddress()->getVatId();
			}

			/**
			 * If grand total = 0, automatically validate the order
			 */
			if ($order->getGrandTotal() == 0) {
				$orderArray['ValidOrder'] = true;
			}

      /**
       * [Custom] Check if order is blocked
       *    0 => Order not blocked, to be sent to OpenSi (standard case)
       *    1 => Order blocked, do not send to OpenSi
       *    2 => Order not blocked but sent to OpenSi canceled
       */
      if ($order->getBlockedForOpensi()) {
        $orderArray['BlockedOrder'] = $order->getBlockedForOpensi();
      }

			/**
			 * Shipment method
			 */
			if ($this->_manager->create('\Magento\Framework\Module\Manager')->isEnabled('Colissimo_Shipping') && $this->_manager->create('\Magento\Framework\Module\Manager')->isOutputEnabled('Colissimo_Shipping') && !is_null($order->getShippingAddress()) && $order->getShippingAddress()->getColissimoPickupId() && $order->getShippingAddress()->getColissimoProductCode())
 			{
 			  // So Colissimo by Magentix (https://colissimo.magentix.fr/)
 			  $colissimoProductCode = $order->getShippingAddress()->getColissimoProductCode();
 			  $shipmentMethod = ($colissimoProductCode?$colissimoProductCode.' - '.$order->getShippingDescription():$order->getShippingDescription());

      } elseif ($this->_manager->create('\Magento\Framework\Module\Manager')->isEnabled('Amasty_ShippingTableRates') && $this->_manager->create('\Magento\Framework\Module\Manager')->isOutputEnabled('Amasty_ShippingTableRates') && false !== strpos($order->getShippingMethod(), 'amstrates_amstrates')) {
        $amRateId = substr($order->getShippingMethod(), -1);
        $countryId = $order->getShippingAddress()->getCountryId();
        $cleanDescription = trim(strip_tags($order->getShippingDescription()));
        $shipmentMethod = $countryId.$amRateId.' - '.$cleanDescription;

 			} else {

				// Standard behavior
 			  $shippingDescription = strip_tags($order->getShippingDescription());

 			  if ($shippingDescription)
 			  {
 			    if (strlen($shippingDescription) > 30)
 			    {
 			      $shippingDescription = explode(' - ', $shippingDescription);
 			      if ($shippingDescription[1]) {
 			        $shippingDescription = array_slice($shippingDescription, 1);
 			        $shippingDescription = implode(' - ', $shippingDescription);
 			      } else {
 			        $shippingDescription = $shippingDescription[0];
 			      }
 			    }

 			    $shippingDescription = trim($shippingDescription);
 			    $shipmentMethod = $shippingDescription;

 			  } else {
 			    $shipmentMethod = $order->getShippingMethod();
 			  }
 			}

			$orderArray['ShipmentMethod'] = $shipmentMethod;

			/**
			 * Billing address
			 */
			$orderArray['BillingCivility'] = 0;
			$orderArray['BillingLastname'] = $order->getBillingAddress()->getLastname();
      if($orderArray['BillingLastname'] == '' || null == $orderArray['BillingLastname'])
      {
        $orderArray['BillingLastname'] = $order->getShippingAddress()->getLastname();
      }
			$orderArray['BillingFirstname'] = $order->getBillingAddress()->getFirstname();
      if($orderArray['BillingFirstname'] == '' || null == $orderArray['BillingFirstname'])
      {
        $orderArray['BillingFirstname'] = $order->getShippingAddress()->getFirstname();
      }

			if ($order->getBillingAddress()->getCompany()) {
				$orderArray['BillingCompany'] = $order->getBillingAddress()->getCompany();
        if($orderArray['BillingCompany'] == '' || null == $orderArray['BillingCompany'])
        {
          if ($order->getShippingAddress()->getCompany()) {
            $orderArray['BillingCompany'] = $order->getShippingAddress()->getCompany();
          }
        }
			}

			$street = $order->getBillingAddress()->getStreet();
      $shipStreet = (!is_null($order->getShippingAddress()) ? $order->getShippingAddress()->getStreet() : '');
			$orderArray['BillingAddress_1'] = $street[0];
      if($orderArray['BillingAddress_1'] == '' || null == $orderArray['BillingAddress_1'])
      {
        $orderArray['BillingAddress_1'] = $shipStreet[0];
        if(empty($street[1]))
        {
          if(!empty($shipStreet[1]))
          {
            $orderArray['BillingAddress_2'] = $shipStreet[1];
          }
        }
      }

			if (!empty($street[1])) {
				$orderArray['BillingAddress_2'] = $street[1];
			}

			$orderArray['BillingZipcode'] = $order->getBillingAddress()->getPostcode();
      if($orderArray['BillingZipcode'] == '' || null == $orderArray['BillingZipcode'])
      {
        $orderArray['BillingZipcode'] = $order->getShippingAddress()->getPostcode();
      }

			$orderArray['BillingCity'] = $order->getBillingAddress()->getCity();
      if($orderArray['BillingCity'] == '' || null == $orderArray['BillingCity'])
      {
        $orderArray['BillingCity'] = $order->getShippingAddress()->getCity();
      }

			$orderArray['BillingPhone'] = $order->getBillingAddress()->getTelephone();
      if($orderArray['BillingPhone'] == '' || null == $orderArray['BillingPhone'])
      {
        $orderArray['BillingPhone'] = $order->getShippingAddress()->getTelephone();
      }

			if ($order->getBillingAddress()->getFax()) {
				$orderArray['BillingFax'] = $order->getBillingAddress()->getFax();
        if($orderArray['BillingFax'] == '' || null == $orderArray['BillingFax'])
        {
          if ($order->getShippingAddress()->getFax()) {
            $orderArray['BillingFax'] = $order->getShippingAddress()->getFax();
          }
        }
			}

			$orderArray['BillingCountryCode'] = $order->getBillingAddress()->getCountryId();
      if($orderArray['BillingCountryCode'] == '' || null == $orderArray['BillingCountryCode'])
      {
        $orderArray['BillingCountryCode'] = $order->getShippingAddress()->getCountryId();
      }

			$billingEmail = ($order->getBillingAddress()->getEmail())?$order->getBillingAddress()->getEmail():$order->getCustomerEmail();
			$orderArray['BillingEmail'] = $billingEmail;

			/**
			 * Shipping address
			 */
			if ($order->getShippingAddress())
			{
				/**
				 * Delivery address is available
				 */
				$orderArray['DeliveryCivility'] = 0;
				$orderArray['DeliveryLastname'] = $order->getShippingAddress()->getLastname();
				$orderArray['DeliveryFirstname'] = $order->getShippingAddress()->getFirstname();

				if ($order->getShippingAddress()->getCompany()) {
					$orderArray['DeliveryCompany'] = $order->getShippingAddress()->getCompany();
				}

				$street = $order->getShippingAddress()->getStreet();
				$orderArray['DeliveryAddress_1'] = $street[0];

				if (!empty($street[1])) {
					$orderArray['DeliveryAddress_2'] = $street[1];
				}

				$orderArray['DeliveryZipcode'] = $order->getShippingAddress()->getPostcode();
				$orderArray['DeliveryCity'] = $order->getShippingAddress()->getCity();
				$orderArray['DeliveryPhone'] = $order->getShippingAddress()->getTelephone();

				if ($order->getShippingAddress()->getFax()) {
					$orderArray['DeliveryFax'] = $order->getShippingAddress()->getFax();
				}

				$orderArray['DeliveryCountryCode'] = $order->getShippingAddress()->getCountryId();

        $marketplaces_emails = array();

        if ($this->getStoreConfigValue('opensi_preferences/manage_marketplaces/marketplaces_emails')) {
          if ($this->getStoreConfigValue('opensi_preferences/manage_marketplaces/all_modules')) {
            $marketplaces_emails = explode(',', $this->getStoreConfigValue('opensi_preferences/manage_marketplaces/all_modules'));
          }
        }

				if (!in_array($order->getPayment()->getMethodInstance()->getCode(), $marketplaces_emails)) {
					$orderArray['DeliveryEmail'] = ($order->getShippingAddress()->getEmail())?$order->getShippingAddress()->getEmail():$billingEmail;
				}

        /**
         * WithdrawalPoint - So Colissimo (by Magentix)
         * https://colissimo.magentix.fr/
         */
        if ($this->_manager->create('\Magento\Framework\Module\Manager')->isEnabled('Colissimo_Shipping') && $this->_manager->create('\Magento\Framework\Module\Manager')->isOutputEnabled('Colissimo_Shipping') && $order->getShippingAddress()->getColissimoPickupId() && $order->getShippingAddress()->getColissimoProductCode()) {
          $orderArray['WithdrawalPoint'] = $order->getShippingAddress()->getColissimoPickupId();
        }

        /**
         * WithdrawalPoint - Mondial Relay Shipping (by Magentix)
         * https://mondialrelay.magentix.fr/
         */
        if ($this->_manager->create('\Magento\Framework\Module\Manager')->isEnabled('MondialRelay_Shipping') && $this->_manager->create('\Magento\Framework\Module\Manager')->isOutputEnabled('MondialRelay_Shipping') && $order->getShippingAddress()->getMondialrelayPickupId() && $order->getShippingAddress()->getMondialrelayCode()) {
          $orderArray['WithdrawalPoint'] = $order->getShippingAddress()->getMondialrelayPickupId();
        }

        /**
         * WithdrawalPoint - Chronopost - Chrono Relais
         * https://www.chronopost.fr/fr/plateformes-e-commerce
         */
        if ($this->_manager->create('\Magento\Framework\Module\Manager')->isEnabled('Chronopost_Chronorelais') && $this->_manager->create('\Magento\Framework\Module\Manager')->isOutputEnabled('Chronopost_Chronorelais') && $order->getRelaisId()) {
          $orderArray['WithdrawalPoint'] = $order->getRelaisId();
        }

        /**
         * WithdrawalPoint - DPD France
         * https://www.dpd.fr/magento
         */
        if ($order->getShippingMethod() == 'dpdrelais_dpdrelais')
        {
          $company = $order->getShippingAddress()->getCompany();
          $lastWord = substr($company , strrpos($company, ' ') + 1);

          if (preg_match('/^P[0-9]{5}$/', $lastWord)) {
            $orderArray['WithdrawalPoint'] = $lastWord;
          }
        }

			} else {

				/**
				 * No delivery address (e.g. virtual product) -> fill it with billing address
				 */
				$orderArray['DeliveryCivility'] = 0;
				$orderArray['DeliveryLastname'] = $order->getBillingAddress()->getLastname();
				$orderArray['DeliveryFirstname'] = $order->getBillingAddress()->getFirstname();

				if ($order->getBillingAddress()->getCompany()) {
					$orderArray['DeliveryCompany'] = $order->getBillingAddress()->getCompany();
				}

				$street = $order->getBillingAddress()->getStreet();
				$orderArray['DeliveryAddress_1'] = $street[0];

				if (!empty($street[1])) {
					$orderArray['DeliveryAddress_2'] = $street[1];
				}

				$orderArray['DeliveryZipcode'] = $order->getBillingAddress()->getPostcode();
				$orderArray['DeliveryCity'] = $order->getBillingAddress()->getCity();
				$orderArray['DeliveryPhone'] = $order->getBillingAddress()->getTelephone();
				$orderArray['DeliveryCountryCode'] = $order->getBillingAddress()->getCountryId();
				$orderArray['DeliveryEmail'] = $billingEmail;
			}

			/**
			 * Printable comments
			 *
			 * Depending on the configuration (if the preference "Manage order comments" is enabled or not),
			 * get the first comment added to the order
			 */
			$printableComments = array();
			$commentsOption = $this->getStoreConfigValue('opensi_preferences/manage_orders/comments');
			$commentsTitlesOption = $this->getStoreConfigValue('opensi_preferences/manage_orders/comments_titles');

			if ($commentsOption > 0 && $commentsOption)
			{
				$orderAllComments = array();
				$orderComments = $order->getAllStatusHistory();

		    	foreach ($orderComments as $comment)
				{
					if (($commentsOption == 1 && $comment['is_visible_on_front'] == 1) || $commentsOption == 2)
					{
						$orderAllComments[] = $comment->getComment();
					}
		    	}

				if (end($orderAllComments)) {
					$printableComments[] = end($orderAllComments);
				}
			}

			/**
			 * Gift message
			 */
			$giftMessage = $this->_manager->create('\Magento\GiftMessage\Helper\Message')->getGiftMessage($order->getGiftMessageId())->getMessage();

			if ($giftMessage && $commentsOption)
			{
				$giftSender = $this->_manager->create('\Magento\GiftMessage\Helper\Message')->getGiftMessage($order->getGiftMessageId())->getSender();
				$giftRecipient = $this->_manager->create('\Magento\GiftMessage\Helper\Message')->getGiftMessage($order->getGiftMessageId())->getRecipient();

				if ($giftSender && $giftRecipient)
				{
					$printableComments[] = ($commentsTitlesOption ? 'Message d\'accompagnement à la commande (de la part de "'.$giftSender.'" pour "'.$giftRecipient.'") : '.preg_replace("~[\r\n]+~", ' ', $giftMessage) : preg_replace("~[\r\n]+~", ' ', $giftMessage));
				} else {
					$printableComments[] = ($commentsTitlesOption ? 'Message d\'accompagnement à la commande : '.preg_replace("~[\r\n]+~", ' ', $giftMessage) : preg_replace("~[\r\n]+~", ' ', $giftMessage));
				}
			}

			if ($order->getCouponRuleName()) {
				$printableComments[] = 'Code promotion : '.$order->getCouponRuleName();
			}

			if (!empty($printableComments)) {
				$orderArray['PrintableComments'] = implode(' - ', $printableComments);
			}

			/**
			 * Non printable comments
			 */
			$nonPrintableComments = array();

			if ($this->getStoreConfigValue('opensi_preferences/manage_orders/ip_address'))
			{
				if (!empty($order->getXForwardedFor())) {
					$ipAddress = 'Adresse IP : '.$order->getRemoteIp().' ('.$order->getXForwardedFor().')';
				} else {
					$ipAddress = 'Adresse IP : '.$order->getRemoteIp();
				}

				$nonPrintableComments[] = $ipAddress;
				$orderArray['NonPrintableComments'] = implode(' - ', $nonPrintableComments);
			}

			/**
			 * Order items
			 *
			 * Get the products of the current order
			 */
			$items = array();
 			$itemArray = array();
			$expeditionRule = 0;

 			foreach ($order->getAllVisibleItems() as $item)
 			{
				/**
				 * Get product references which have to generate multi shipment
				 * If reference is found in the order, we have to send on option to Opensi
				 */
				if ($this->getStoreConfigValue('opensi_preferences/manage_orders/expedition_rule'))
 				{
					$expeditionRules = explode(',', $this->getStoreConfigValue('opensi_preferences/manage_orders/expedition_rule'));

					foreach ($expeditionRules as $expedition)
					{
						if ($expedition == $item->getSku())
						{
							$expeditionRule++;
						}
					}
				}

				/**
				 * Bundles treatment
				 */
				if ($item->getProductType() == 'bundle')
				{
					/**
					 * BUNDLE PRODUCT
					 * ------------------------------------------------------------
					 *
					 * BUNDLE WITH ONE OR MORE PRODUCTS PER OPTION
					 *
					 * Check if the bundle has more than one product per option
					 * The products sent to OpenSi are depending on :
					 * - if the product has at least one option that contains at least 2 products
					 * - if the price of the product is typed fixed vs dynamic
					 *
					 * CONDITIONS
					 *
					 * if (bundle has more than one product per option) {
					 *
					 * 		// Only children are sent to OpenSi !
					 * 		if (bundle has a fixed price) {
					 * 			The children are sent to OpenSi but only the first child has the price, the other are sent to zero
					 * 		} else {
					 * 			The children are sent to OpenSi with their own price
					 * 		}
					 *
					 * } else {
					 *
					 * 		// Depending on the price type (fixed / dynamic), the parent or the children are sent to OpenSi
					 * 		if (bundle has a fixed price) {
					 * 			The bundle reference is sent to OpenSi with the total price (parent + children)
					 * 		} else {
					 * 			The children are sent to OpenSi with their own price
					 * 		}
					 *
					 * }
					 *
					 *
					 * IMPORTANT !
					 * Prices are those stored in the command (if update between the date of the order and the date of the synchronization,
					 * the prices are always those of the order).
					 *
					 */
					$bundleProduct = $this->_manager->create('\Magento\Catalog\Model\Product')->load($item->getProductId());

					if ($this->checkIfBundleHasManyProductsInOneOption($bundleProduct))
 					{
 						/**
 						 * BUNDLE WITH AT LEAST 2 PRODUCTS IN ONE OPTION
 						 *
 						 * Only children (children reference) are sent to OpenSi
 						 * The treatment is different depending on the price type (fixed <> dynamic price)
 					 	 */
 						$i = 0;

 						foreach ($item->getChildrenItems() as $child)
 						{
 							/**
 							 * Product informations
 							 */
 							$itemArray['Reference'] = $child->getSku();
 							$itemArray['Name'] = $child->getName();
 							$itemArray['Quantity'] = $child->getQtyOrdered();
 							$itemArray['VATRate'] = ($bundleProduct->getPriceType()?$item->getData('tax_percent'):$child->getData('tax_percent'));
 							$itemArray['DiscountPercent'] = 0;

							/**
							 * Ecotax
							 */
							$ecotax = 0;
							$ecotaxOptions = $child->getWeeeTaxApplied();

							if (!empty($ecotaxOptions))
							{
								$ecotaxAttributes = json_decode($ecotaxOptions);

								foreach ($ecotaxAttributes as $ecotaxAttribute)
								{
									if ($priceManagement) {
										$ecotax = $ecotaxAttribute->amount_incl_tax;
									} else {
										$ecotax = $ecotaxAttribute->base_amount;
									}
								}
							}

							/**
							 * Price
							 */
 							if ($bundleProduct->getPriceType())
 							{
 								/**
 								 * Bundle with a fixed price
 								 * Only the first child has a price, the other are set to 0
 								 */
 								$totalPrice = (($priceManagement ? $item->getBaseRowTotalInclTax() : $item->getBaseRowTotal()) - $item->getDiscountAmount()) / $child->getQtyOrdered();

								$itemArray['UnitPrice'] = ($i == 0 ? $totalPrice : 0);

 							} else {

 								/**
 								 * Bundle with a dynamic price
 								 * Only the children have to be returned to OpenSi with their own price
 								 */
 								$options = $child->getProductOptions();
 								$bundleSelectionAttributes = $this->_manager->create('\Magento\Framework\Serialize\Serializer\Json')->unserialize($options['bundle_selection_attributes']);

 								$totalPrice = $bundleSelectionAttributes['price'] / $bundleSelectionAttributes['qty'] + $ecotax;

 								$itemArray['UnitPrice'] = $totalPrice;
 							}

              /**
               * [Custom] Get expected delivery date
               */
              if ($child->getExpectedDeliveryDate()) {
                $itemArray['ExpectedDeliveryDate'] = $child->getExpectedDeliveryDate();
              }

 							$items[] = $itemArray;
 							$i++;
 						}

 					} else {

 						/**
 						 * BUNDLE WITH ONLY ONE PRODUCT IN ONE OPTION
 						 *
 						 * The treatment is different depending on the price type (fixed <> dynamic price)
 						 */
 						if ($bundleProduct->getPriceType())
 						{
 							/**
 							 * Bundle with a fixed price
 							 * Only the parent product (bundle reference) is sent to OpenSi with its price but also the price of the childrens
 							 */
 							$itemArray['Reference'] = $item->getSku();
 							$itemArray['Name'] = $item->getName();
 							$itemArray['Quantity'] = $item->getQtyOrdered();
 							$itemArray['VATRate'] = $item->getData('tax_percent');
 							$itemArray['DiscountPercent'] = 0;

              if ($priceManagement) {
                $itemArray['UnitPrice'] = $item->getBaseRowTotalInclTax() / $item->getQtyOrdered();
              } else {
                $itemArray['UnitPrice'] = $item->getBaseRowTotal() / $item->getQtyOrdered();
              }

              /**
               * [Custom] Get expected delivery date
               */
              if ($item->getExpectedDeliveryDate()) {
                $itemArray['ExpectedDeliveryDate'] = $item->getExpectedDeliveryDate();
              }

 							$items[] = $itemArray;

 						} else {

 							/**
 							 * Bundle with a dynamic price
 							 * Only the children have to be returned to OpenSi
 							 */
 							foreach ($item->getChildrenItems() as $child)
 							{
 								/**
 								 * Product informations
 								 */
 								$itemArray['Reference'] = $child->getSku();
 								$itemArray['Name'] = $child->getName();
 								$itemArray['Quantity'] = $child->getQtyOrdered();
 								$itemArray['VATRate'] = ($bundleProduct->getPriceType()?$item->getData('tax_percent'):$child->getData('tax_percent'));
 								$itemArray['DiscountPercent'] = 0;

								/**
			 					 * Ecotax
			 					 */
								$ecotax = 0;
			 					$ecotaxOptions = $child->getWeeeTaxApplied();

								if (!empty($ecotaxOptions))
								{
									$ecotaxAttributes = json_decode($ecotaxOptions);

				 					foreach ($ecotaxAttributes as $ecotaxAttribute)
									{
										if ($priceManagement) {
					 						$ecotax = $ecotaxAttribute->amount_incl_tax;
					 					} else {
					 						$ecotax = $ecotaxAttribute->base_amount;
					 					}
									}
								}

 								/**
 								 * Price
 								 */
 								if ($priceManagement) {
                  $itemArray['UnitPrice'] = $child->getBaseRowTotalInclTax() / $child->getQtyOrdered();
                } else {
                  $itemArray['UnitPrice'] = $child->getBaseRowTotal() / $child->getQtyOrdered();
                }

                /**
                 * [Custom] Get expected delivery date
                 */
                if ($child->getExpectedDeliveryDate()) {
                  $itemArray['ExpectedDeliveryDate'] = $child->getExpectedDeliveryDate();
                }

 								$items[] = $itemArray;
 							}

 						}
 					}

				 } else {

 					/**
 					 * NORMAL PRODUCT (NOT A BUNDLE)
 					 * ------------------------------------------------------------
 					 *
 					 * Get attribute(s) of the item if exist
 					 */
 					$options = $item->getProductOptions();
 					$itemOptions = array();

 					if (isset($options['attributes_info']))
 					{
 						// Attributes option
 						foreach ($options['attributes_info'] as $option)
 						{
 							$itemOptions[] = $option['label'].' : '.$option['value'];
 						}
 					}

 					if (isset($options['options']))
 					{
 						// Product options
 						foreach ($options['options'] as $option)
 						{
 						    $itemOptions[] = $option['label'].' : '.$option['value'];
 						}
 					}

 					/**
 					 * Manage name (concatenate attribute(s) if exist to the name)
 					 */
 					$name = $item->getName();

 					if (!empty($itemOptions)) {
 						$name .= ' ('.implode(', ', $itemOptions).')';
 					}

					/**
					 * Tax rate
					 */
					$taxRate = 0;

					if  ($item->getData('tax_percent') > 0) {
						$taxRate = $item->getData('tax_percent');
					}

 					/**
 					 * Other informations
 					 */
 					$itemArray['Reference'] = $item->getSku();
 					$itemArray['Name'] = $name;
 					$itemArray['Quantity'] = $item->getQtyOrdered();
 					$itemArray['VATRate'] = $taxRate;
 					$itemArray['DiscountPercent'] = 0;

 					/**
 					 * Weee tax - Ecotax
					 *
					 * Important!, the ecotax should always be sent excluding VAT to OpenSi !
					 * Depending on the wee tax configuration (System/Configuration/Tax/Fixed Product Taxes/FPT Tax Configuration),
					 * it is necessary to remove (or not) the tax on the ecotax to sent to OpenSi.
					 *
					 * If the catalog price is managed including tax, the ecotax should be filled including tax
					 * OR excluding tax with "FPT Tax Configuration" as "Taxed".
					 *
					 * On the other hand, if it is managed excluding tax, the ecotax should be excluding tax.
 					 */
					$ecotax = 0;
 					$ecotaxOptions = $item->getWeeeTaxApplied();

					if (!empty($ecotaxOptions))
					{
						$ecotaxAttributes = json_decode($ecotaxOptions);

	 					foreach ($ecotaxAttributes as $ecotaxAttribute)
						{
							switch ($this->getStoreConfigValue('tax/weee/apply_vat'))
							{
								case 0:
								case 2:
									$weeeTaxApplyVatManagement = false;
									break;

								case 1:
									$weeeTaxApplyVatManagement = true;
									break;
							}

							if ($priceManagement) {
								$ecotax = $ecotaxAttribute->amount_incl_tax;
							} else {
								if ($weeeTaxApplyVatManagement)
								{
									$ecotax = $ecotaxAttribute->base_amount;
								} else {
									$ecotax = $ecotaxAttribute->base_amount / (1 + $taxRate / 100);
								}
							}
						}
					}

 					/**
 					 * Price
 					 */
 					if ($priceManagement)
					{
 						if ($item->getBasePriceInclTax())
						{
							$totalPrice = $item->getBasePriceInclTax() + $ecotax;
						} else {
 							$totalPrice = $this->_manager->create('\Magento\Catalog\Helper\Data')->getTaxPrice($item, $item->getBasePrice(), true) + $ecotax;
 						}
 					} else {
						$totalPrice = $this->_manager->create('\Magento\Catalog\Helper\Data')->getTaxPrice($item, $item->getBasePrice() + $ecotax, false);
 					}

 					$itemArray['UnitPrice'] = $totalPrice;

          /**
           * [Custom] Get expected delivery date
           */
          if ($item->getExpectedDeliveryDate()) {
            $itemArray['ExpectedDeliveryDate'] = $item->getExpectedDeliveryDate();
          }

 					$items[] = $itemArray;
 				}
			}

			if ($expeditionRule > 0) {
				$orderArray['ExpeditionRule'] = 'U';
			}

      /**
			 * ShoppingFlux fees
			 */
			if ($order->getSfmMarketplaceFeesBaseAmount() > 0)
			{
				/**
				 * Create an additional product for ShoppingFlux (price = fees)
				 */
				$itemArray['Reference'] = '_FEES';
        if ($order->getPayment()->getAdditionalInformation()['method_title']) {
          $itemArray['Name'] = 'Commission '.$order->getPayment()->getAdditionalInformation()['method_title'];
        } else {
          $itemArray['Name'] = 'Commission Marketplace';
        }
				
				$itemArray['Quantity'] = 1;
				$itemArray['UnitPrice'] = $order->getSfmMarketplaceFeesBaseAmount();
				$itemArray['VATRate'] = 0;
				$itemArray['DiscountPercent'] = 0;

				$items[] = $itemArray;
			}

			/**
			 * return products & orders
			 */
			$orderArray['Product'] = $items;
			$orders[] = $orderArray;
		}

		/**
		 * Return
		 */
		return $orders;
	}








  /*=================================================================
   *
   * 						G E T   O R D E R S   U P D A T E
   *
   ================================================================*/

  /**
   * GET ORDERS TO UPDATE
   * ---------------------------------------
   *
   * SWO-P110
   * Magento => OpenSi
   * Get the list of the orders to update
   *
   * @param object $values
   * @return array The orders to update
   */
  public function getOrdersUpdate($values)
  {
    /**
     * Authentification
     */
    $auth = new Authenticate();

    if (!$auth->login($this->_manager, $this->_header->{'key'})) {
      throw new Exception(OSI_INVALID_AUTH);
    }

    /**
     * Check configuration depending on the website code
     */
    $this->checkConfiguration($values);

    /**
     * Get orders update collection
     */
    $orders = array();
    $ordersCollection = $this->_manager->create('Magento\Sales\Model\Order')->getCollection();

    if (empty($values->{'Order'}))
    {
      /**
       * No order number specified
       * Query based on datetime range
       * 
       * Generate the following query:
       * SELECT `main_table`.* 
       * FROM `sales_order` AS `main_table` 
       * WHERE (
       *  (`opensi_date` >= '0000-00-00 00:00:00' AND `opensi_date` <= '0000-00-00 00:00:000') OR
       *  (`updated_at` >= '0000-00-00 00:00:00' AND `updated_at` <= '0000-00-00 00:00:00')
       * )
       */
      $datetimeMin = $this->convertDatetoUTC($values->{'Datetime_Min'});
      $datetimeMax = $this->convertDatetoUTC($values->{'Datetime_Max'});

      $ordersCollection->addFieldToFilter(
        array('opensi_date', 'updated_at'),
        array(
          array('date' => true, 'from' => $datetimeMin, 'to' => $datetimeMax),
          array('date' => true, 'from' => $datetimeMin, 'to' => $datetimeMax)
        )
      );

    } else {

      /**
       * At least one order number is specified
       * Query based on the order id(s)
       */
      $orderNumbers = array();

      if (!is_array($values->{'Order'})) {
        $values->{'Order'} = array($values->{'Order'});
      }

      foreach ($values->{'Order'} as $value) {
        $orderNumbers[] = $value->OrderNumber;
      }

      $ordersCollection->addAttributeToFilter('increment_id', array('in' => $orderNumbers));
    }

    $ordersCollection
      ->addFieldToFilter('store_id', $this->getCurrentStoreId())
      ->addFieldToFilter('base_shipping_amount', array('notnull' => true))
      ->getSelect()
      ->limit($values->{'Range_Max'}, $values->{'Range_Min'});

    /**
     * Get order update informations
     */
    foreach ($ordersCollection as $order)
    {
      $orderArray = array();
      $priceManagement = $this->getStoreConfigValue('tax/calculation/price_includes_tax');

      /**
       * Set order
       */
      $orderArray['OrderNumber'] = $order->getIncrementId();

      /**
       * Billing address
       */
      $orderArray['BillingCivility'] = 0;
      $orderArray['BillingLastname'] = $order->getBillingAddress()->getLastname();
      if ($orderArray['BillingLastname'] == '' || null == $orderArray['BillingLastname']) {
        $orderArray['BillingLastname'] = $order->getShippingAddress()->getLastname();
      }
      $orderArray['BillingFirstname'] = $order->getBillingAddress()->getFirstname();
      if ($orderArray['BillingFirstname'] == '' || null == $orderArray['BillingFirstname']) {
        $orderArray['BillingFirstname'] = $order->getShippingAddress()->getFirstname();
      }

      if ($order->getBillingAddress()->getCompany())
      {
        $orderArray['BillingCompany'] = $order->getBillingAddress()->getCompany();
        if ($orderArray['BillingCompany'] == '' || null == $orderArray['BillingCompany'])
        {
          if ($order->getShippingAddress()->getCompany()) {
            $orderArray['BillingCompany'] = $order->getShippingAddress()->getCompany();
          }
        }
      }

      $street = $order->getBillingAddress()->getStreet();
      $shipStreet = (!is_null($order->getShippingAddress()) ? $order->getShippingAddress()->getStreet() : '');
      $orderArray['BillingAddress_1'] = $street[0];
      if ($orderArray['BillingAddress_1'] == '' || null == $orderArray['BillingAddress_1'])
      {
        $orderArray['BillingAddress_1'] = $shipStreet[0];
        if (empty($street[1]))
        {
          if (!empty($shipStreet[1])) {
            $orderArray['BillingAddress_2'] = $shipStreet[1];
          }
        }
      }

      if (!empty($street[1])) {
        $orderArray['BillingAddress_2'] = $street[1];
      }

      $orderArray['BillingZipcode'] = $order->getBillingAddress()->getPostcode();
      if ($orderArray['BillingZipcode'] == '' || null == $orderArray['BillingZipcode'])
      {
        $orderArray['BillingZipcode'] = $order->getShippingAddress()->getPostcode();
      }

      $orderArray['BillingCity'] = $order->getBillingAddress()->getCity();
      if ($orderArray['BillingCity'] == '' || null == $orderArray['BillingCity']) {
        $orderArray['BillingCity'] = $order->getShippingAddress()->getCity();
      }

      $orderArray['BillingPhone'] = $order->getBillingAddress()->getTelephone();
      if ($orderArray['BillingPhone'] == '' || null == $orderArray['BillingPhone']) {
        $orderArray['BillingPhone'] = $order->getShippingAddress()->getTelephone();
      }

      if ($order->getBillingAddress()->getFax())
      {
        $orderArray['BillingFax'] = $order->getBillingAddress()->getFax();
        if ($orderArray['BillingFax'] == '' || null == $orderArray['BillingFax'])
        {
          if ($order->getShippingAddress()->getFax()) {
            $orderArray['BillingFax'] = $order->getShippingAddress()->getFax();
          }
        }
      }

      $orderArray['BillingCountryCode'] = $order->getBillingAddress()->getCountryId();
      if ($orderArray['BillingCountryCode'] == '' || null == $orderArray['BillingCountryCode'])
      {
        $orderArray['BillingCountryCode'] = $order->getShippingAddress()->getCountryId();
      }

      $billingEmail = ($order->getBillingAddress()->getEmail())?$order->getBillingAddress()->getEmail():$order->getCustomerEmail();
      $orderArray['BillingEmail'] = $billingEmail;

      /**
       * Shipping address
       */
      if ($order->getShippingAddress())
      {
        /**
         * Delivery address is available
         */
        $orderArray['DeliveryCivility'] = 0;
        $orderArray['DeliveryLastname'] = $order->getShippingAddress()->getLastname();
        $orderArray['DeliveryFirstname'] = $order->getShippingAddress()->getFirstname();

        if ($order->getShippingAddress()->getCompany()) {
          $orderArray['DeliveryCompany'] = $order->getShippingAddress()->getCompany();
        }

        $street = $order->getShippingAddress()->getStreet();
        $orderArray['DeliveryAddress_1'] = $street[0];

        if (!empty($street[1])) {
          $orderArray['DeliveryAddress_2'] = $street[1];
        }

        $orderArray['DeliveryZipcode'] = $order->getShippingAddress()->getPostcode();
        $orderArray['DeliveryCity'] = $order->getShippingAddress()->getCity();
        $orderArray['DeliveryPhone'] = $order->getShippingAddress()->getTelephone();

        if ($order->getShippingAddress()->getFax()) {
          $orderArray['DeliveryFax'] = $order->getShippingAddress()->getFax();
        }

        $orderArray['DeliveryCountryCode'] = $order->getShippingAddress()->getCountryId();

        $marketplaces_emails = array();

        if ($this->getStoreConfigValue('opensi_preferences/manage_marketplaces/marketplaces_emails')) {
          if ($this->getStoreConfigValue('opensi_preferences/manage_marketplaces/all_modules')) {
            $marketplaces_emails = explode(',', $this->getStoreConfigValue('opensi_preferences/manage_marketplaces/all_modules'));
          }
        }

        if (!in_array($order->getPayment()->getMethodInstance()->getCode(), $marketplaces_emails)) {
          $orderArray['DeliveryEmail'] = ($order->getShippingAddress()->getEmail())?$order->getShippingAddress()->getEmail():$billingEmail;
        }

        /**
         * WithdrawalPoint - So Colissimo (by Magentix)
         * https://colissimo.magentix.fr/
         */
        if ($this->_manager->create('\Magento\Framework\Module\Manager')->isEnabled('Colissimo_Shipping') && $this->_manager->create('\Magento\Framework\Module\Manager')->isOutputEnabled('Colissimo_Shipping') && $order->getShippingAddress()->getColissimoPickupId() && $order->getShippingAddress()->getColissimoProductCode()) {
          $orderArray['WithdrawalPoint'] = $order->getShippingAddress()->getColissimoPickupId();
        }

        /**
         * WithdrawalPoint - Mondial Relay Shipping (by Magentix)
         * https://mondialrelay.magentix.fr/
         */
        if ($this->_manager->create('\Magento\Framework\Module\Manager')->isEnabled('MondialRelay_Shipping') && $this->_manager->create('\Magento\Framework\Module\Manager')->isOutputEnabled('MondialRelay_Shipping') && $order->getShippingAddress()->getMondialrelayPickupId() && $order->getShippingAddress()->getMondialrelayCode()) {
          $orderArray['WithdrawalPoint'] = $order->getShippingAddress()->getMondialrelayPickupId();
        }

        /**
         * WithdrawalPoint - Chronopost - Chrono Relais
         * https://www.chronopost.fr/fr/plateformes-e-commerce
         */
        if ($this->_manager->create('\Magento\Framework\Module\Manager')->isEnabled('Chronopost_Chronorelais') && $this->_manager->create('\Magento\Framework\Module\Manager')->isOutputEnabled('Chronopost_Chronorelais') && $order->getRelaisId()) {
          $orderArray['WithdrawalPoint'] = $order->getRelaisId();
        }

        /**
         * WithdrawalPoint - DPD France
         * https://www.dpd.fr/magento
         */
        if ($order->getShippingMethod() == 'dpdrelais_dpdrelais')
        {
          $company = $order->getShippingAddress()->getCompany();
          $lastWord = substr($company , strrpos($company, ' ') + 1);

          if (preg_match('/^P[0-9]{5}$/', $lastWord)) {
            $orderArray['WithdrawalPoint'] = $lastWord;
          }
        }

      } else {

        /**
         * No delivery address (e.g. virtual product) -> fill it with billing address
         */
        $orderArray['DeliveryCivility'] = 0;
        $orderArray['DeliveryLastname'] = $order->getBillingAddress()->getLastname();
        $orderArray['DeliveryFirstname'] = $order->getBillingAddress()->getFirstname();

        if ($order->getBillingAddress()->getCompany()) {
          $orderArray['DeliveryCompany'] = $order->getBillingAddress()->getCompany();
        }

        $street = $order->getBillingAddress()->getStreet();
        $orderArray['DeliveryAddress_1'] = $street[0];

        if (!empty($street[1])) {
          $orderArray['DeliveryAddress_2'] = $street[1];
        }

        $orderArray['DeliveryZipcode'] = $order->getBillingAddress()->getPostcode();
        $orderArray['DeliveryCity'] = $order->getBillingAddress()->getCity();
        $orderArray['DeliveryPhone'] = $order->getBillingAddress()->getTelephone();
        $orderArray['DeliveryCountryCode'] = $order->getBillingAddress()->getCountryId();
        $orderArray['DeliveryEmail'] = $billingEmail;
      }

      /**
       * Order items
       *
       * Get the products of the current order
       */
      $items = array();
      $itemArray = array();

      foreach ($order->getAllVisibleItems() as $item)
      {
        /**
         * Bundles treatment
         */
        if ($item->getProductType() == 'bundle')
        {
          /**
           * BUNDLE PRODUCT
           * ------------------------------------------------------------
           *
           * BUNDLE WITH ONE OR MORE PRODUCTS PER OPTION
           *
           * Check if the bundle has more than one product per option
           * The products sent to OpenSi are depending on :
           * - if the product has at least one option that contains at least 2 products
           * - if the price of the product is typed fixed vs dynamic
           *
           * CONDITIONS
           *
           * if (bundle has more than one product per option) {
           *
           * 		// Only children are sent to OpenSi !
           * 		if (bundle has a fixed price) {
           * 			The children are sent to OpenSi but only the first child has the price, the other are sent to zero
           * 		} else {
           * 			The children are sent to OpenSi with their own price
           * 		}
           *
           * } else {
           *
           * 		// Depending on the price type (fixed / dynamic), the parent or the children are sent to OpenSi
           * 		if (bundle has a fixed price) {
           * 			The bundle reference is sent to OpenSi with the total price (parent + children)
           * 		} else {
           * 			The children are sent to OpenSi with their own price
           * 		}
           *
           * }
           *
           *
           * IMPORTANT !
           * Prices are those stored in the command (if update between the date of the order and the date of the synchronization,
           * the prices are always those of the order).
           *
           */
          $bundleProduct = $this->_manager->create('\Magento\Catalog\Model\Product')->load($item->getProductId());

          if ($this->checkIfBundleHasManyProductsInOneOption($bundleProduct))
          {
            /**
             * BUNDLE WITH AT LEAST 2 PRODUCTS IN ONE OPTION
             *
             * Only children (children reference) are sent to OpenSi
             * The treatment is different depending on the price type (fixed <> dynamic price)
              */
            $i = 0;

            foreach ($item->getChildrenItems() as $child)
            {
              /**
               * Product informations
               */
              $itemArray['Reference'] = $child->getSku();
              $itemArray['Name'] = $child->getName();
              $itemArray['Quantity'] = $child->getQtyOrdered();
              $itemArray['VATRate'] = ($bundleProduct->getPriceType()?$item->getData('tax_percent'):$child->getData('tax_percent'));
              $itemArray['DiscountPercent'] = 0;

              /**
              * Ecotax
              */
              $ecotax = 0;
              $ecotaxOptions = $child->getWeeeTaxApplied();

              if (!empty($ecotaxOptions))
              {
                $ecotaxAttributes = json_decode($ecotaxOptions);

                foreach ($ecotaxAttributes as $ecotaxAttribute)
                {
                  if ($priceManagement) {
                    $ecotax = $ecotaxAttribute->amount_incl_tax;
                  } else {
                    $ecotax = $ecotaxAttribute->base_amount;
                  }
                }
              }

              /**
              * Price
              */
              if ($bundleProduct->getPriceType())
              {
                /**
                 * Bundle with a fixed price
                 * Only the first child has a price, the other are set to 0
                 */
                $totalPrice = (($priceManagement ? $item->getBaseRowTotalInclTax() : $item->getBaseRowTotal()) - $item->getDiscountAmount()) / $child->getQtyOrdered();

                $itemArray['UnitPrice'] = ($i == 0 ? $totalPrice : 0);

              } else {

                /**
                 * Bundle with a dynamic price
                 * Only the children have to be returned to OpenSi with their own price
                 */
                $options = $child->getProductOptions();
                $bundleSelectionAttributes = $this->_manager->create('\Magento\Framework\Serialize\Serializer\Json')->unserialize($options['bundle_selection_attributes']);

                $totalPrice = $bundleSelectionAttributes['price'] / $bundleSelectionAttributes['qty'] + $ecotax;

                $itemArray['UnitPrice'] = $totalPrice;
              }

              $items[] = $itemArray;
              $i++;
            }

          } else {

            /**
             * BUNDLE WITH ONLY ONE PRODUCT IN ONE OPTION
             *
             * The treatment is different depending on the price type (fixed <> dynamic price)
             */
            if ($bundleProduct->getPriceType())
            {
              /**
               * Bundle with a fixed price
               * Only the parent product (bundle reference) is sent to OpenSi with its price but also the price of the childrens
               */
              $itemArray['Reference'] = $item->getSku();
              $itemArray['Name'] = $item->getName();
              $itemArray['Quantity'] = $item->getQtyOrdered();
              $itemArray['VATRate'] = $item->getData('tax_percent');
              $itemArray['DiscountPercent'] = 0;

              if ($priceManagement) {
                $itemArray['UnitPrice'] = $item->getBaseRowTotalInclTax() / $item->getQtyOrdered();
              } else {
                $itemArray['UnitPrice'] = $item->getBaseRowTotal() / $item->getQtyOrdered();
              }

              $items[] = $itemArray;

            } else {

              /**
               * Bundle with a dynamic price
               * Only the children have to be returned to OpenSi
               */
              foreach ($item->getChildrenItems() as $child)
              {
                /**
                 * Product informations
                 */
                $itemArray['Reference'] = $child->getSku();
                $itemArray['Name'] = $child->getName();
                $itemArray['Quantity'] = $child->getQtyOrdered();
                $itemArray['VATRate'] = ($bundleProduct->getPriceType()?$item->getData('tax_percent'):$child->getData('tax_percent'));
                $itemArray['DiscountPercent'] = 0;

                /**
                 * Ecotax
                 */
                $ecotax = 0;
                $ecotaxOptions = $child->getWeeeTaxApplied();

                if (!empty($ecotaxOptions))
                {
                  $ecotaxAttributes = json_decode($ecotaxOptions);

                  foreach ($ecotaxAttributes as $ecotaxAttribute)
                  {
                    if ($priceManagement) {
                      $ecotax = $ecotaxAttribute->amount_incl_tax;
                    } else {
                      $ecotax = $ecotaxAttribute->base_amount;
                    }
                  }
                }

                /**
                 * Price
                 */
                if ($priceManagement) {
                  $itemArray['UnitPrice'] = $child->getBaseRowTotalInclTax() / $child->getQtyOrdered();
                } else {
                  $itemArray['UnitPrice'] = $child->getBaseRowTotal() / $child->getQtyOrdered();
                }

                $items[] = $itemArray;
              }
            }
          }

        } else {

          /**
           * NORMAL PRODUCT (NOT A BUNDLE)
           * ------------------------------------------------------------
           *
           * Get attribute(s) of the item if exist
           */
          $options = $item->getProductOptions();
          $itemOptions = array();

          if (isset($options['attributes_info']))
          {
            // Attributes option
            foreach ($options['attributes_info'] as $option)
            {
              $itemOptions[] = $option['label'].' : '.$option['value'];
            }
          }

          if (isset($options['options']))
          {
            // Product options
            foreach ($options['options'] as $option)
            {
                $itemOptions[] = $option['label'].' : '.$option['value'];
            }
          }

          /**
           * Manage name (concatenate attribute(s) if exist to the name)
           */
          $name = $item->getName();

          if (!empty($itemOptions)) {
            $name .= ' ('.implode(', ', $itemOptions).')';
          }

          /**
           * Tax rate
           */
          $taxRate = 0;

          if ($item->getData('tax_percent') > 0) {
            $taxRate = $item->getData('tax_percent');
          }

          /**
           * Other informations
           */
          $itemArray['Reference'] = $item->getSku();
          $itemArray['Name'] = $name;
          $itemArray['Quantity'] = $item->getQtyOrdered();
          $itemArray['VATRate'] = $taxRate;
          $itemArray['DiscountPercent'] = 0;

          /**
           * Weee tax - Ecotax
           *
           * Important!, the ecotax should always be sent excluding VAT to OpenSi !
           * Depending on the wee tax configuration (System/Configuration/Tax/Fixed Product Taxes/FPT Tax Configuration),
           * it is necessary to remove (or not) the tax on the ecotax to sent to OpenSi.
           *
           * If the catalog price is managed including tax, the ecotax should be filled including tax
           * OR excluding tax with "FPT Tax Configuration" as "Taxed".
           *
           * On the other hand, if it is managed excluding tax, the ecotax should be excluding tax.
           */
          $ecotax = 0;
          $ecotaxOptions = $item->getWeeeTaxApplied();

          if (!empty($ecotaxOptions))
          {
            $ecotaxAttributes = json_decode($ecotaxOptions);

            foreach ($ecotaxAttributes as $ecotaxAttribute)
            {
              switch ($this->getStoreConfigValue('tax/weee/apply_vat'))
              {
                case 0:
                case 2:
                  $weeeTaxApplyVatManagement = false;
                  break;

                case 1:
                  $weeeTaxApplyVatManagement = true;
                  break;
              }

              if ($priceManagement) {
                $ecotax = $ecotaxAttribute->amount_incl_tax;
              } else {
                if ($weeeTaxApplyVatManagement)
                {
                  $ecotax = $ecotaxAttribute->base_amount;
                } else {
                  $ecotax = $ecotaxAttribute->base_amount / (1 + $taxRate / 100);
                }
              }
            }
          }

          /**
           * Price
           */
          if ($priceManagement)
          {
            if ($item->getBasePriceInclTax())
            {
              $totalPrice = $item->getBasePriceInclTax() + $ecotax;
            } else {
              $totalPrice = $this->_manager->create('\Magento\Catalog\Helper\Data')->getTaxPrice($item, $item->getBasePrice(), true) + $ecotax;
            }
          } else {
            $totalPrice = $this->_manager->create('\Magento\Catalog\Helper\Data')->getTaxPrice($item, $item->getBasePrice() + $ecotax, false);
          }

          $itemArray['UnitPrice'] = $totalPrice;
          $items[] = $itemArray;
        }
      }

      /**
       * ShoppingFlux fees
       */
      if ($order->getSfmMarketplaceFeesBaseAmount() > 0)
      {
        /**
         * Create an additional product for ShoppingFlux (price = fees)
         */
        $itemArray['Reference'] = '_FEES';
        if ($order->getPayment()->getAdditionalInformation()['method_title']) {
          $itemArray['Name'] = 'Commission '.$order->getPayment()->getAdditionalInformation()['method_title'];
        } else {
          $itemArray['Name'] = 'Commission Marketplace';
        }

        $itemArray['Quantity'] = 1;
        $itemArray['UnitPrice'] = $order->getSfmMarketplaceFeesBaseAmount();
        $itemArray['VATRate'] = 0;
        $itemArray['DiscountPercent'] = 0;

        $items[] = $itemArray;
      }

      /**
       * return products & orders
       */
      $orderArray['Product'] = $items;
      $orders[] = $orderArray;
    }

    /**
     * Return
     */
    return $orders;
  }








	/*=================================================================
	 *
	 *				G E T   B A N K   T R A N S A C T I O N S
	 *
	 ================================================================*/

	/**
	 * Get bank transactions
	 * Get the list of the bank transactions to create in OpenSi
	 * SWO-P009
	 *
	 * Magento => OpenSi
	 *
	 * @param $values
	 */
	public function getBankTransactions($values)
	{
		/**
		 * Authentification
		 */
		$auth = new Authenticate();

		if (!$auth->login($this->_manager, $this->_header->{'key'})) {
			throw new Exception(OSI_INVALID_AUTH);
		}

		/**
		 * Check configuration depending on the website code
		 */
		$this->checkConfiguration($values);

		/**
		 * Get bank transactions selected modules
		 */
		$modules = array();

		switch ($this->getStoreConfigValue('opensi_configuration/bank_transactions/bank_transactions_standard/payment_methods_type'))
		{
			case 1:
				$modules = $this->getStoreConfigValue('opensi_configuration/bank_transactions/bank_transactions_standard/payment_methods_active');
				break;

			case 2:
				$modules = $this->getStoreConfigValue('opensi_configuration/bank_transactions/bank_transactions_standard/payment_methods');
				break;

			case 3:
				$modules = $this->getStoreConfigValue('opensi_configuration/bank_transactions/bank_transactions_standard/payment_methods_used');
				break;
		}

    if ($modules) {
      $modules = implode('","', explode(',', $modules));
    }

		/**
		 * Get bank transactions collection
		 */
		$transactions = array();
		$transactionsCollection = $this->_manager->create('Magento\Sales\Model\Order\Payment')->getCollection();
		$transactionsCollection->addAttributeToSelect('method');
		$transactionsCollection->addAttributeToSelect('base_amount_paid');

    if (empty($values->{'Order'}))
		{

			/**
			 * No order number specified
			 * Query based on datetime range
			 */
			$datetimeMin = $this->convertDatetoUTC($values->{'Datetime_Min'});
 			$datetimeMax = $this->convertDatetoUTC($values->{'Datetime_Max'});

			$transactionsCollection->addFieldToFilter('si.created_at',
				array('date' => true, 'from' => $datetimeMin, 'to' => $datetimeMax)
			);

		} else {

			/**
			 * At least one order number is specified
			 * Query based on the order id(s)
			 */
			$orderNumbers = array();

			if (!is_array($values->{'Order'})) {
				$values->{'Order'} = array($values->{'Order'});
			}

			foreach ($values->{'Order'} as $value)
			{
				$orderNumbers[] = $value->OrderNumber;
			}

			$transactionsCollection->addAttributeToFilter('so.increment_id', array('in' => $orderNumbers));
		}

		$txnTypes = '"'.str_replace(',', '","', $this->getStoreConfigValue('opensi_configuration/bank_transactions/bank_transactions_advanced/payment_txn_types')).'"';

		$transactionsCollection
			->getSelect()
			->join(array('so' => $transactionsCollection->getTable('sales_order')), 'so.entity_id = main_table.parent_id', array('increment_id'))
			->join(array('si' => $transactionsCollection->getTable('sales_invoice')), 'si.order_id = main_table.parent_id', array('created_at'))
			->joinLeft(array('spt' => $transactionsCollection->getTable('sales_payment_transaction')), 'spt.order_id = so.entity_id AND spt.txn_type IN ('.$txnTypes.') AND spt.txn_id = si.transaction_id', array('txn_id'))
			->where('so.store_id = '.$this->getCurrentStoreId())
			->where('main_table.method IN ("'.$modules.'")')
			->where('so.base_grand_total > 0')
			->where('main_table.base_amount_paid > 0');


		/** Store credit - Magento Enterprise extension module - Magento_CustomerBalance */
		$moduleManager = $this->_manager->create('\Magento\Framework\Module\Manager');

		if ($moduleManager->isEnabled('Magento_CustomerBalance') && $moduleManager->isOutputEnabled('Magento_CustomerBalance'))
		{
			$ordersCollection = $this->_manager->create('Magento\Sales\Model\Order')->getCollection();

			$fieldCredit = new \Zend_Db_Expr('"store_credit"');
			$fieldTxnId = new \Zend_Db_Expr('null');

			$ordersCollection
				->getSelect()
				->reset(\Zend_Db_Select::COLUMNS)
				->columns(array('method' => $fieldCredit))
				->columns(array('base_amount_paid' => 'base_customer_balance_amount'))
				->columns(array('increment_id'))
				->columns(array('created_at'))
				->columns(array('txn_id' => $fieldTxnId));

			if (empty($values->{'Order'})) {
				$ordersCollection->addFieldToFilter('created_at', array('date' => true, 'from' => $datetimeMin, 'to' => $datetimeMax));
			} else {
				$ordersCollection->addAttributeToFilter('increment_id', array('in' => $orderNumbers));
			}

			$ordersCollection
				->getSelect()
				->join(array('mcb' => $ordersCollection->getTable('magento_customerbalance')), 'mcb.customer_id = main_table.customer_id', array())
				->join(array('mcbh' => $ordersCollection->getTable('magento_customerbalance_history')), 'mcbh.balance_id = mcb.balance_id AND mcbh.action = 3 AND ABS(mcbh.balance_delta) = main_table.base_customer_balance_amount AND SUBSTRING_INDEX(additional_info, "#", -1) = main_table.increment_id', array())
				->where('main_table.store_id = '.$this->getCurrentStoreId())
				->where('main_table.base_customer_balance_amount > 0');

			$cloneTransactionsCollection = clone $transactionsCollection->getSelect();
			$transactionsCollection
				->getSelect()
				->reset()
				->union(
					array(
						$cloneTransactionsCollection,
						$ordersCollection->getSelect()
					)
				);

			$transactionsCollection->getSelect()->limit($values->{'Range_Max'}, $values->{'Range_Min'});
		}
		/** End Store credit */


    /** Amasty Gift Card */
    if ($moduleManager->isEnabled('Amasty_GiftCardAccount') && $moduleManager->isOutputEnabled('Amasty_GiftCardAccount'))
    {
      $ordersCollection = $this->_manager->create('Magento\Sales\Model\Order')->getCollection();

      $fieldCredit = new \Zend_Db_Expr('"gift_card"');
      $fieldTxnId = new \Zend_Db_Expr('null');

      $ordersCollection
        ->getSelect()
        ->reset(\Zend_Db_Select::COLUMNS)
        ->columns(array('method' => $fieldCredit))
        ->columns(array('base_amount_paid' => 'agco.base_invoice_gift_amount'))
        ->columns(array('increment_id'))
        ->columns(array('created_at'))
        ->columns(array('txn_id' => $fieldTxnId));

      if (empty($values->{'Order'})) {
        $ordersCollection->addFieldToFilter('created_at', array('date' => true, 'from' => $datetimeMin, 'to' => $datetimeMax));
      } else {
        $ordersCollection->addAttributeToFilter('increment_id', array('in' => $orderNumbers));
      }

      $ordersCollection
        ->getSelect()
        ->join(array('agco' => $ordersCollection->getTable('amasty_giftcard_order')), 'agco.order_id = main_table.entity_id', array())
        ->where('main_table.store_id = '.$this->getCurrentStoreId())
        ->where('agco.base_invoice_gift_amount > 0');

      $cloneTransactionsCollection = clone $transactionsCollection->getSelect();
      $transactionsCollection
        ->getSelect()
        ->reset()
        ->union(
          array(
            $cloneTransactionsCollection,
            $ordersCollection->getSelect()
          )
        );

      $transactionsCollection->getSelect()->limit($values->{'Range_Max'}, $values->{'Range_Min'});
    }
    /** End Amasty Gift Card */


		foreach ($transactionsCollection as $transaction)
		{
			/**
			 * Bank transactions
			 * Construct transactions array
			 */
			$transactionsToGenerate = array();

			if ($transaction->getTxnId()) {
				$transactionNumber = $transaction->getTxnId();
			} else {
				$transactionNumber = $transaction->getIncrementId();
			}

			if ($transaction->getCreatedAt()) {
				$transactionDate = $transaction->getCreatedAt();
			} else {
				$transactionDate = date('Y-m-d H:i:s');
			}

			/**
       * Payment method
       */
      switch ($transaction->getMethod()) {
        case 'store_credit':
          $paymentMethod = __('Store Credit');
          break;
        case 'gift_card':
          $paymentMethod = __('Gift Card');
          break;
        default:
          $paymentMethod = $this->_manager->create('\Magento\Payment\Helper\Data')->getMethodInstance($transaction->getMethod())->getConfigData('title');
          break;
      }

      /**
       * Generate transactions
       */
      $transactionsToGenerate[] = array(
        'OrderNumber' => $transaction->getIncrementId(),
        'TransactionNumber' => $transactionNumber,
        'PaymentMethod' => $paymentMethod,
        'Amount' => $transaction->getBaseAmountPaid(),
        'TransactionDate' => $this->convertDatetoTimezone($transactionDate),
        'Valid' => '1'
      );

			foreach ($transactionsToGenerate as $key => $transactionToGenerate)
			{
				$key++;

				$transactions[] = array(
					'OrderNumber' => $transactionToGenerate['OrderNumber'],
					'TransactionNumber' => ($transactionsToGenerate && count($transactionsToGenerate) > 1?$transactionToGenerate[TransactionNumber].'-'.$key:$transactionToGenerate['TransactionNumber']),
					'PaymentMethod' => $transactionToGenerate['PaymentMethod'],
					'Amount' => $transactionToGenerate['Amount'],
					'TransactionDate' => $transactionToGenerate['TransactionDate'],
					'Valid' => $transactionToGenerate['Valid']
				);
			}
		}

		/**
		 * Return
		 */
		return $transactions;
	}








	/*=================================================================
	 *
	 *			G E T   C U S T O M E R S   F R O M   S H O P
	 *
	 ================================================================*/

	/**
	 * Get Customers
	 * Get the list of the customers to create in OpenSi
	 * SWO-P082
	 *
	 * Magento => OpenSi
	 *
	 * @param $values
	 * @return $customers
	 */
	public function getCustomers($values)
	{
		/**
		 * Authentification
		 */
		$auth = new Authenticate();

		if (!$auth->login($this->_manager, $this->_header->{'key'})) {
			throw new Exception(OSI_INVALID_AUTH);
		}

		/**
		 * Check configuration depending on the website code
		 */
		$this->checkConfiguration($values);

		/**
		 * Get customers collection
		 */
		$customers = array();
		$customersCollection = $this->_manager->create('\Magento\Customer\Model\Customer')->getCollection();
		$customersCollection
			->addAttributeToSelect('*')
		  ->joinAttribute('company', 'customer_address/company', 'default_billing', null, 'left')
		  ->joinAttribute('street', 'customer_address/street', 'default_billing', null, 'left')
		  ->joinAttribute('zipcode', 'customer_address/postcode', 'default_billing', null, 'left')
		  ->joinAttribute('city', 'customer_address/city', 'default_billing', null, 'left')
		  ->joinAttribute('phone', 'customer_address/telephone', 'default_billing', null, 'left')
		  ->joinAttribute('fax', 'customer_address/fax', 'default_billing', null, 'left')
		  ->joinAttribute('country_code', 'customer_address/country_id', 'default_billing', null, 'left');

		if (empty($values->{'Customer'}))
		{
		  /**
		   * No customer id specified
		   * Query based on datetime range
		   */
		  $datetimeMin = $this->convertDatetoUTC($values->{'Datetime_Min'});
		  $datetimeMax = $this->convertDatetoUTC($values->{'Datetime_Max'});

		  $customersCollection->addFieldToFilter(array(
		    array('attribute'=>'created_at', 'from' => $datetimeMin, 'to' => $datetimeMax),
		    array('attribute'=>'updated_at', 'from' => $datetimeMin, 'to' => $datetimeMax)
		  ));

		} else {

		  /**
		   * At least one customer id is specified
		   * Query based on the customer id(s)
		   */
		  $customerIds = array();

		  if (!is_array($values->{'Customer'})) {
		    $values->{'Customer'} = array($values->{'Customer'});
		  }

		  foreach ($values->{'Customer'} as $value)
		  {
		    $customerIds[] = $value->CustomerId;
		  }

		  $customersCollection->addAttributeToFilter('entity_id', array('in' => $customerIds));
		}
		$customersCollection
			->addFieldToFilter('store_id', $this->getCurrentStoreId())
		  ->getSelect()
		  ->limit($values->{'Range_Max'}, $values->{'Range_Min'});

		/**
		 * Get customer informations
		 */
		foreach ($customersCollection as $customer)
		{
			$customerArray = array();

		  $customerArray['Login'] = $customer->getEmail();
			$customerArray['CustomerId'] = $customer->getEntityId();

			$customerGroup = $this->_manager->create('\Magento\Customer\Model\Group')->load($customer->getGroupId())->getCustomerGroupCode();
			if ($customerGroup) {
			  $customerArray['CustomerGroup'] = $customerGroup;
			}

			if (!empty($customer->getGender())) {
				$customerArray['Civility'] = $customer->getGender();
			}

			$customerArray['Lastname'] = $customer->getLastname();
			$customerArray['Firstname'] = $customer->getFirstname();

			if (!empty($customer->getCompany())) {
				$customerArray['Company'] = $customer->getCompany();
			}

			$street = explode(PHP_EOL, $customer->getStreet());
			$customerArray['Address_1'] = $street[0];

			if (!empty($street[1])) {
				$customerArray['Address_2'] = $street[1];
			}

			$customerArray['Zipcode'] = $customer->getCity();
			$customerArray['Phone'] = $customer->getPhone();
			$customerArray['City'] = $customer->getCity();

			if (!empty($customer->getFax())) {
				$customerArray['Fax'] = $customer->getFax();
			}

			$customerArray['Email'] = $customer->getEmail();
			$customerArray['CountryCode'] = $customer->getCountryCode();

			/**
			 * Return customer
			 */
			$customers[] = $customerArray;
		}

		/**
		 * Return customers
		 */
		return $customers;

	}








	/*=================================================================
	 *
	 *			G E T   P R I C E S   F R O M   S H O P
	 *
	 ================================================================*/

	/**
	 * Get Prices
	 * Get the list of the product prices to update in OpenSi
	 * SWO-P026
	 *
	 * Magento => OpenSi
	 *
	 * @param $values
	 * @return $products prices
	 */
	public function getPrices($values)
	{
	  if (($productsCollection = $this->getProductsCollection($values)) != false)
	  {
	    $productsPrice = array();

	    foreach ($productsCollection as $product)
	    {
				$priceArray = array();

				/**
	       * Get fields to sync depending on the configuration
	       */
	      $fieldsToSync = array();
        $customWebservice = $this->getStoreConfigValue('opensi_preferences/manage_flux/products_price/fields_sync');

	      if ($customWebservice)
	      {
	        if ($this->getStoreConfigValue('opensi_preferences/manage_flux/products_price/fields_to_sync')) {
            $fieldsToSync = explode(',', $this->getStoreConfigValue('opensi_preferences/manage_flux/products_price/fields_to_sync'));
          }
	      }

	      /**
	       * Load current product
	       */
				$productOsi = $this->_manager->create('\Magento\Catalog\Model\Product')->load($product->getId());
				$priceManagement = $this->getStoreConfigValue('tax/calculation/price_includes_tax');

				/**
				 * Reference
				 */
				$priceArray['Reference'] = $productOsi->getSku();

				/**
				 * Price definition
				 */
				$priceArray['IncludingVATPriceDefinition'] = $priceManagement;

				/**
				 * Tax rate
				 */
				if ($this->isSynchronizable('tax_rate', $fieldsToSync, $customWebservice))
 				{
					$taxRate = $this->_manager->create('\Magento\Tax\Model\TaxCalculation')->getCalculatedRate($productOsi->getTaxClassId());
					$priceArray['VATRate'] = $taxRate;
				}

				/**
				 * Weee tax - Ecotax
				 *
				 * Important!, the ecotax should always be sent excluding VAT to OpenSi !
				 * Depending on the wee tax configuration (System/Configuration/Tax/Fixed Product Taxes/FPT Tax Configuration),
				 * it is necessary to remove (or not) the tax on the ecotax to sent to OpenSi.
				 *
				 * If the catalog price is managed including tax, the ecotax should be filled including tax
				 * OR excluding tax with "FPT Tax Configuration" as "Taxed".
				 *
				 * On the other hand, if it is managed excluding tax, the ecotax should be excluding tax.
				 */
				$ecotax = 0;
				$ecotaxForOpenSi = 0;

				if ($this->isSynchronizable('ecotax', $fieldsToSync, $customWebservice))
				{
				  if ($this->getStoreConfigValue('tax/weee/enable'))
				  {
				    $weeTaxCodes = $this->_manager->create('\Magento\Catalog\Model\Resourcemodel\Eav\Attribute')->getAttributeCodesByFrontendType('weee');

				    foreach ($weeTaxCodes as $weeTaxCode)
				    {
				      $ecotaxList = $productOsi->getResource()->getAttribute($weeTaxCode)->getFrontend()->getValue($productOsi);

				      if ($ecotaxList && count($ecotaxList) > 0)
				      {
				        foreach ($ecotaxList as $value)
				        {
				          switch ($this->getStoreConfigValue('tax/weee/apply_vat'))
				          {
				            case 0:
				            case 2:
				              $weeeTaxApplyVatManagement = false;
				              break;

				            case 1:
				              $weeeTaxApplyVatManagement = true;
				              break;
				          }

				          if ($weeeTaxApplyVatManagement)
				          {
				            $ecotax = $value['value'] * (1 + $taxRate / 100);
				            $ecotaxForOpenSi = $value['value'];
				          } else {
				            $ecotax = $value['value'];
				            $ecotaxForOpenSi = $value['value'] / (1 + $taxRate / 100);
				          }
				        }
				      }
				    }
				    $priceArray['Ecotax'] = $ecotaxForOpenSi;
				  } else {
				    $priceArray['Ecotax'] = 0;
				  }
				}

				/**
				 * Get product price or special price
				 *
				 * Depending on the preferences, need to get the special price or the normal price
				 * Depending on the configuration, need to send to OpenSi this price excluding or including tax
				 */
				if ($this->getStoreConfigValue('opensi_preferences/manage_prices/prices') == 2)
				{
					$productPrice = 0;

				  // Get special price if available
				  $now = date('Y-m-d H:i:s');

				  if ($productOsi->getSpecialPrice() && $now > $productOsi->getSpecialFromDate() && ($now < $productOsi->getSpecialToDate() || $productOsi->getSpecialToDate() == ''))
				  {
				    // Special price is defined and now is in the range of dates defined
				    if ($priceManagement) {
				      $productPrice = $productOsi->getPriceInfo()->getPrice('special_price')->getAmount()->getValue();
				    } else {
				      $productPrice = $productOsi->getPriceInfo()->getPrice('special_price')->getAmount()->getBaseAmount()  + ($ecotax / (1 + $taxRate / 100));
				    }

				  } else {

				    // Special price is defined but now is NOT in the range of dates defined
				    if ($priceManagement) {
				      $productPrice = $productOsi->getPriceInfo()->getPrice('regular_price')->getAmount()->getValue();
				    } else {
				      $productPrice = $productOsi->getPriceInfo()->getPrice('regular_price')->getAmount()->getBaseAmount()  + ($ecotax / (1 + $taxRate / 100));
				    }

				  }

				} else {

				  // Get regular price
				  if ($priceManagement) {
				    $productPrice = $productOsi->getPriceInfo()->getPrice('regular_price')->getAmount()->getValue();
				  } else {
				    $productPrice = $productOsi->getPriceInfo()->getPrice('regular_price')->getAmount()->getBaseAmount() + ($ecotax / (1 + $taxRate / 100));
				  }
				}

				if ($this->isSynchronizable('price', $fieldsToSync, $customWebservice)) {
					$priceArray['Price'] = $productPrice;
				}

				/**
				 * Wholesale price
				 */
				if ($this->isSynchronizable('wholesale_price', $fieldsToSync, $customWebservice)) {
 					if ($this->isProductAttributeExists('cost')) {
 						$priceArray['WholesalePrice'] = ($productOsi->getCost() ? $productOsi->getCost() : 0);
 					}
 				}

				/**
				 * Retail price (public price - MSRP)
				 */
				if ($this->isSynchronizable('retail_price', $fieldsToSync, $customWebservice))
				{
				  if ($this->getStoreConfigValue('opensi_preferences/manage_prices/retail_price'))
				  {
				    // Custom attribute
				    $retailPrice = $productOsi->getResource()->getAttribute($this->getStoreConfigValue('opensi_preferences/manage_prices/retail_price_custom'))->getFrontend()->getValue($productOsi);

				  } else {

				    // MSRP (default)
				    $retailPrice = $productOsi->getMsrp();

				  }

				  if ($retailPrice && is_numeric($retailPrice)) {
				    $priceArray['RetailPrice'] = $retailPrice;
				  }
				}

				/**
				 * Return product
				 */
				$productsPrice[] = $priceArray;
	    }

	    /**
	     * Return
	     */
	    return $productsPrice;
	  }
	}








	/*=================================================================
	 *
	 * 						S E T   S T O C K S
	 *
	 ================================================================*/

	/**
	 * Set stocks
	 * Update stock informations on products on the store
	 * SWO-G005
	 *
	 * OpenSi => Magento
	 *
	 * @param $values
	 */
	public function setStocks($values)
	{
		/**
		 * Authentification
		 */
		$auth = new Authenticate();

		if (!$auth->login($this->_manager, $this->_header->{'key'})) {
			throw new Exception(OSI_INVALID_AUTH);
		}

		/**
		 * Check configuration depending on the website code
		 */
		$this->checkConfiguration($values);

		/**
		 * Update stock
		 */
		$stockResponse = array();
 		$reindexProductIds = array();
		$childProductMapping = null;

 		if (!is_array($values->{'Stock'})) {
 			$values->{'Stock'} = array($values->{'Stock'});
 		}

 		foreach ($values->{'Stock'} as $item)
 		{
			$productId = $this->_manager->create('Magento\Catalog\Model\Product')->getIdBySku($item->Reference);

			if ($productId)
			{
				// Check if item has reserved stock
				$reservedStock = $this->_manager->create('\Magento\InventoryReservations\Model\ResourceModel\GetReservationsQuantity')->execute($item->Reference, 1);

				/**
				 * Update stock depending on the stock management configuration
				 */
        $supplierCustomField = false; // See below : Update supplier stock in custom field

				switch($this->getStoreConfigValue('opensi_preferences/manage_stocks/stocks'))
				{
					case 1:
						$newStock = max($item->AvailableStock, 0) + abs($reservedStock);
            $stockStatus = max($item->AvailableStock, 0);
						break;
					case 2:
						$newStock = $item->SupplierAvailability;
            $stockStatus = $newStock;
						break;
					case 3:
						$newStock = max($item->AvailableStock, 0) + abs($reservedStock) + $item->SupplierAvailability;
            $stockStatus = max($item->AvailableStock, 0) + $item->SupplierAvailability;
						break;
					case 4:
						$newStock = $item->RealStock;
            $stockStatus = $newStock;
						break;
					case 5:
						$newStock = $item->RealStock + $item->SupplierAvailability;
            $stockStatus = $newStock;
						break;
					case 6:
						$percent = 100 - $this->getStoreConfigValue('opensi_preferences/manage_stocks/stock_percentage');
						$newStock = floor($item->AvailableStock * (1 - ($percent / 100)));
            $stockStatus = $newStock;
						break;
					case 7:
						$newStock = $item->VirtualStock;
            $stockStatus = $newStock;
						break;
          case 8:
            $newStock = max($item->AvailableStock, 0) + abs($reservedStock);
            $stockStatus = max($item->AvailableStock, 0);
            $supplierCustomField = true; // See below : Update supplier stock in custom field
            break;
					default:
            $newStock = max($item->AvailableStock, 0) + abs($reservedStock);
            $stockStatus = max($item->AvailableStock, 0);
						break;
				}

				/**
				 * Get fields to sync
				 */
				$fieldsToSync = array();
        $customWebservice = $this->getStoreConfigValue('opensi_preferences/manage_flux/products_stock/fields_sync');

				if ($customWebservice)
				{
					if ($this->getStoreConfigValue('opensi_preferences/manage_flux/products_stock/fields_to_sync')) {
            $fieldsToSync = explode(',', $this->getStoreConfigValue('opensi_preferences/manage_flux/products_stock/fields_to_sync'));
          }
				}

				/**
				 * Update is made only if the new stock is different!
				 */
				if ($this->isSynchronizable('stock', $fieldsToSync, $customWebservice))
 	    	{
          // Update stock item (on recents versions of Magento 2.0 (> 2.4.3), the field "stock_status" on table "cataloginventory_stock_status" is no more updated, need to update it manually!)
          $stockItem = $this->_manager->create('\Magento\CatalogInventory\Api\StockRegistryInterface')->getStockItemBySku($item->Reference);

          if ($stockItem->getQty() != $newStock)
          {
            $stockItem->setData('qty', $newStock);

            if ($stockItem->getBackorders() > 0) {
              $stockItem->setData('is_in_stock', 1);
            } else {
              $stockItem->setData('is_in_stock', (bool)$newStock);
            }

            $this->_manager->create('\Magento\CatalogInventory\Api\StockRegistryInterface')->updateStockItemBySku($item->Reference, $stockItem);
          }

          // Update source(s) stock
					$sourceItemList = $this->_manager->create('\Magento\InventoryApi\Api\GetSourceItemsBySkuInterface')->execute($item->Reference);
					$source = reset($sourceItemList); //Stock update is only made on the first source (e.g. basically the defaut source - same behavior as the getInventory feed)
					$stockItemConfiguration = $this->_manager->create('\Magento\InventoryConfigurationApi\Api\GetStockItemConfigurationInterface')->execute($item->Reference, 1); // Get backorders - Allow quantity below 0

					if (empty($source))
					{
						/**
						 * No source found for the requested reference, assign the new value to the default source!
						 */
						$sourceItemList = $this->_manager->create('\Magento\Inventory\Model\ResourceModel\Source\Collection');

						foreach ($sourceItemList as $sourceItem)
						{
							$source = $this->_manager->create('\Magento\InventoryApi\Api\Data\SourceItemInterfaceFactory')->create();
							$source->setSourceCode($sourceItem->getSourceCode());
							$source->setSku($item->Reference);
							$source->setQuantity($newStock);

							if ($stockItemConfiguration->getBackorders() > 0) {
								$source->setStatus(1);
							} else {
								$source->setStatus((bool)$stockStatus);
							}

							// Pass the source as an array element, we can add more source items in the same call as further array elements
							$this->_manager->create('\Magento\InventoryApi\Api\SourceItemsSaveInterface')->execute([$source]);
						}

					} else {

						/**
						 * Source found for the requested reference, assign the new value to the current source!
						 */
						if ($source->getQuantity() != $newStock)
						{
							// Set new source quantity
							$source->setSourceCode($source['source_code']);
							$source->setSku($item->Reference);
							$source->setQuantity($newStock);

							if ($stockItemConfiguration->getBackorders() > 0) {
								$source->setStatus(1);
							} else {
								$source->setStatus((bool)$stockStatus);
							}

							// Pass the source as an array element, we can add more source items in the same call as further array elements
							$this->_manager->create('\Magento\InventoryApi\Api\SourceItemsSaveInterface')->execute([$source]);
						}
					}

					/**
					 * Update parent availability ("is_in_stock" field)
					 * For grouped & configurable products
					 */
					$isParentInStock = 0;
					$parentProductId = $this->hasParent($productId);

					if ($parentProductId)
					{
						/** @var \Magento\Catalog\Model\Product $productParent */
						$productParent = $this->_manager->create('\Magento\Catalog\Model\Product')->load($parentProductId);

						if ($productParent->getTypeId() == \Magento\GroupedProduct\Model\Product\Type\Grouped::TYPE_CODE || $productParent->getTypeId() == \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE)
						{
							if (!isset($childProductMapping[$parentProductId]))
							{
								$childProductIds = $productParent->getTypeInstance()->getChildrenIds($parentProductId);

								foreach ($childProductIds as $childProductIdsArray)
								{
									foreach ($childProductIdsArray as $childProductId)
									{
										$productChild = $this->_manager->create('\Magento\Catalog\Model\Product')->load($childProductId);
										$childProductMapping[$parentProductId][] = $productChild["sku"];
									}
								}
							}

							if (!empty($childProductMapping[$parentProductId]))
							{
								foreach ($childProductMapping[$parentProductId] as $childSku)
								{
									// Check child products quantity. If in stock, the parent must be set as available too!
									$sourceChildsItemList = $this->_manager->create('\Magento\InventoryApi\Api\GetSourceItemsBySkuInterface')->execute($childSku);

									foreach ($sourceChildsItemList as $sourceChild)
									{
										if ($sourceChild->getQuantity() > 0) {
											$isParentInStock++;
										}
									}
								}

                // Update cataloginventory_stock_item
                $parentStockItem = $this->_manager->create('\Magento\CatalogInventory\Api\StockRegistryInterface')->getStockItem($parentProductId);
                $parentStockItem->setQty($productParent->getQty());
                $parentStockItem->setIsInStock(($stockItemConfiguration->getBackorders() > 0 ? 1 : (bool)$isParentInStock));
                $parentStockItem->setStockStatusChangedAuto(true);
                $parentStockItem->save();
							}
						}
					}
				}

				/**
				 * Update expiration date (DLC)
				 */
				$attributesToUpdate = array();
				$entityIds = array();

				if ($this->isSynchronizable('expiration_date', $fieldsToSync, $customWebservice))
  	    		{
					$attributeExpirationDate = $this->getStoreConfigValue('opensi_configuration/attributes/expiration_date');

					if ($attributeExpirationDate && isset($item->ExpirationDate) && $item->ExpirationDate)
					{
						if (!in_array($productId, $entityIds)) {
							$entityIds[] = $productId;
						}

						$attributesToUpdate[$attributeExpirationDate] = $item->ExpirationDate;
					}
				}

        /**
         * Update date availability
         */
				if ($this->isSynchronizable('date_availability', $fieldsToSync, $customWebservice))
				{
					$attributeDateAvailability = $this->getStoreConfigValue('opensi_configuration/attributes/date_availability');

					if ($attributeDateAvailability && isset($item->DateAvailability) && $item->DateAvailability)
					{
						if (!in_array($productId, $entityIds)) {
							$entityIds[] = $productId;
						}

						$attributesToUpdate[$attributeDateAvailability] = $item->DateAvailability;
					}
				}

        /**
         * Update picking zone
         */
				if ($this->isSynchronizable('picking_zone', $fieldsToSync, $customWebservice))
				{
					$attributePickingZone = $this->getStoreConfigValue('opensi_configuration/attributes/picking_zone');

					if ($attributePickingZone && isset($item->PickingZone) && $item->PickingZone)
					{
						if (!in_array($productId, $entityIds)) {
							$entityIds[] = $productId;
						}

						$attributesToUpdate[$attributePickingZone] = $item->PickingZone;
					}
				}

        /**
         * Update supplier stock in custom field
         */
        if ($supplierCustomField)
        {
          $supplierAttribute = $this->getStoreConfigValue('opensi_preferences/manage_stocks/supplier_attribute');

          if ($supplierAttribute && isset($item->SupplierAvailability) && $item->SupplierAvailability)
					{
            if (!in_array($productId, $entityIds)) {
							$entityIds[] = $productId;
						}

						$attributesToUpdate[$supplierAttribute] = $item->SupplierAvailability;
          }
        }

        /**
         * Set attributes to update
         */
				if (!empty($attributesToUpdate) && !empty($entityIds))
				{
					$storeId = ($this->isDefaultWebsite($this->getCurrentStoreId()) == 1?0:$this->getCurrentStoreId());
					$this->_manager->create('\Magento\Catalog\Model\ResourceModel\Product\Action')->updateAttributes($entityIds, $attributesToUpdate, $storeId);
				}

				/**
				 * Set products to index after update
				 */
				$reindexProductIds[] = $productId;

				/**
				 * Response
				 */
				$stockResponse[] = new \Speedinfo\Opensi\Webservices\Classes\Response($item->Reference, OSI_SUCCESS_UPDATE);

			} else {

				/**
				 * The reference doesn't exist!
				 */
				$stockResponse[] = new \Speedinfo\Opensi\Webservices\Classes\Response($item->Reference, OSI_ERROR_NOT_FOUND, OSI_INVALID_REFERENCE);

			}
		}

		/**
		 * Clear cache & reindex product(s)
		 */
		$this->clearCacheAndReindex($reindexProductIds, 'cataloginventory_stock');

		/**
		 * Return
		 */
		return array('return' => $stockResponse);
	}








	/*=================================================================
	 *
	 * 						S E T   P R I C E S
	 *
	 ================================================================*/

	/**
 	 * Set prices
 	 * Update price informations on products on the store
 	 * SWO-G002
 	 *
 	 * OpenSi => Magento
 	 *
 	 * @param $values
 	 */
 	public function setPrices($values)
 	{
		/**
		 * Authentification
		 */
		$auth = new Authenticate();

		if (!$auth->login($this->_manager, $this->_header->{'key'})) {
			throw new Exception(OSI_INVALID_AUTH);
		}

		/**
		 * Check configuration depending on the website code
		 */
		$this->checkConfiguration($values);

		/**
		 * Update prices
		 */
		$priceResponse = array();
		$reindexProductIds = array();

 		if (!is_array($values->{'Price'})) {
 			$values->{'Price'} = array($values->{'Price'});
 		}

		foreach ($values->{'Price'} as $item)
		{
			/**
			 * Update price informations
			 */
			$productIdBySku = $this->_manager->create('Magento\Catalog\Model\Product')->getIdBySku($item->Reference);

			if ($productIdBySku)
 	    {
				/**
				 * Load product
				 */
				$product = $this->_manager->create('\Magento\Catalog\Model\ProductRepository')->getById($productIdBySku);

				/**
				 * Update informations on product
				 */
				$this->setProductPrice($product, $item, false);

				/**
				 * Depending on the configuration, update or not products parent prices
				 */
				if ($this->getStoreConfigValue('opensi_preferences/manage_prices/parent_price'))
				{
					$parentId = $this->hasParent($product->getId());

					if ($parentId)
					{
					  $parentProduct = $this->_manager->create('\Magento\Catalog\Model\Product')->load($parentId);

						$newItem = (object) array(
							'Reference' => $parentProduct->getSku(),
							'VATRate' => $item->VATRate,
							'WholesalePrice' => $item->WholesalePrice,
							'CostPrice' => $item->CostPrice,
							'PriceExcludingVAT' => $item->PriceExcludingVAT,
							'PriceIncludingVAT' => $item->PriceIncludingVAT,
							'Ecotax' => ($this->getStoreConfigValue('tax/weee/enable') ? $item->Ecotax : 0),
							'RetailPrice' => $item->RetailPrice
						);

						$this->setProductPrice($parentProduct, $newItem, true);

						$parentProduct->cleanCache();
						$reindexProductIds[] = $parentProduct->getId();
					}
				}

				/**
				 * Set products to index after update
				 */
				$product->cleanCache();
				$reindexProductIds[] = $product->getId();

				/**
				 * Response
				 */
				$priceResponse[] = new \Speedinfo\Opensi\Webservices\Classes\Response($item->Reference, OSI_SUCCESS_UPDATE);

			} else {

				/**
	       * The reference doesn't exist!
	       */
	      $priceResponse[] = new \Speedinfo\Opensi\Webservices\Classes\Response($item->Reference, OSI_ERROR_NOT_FOUND, OSI_INVALID_REFERENCE);

			}
		}

		/**
		 * Clear cache & reindex product(s)
		 */
		$this->clearCacheAndReindex($reindexProductIds, 'catalog_product_price');

		/**
		 * Return
		 */
		return array('return' => $priceResponse);
	}


	/**
	 * Set product price
	 *
	 * @param $product
	 * @param $item
	 * @param $fieldsToSync
	 * @param $parent
	 */
	private function setProductPrice($product, $item, $parent = false)
	{
		/**
		 * Definitions
		 */
		$priceManagement = $this->getStoreConfigValue('tax/calculation/price_includes_tax');
		$ecotax = 0;

		/**
		 * Get fields to sync
		 */
		$fieldsToSync = array();
    $customWebservice = $this->getStoreConfigValue('opensi_preferences/manage_flux/products_price/fields_sync');

		if ($customWebservice)
		{
			if ($this->getStoreConfigValue('opensi_preferences/manage_flux/products_price/fields_to_sync')) {
        $fieldsToSync = explode(',', $this->getStoreConfigValue('opensi_preferences/manage_flux/products_price/fields_to_sync'));
      }
		}

		/**
		 * Update price depending on the configuration ('tax/calculation/price_includes_tax')
		 */
		if ($this->isSynchronizable('price', $fieldsToSync, $customWebservice))
		{
			switch ($this->getStoreConfigValue('tax/weee/apply_vat'))
			{
			  case 0:
			  case 2:
			    $weeeTaxApplyVatManagement = false;
			    break;

			  case 1:
			    $weeeTaxApplyVatManagement = true;
			    break;
			}

			if ($priceManagement)
			{
				// Price including VAT
				if ($this->getStoreConfigValue('tax/weee/enable'))
				{
					// Ecotax enabled
					if ($weeeTaxApplyVatManagement)
					{
						// Ecotax has a tax (System / Configuration / FPT / FPT Tax Configuration)
						$ecotax = $item->Ecotax;
						$price = $item->PriceIncludingVAT - $ecotax;
					} else {
						// Ecotax has no tax
						$ecotax = $item->Ecotax * (1 + ($item->VATRate / 100));
						$price = $item->PriceIncludingVAT - $ecotax;
					}
				} else {
					// Ecotax disabled
					$price = $item->PriceIncludingVAT;
				}

			} else {

				// Price excluding VAT
				if ($this->getStoreConfigValue('tax/weee/enable'))
				{
					// Ecotax enabled
					if ($weeeTaxApplyVatManagement)
					{
						// Ecotax has a tax (System / Configuration / FPT / FPT Tax Configuration)
						$ecotax = $item->Ecotax;
						$price = $item->PriceExcludingVAT - $ecotax;
					} else {
						// Ecotax has no tax
						$ecotax = $item->Ecotax * (1 + ($item->VATRate / 100));
						$price = ($item->PriceIncludingVAT - $ecotax) / (1 + ($item->VATRate / 100));
					}
				} else {
					// Ecotax disabled
					$price = $item->PriceExcludingVAT;
				}
			}

			// If negative price
			if ($price < 0) {
				$price = 0;
			}

			/**
			 * Define the params if the price scope is set to website instead of global
			 * catalog/price/scope (0 => global, 1 => website)
			 */
			$storeId = $this->getCurrentStoreId();

			if ($this->getStoreConfigValue('catalog/price/scope')) {

				/**
				 * Price scope : website
				 * If the website code sent by OpenSi corresponds to default website, the websiteId & storeId need to be set to 0 in order to update the default configuration
				 */
				if ($this->isDefaultWebsite($storeId) == 1) {
					$storeId = 0;
					$websiteId = 0;
				}

			} else {

				/**
				 * Price scope : global
				 * If the price are managed in global, the websiteId & storeId need to be set to 0 in order to update the default configuration
				 */
				$storeId = 0;
				$websiteId = 0;

			}

			if ($product->getPrice() != $price) {
				$product->addAttributeUpdate('price', $price, $storeId);
			}
		}

		/**
		 * Update wholesale price depending on the configuration
		 */
		if ($this->isSynchronizable('wholesale_price', $fieldsToSync, $customWebservice) || $product->getCost() == null)
		{
			switch ($this->getStoreConfigValue('opensi_preferences/manage_prices/wholesale_prices'))
			{
				case 1:
					$wholesaleprice = $item->WholesalePrice;
					break;
				case 2:
					$wholesaleprice = $item->CostPrice;
					break;
				case 3:
					$wholesaleprice = $item->CUMP;
					break;
			}

			if ($product->getCost() != $wholesaleprice) {
				$product->addAttributeUpdate('cost', $wholesaleprice, $this->getCurrentStoreId());
			}
		}

		/**
		 * Update retail price
		 */
 		if ($this->getStoreConfigValue('opensi_preferences/manage_prices/retail_price'))
 		{
 			// Update custom attribute
 			$retail_price_attribute = $this->getStoreConfigValue('opensi_preferences/manage_prices/retail_price_custom');

 			if ($priceManagement)
			{
 				$retailPrice = $item->RetailPrice * (1 + ($item->VATRate / 100));
 				$product->addAttributeUpdate($retail_price_attribute, $retailPrice , $this->getCurrentStoreId());
 			} else {
 				$product->addAttributeUpdate($retail_price_attribute, $item->RetailPrice, $this->getCurrentStoreId());
 			}

 		} else {

 			// Update MSRP field
			if ($this->isSynchronizable('retail_price', $fieldsToSync, $customWebservice) || $product->getMsrp() == null)
 			{
 				if ($product->getMsrp() != $item->RetailPrice)
				{
 					if ($priceManagement)
					{
 						$retailPrice = $item->RetailPrice * (1 + ($item->VATRate / 100));
 						$product->addAttributeUpdate('msrp', $retailPrice , $this->getCurrentStoreId());
 					} else {
 						$product->addAttributeUpdate('msrp', $item->RetailPrice, $this->getCurrentStoreId());
 					}
 				}
 			}
 		}

		/**
		 * If parent product (type = configurables/grouped), no update of the following fields
		 * Update only on price, wholesale price & retail price!
		 */
		if (!$parent)
		{
			/**
			 * Update ecotax if enabled
			 * Need to get all ecotaxes, delete all ecotaxes and recreate all ecotaxes with the new one sent from OpenSi
			 */
			if ($this->getStoreConfigValue('tax/weee/enable'))
			{
				$weeeAttributes = $this->_manager->create('\Magento\Weee\Model\Tax')->getProductWeeeAttributes($product);

				$weeTaxCodes = $this->_manager->create('\Magento\Catalog\Model\ResourceModel\Attribute')->getAttributeCodesByFrontendType('weee');
				$i = 0;

				foreach ($weeTaxCodes as $weeTaxCode)
				{
					$i++;
					if ($this->isSynchronizable($weeTaxCode, $fieldsToSync, $customWebservice))
					{
						if ($i != 1) {
							$ecotax = 0;
						}

						if (empty($weeeAttributes) || $weeeAttributes[0]['amount'] != $ecotax || $ecotax == 0)
						{
							/**
							 * Update ecotax
							 */
							$attribute = $this->_manager->create('\Magento\Eav\Model\Entity\Attribute')->loadByCode($product->getResource()->getEntityType(), $weeTaxCode);

							$resource = $this->_manager->create('Magento\Framework\App\ResourceConnection');
							$connection = $resource->getConnection();
							$tableName = $resource->getTableName($this->_manager->create('\Magento\Weee\Model\Attribute\Backend\Weee\Tax')->getTable());

							/**
							 * Delete weee tax from table
							 */
							$sql = 'DELETE FROM '.$tableName.' WHERE entity_id = '.$product->getId().' AND attribute_id = '.$attribute['attribute_id'].' AND country = "'.$this->getStoreConfigValue('tax/defaults/country').'"';
							$connection->query($sql);

							if ($item->Ecotax != 0)
							{
								/**
								 * Insert new weee tax into table
								 */
								$sql = 'INSERT INTO '.$tableName.' (entity_id, country, value, attribute_id) VALUES ('.$product->getId().', "'. $this->getStoreConfigValue('tax/defaults/country').'", '.$ecotax.', '.$attribute['attribute_id'].') ON DUPLICATE KEY UPDATE value = '.$ecotax;
								$connection->query($sql);
							}
						}
					}
				}
			}

			/**
			 * Update tax rate
			 * Get new taxClassId of the product from the tax rate sent by OpenSi
			 */
			$taxRate = $this->_manager->create('\Magento\Tax\Model\TaxCalculation')->getCalculatedRate($product->getData('tax_class_id'));

			if ($this->isSynchronizable('tax_rate', $fieldsToSync, $customWebservice))
			{
				if ($taxRate != $item->VATRate || $taxRate == 0)
				{
          $newTaxClassId = 0;

					$productTaxCollection = $this->_manager->create('\Magento\Tax\Model\ResourceModel\Calculation\Collection');
					$productTaxCollection
						->getSelect()
						->joinLeft(array('tcr' => $productTaxCollection->getTable('tax_calculation_rate')), 'main_table.tax_calculation_rate_id = tcr.tax_calculation_rate_id', '')
						->where('tcr.rate = "'.$item->VATRate.'" AND tcr.tax_country_id = "'.$this->getStoreConfigValue('tax/defaults/country').'"');

					foreach ($productTaxCollection as $tax)
					{
						$newTaxClassId = $tax->getProductTaxClassId();
					}

					$product->addAttributeUpdate('tax_class_id', $newTaxClassId, $storeId);
				}
			}
		}

		/**
		 * Return
		 */
		return true;
	}








	/*=================================================================
	 *
	 * 			S E T   P R O D U C T S   L O G I S T I C
	 *
	 ================================================================*/

	/**
	 * Set products logistic
	 * Update logistic informations on products on the store
	 * SWO-G014
	 *
	 * OpenSi => Magento
	 *
	 * @param $values
	 */
	public function setProductsLogistic($values)
	{
		/**
	   * Authentification
	   */
	  $auth = new Authenticate();

	  if (!$auth->login($this->_manager, $this->_header->{'key'})) {
	    throw new Exception(OSI_INVALID_AUTH);
	  }

	  /**
	   * Check configuration depending on the website code
	   */
	  $this->checkConfiguration($values);

		/**
		 * Update logistic informations
		 */
		$productLogisticResponse = array();
		$reindexProductIds = array();

		if (!is_array($values->{'ProductLogistic'})) {
			$values->{'ProductLogistic'} = array($values->{'ProductLogistic'});
		}

		foreach ($values->{'ProductLogistic'} as $item)
		{
			$productIdBySku = $this->_manager->create('Magento\Catalog\Model\Product')->getIdBySku($item->Reference);

			if ($productIdBySku)
 	    {
				/**
				 * Load product
				 */
				$product = $this->_manager->create('\Magento\Catalog\Model\ProductRepository')->getById($productIdBySku);

				/**
				 * Get fields to sync
				 */
				$fieldsToSync = array();
        $customWebservice = $this->getStoreConfigValue('opensi_preferences/manage_flux/logistic_informations/fields_sync');

				if ($customWebservice)
				{
					if ($this->getStoreConfigValue('opensi_preferences/manage_flux/logistic_informations/fields_to_sync')) {
            $fieldsToSync = explode(',', $this->getStoreConfigValue('opensi_preferences/manage_flux/logistic_informations/fields_to_sync'));
          }
				}

				/**
				 * Update barcode
				 */
				$this->updateAttribute('opensi_configuration/attributes/barcode', $item->Barcode, $product, $fieldsToSync, $customWebservice);

				/**
				 * Update volume
				 */
				$this->updateAttribute('opensi_configuration/attributes/volume', $item->Volume, $product, $fieldsToSync, $customWebservice);

				/**
				 * Update height
				 */
				$this->updateAttribute('opensi_configuration/attributes/height', $item->Height, $product, $fieldsToSync, $customWebservice);

				/**
				 * Update length
				 */
				$this->updateAttribute('opensi_configuration/attributes/length', $item->Length, $product, $fieldsToSync, $customWebservice);

				/**
				 * Update width
				 */
				$this->updateAttribute('opensi_configuration/attributes/width', $item->Width, $product, $fieldsToSync, $customWebservice);

				/**
				 * Update manufacturer reference
				 */
				$this->updateAttribute('opensi_configuration/attributes/manufacturer_reference', $item->ManufacturerReference, $product, $fieldsToSync, $customWebservice);

				/**
				 * Update restocking time
				 */
				$this->updateAttribute('opensi_configuration/attributes/restocking_time', $item->RestockingTime, $product, $fieldsToSync, $customWebservice);

				/**
				 * Update direct supplier
				 */
				$this->updateAttribute('opensi_configuration/attributes/direct_supplier', $item->DirectSupplier, $product, $fieldsToSync, $customWebservice);

				/**
				 * Update ABC Class
				 */
				$this->updateAttribute('opensi_configuration/attributes/abc_class', $item->ABCClass, $product, $fieldsToSync, $customWebservice);

        /**
				 * Update Conditioning
				 */
				$this->updateAttribute('opensi_configuration/attributes/conditioning', $item->Conditioning, $product, $fieldsToSync, $customWebservice);

				/**
				 * Update NC8 code
				 */
				$this->updateAttribute('opensi_preferences/manage_customs/nc8_code', $item->NC8Code, $product, $fieldsToSync, $customWebservice);

				/**
				 * Update Country code of manufacture
				 */
				$this->updateAttribute('opensi_preferences/manage_customs/country_code_manufacture', $item->CountryCodeManufacture, $product, $fieldsToSync, $customWebservice);

				/**
				 * Update Net weight
				 */
				$this->updateAttribute('opensi_preferences/manage_customs/net_weight', $item->NetWeight, $product, $fieldsToSync, $customWebservice);

				/**
				 * Update weight
				 */
				if ($this->isSynchronizable('weight', $fieldsToSync, $customWebservice))
  			{
  			  $product->addAttributeUpdate('weight', $item->Weight, $this->getCurrentStoreId());
  			}

				/**
	       * Set products to index after update
	       */
				$product->cleanCache();
	      $reindexProductIds[] = $product->getId();

				/**
				 * Response
				 */
				$productLogisticResponse[] = new \Speedinfo\Opensi\Webservices\Classes\Response($item->Reference, OSI_SUCCESS_UPDATE);

			} else {

				/**
				 * The reference doesn't exist!
				 */
				$productLogisticResponse[] = new \Speedinfo\Opensi\Webservices\Classes\Response($item->Reference, OSI_ERROR_NOT_FOUND, OSI_INVALID_REFERENCE);
			}
		}

		/**
		 * Clear cache & reindex product(s)
		 */
		$this->clearCacheAndReindex($reindexProductIds, 'catalog_product_attribute');

		/**
		 * Return
		 */
		return array('return' => $productLogisticResponse);
	}








	/*=================================================================
	 *
	 * 					S E T   O R D E R   S T A T E S
	 *
	 ================================================================*/

	/**
	 * Set order states
	 * Set the state of the order sent by OpenSi
	 * SWO-G008
	 *
	 * OpenSi => Magento
	 *
	 * @param $values
	 */
	public function setStates($values)
	{
		/**
	   * Authentification
	   */
	  $auth = new Authenticate();

	  if (!$auth->login($this->_manager, $this->_header->{'key'})) {
	    throw new Exception(OSI_INVALID_AUTH);
	  }

	  /**
	   * Check configuration depending on the website code
	   */
	  $this->checkConfiguration($values);

		/**
		 * Update order state
		 */
		 $orderStateResponse = array();

 		if (!is_array($values->{'State'})) {
 			$values->{'State'} = array($values->{'State'});
 		}

 		foreach ($values->{'State'} as $item)
 		{
			$order = $this->_manager->create('\Magento\Sales\Api\Data\OrderInterface')->loadByIncrementId($item->WebOrderNumber);
      $orderStateComplete = $this->getStoreConfigValue('opensi_configuration/order_statuses/order_statuses_standard/complete');
			$orderStateCancel = $this->getStoreConfigValue('opensi_configuration/order_statuses/order_statuses_standard/cancel');
			$currentOrderStatus = $order->getStatus();

			if ($order && $order->getId())
			{
        // Get order statuses history
        $orderStatusHistory = array();

        foreach ($order->getStatusHistories() as $history) {
          $orderStatusHistory[] = $history->getStatus();
        }

				/**
				 * Magento invoice
				 * Depending on the payment status sent by OpenSi, create the Magento invoice
				 *
				 * OpenSi Payment Status :
				 *  - PayState = N (not payed)
				 *  - PayState = P (partially payed)
				 *  - PayState = T (totally payed)
				 */
				if ($item->PayState == 'T')
				{
					/**
					 * Order paid, check if the order can be invoiced in Magento
					 * Generate the Magento invoice
					 */
					if ($order->canInvoice())
					{
						$invoice = $this->_manager->create('\Magento\Sales\Model\Service\InvoiceService')->prepareInvoice($order);
						//$invoice->setRequestedCaptureCase(\Magento\Sales\Model\Order\Invoice::CAPTURE_ONLINE);
						$invoice->setRequestedCaptureCase(\Magento\Sales\Model\Order\Invoice::CAPTURE_OFFLINE);
						$invoice->register();

						$order->addStatusHistoryComment(__('Invoice automatically added by OpenSi.'), false);

						$transactionSave = $this->_manager->create('\Magento\Framework\DB\TransactionFactory')->create();
						$transactionSave->addObject($invoice);
						$transactionSave->addObject($invoice->getOrder());
						$transactionSave->save();
					}

          /**
           * Order totally paid in OpenSi
           * Update status (depending on the configuration - if status "Order totally paid" is enabled)
           * Check if this order already has this status before updating
           */
          if ($this->getStoreConfigValue('opensi_configuration/order_statuses/order_statuses_optionnal/totally_paid_active') == 1 && !in_array($this->getStoreConfigValue('opensi_configuration/order_statuses/order_statuses_optionnal/totally_paid'), $orderStatusHistory)) {
            if (!empty($currentOrderStatus) && $currentOrderStatus != $orderStateComplete && $currentOrderStatus != $orderStateCancel) {
              $this->updateOrderStatus('opensi_configuration/order_statuses/order_statuses_optionnal/totally_paid', $order);
            }
          }
				}
        elseif ($item->PayState == 'P' && $this->getStoreConfigValue('opensi_configuration/order_statuses/order_statuses_optionnal/partially_paid_active') == 1 && !in_array($this->getStoreConfigValue('opensi_configuration/order_statuses/order_statuses_optionnal/partially_paid'), $orderStatusHistory))
        {
          /**
           * Order partially paid in OpenSi
           * Update status (depending on the configuration - if status "Order partially paid" is enabled)
           * Check if this order already has this status before updating
           */
          if (!empty($currentOrderStatus) && $currentOrderStatus != $orderStateComplete && $currentOrderStatus != $orderStateCancel) {
            $this->updateOrderStatus('opensi_configuration/order_statuses/order_statuses_optionnal/partially_paid', $order);
          }
        }

				/**
				 * Update order states
				 * Depending on the configuration and the datas sent by OpenSi, update Magento order status
				 *
				 * N => not valid
				 * T => in order (validated)
				 * A => cancel
				 * C => finished
				 *
				 *
				 * Logistic state
				 *
				 * T => not delivered
				 * E => delivered
				 */
				$orderState = $item->OrderState;
				$orderLogisticState = $item->LogisticState;
				$preparation = $item->Preparation;
				$deliveryNumber = $item->DeliveryNumber;

				if ($this->getStoreConfigValue('opensi_configuration/order_statuses/order_statuses_standard/cancel_active') == 1 && $orderState == 'A')
        {
					/**
           * Order CANCELED
           */
          if (!empty($currentOrderStatus) && $this->getStoreConfigValue('opensi_configuration/order_statuses/order_statuses_standard/cancel') !== $currentOrderStatus) {
            // Check if order is cancellable (native magento order object method)
            if (true === $order->canCancel())
            {
              $order->cancel();
            } else {
              $websiteRepository = $this->_manager->create('\Magento\Store\Api\WebsiteRepositoryInterface');
              $salesChannelFactory = $this->_manager->create('\Magento\InventorySalesApi\Api\Data\SalesChannelInterfaceFactory');
              $salesEventExtensionFactory = $this->_manager->create('\Magento\InventorySalesApi\Api\Data\SalesEventExtensionFactory');
              $salesEventFactory = $this->_manager->create('\Magento\InventorySalesApi\Api\Data\SalesEventInterfaceFactory');
              $placeReservationsForSalesEvent = $this->_manager->create('\Magento\InventorySalesApi\Api\PlaceReservationsForSalesEventInterface');
              $priceIndexer = $this->_manager->create('\Magento\Catalog\Model\Indexer\Product\Price\Processor');
              $jsonSerializer = $this->_manager->create('\Magento\Framework\Serialize\Serializer\Json');
              $getSkuFromOrderItem = $this->_manager->create('\Magento\InventorySalesApi\Model\GetSkuFromOrderItemInterface');
              $itemsToSellFactory = $this->_manager->create('\Magento\InventorySalesApi\Api\Data\ItemToSellInterfaceFactory');

              // Here we know Magento won't update inventory reservations as usual, so we do it ourselves
              foreach ($order->getAllItems() as $orderItem)
              {
                $websiteId = $orderItem->getStore()->getWebsiteId();
                $websiteCode = $websiteRepository->getById($websiteId)->getCode();
                $salesChannel = $salesChannelFactory->create([
                  'data' => [
                    'type' => 'website',
                    'code' => $websiteCode
                  ]
                ]);

                /** @var SalesEventExtensionInterface */
                $salesEventExtension = $salesEventExtensionFactory->create([
                  'data' => ['objectIncrementId' => (string)$orderItem->getOrder()->getIncrementId()]
                ]);

                $salesEvent = $salesEventFactory->create([
                  'type' => 'order_canceled_opensi',
                  'objectType' => 'order',
                  'objectId' => (string)$orderItem->getOrderId()
                ]);
                $salesEvent->setExtensionAttributes($salesEventExtension);

                $itemsToCancel = [];

                if ($orderItem->getHasChildren())
                {
                  if (!$orderItem->isDummy(true))
                  {
                    foreach ($orderItem->getChildrenItems() as $item)
                    {
                      $productOptions = $item->getProductOptions();

                      if (isset($productOptions['bundle_selection_attributes']))
                      {
                        $bundleSelectionAttributes = $jsonSerializer->unserialize(
                          $productOptions['bundle_selection_attributes']
                        );

                        if ($bundleSelectionAttributes) {
                          $qty = $item->getQtyOrdered();
                          $itemSku = $getSkuFromOrderItem->execute($item);
                          $itemsToCancel[] = $itemsToSellFactory->create([
                            'sku' => $itemSku,
                            'qty' => $qty
                          ]);
                        }
                      } else {
                        // configurable product
                        $itemSku = $getSkuFromOrderItem->execute($orderItem);
                        $itemsToCancel[] = $itemsToSellFactory->create([
                          'sku' => $itemSku,
                          'qty' => $orderItem->getQtyOrdered()
                        ]);
                      }
                    }
                  }
                }
                elseif (!$orderItem->isDummy(true))
                {
                  $itemSku = $getSkuFromOrderItem->execute($orderItem);
                  $itemsToCancel[] = $itemsToSellFactory->create([
                    'sku' => $itemSku,
                    'qty' => $orderItem->getQtyOrdered()
                  ]);
                }

                $placeReservationsForSalesEvent->execute($itemsToCancel, $salesChannel, $salesEvent);
                $priceIndexer->reindexRow($orderItem->getProductId());
              }
            }

            $this->updateOrderStatus('opensi_configuration/order_statuses/order_statuses_standard/cancel', $order);
          }
				}
				elseif ($this->getStoreConfigValue('opensi_configuration/order_statuses/order_statuses_standard/complete_active') == 1 && ($orderState == 'T' || $orderState == 'C') && $orderLogisticState == 'E' && $preparation == false && $item->PayState == 'T') {
					/**
           * Order COMPLETE
           */
          $resource = $this->_manager->create('\Magento\Framework\App\ResourceConnection');
          $groupConcatMaxLen = 32768;
          $connection = $resource->getConnection();
          $reservationTable = $resource->getTableName('inventory_reservation');
          $select = $connection->select()
            ->from($reservationTable, ['GROUP_CONCAT(reservation_id)'])
            ->group("JSON_EXTRACT(metadata, '$.object_increment_id')", "JSON_EXTRACT(metadata, '$.object_type')")
            ->where("(JSON_EXTRACT(metadata, '$.object_increment_id')) = '".$order->getIncrementId()."'");

          $connection->query('SET group_concat_max_len = ' . $groupConcatMaxLen);
          $reservationIds = $connection->fetchCol($select);

          if (!empty($reservationIds)) {
            $condition = ['reservation_id IN (?)' => explode(',', implode(',', array_unique($reservationIds)))];
            $connection->delete($reservationTable, $condition);
          }

          if (!empty($currentOrderStatus) && $currentOrderStatus != $orderStateCancel) {
            $this->updateOrderStatus('opensi_configuration/order_statuses/order_statuses_standard/complete', $order);
          }
				}
				elseif ($this->getStoreConfigValue('opensi_preferences/manage_shipments/multi_shipments') == 1 && $this->getStoreConfigValue('opensi_configuration/order_statuses/order_statuses_standard/partially_shipped_active') == 1 && $order->hasShipments() && (($orderState == 'T' && $orderLogisticState == 'T') || (($orderState == 'T' || $orderState == 'C') && $orderLogisticState == 'E' && $preparation == true))) {
					/**
					 * Order PARTIALLY SHIPPED
					 * Must be BEFORE the processing state!
					 */
					if (!empty($currentOrderStatus) && $currentOrderStatus != $orderStateComplete && $currentOrderStatus != $orderStateCancel) {
						$this->updateOrderStatus('opensi_configuration/order_statuses/order_statuses_standard/partially_shipped', $order);
					}
				}
				elseif ($this->getStoreConfigValue('opensi_configuration/order_statuses/order_statuses_standard/processing_active') == 1 && ($orderState == 'T' || ($orderState == 'C' && $orderLogisticState == 'E')) && $preparation == true) {
					/**
					 * Order PROCESSING
					 */
					if (!empty($currentOrderStatus) && $currentOrderStatus != $orderStateComplete && $currentOrderStatus != $orderStateCancel) {
						$this->updateOrderStatus('opensi_configuration/order_statuses/order_statuses_standard/processing', $order);
					}
				}
				elseif ($this->getStoreConfigValue('opensi_configuration/order_statuses/order_statuses_standard/validated_active') == 1 && $orderState == 'T' && $deliveryNumber == 0) {
					/**
					 * Order VALIDATED
					 */
					if (!empty($currentOrderStatus) && $currentOrderStatus != $orderStateComplete && $currentOrderStatus != $orderStateCancel) {
						$this->updateOrderStatus('opensi_configuration/order_statuses/order_statuses_standard/validated', $order);
					}
				}

				/**
				 * Mark order as synchronized with OpenSi
				 * Update fields on tables `sales_order` and `sales_order_grid`
				 * View etc/di.xml too!
				 */
				if (!$order->getOpensiSync())
				{
					$order->setOpensiSync('1');
					$order->setOpensiSyncAt(date('Y-m-d H:i:s', time()));
					$order->setOpensiReference($item->OrderNumber);
					$order->save();
				}

				/**
				 * Response
				 */
				$orderStateResponse[] = new \Speedinfo\Opensi\Webservices\Classes\Response($item->WebOrderNumber, OSI_SUCCESS_UPDATE);

			} else {

				/**
				 * The order doesn't exist!
				 */
				$orderStateResponse[] = new \Speedinfo\Opensi\Webservices\Classes\Response($item->WebOrderNumber, OSI_ERROR_NOT_FOUND, OSI_INVALID_ORDER);
			}
		}

		/**
		 * Return
		 */
		return array('return' => $orderStateResponse);
	}


	/**
	 * Update order status
	 * Depending on the connect configuration, set the new status on an order
	 *
	 * @param $path
	 * @param $currentOrderState
	 */
	private function updateOrderStatus($path, $order)
	{
		$orderStateMapping = $this->getStoreConfigValue($path);
		$orderStatuses = $this->getOrderStatus($order);

		/**
		 * Update order status only if the current status is different from the previous
		 */
		if (empty($orderStatuses) || $orderStateMapping != $orderStatuses[0])
		{
			/**
			 * Get label
			 */
			$nameOrderState = $this->_manager->create('\Magento\Sales\Model\Order\Config')->getStatusLabel($orderStateMapping);

			/**
			 * Update order status
			 */
			$orderStateCollection = $this->_manager->create('Magento\Sales\Model\ResourceModel\Order\Status\Collection')->joinStates();
			$orderStateCollection->getSelect()->where('state_table.status = "'.$orderStateMapping.'"');

			foreach ($orderStateCollection as $state)
			{
				$orderState = $state->getState();
			}

			$order->setData('state', $orderState);
			$order->setData('status', $orderStateMapping);
			$order->save();

			/**
			 * Add to history
			 */
			$history = $order->addStatusHistoryComment(__('Order status automatically updated by OpenSi').' ('.__($nameOrderState).')', $orderStateMapping);
			$history->setIsCustomerNotified(true);
			$history->save();

			/**
			 * Send email to customer
			 */
			$this->_manager->create('\Magento\Sales\Model\Order\Email\Sender\OrderCommentSender')->send($order, true);
		}
	}


	/**
	 * Get order status history
	 *
	 * Check if the command has already had this status
	 * @param $order
	 */
	private function getOrderStatus($order)
	{
		/**
		 * Get order status history
		 */
		$orderStatusList = array();
		$orderStatusHistory = $order->getStatusHistoryCollection();

		foreach($orderStatusHistory as $orderStatus)
		{
			$orderStatusList[] = $orderStatus->getStatus();
		}

		return $orderStatusList;
	}








	/*=================================================================
	 *
	 * 				S E T   O P E N S I   I N V O I C E S
	 *
	 ================================================================*/

	/**
	 * Set OpenSi invoice
	 * Send the OpenSi invoice on the store.
	 * Depending on the configuration, this document will be displayed to the customer instead of the Magento invoice
	 * See "opensi_preferences/manage_invoices/invoices" in core_config_data table
	 * SWO-G011
	 *
	 * OpenSi => Magento
	 *
	 * @param $values (invoices)
	 */
	public function setInvoices($values)
	{
		/**
	   * Authentification
	   */
	  $auth = new Authenticate();

	  if (!$auth->login($this->_manager, $this->_header->{'key'})) {
	    throw new Exception(OSI_INVALID_AUTH);
	  }

	  /**
	   * Check configuration depending on the website code
	   */
	  $this->checkConfiguration($values);

		/**
		 * Add OpenSi invoice
		 */
		$invoiceResponse = array();

		if (!is_array($values->{'Invoice'})) {
			$values->{'Invoice'} = array($values->{'Invoice'});
		}

		foreach ($values->{'Invoice'} as $item)
		{
			/**
			 * Check if invoice already exists in the table `opensi_documents`
			 */
			$document = $this->_manager->create('\Speedinfo\Opensi\Model\ResourceModel\Documents\Collection');
			$document->addFieldToFilter('increment_id', array('eq' => $item->WebOrderNumber));
			$document->addFieldToFilter('document_number', array('eq' => $item->InvoiceNumber));
			$document->addFieldToFilter('document_type', array('eq' => $item->Type));

			if (empty($document->getColumnValues('increment_id')))
			{
				/**
				 * Insert entry in the table `opensi_documents`
				 */
				$document = $this->_manager->create('\Speedinfo\Opensi\Model\Documents');
				$document->setData('increment_id', $item->WebOrderNumber);
				$document->setData('document_number', $item->InvoiceNumber);
				$document->setData('document_type', $item->Type);
				$document->setData('document_key', $item->Url);
				$document->setData('created_at', date('Y-m-d H:i:s', time()));
				$document->save();

				/**
				 * Response
				 */
				$invoiceResponse[] = new \Speedinfo\Opensi\Webservices\Classes\Response($item->WebOrderNumber, OSI_SUCCESS_UPDATE);

			} else {

				/**
				 * The document already exists!
				 */
				$invoiceResponse[] = new \Speedinfo\Opensi\Webservices\Classes\Response($item->WebOrderNumber, OSI_WARNING_DUPLICATE, OSI_DUPLICATE_DOCUMENT);

			}
		}


		/**
		 * Return
		 */
		return array('return' => $invoiceResponse);
	}








	/*=================================================================
	 *
	 * 			S E T   O P E N S I   D E L I V E R Y   N O T E S
	 *
	 ================================================================*/

	/**
	 * Set Delivery Notes
	 * Send the OpenSi delivery notes on the store.
	 * Depending on the configuration, this document will be displayed to the customer
	 * See "opensi_preferences/manage_deliverynotes/deliverynotes" in core_config_data table
	 * SWO-G056
	 *
	 * OpenSi => PrestaShop
	 *
	 * @param $values
	 */
	public function setDeliveryNotes($values)
	{
		/**
	   * Authentification
	   */
	  $auth = new Authenticate();

	  if (!$auth->login($this->_manager, $this->_header->{'key'})) {
	    throw new Exception(OSI_INVALID_AUTH);
	  }

	  /**
	   * Check configuration depending on the website code
	   */
	  $this->checkConfiguration($values);

		/**
		 * Add delivery note
		 */
		$deliveryNoteResponse = array();

		if (!is_array($values->{'DeliveryNote'})) {
			$values->{'DeliveryNote'} = array($values->{'DeliveryNote'});
		}

		foreach ($values->{'DeliveryNote'} as $item)
		{
			/**
			 * Check if deliverynote already exists in the table `opensi_documents`
			 */
			$document = $this->_manager->create('\Speedinfo\Opensi\Model\ResourceModel\Documents\Collection');
			$document->addFieldToFilter('increment_id', array('eq' => $item->WebOrderNumber));
			$document->addFieldToFilter('document_number', array('eq' => $item->DeliveryNoteNumber));
			$document->addFieldToFilter('document_type', array('eq' => 'BL'));

			if (empty($document->getColumnValues('increment_id')))
			{

				/**
				 * Insert entry in the table `opensi_documents`
				 */
				$document = $this->_manager->create('\Speedinfo\Opensi\Model\Documents');
				$document->setData('increment_id', $item->WebOrderNumber);
				$document->setData('document_number', $item->DeliveryNoteNumber);
				$document->setData('document_type', 'BL');
				$document->setData('document_key', $item->Url);
				$document->setData('created_at', date('Y-m-d H:i:s', time()));
				$document->save();

				/**
				 * Response
				 */
				$deliveryNoteResponse[] = new \Speedinfo\Opensi\Webservices\Classes\Response($item->WebOrderNumber, OSI_SUCCESS_UPDATE);

			} else {

				/**
				 * The document already exists!
				 */
				$deliveryNoteResponse[] = new \Speedinfo\Opensi\Webservices\Classes\Response($item->WebOrderNumber, OSI_WARNING_DUPLICATE, OSI_DUPLICATE_DOCUMENT);

			}
		}

		/**
		 * Return
		 */
		return array('return' => $deliveryNoteResponse);
	}








	/*=================================================================
	 *
	 * 			S E T   O R D E R   S H I P M E N T S
	 *
	 ================================================================*/

	/**
	 * Set order shipments
	 * Add shipment on order (one or more shipments)
	 * SWO-G078
	 *
	 * Please, see function setOrderTrackingCodes() for sending e-mail to the customer
	 *
	 * OpenSi => Magento
	 *
	 * @param $values (shipment)
	 */
	public function setOrderShipments($values)
	{
		/**
	   * Authentification
	   */
	  $auth = new Authenticate();

	  if (!$auth->login($this->_manager, $this->_header->{'key'})) {
	    throw new Exception(OSI_INVALID_AUTH);
	  }

	  /**
	   * Check configuration depending on the website code
	   */
	  $this->checkConfiguration($values);

		/**
		 * Add order shipment(s)
		 */
		$orderShipmentResponse = array();
    $multipleShipments = $this->getStoreConfigValue('opensi_preferences/manage_shipments/multi_shipments');

		if (!is_array($values->{'OrderShipment'})) {
			$values->{'OrderShipment'} = array($values->{'OrderShipment'});
		}

		foreach ($values->{'OrderShipment'} as $shipmentItem)
		{
			/**
			 * Load order & get shipment configuration
			 */
			$order = $this->_manager->create('\Magento\Sales\Api\Data\OrderInterface')->loadByIncrementId($shipmentItem->WebOrderNumber);

      if ($shipmentItem->Status == 'V')
      {
        /**
         * Create new shipment
         */
        if ($order && $order->canShip())
        {
          if ($multipleShipments)
          {
            /**
             * MULTIPLE SHIPMENTS
             * Get all existing shipment for this order
             */
            $orderShipmentsAlreadyCreated = array();
            $orderShipments = $order->getShipmentsCollection();

            foreach ($orderShipments as $orderShipment) {
              $orderShipmentsAlreadyCreated[$orderShipment->getIncrementId()] = $orderShipment->getOpensiDeliveryNote();
            }

            /**
             * Check if the shipment already exist
             */
            if (!is_array($shipmentItem->{'OrderShipmentDeliveryNotes'})) {
              $shipmentItem->{'OrderShipmentDeliveryNotes'} = array($shipmentItem->{'OrderShipmentDeliveryNotes'});
            }

            foreach ($shipmentItem->{'OrderShipmentDeliveryNotes'} as $shipmentDeliveryNote)
            {
              if ($order->getIncrementId() == $shipmentItem->WebOrderNumber && !in_array($shipmentDeliveryNote->DeliveryNoteReference, $orderShipmentsAlreadyCreated))
              {
                /**
                 * If the shipment doesn't exists, then create it
                 * Get reference/quantity in order to create the shipment
                 */
                $qtys = array();
                $products = array();

                if (!is_array($shipmentDeliveryNote->OrderShipmentDeliveryNotesDetail)) {
                  $shipmentDeliveryNote->OrderShipmentDeliveryNotesDetail = array($shipmentDeliveryNote->OrderShipmentDeliveryNotesDetail);
                }

                foreach ($shipmentDeliveryNote->OrderShipmentDeliveryNotesDetail as $item) {
                  $qtys[$item->Reference] = $item->Quantity;
                }

                foreach ($order->getAllItems() as $orderItem)
                {
                  // Check if order item has qty to ship or is virtual
                  if (!$orderItem->getQtyToShip() || $orderItem->getIsVirtual()) {
                    continue;
                  }

                  if (array_key_exists($orderItem->getSku(), $qtys))
                  {
                    $item = $this->_manager->create('\Magento\Sales\Api\Data\ShipmentItemCreationInterfaceFactory')->create();
                    $item->setOrderItemId($orderItem->getId());
                    $item->setQty($qtys[$orderItem->getSku()]);

                    $products[] = $item;
                  }
                }

                /**
                 * Set new shipment (multi)
                 */
                $orderShipmentResponse[] = $this->setNewShipment($order->getId(), $shipmentDeliveryNote->DeliveryNoteReference, $products, $shipmentItem);

              }  else {

                /**
                 * The shipment is already set or the order cannot be shipped!
                 */
                $orderShipmentResponse[] = new \Speedinfo\Opensi\Webservices\Classes\Response($shipmentItem->WebOrderNumber, OSI_ERROR_SHIPMENT, OSI_INVALID_SHIPMENT);

              }
            }

          } else {

            /**
             * SINGLE SHIPMENT
             * Set new single shipment
             */
            $orderShipmentResponse[] = $this->setNewShipment($order->getId(), $shipmentItem->{'OrderShipmentDeliveryNotes'}->DeliveryNoteReference, array(), $shipmentItem);
          }

        } else {

          /**
           * The shipment is already set or the order cannot be shipped!
           */
          $orderShipmentResponse[] = new \Speedinfo\Opensi\Webservices\Classes\Response($shipmentItem->WebOrderNumber, OSI_ERROR_SHIPMENT, OSI_INVALID_SHIPMENT);

        }

      } elseif ($shipmentItem->Status == 'A') {
        
        /**
         * [Custom] Update field Opensi_DlyState for the shipment
         */
        $orderShipmentResponse[] = $this->updateShipmentStatus($order->getId(), $shipmentItem->{'OrderShipmentDeliveryNotes'}->DeliveryNoteReference, $shipmentItem);

      }
		}

		/**
		 * Return
		 */
		return array('return' => $orderShipmentResponse);
	}


  /**
   * Set shipment & OpenSi informations
   * Create a new shipment and add OpenSi informations
   *
   * @params $orderId, $deliveryNoteReference, $products, $shipmentItem
   */
  public function setNewShipment($orderId, $deliveryNoteReference, $products, $shipmentItem)
  {
    /**
     * Create & save shipment
     *
     * $products is empty for single shipment
     * $product is set for multiple shipments
     */
    try {

      $shipmentId = $this->_manager->create('\Magento\Sales\Model\ShipOrder')->execute($orderId, $products);

      /**
       * Add OpenSi delivery note reference on the shipment (eg. BL16040003) on table `sales_shipment`
       */
      $shipment = $this->_manager->create('\Magento\Sales\Model\Order\ShipmentRepository')->get($shipmentId);
      $shipment->setOpensiDeliveryNote($deliveryNoteReference);

      /**
       * [Custom] Set fields Opensi_DlyActor && Opensi_DlyState if available
       */
      $shipment->setOpensiDlyState($shipmentItem->Status);

      if ($shipmentItem->SupplierNumber) {
        $shipment->setOpensiDlyActor($shipmentItem->SupplierNumber);
      } else {
        $shipment->setOpensiDlyActor($shipmentItem->DepositCode);
      }

      /**
       * Save shipment
       */
      $shipment->save();

      /**
       * Update order
       */
      $message = sprintf(__('Shipment n°%1s automatically added by OpenSi.<br/>OpenSi delivery note n&deg;%2s.'), $shipment->getIncrementId(), $deliveryNoteReference);

      $order = $this->_manager->create('\Magento\Sales\Api\OrderRepositoryInterface')->get($orderId);
      $order->setIsInProcess(true);
      $order->addStatusHistoryComment($message, false);
      $order->save();

      /**
       * Send shipping confirmation e-mail to customer
       */
      $this->_manager->create('\Magento\Shipping\Model\ShipmentNotifier')->notify($shipment);

      /**
       * Response
       */
      return new \Speedinfo\Opensi\Webservices\Classes\Response($shipmentItem->WebOrderNumber, OSI_SUCCESS_UPDATE);

    } catch (Exception $e) {

      /**
       * Response
       */
      return new \Speedinfo\Opensi\Webservices\Classes\Response($shipmentItem->WebOrderNumber, OSI_ERROR_SHIPMENT, OSI_SHIPMENT_CREATION_ERROR);

    }
  }


  /**
   * [Custom] Update shipment
   * Update custom field Opensi_DlyState if available
   *
   * @params $orderId, $deliveryNoteReference, $shipmentItem
   */
  public function updateShipmentStatus($orderId, $deliveryNoteReference, $shipmentItem)
  {
    try {

      $order = $this->_manager->create('\Magento\Sales\Api\OrderRepositoryInterface')->get($orderId);
      $orderShipments = $order->getShipmentsCollection();

      if ($orderShipments->count() > 0)
      {
        foreach ($orderShipments as $orderShipment)
        {
          if ($orderShipment->getOpensiDeliveryNote() == $deliveryNoteReference)
          {
            $orderShipment->setOpensiDlyState($shipmentItem->Status);
            $orderShipment->save();
          }
        }

        /**
         * Response
         */
        return new \Speedinfo\Opensi\Webservices\Classes\Response($shipmentItem->WebOrderNumber, OSI_SUCCESS_UPDATE);

      } else {

        /**
         * Response
         */
        return new \Speedinfo\Opensi\Webservices\Classes\Response($shipmentItem->WebOrderNumber, OSI_ERROR_SHIPMENT, OSI_SHIPMENT_NOT_FOUND);

      }

    } catch (Exception $e) {

      /**
       * Response
       */
      return new \Speedinfo\Opensi\Webservices\Classes\Response($shipmentItem->WebOrderNumber, OSI_ERROR_SHIPMENT, OSI_SHIPMENT_NOT_FOUND);

    }
  }








	/*=================================================================
	 *
	 * 			S E T   O R D E R S   T R A C K I N G   C O D E
	 *
	 ================================================================*/

	/**
	 * Set tracking code
	 * Add a package tracking number on an order on the store
	 * SWO-G010
	 *
	 * OpenSi => Magento
	 *
	 * @param $values (tracking number)
	 */
	public function setOrderTrackingCodes($values)
	{
		/**
	   * Authentification
	   */
	  $auth = new Authenticate();

	  if (!$auth->login($this->_manager, $this->_header->{'key'})) {
	    throw new Exception(OSI_INVALID_AUTH);
	  }

	  /**
	   * Check configuration depending on the website code
	   */
	  $this->checkConfiguration($values);

		/**
		 * Add tracking number
		 */
		$trackingCodeResponse = array();

		if (!is_array($values->{'OrderTrackingCode'})) {
			$values->{'OrderTrackingCode'} = array($values->{'OrderTrackingCode'});
		}

		foreach ($values->{'OrderTrackingCode'} as $item)
		{
			/**
	     * Load order
	     */
	    $order = $this->_manager->create('\Magento\Sales\Api\Data\OrderInterface')->loadByIncrementId($item->WebOrderNumber);

			if($order && $order->getId())
			{
				/**
 				 * Get all shipments for this order
 			 	 */
				$shipmentCollection = $order->getShipmentsCollection();
				$shipmentResponse = array();

				foreach ($shipmentCollection as $shipment)
				{
					if ($shipment->getOpensiDeliveryNote() == $item->ShippingNumber)
					{
						$trackingNumbers = array();

						if ($shipment->getAllTracks())
						{
							foreach($shipment->getAllTracks() as $trackingNumber)
							{
								$trackingNumbers[] = $trackingNumber->getNumber();
							}
						}

						if (!in_array($item->TrackingNumber, $trackingNumbers))
						{
							$this->addTrackingNumber($order, $shipment, $item);
							$shipmentResponse[] = new \Speedinfo\Opensi\Webservices\Classes\Response($item->WebOrderNumber, OSI_SUCCESS_UPDATE);

						} else {

							$shipmentResponse[] = new \Speedinfo\Opensi\Webservices\Classes\Response($item->WebOrderNumber, OSI_ERROR_SHIPMENT, OSI_TRACKING_EXIST);

						}
					}
				}

				if(empty($shipmentResponse)) {
					$shipmentResponse[] = new \Speedinfo\Opensi\Webservices\Classes\Response($item->WebOrderNumber, OSI_ERROR_SHIPMENT, OSI_SHIPMENT_NOT_FOUND);
				}

				$trackingCodeResponse[] = $shipmentResponse[0];

			} else {

				/**
				 * The order doesn't exist!
				 */
				$trackingCodeResponse[] = new \Speedinfo\Opensi\Webservices\Classes\Response($item->WebOrderNumber, OSI_ERROR_NOT_FOUND, OSI_INVALID_ORDER);

			}
		}

		/**
		 * Return
		 */
		return array('return' => $trackingCodeResponse);
	}


	/**
	 * Add new tracking number on shipment
	 *
	 * Check if the shipping methods exists in the table `opensi_shipping_methods` and if mapped.
	 * If this is the case, add the tracking with the informations found
	 * If not, add a custom tracking number and insert the shipping method sent by OpenSi in the table (if not existing)
	 *
	 * @param $order
	 * @param $shipment
	 * @param $item
	 * @param $storeId
	 */
	private function addTrackingNumber($order, $shipment, $item)
	{
		/**
		 * Two methods available depending on the configuration
		 *
		 * If shipping methods mapping is enabled, set tracking number and set new carrier
		 * If the option is disabled, try to set the tracking number as the default way
		 */
		if ($this->getStoreConfigValue('opensi_configuration/shipping_methods/shipping_methods_enable'))
 		{
 			/**
 			 * SHIPPING METHODS ARE ENABLED!
 			 * Get mapping configured for OpenSi <> Magento shipping methods
 			 */
			$deliveryMethods = array();
			$deliveryMethodsMapping = $this->_manager->create('\Magento\Framework\Serialize\Serializer\Json')->unserialize($this->getStoreConfigValue('opensi_configuration/shipping_methods/shipping_methods_mapping'));

			foreach ($deliveryMethodsMapping as $deliveryMethod)
			{
				$deliveryMethodOpenSi = $deliveryMethod['opensi_shipping_method'];
				$deliveryMethods[$deliveryMethodOpenSi] = $deliveryMethod['magento_shipping_method'];
			}

			/**
			 * Get shipping methods depending on the delivery methods configuration
			 */
			$deliveryMethodsMapped = $this->_manager->create('\Speedinfo\Opensi\Model\ResourceModel\ShippingMethodsOpensi\Collection');
			$deliveryMethodsMapped->addFieldToFilter('shipping_method_id', array('in' => array_keys($deliveryMethods)));
			$deliveryMethodsMatchFound = false;

			foreach ($deliveryMethodsMapped as $deliveryMethodMapped)
			{
				/**
				 * Get carrier informations only if the OpenSi shipping method has matched
				 */
				if ($item->ShippingMode == $deliveryMethodMapped->getName())
				{
					$carriers = array();
					$deliveryMethodsMatchFound = true;
					$deliveryCarrierCode = $deliveryMethods[$deliveryMethodMapped->getShippingMethodId()];

					foreach ($this->_manager->create('\Magento\Shipping\Model\Config')->getAllCarriers() as $code => $carrier)
					{
						if ($code == $deliveryCarrierCode)
						{
							if ($carrier->getConfigData('title')) {
								$title = $carrier->getConfigData('title');
							} else {
								$title = $code;
							}

							if ($name = $carrier->getConfigData('name')) {
								$shippingCarrierName = $title.' - '.$name;
							} else {
								$shippingCarrierName = $title;
							}
						}
					}
				}
			}

			/**
			 * Insert the shipping method if it does not exist in `opensi_shipping_methods`
			 */
			$newShippingMethod = $this->_manager->create('\Speedinfo\Opensi\Model\ShippingMethodsOpensi');
 			$newShippingMethod->loadByName($item->ShippingMode);
 			$newShippingMethod->setData('created_at', date('Y-m-d H:i:s', time()));
 			$newShippingMethod->save();

		 } else {

 			/**
 			 * SHIPPING METHODS ARE DISABLED!
 			 * Get carrier informations
 			 */
			$carriers = array();

			foreach ($this->_manager->create('\Magento\Shipping\Model\Config')->getAllCarriers() as $code => $carrier)
			{
				if ($carrier->isTrackingAvailable()) {
					$shippingTitle = $this->_manager->create('\Magento\Framework\App\Config\ScopeConfigInterface')->getValue('carriers/'.$code.'/title');
        	$carriers[$code] = array(
          	'label' => $shippingTitle,
          	'value' => $code
        	);
        }
			}

			/**
			 * Get order carrier informations
			 */
			$allCarriers = $this->_manager->create('\Magento\Shipping\Model\Config')->getAllCarriers();

			foreach ($allCarriers as $shippingCode => $shippingModel)
     	{
        if ($carrierMethods = $shippingModel->getAllowedMethods())
				{
					foreach ($carrierMethods as $methodCode => $method)
          {
						$code = $shippingCode.'_'.$methodCode;

						if ($code == $order->getShippingMethod() || $shippingCode == $order->getShippingMethod())
						{
							$title = $this->_manager->create('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('carriers/'. $shippingCode.'/title');
							$name = $this->_manager->create('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('carriers/'. $shippingCode.'/name');

							$carrierTitle = ($name ? $title.' - '.$name : $carrierTitle);
							$carrierCode = $shippingCode;
						}
					}
        }
			}


			/**
			 * Return information
			 */
			if (array_key_exists($carrierCode, $carriers)) {
				$deliveryMethodsMatchFound = true;
			} else {
				$deliveryMethodsMatchFound = false;
			}

			$deliveryCarrierCode = $carrierCode;
			$shippingCarrierName = $carrierTitle;

		}


		/**
		 * Add the new tracking number on shipment
		 */
		$trackingData = array(
			'carrier_code' => ($deliveryMethodsMatchFound?$deliveryCarrierCode:'custom'),
	    'title' => ($deliveryMethodsMatchFound?$shippingCarrierName:'-'),
	    'number' => $item->TrackingNumber,
		);

		$track = $this->_manager->create('\Magento\Sales\Model\Order\Shipment\TrackFactory')->create()->addData($trackingData);
		$shipment->addTrack($track)->save();

		/**
		 * Send e-mail with tracking number
		 */
		$this->_manager->create('\Magento\Sales\Model\Order\Shipment\Sender\EmailSender')->send($order, $shipment);

 		/**
 		 * Add comment on order
 		 */
 		$history = $order->addStatusHistoryComment(sprintf(__('Tracking number n°%1 automatically added on shipment n°%2 by OpenSi.', $item->TrackingNumber, $shipment->getIncrementId())), false);
 		$history->setIsCustomerNotified(true);

		$order->save();
	}








	/*=================================================================
	 *
	 * 				S E T   S H I P P I N G   M E T H O D S
	 *
	 ================================================================*/

	/**
	 * Set shipping methods
	 * Add shipping methods sent by OpenSi on Magento
	 * SWO-G084
	 *
	 * OpenSi => Magento
	 *
	 * @param $values (comments)
	 */
	public function setShippingMethods($values)
	{
		/**
	   * Authentification
	   */
	  $auth = new Authenticate();

	  if (!$auth->login($this->_manager, $this->_header->{'key'})) {
	    throw new Exception(OSI_INVALID_AUTH);
	  }

	  /**
	   * Check configuration depending on the website code
	   */
	  $this->checkConfiguration($values);

		/**
		 * Add new shipping methods
		 */
		$shippingMethodResponse = array();

		if (!is_array($values->{'ShippingMethod'})) {
			$values->{'ShippingMethod'} = array($values->{'ShippingMethod'});
		}

		foreach ($values->{'ShippingMethod'} as $item)
		{
			/**
			 * Check if the shipping method already exists in the table `opensi_shipping_methods`
			 */
			$shippingMethod = $this->_manager->create('\Speedinfo\Opensi\Model\ResourceModel\ShippingMethodsOpensi\Collection');
			$shippingMethod->addFieldToFilter('name', array('eq' => $item->Name));

			if (empty($shippingMethod->getColumnValues('shipping_method_id')))
			{
				/**
				 * Insert entry in the table `opensi_shipping_methods`
				 */
				$newShippingMethod = $this->_manager->create('\Speedinfo\Opensi\Model\ShippingMethodsOpensi');
				$newShippingMethod->setData('name', $item->Name);
				$newShippingMethod->setData('created_at', date('Y-m-d H:i:s', time()));
				$newShippingMethod->save();

				/**
				 * Response
				 */
				$shippingMethodResponse[] = new \Speedinfo\Opensi\Webservices\Classes\Response($item->Name, OSI_SUCCESS_UPDATE);

			} else {

				/**
				 * The shipping method already exists!
				 */
				$shippingMethodResponse[] = new \Speedinfo\Opensi\Webservices\Classes\Response($item->Name, OSI_WARNING_DUPLICATE, OSI_DUPLICATE_SHIPPING_METHOD);

			}
		}


		/**
		 * Return
		 */
		return array('return' => $shippingMethodResponse);
	}








	/*=================================================================
	 *
	 * 				S E T   C U S T O M E R S   F R O M   O P E N S I
	 *
	 ================================================================*/

	/**
	 * Set customers
	 * Create the customers sent by OpenSi on the store
	 * SWO-G004
	 *
	 * OpenSi => Magento
	 *
	 * @param $values (customers)
	 */
	public function setCustomers($values)
	{
		/**
	   * Authentification
	   */
	  $auth = new Authenticate();

	  if (!$auth->login($this->_manager, $this->_header->{'key'})) {
	    throw new Exception(OSI_INVALID_AUTH);
	  }

	  /**
	   * Check configuration depending on the website code
	   */
	  $this->checkConfiguration($values);

		/**
		 * Add new customer
		 */
		$customerResponse = array();

		if (!is_array($values->{'Customer'})) {
			$values->{'Customer'} = array($values->{'Customer'});
		}

		foreach ($values->{'Customer'} as $item)
		{
			/**
			 * Check if the new customer already exists on the store
			 * If not, then create it
			 */
			$emailAddress = $item->Login;
			$websiteId = $this->_manager->create('\Magento\Store\Model\StoreManagerInterface')->getStore()->getWebsiteId();

			$customerAlreadyExists = $this->_manager->create('Magento\Customer\Model\Customer');
			$customerAlreadyExists->setWebsiteId($websiteId);
			$customerAlreadyExists->loadByEmail($emailAddress);

			if (!$customerAlreadyExists->getId())
			{
				/**
				 * Definitions
				 */
				$billingCivility = $this->mapCivility($item->BillingCivility);
				$deliveryCivility = $this->mapCivility($item->DeliveryCivility);

				switch ($item->BillingCivility)
				{
					case 0:
						// Unkown gender
						$gender = 3;
						break;

					case 1:
						// Male
						$gender = 1;
						break;

					case 2:
					case 3:
						// Female
						$gender = 2;
						break;
				}

				$billingLastname = $item->BillingLastname;
				$billingFirstname = $item->BillingFirstname;
				$billingCompany = $item->BillingCompany;
				$billingAddress_1 = $item->BillingAddress_1;
				$billingAddress_2 = $item->BillingAddress_2;
				$billingAddress_3 = $item->BillingAddress_3;
				$billingZipcode = $item->BillingZipcode;
				$billingCity = $item->BillingCity;
				$billingPhone = $item->BillingPhone;
				$billingFax = $item->BillingFax;
				$billingCountryCode = $item->BillingCountryCode;

				$deliveryLastname = $item->DeliveryLastname;
				$deliveryFirstname = $item->DeliveryFirstname;
				$deliveryCompany = $item->DeliveryCompany;
				$deliveryAddress_1 = $item->DeliveryAddress_1;
				$deliveryAddress_2 = $item->DeliveryAddress_2;
				$deliveryAddress_3 = $item->DeliveryAddress_3;
				$deliveryZipcode = $item->DeliveryZipcode;
				$deliveryCity = $item->DeliveryCity;
				$deliveryPhone = $item->DeliveryPhone;
				$deliveryFax = $item->DeliveryFax;
				$deliveryCountryCode = $item->DeliveryCountryCode;

				/**
				 * Creation of the customer
				 */
				$customer = $this->_manager->create('\Magento\Customer\Model\CustomerFactory')->create();

				$customer->setWebsiteId($websiteId);
				$customer->setEmail($emailAddress);
				$customer->setPrefix($billingCivility);
				$customer->setFirstname($billingFirstname);
				$customer->setLastname($billingLastname);
				$customer->setGender($gender);
				$customer->setPassword($item->Password);
				$customer->setConfirmation(null);

				if ($customer->save())
				{
					/**
					 * Add customer address
					 */
					$address = $this->_manager->create('\Magento\Customer\Model\AddressFactory')->create();
					$address->setCustomerId($customer->getId());
					$address->setPrefix($billingCivility);
					$address->setLastname($billingLastname);
					$address->setFirstname($billingFirstname);
					$address->setCompany($billingCompany);
					$address->setStreet(array('0'=>$billingAddress_1, '1'=>$billingAddress_2.' '.$billingAddress_3));
					$address->setPostcode($billingZipcode);
					$address->setCity($billingCity);
					$address->setTelephone($billingPhone);
					$address->setFax($billingFax);
					$address->setCountryId($billingCountryCode);
					$address->setIsDefaultBilling(true);
					$address->setIsDefaultShipping(true);
					$address->save();

					/**
					 * If the delivery address is different from the billing address, then create it
					 */
					$billingAddress = md5($billingCivility . $billingLastname . $billingFirstname . $billingCompany . $billingAddress_1 . $billingAddress_2 . $billingAddress_3 . $billingZipcode . $billingCity . $billingPhone . $billingFax . $billingCountryCode);
					$deliveryAddress = md5($deliveryCivility . $deliveryLastname . $deliveryFirstname . $deliveryCompany . $deliveryAddress_1 . $deliveryAddress_2 . $deliveryAddress_3 . $deliveryZipcode . $deliveryCity . $deliveryPhone . $deliveryFax . $deliveryCountryCode);

					if ($billingAddress != $deliveryAddress)
					{
						$address = $this->_manager->create('\Magento\Customer\Model\AddressFactory')->create();
						$address->setCustomerId($customer->getId());
						$address->setPrefix($deliveryCivility);
						$address->setLastname($deliveryLastname);
						$address->setFirstname($deliveryFirstname);
						$address->setCompany($deliveryCompany);
						$address->setStreet(array('0'=>$deliveryAddress_1, '1'=>$deliveryAddress_2.' '.$deliveryAddress_3));
						$address->setPostcode($deliveryZipcode);
						$address->setCity($deliveryCity);
						$address->setTelephone($deliveryPhone);
						$address->setFax($deliveryFax);
						$address->setCountryId($deliveryCountryCode);
						$address->setIsDefaultShipping(true);
						$address->save();
					}

					/**
		    	 * Newsletter subscription
		    	 * Depending on the configuration, register (or not) the new customer to the newsletter
		    	 */
					if ($this->getStoreConfigValue('opensi_preferences/manage_customers/newsletter'))
					{
						$subscriber = $this->_manager->create('\Magento\Newsletter\Model\SubscriberFactory')->create()->subscribe($emailAddress);
					}

					/**
					 * Send email depending on the configuration
					 * Depending on the configuration, send (or not) an e-mail to the customer with its identifiers
					 */
					if ($this->getStoreConfigValue('opensi_preferences/manage_customers/notification'))
					{
						$customer->sendNewAccountEmail('registered');
					}
				}

				/**
				 * Response
				 */
				$customerResponse[] = new \Speedinfo\Opensi\Webservices\Classes\Response($item->Login, OSI_SUCCESS_UPDATE);

			} else {

				/**
				 * The customer already exists!
				 */
				$customerResponse[] = new \Speedinfo\Opensi\Webservices\Classes\Response($item->Login, OSI_WARNING_DUPLICATE, OSI_CUSTOMER_EXIST);

			}
		}

		/**
		 * Return
		 */
		return array('return' => $customerResponse);
	}


	/**
	 * Get civility
	 * Map the different civilities between OpenSi and Magento
	 *  - 0 => No gender
	 *  - 1 => M.
	 *  - 2 => Mme
	 *  - 3 => Mlle
	 *
	 * @param $civility
	 */
	private function mapCivility($civility)
	{
		switch ($civility) {
			case 1:
				return 'M.';
				break;
			case 2:
				return 'Mme';
				break;
			case 3:
				return 'Mlle';
				break;
		}
	}








	/*=================================================================
	 *
	 * 						S E T   C O M M E N T S
	 *
	 ================================================================*/

	/**
	 * Set comments
	 * Add comments sent by OpenSi on Magento orders
	 * SWO-G040
	 *
	 * OpenSi => Magento
	 *
	 * @param $values (comments)
	 */
	public function setComments($values)
	{
		/**
	   * Authentification
	   */
	  $auth = new Authenticate();

	  if (!$auth->login($this->_manager, $this->_header->{'key'})) {
	    throw new Exception(OSI_INVALID_AUTH);
	  }

	  /**
	   * Check configuration depending on the website code
	   */
	  $this->checkConfiguration($values);

		/**
		 * Add comment on order
		 */
		$commentResponse = array();

 		if (!is_array($values->{'Comment'})) {
 			$values->{'Comment'} = array($values->{'Comment'});
 		}

 		foreach ($values->{'Comment'} as $item)
 		{
			$order = $this->_manager->create('\Magento\Sales\Api\Data\OrderInterface')->loadByIncrementId($item->WebOrderNumber);

			if($order && $order->getId())
			{
				/**
				 * it is possible to add a comment on the order
				 */
				if ($order->canComment())
				{
					/**
					 * Check if the comment already exist for this order
					 */
					$commentCollection = $this->_manager->create('\Speedinfo\Opensi\Model\Comments')->getCollection();
					$commentCollection
						->getSelect()
						->where('opensi_comment_id = '.$item->CommentId);

					if (!$commentCollection->getSize() > 0)
					{
						/**
						 * Add comment
						 */
						$order->addStatusHistoryComment($item->Comment)->setIsCustomerNotified($item->SendMail)->setIsVisibleOnFront($item->VisibleForCustomer);
						$order->save();

						/**
						 * Insert entry in the table `opensi_comments`
						 */
						$comment = $this->_manager->create('\Speedinfo\Opensi\Model\Comments');
						$comment->setOpensiCommentId($item->CommentId);
						$comment->setCreatedAt(date('Y-m-d H:i:s', time()));
						$comment->save();

						/**
						 * Depending on the value Sendmail, send email or not
						 */
						$sendComment = $this->_manager->create('\Magento\Sales\Model\Order\Email\Sender\OrderCommentSender');
						$sendComment->send($order, $item->SendMail, $item->Comment);

						/**
						 * Response
						 */
						$commentResponse[] = new \Speedinfo\Opensi\Webservices\Classes\Response($item->WebOrderNumber, OSI_SUCCESS_UPDATE);

					} else {

						/**
						 * Return duplicate warning
						 */
						$commentResponse[] = new \Speedinfo\Opensi\Webservices\Classes\Response($item->WebOrderNumber, OSI_WARNING_DUPLICATE, OSI_DUPLICATE_COMMENT);

					}
				}

			} else {

				/**
				 * The order doesn't exist!
				 */
				$commentResponse[] = new \Speedinfo\Opensi\Webservices\Classes\Response($item->WebOrderNumber, OSI_ERROR_NOT_FOUND, OSI_INVALID_ORDER);

			}
		}

		/**
		 * Return
		 */
		return array('return' => $commentResponse);
	}








	/*=================================================================
	 *
	 * 												U T I L S
	 *
	 ================================================================*/

	/**
	 * Get store id
	 */
	public function getCurrentStoreId()
	{
		return $this->_manager->create('\Magento\Store\Model\StoreManagerInterface')->getStore()->getId();
	}


	/**
	 * Is default website ?
	 */
	private function isDefaultWebsite($storeId)
	{
		return $this->_manager->create('\Magento\Store\Model\Website')->load($this->_manager->create('\Magento\Store\Model\StoreManagerInterface')->getWebsite()->getId())->getIsDefault();
	}


	/**
 	 * Check the website code configuration
 	 *
 	 * @param $values
 	 */
 	public function checkConfiguration($values)
 	{
 		if ($values->{'Website_Code'} != $this->getStoreConfigValue('opensi_configuration/identification/websitecode'))
 		{
 			throw new Exception(OSI_INVALID_SHOP_CONFIGURATION);
 		}

 		return true;
 	}


 	/**
 	 * Convert date to UTC
 	 * Convert current date from config timezone to UTC
 	 *
 	 * @param $date (format yyyy-mm-dd hh:mm:ss)
 	 */
 	public function convertDatetoUTC($date)
 	{
 		//return $this->_manager->create('\Magento\Framework\Stdlib\DateTime\Timezone')->convertConfigTimeToUtc($date);
 		return $this->_manager->create('\Magento\Framework\Stdlib\DateTime\TimezoneInterface')->convertConfigTimeToUtc($date, \Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT);
 	}


	/**
 	 * Convert date to config timezone
 	 * Convert current date from UTC to config timezone
 	 *
 	 * @param $date (format yyyy-mm-dd hh:mm:ss)
 	 */
 	public function convertDatetoTimezone($date)
 	{
 		return $this->_manager->create('\Magento\Framework\Stdlib\DateTime\TimezoneInterface')->date(new \DateTime($date))->format('Y-m-d H:i:s');
 	}


 	/**
 	 * Get store config values
 	 *
 	 * @param $path
 	 */
 	public function getStoreConfigValue($path)
 	{
 		return $this->_manager->create('Magento\Framework\App\Config\ScopeConfigInterface')->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
 	}


	/**
	 * Check if field is synchronizable (depending on the fields configuration)
	 */
	private function isSynchronizable($name, $fieldsToSync, $customWebservice)
	{
    if ($customWebservice)
    {
      if ($fieldsToSync && count($fieldsToSync) > 0) {
        if (in_array($name, $fieldsToSync)) {
          return true;
        }
        return false;
      } else {
        return false;
      }
    } else {
      return true;
    }
	}


	/**
	 * Get attribute value depending on the type (select, input, ...)
	 */
	public function getAttributeValue($attributeCode, $product, $fieldsToSync, $customWebservice)
	{
		// Check if the attribute code is a path or not (i.e weight which doesn't contain a path with /)
		$code = $attributeCode;

		if (strpos($attributeCode, '/')) {
			$code = substr($attributeCode, strrpos($attributeCode, '/') + 1);
		}

		// Check if the code is synchronizable (preferences)
		if ($this->isSynchronizable($code, $fieldsToSync, $customWebservice))
		{
			$attributeCodeConfig = $this->getStoreConfigValue($attributeCode);

			if ($attributeCodeConfig)
			{
				if ($product->getResource()->getAttribute($attributeCodeConfig)->getIsUserDefined()) {
					$attributeValue = $product->getResource()->getAttribute($attributeCodeConfig)->getFrontend()->getValue($product);
				} else {
					$attributeValue = $product->getData($attributeCodeConfig);
				}
			} else {
				$attributeValue = $product->getData($attributeCode);
			}

			if ($attributeValue) {
				return $attributeValue;
			}
		}
	}


	/**
	 * Check if product attribute exists
	 */
	public function isProductAttributeExists($attributeCode)
	{
		$productAttribute = $this->_manager->create('\Magento\Eav\Model\Config')->getAttribute(\Magento\Catalog\Model\Product::ENTITY, $attributeCode);

		return ($productAttribute && $productAttribute->getId()) ? true : false;
	}


	/**
	 * Update attribute value
	 *
	 * @param $attributeCode
	 * @param $attributeValue
	 * @param $product
	 */
	public function updateAttribute($attributeCode, $attributeValue, $product, $fieldsToSync, $customWebservice)
	{
		/**
		 * If the website code sent by OpenSi corresponds to default website, the websiteId & storeId need to be set to 0 in order to update the default configuration
		 */
		$storeId = ($this->isDefaultWebsite($this->getCurrentStoreId()) == 1?0:$this->getCurrentStoreId());

	  if ($this->isSynchronizable(substr($attributeCode, strrpos($attributeCode, '/') + 1), $fieldsToSync, $customWebservice))
	  {
	    $attributeConfig = $this->getStoreConfigValue($attributeCode);

	    if ($attributeConfig)
	    {
	      $attribute = $this->_manager->create('\Magento\Catalog\Model\Product\Attribute\Repository')->get($attributeConfig);

	      if ($attribute->getFrontendInput() == 'select')
	      {
					if ($attribute->getOptions())
					{
						// Attribute with custom options
		        foreach ($attribute->getOptions() as $option)
		        {
		          if ($option->getLabel() == $attributeValue) {
		            $product->addAttributeUpdate($attributeConfig, $option->getValue(), $storeId);
		          } elseif ($option->getValue() == $attributeValue) {
								$product->addAttributeUpdate($attributeConfig, $attributeValue, $storeId); // Attribute from magento options (e.g. country of manufacturer)
							}
		        }
					}
	      } else {
	        $product->addAttributeUpdate($attributeConfig, $attributeValue, $storeId);
	      }
	    }
	  }
	}


	/**
	 * Bundles
	 * Check if a bundle has many products in one option
	 * If true, the parent product and its components are not sent
	 *
	 * @param $product
	 */
	public function checkIfBundleHasManyProductsInOneOption($product)
	{
		foreach ($product->getTypeInstance(true)->getChildrenIds($product->getId(), false) as $option)
		{
			if (!($option && count($option) === 1)) {
				return true;
			}
		}

		return false;
	}


	/**
	 * Get parentId
	 * Check if product has a parent and get the id
	 *
	 * @param $productId
	 * @return $parentId
	 */
	public function hasParent($productId)
	{
		$parentId = $this->_manager->create('\Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable')->getParentIdsByChild($productId);

		if (empty($parentId)) {
			$parentId = $this->_manager->create('\Magento\GroupedProduct\Model\ResourceModel\Product\Link')->getParentIdsByChild($productId, \Magento\GroupedProduct\Model\ResourceModel\Product\Link::LINK_TYPE_GROUPED);
		}

		if (!empty($parentId[0])) {
			return $parentId[0];
		} else {
			return false;
		}
	}



	/**
	 * Clear cache and reindex data(s)
	 *
	 * @param $productIds
	 * @param $reindexType (catalog_category_product, catalog_product_category, catalog_product_price, catalog_product_attribute, cataloginventory_stock, catalogrule_rule, catalogrule_product, catalogsearch_fulltext)
	 */
	public function clearCacheAndReindex($productIds, $reindexType)
	{
		if (!empty($productIds) && $this->_manager->create('\Magento\Indexer\Model\Indexer')->load($reindexType)->isScheduled())
		{
			// Reindex
			$indexTypes = array('catalog_product_attribute', $reindexType);
			foreach ($indexTypes as $indexType) {
				$this->_manager->create('\Magento\Indexer\Model\Indexer')->load($indexType)->reindexList($productIds);
			}

			// Clear cache
			$cache = $this->_manager->create('\Magento\Framework\Indexer\CacheContext')->registerEntities(\Magento\Catalog\Model\Product::CACHE_TAG, $productIds);

			$this->_manager->create('\Magento\Framework\Event\ManagerInterface')->dispatch('clean_cache_by_tags', ['object' => $cache]);
		}
	}

}
