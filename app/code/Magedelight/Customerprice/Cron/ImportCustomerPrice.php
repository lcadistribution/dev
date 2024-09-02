<?php
/**
 * @package Magedelight_Customerprice for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */

namespace Magedelight\Customerprice\Cron;

use Magento\Framework\App\Filesystem\DirectoryList;

class ImportCustomerPrice
{
    
    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $_filesystem;
    /**
     * @var \Magento\Framework\File\Csv
     */
    protected $csv;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $_logger;

    /**
     * Errors in import process.
     *
     * @var array
     */
    protected $_importErrors = [];

    /**
     * Customer factory.
     *
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $_customerFactory;

    /**
     * product model.
     *
     * @var \Magento\Catalog\Model\Product
     */
    protected $product;

    /**
     * customerPrice.
     *
     * @var \Magedelight\Customerprice\Api\Data\CustomerpriceInterface
     */
    protected $customerPrice;
    
    /**
     * customerPrice.
     *
     * @var \Magedelight\Customerprice\Model\ResourceModel\CustomerpriceCategory\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * customerPrice.
     *
     * @var \Magento\Catalog\Model\Category
     */
    protected $category;

    /**
     * customerPrice.
     *
     * @var \Magedelight\Customerprice\Api\Data\CustomerpriceCategoryInterface
     */
    protected $categoryprice;

    /**
     * customerPrice.
     *
     * @var \Magedelight\Customerprice\Helper\Data
     */
    protected $customerPriceHelper;

    /**
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\File\Csv $csv
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Magento\Catalog\Model\Product $product
     * @param \Magedelight\Customerprice\Api\Data\CustomerpriceInterface $customerPrice
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Catalog\Model\Category $category
     * @param \Magedelight\Customerprice\Model\ResourceModel\CustomerpriceCategory\CollectionFactory $collectionFactory
     * @param \Magedelight\Customerprice\Api\Data\CustomerpriceCategoryInterface $categoryprice
     * @param \Magedelight\Customerprice\Helper\Data $customerPriceHelper
     */
    public function __construct(
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\File\Csv $csv,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Catalog\Model\Product $product,
        \Magedelight\Customerprice\Api\Data\CustomerpriceInterface $customerPrice,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Catalog\Model\Category $category,
        \Magedelight\Customerprice\Model\ResourceModel\CustomerpriceCategory\CollectionFactory $collectionFactory,
        \Magedelight\Customerprice\Api\Data\CustomerpriceCategoryInterface $categoryprice,
        \Magedelight\Customerprice\Helper\Data $customerPriceHelper
    ) {
        $this->_filesystem = $filesystem;
        $this->csv = $csv;
        $this->_logger = $logger;
        $this->_customerFactory = $customerFactory;
        $this->product = $product;
        $this->customerPrice=$customerPrice;
        $this->category = $category;
        $this->collectionFactory=$collectionFactory;
        $this->categoryprice = $categoryprice;
        $this->customerPriceHelper = $customerPriceHelper;
    }

     /**
      * update specialprice status
      *
      * @return RedirectFactory
      */
    public function execute()
    {
        if ($this->customerPriceHelper->isEnabled()) {
            if ($this->customerPriceHelper->isImportCronEnabledCategory()) {
                $this->importCategoryWiseCSV();
            }
            if ($this->customerPriceHelper->isImportCronEnabledProduct()) {
                $this->importProductWiseCSV();
            }
          
          
        }
    }


    protected function importCategoryWiseCSV()
    {
        try {

            //import category csv
            $data = [];
            $csvFile = 'pricepercategory.csv';
            $pubDirectory = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA);

            
            $path = $pubDirectory->getRelativePath($csvFile);
            $stream = $pubDirectory->openFile($path);

            // check and skip headers
            $headers = $stream->readCsv();
            if ($headers === false || count($headers) < 1) {
                $stream->close();
                $this->_logger->critical('Please correct Price Per Customer File Format.');
            }

            $rowNumber = 1;
            $importData = [];

            while (false !== ($csvLine = $stream->readCsv())) {
                ++$rowNumber;
                if (empty($csvLine)) {
                    continue;
                }
                $row = $this->_getImportCategoryRow($csvLine, $headers, $rowNumber);

                if ($row !== false && $row['category_id'] > 0 && $row['customer_id'] > 0) {
                    /*$priceCollection = $this->collectionFactory->create();
                    $priceCollection->addFieldToFilter('category_id', $row['category_id']);
                    if (count($priceCollection) > 0) {
                        continue;
                    }*/
                    $this->categoryprice->setData($row)->save();
                }
            }
            $stream->close();
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
           // $stream->close();
            $this->_logger->critical($e->getMessage());
        } catch (\Exception $e) {
            //$stream->close();
            $this->_logger->critical('Something went wrong while importing prices.');
        }

        if ($this->_importErrors) {
            $error = __(
                'We couldn\'t import this file because of these errors: %1',
                implode(" \n", $this->_importErrors)
            );
            $this->_logger->critical($error);
        }
    }

    protected function _getImportCategoryRow($row, $headers, $rowNumber = 0)
    {
        if (count($row) < 3) {
            $this->_importErrors[] = __('Please correct Table Rates format in the Row #%1.', $rowNumber);
            return false;
        }
        $emailKey = array_search('customer_email', $headers);
        $catKey = array_search('category_id', $headers);
        $discountKey = array_search('discount', $headers);
        $websiteKey = array_search('website', $headers);
        $dateKey = array_search('expiry_date', $headers);
        // strip whitespace from the beginning and end of each row
        foreach ($row as $k => $v) {
            $row[$k] = trim($v);
        }

        $email = $row[$emailKey];
        $cat = $row[$catKey];
        if ($websiteKey) {
            $website_id = $row[$websiteKey];
        }
        $discount = $row[$discountKey];
        $date = ($row[$dateKey]) ? date("Y-m-d",strtotime($row[$dateKey])) : NULL;
        $matches = [];
        if (!is_numeric($discount)) {
            preg_match('/(.*)%/', $newprice, $matches);
            if ((is_array($matches) && count($matches) <= 0) || !is_numeric($matches[1])) {
                $this->_importErrors[] = __('Invalid discount "%1" in the Row #%2.', $row[$discountKey], $rowNumber);

                return false;
            } elseif (is_numeric($matches[1]) && ($matches[1] <= 0 || $matches[1] > 100)) {
                $this->_importErrors[] = __(
                    'Invalid New Price "%1" in the Row #%2.
                Percentage should be greater than 0 and less or equals than 100.',
                    $row[$discount],
                    $rowNumber
                );

                return false;
            }
        }

        if (!preg_match("/([\w\-]+\@[\w\-]+\.[\w\-]+)/", $email)) {
            $this->_importErrors[] = __('Invalid email "%1" in the Row #%2.', $row[$emailKey], $rowNumber);

            return false;
        }

        if ($websiteKey) {
            $customer = $this->_customerFactory->create()->getCollection()
                    ->addNameToSelect()
                    ->addAttributeToSelect('entity_id')
                    ->addAttributeToSelect('email')
                    ->addAttributeToSelect('group_id')
                    ->addFieldToFilter('email', $email)
                    ->addFieldToFilter('website_id', $website_id)
                    ->getFirstItem();
        } else {
            $customer = $this->_customerFactory->create()->getCollection()
                    ->addNameToSelect()
                    ->addAttributeToSelect('entity_id')
                    ->addAttributeToSelect('email')
                    ->addAttributeToSelect('group_id')
                    ->addFieldToFilter('email', $email)
                    ->getFirstItem();
        }
        
        $customerId = $customer->getId();
        
        $category = $this->category->load($cat);
        $categoryName = $category->getName();
       
        return [
            'customer_id' => $customerId, // Customer Id
            'customer_name' => $customer->getName(), // Customer Name
            'customer_email' => $email, // customer email
            'category_id' => $category->getId(), // Category Id
            'category_name' => $categoryName, // Category Name
            'discount' => $discount, //discont
            'expiry_date' => $date //date
        ];
    }

    protected function importProductWiseCSV()
    {

        
        try {

          //import product csv
            $data = [];
            $csvFile = 'pricepercustomer.csv';
            $pubDirectory = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA);
            $path = $pubDirectory->getRelativePath($csvFile);
            $stream = $pubDirectory->openFile($path);

          // check and skip headers
            $headers = $stream->readCsv();
            if ($headers === false || count($headers) < 1) {
                $stream->close();
                $this->_logger->critical('Please correct Price Per Customer File Format.');
            }

            $rowNumber = 1;
            $importData = [];
            while (false !== ($csvLine = $stream->readCsv())) {
                ++$rowNumber;

                if (empty($csvLine)) {
                    continue;
                }

                $row = $this->_getImportProductRow($csvLine, $headers, $rowNumber);
                $ppc = $this->customerPrice->getCollection()
                    ->addFieldToFilter('customer_id', $row['customer_id'])
                    ->addFieldToFilter('product_id', $row['product_id'])
                    ->addFieldToFilter('qty', $row['qty'])
                    ->addFieldToFilter('website_id', $row['website_id'])
                    ->getFirstItem();

                if ($ppc->getData('customerprice_id')) {
                    $row['customerprice_id'] = $ppc->getData('customerprice_id');
                }
                if ($row !== false && $row['product_id'] > 0 && $row['customer_id'] > 0) {
                    $this->customerPrice->setData($row)->save();
                }
            }

            $stream->close();
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
           // $stream->close();
            $this->_logger->critical($e->getMessage());
        } catch (\Exception $e) {
            //$stream->close();
            $this->_logger->critical('Something went wrong while importing prices.');
        }

        if ($this->_importErrors) {
            $error = __(
                'We couldn\'t import this file because of these errors: %1',
                implode(" \n", $this->_importErrors)
            );
            $this->_logger->critical($error);
        }
    }

    protected function _getImportProductRow($row, $headers, $rowNumber = 0)
    {
        if (count($row) < 4) {
            $this->_importErrors[] = __('Please correct Table Rates format in the Row #%1.', $rowNumber);

            return false;
        }
        $emailKey = array_search('email', $headers);
        $skuKey = array_search('sku', $headers);
        $qtyKey = array_search('qty', $headers);
        $priceKey = array_search('price', $headers);
        $websiteKey = array_search('website_id', $headers);
        $dateKey = array_search('expiry_date', $headers);
        // strip whitespace from the beginning and end of each row
        foreach ($row as $k => $v) {
            $row[$k] = trim($v);
        }
        $email = $row[0];
        $sku = $row[1];
        $qty = $row[2];
        $website_id = 1;
        if ($websiteKey) {
            $website_id = $row[4];
        }
        
        $newprice = $row[3];
        $logprice = $row[3];
        $date = ($row[$dateKey]) ? date("Y-m-d",strtotime($row[$dateKey])) : NULL;
        if (!is_numeric($qty)) {
            $this->_importErrors[] = __('Invalid Qty Price "%1" in the Row #%2.', $row[$qtyKey], $rowNumber);

            return false;
        } else {
            if ($qty <= 0) {
                $this->_importErrors[] = __('Qty should be greater than 0 in the Row #%1.', $rowNumber);

                return false;
            }
        }
        $matches = [];
        if (!is_numeric($newprice)) {
            preg_match('/(.*)%/', $newprice, $matches);
            if ((is_array($matches) && count($matches) <= 0) || !is_numeric($matches[1])) {
                $this->_importErrors[] = __('Invalid New Price "%1" in the Row #%2.', $row[$priceKey], $rowNumber);

                return false;
            } elseif (is_numeric($matches[1]) && ($matches[1] <= 0 || $matches[1] > 100)) {
                $this->_importErrors[] = __(
                    'Invalid New Price "%1" in the Row #%
                2.Percentage should be greater than 0 and less or equals than 100.',
                    $row[$priceKey],
                    $rowNumber
                );

                return false;
            }
        }
        if (!preg_match("/([\w\-]+\@[\w\-]+\.[\w\-]+)/", $email)) {
            $this->_importErrors[] = __('Invalid email "%1" in the Row #%2.', $row[0], $rowNumber);

            return false;
        }
        if (!empty($website_id)) {
            $customer = $this->_customerFactory->create()->getCollection()
                    ->addNameToSelect()
                    ->addAttributeToSelect('entity_id')
                    ->addAttributeToSelect('email')
                    ->addAttributeToSelect('group_id')
                    ->addFieldToFilter('email', $email)
                    //->addFieldToFilter('website_id', $website_id)
                    ->getFirstItem();
        } else {
            $customer = $this->_customerFactory->create()->getCollection()
                    ->addNameToSelect()
                    ->addAttributeToSelect('entity_id')
                    ->addAttributeToSelect('email')
                    ->addAttributeToSelect('group_id')
                    ->addFieldToFilter('email', $email)
                    ->getFirstItem();
        }
        $customerId = $customer->getId();

        $product = $this->product->loadByAttribute('sku', $sku);
        if (!$product) {
            $this->_importErrors[] = __('%1 Products are not allowed in the row #%2.', ucfirst($sku), $rowNumber);
            return false;
        }
        if ($product->getTypeId() == 'grouped' ||
            $product->getTypeId() == 'bundle' ||
            $product->getTypeId() == 'configurable') {
            $this->_importErrors[] = __('%1 Products are not allowed in the row #%
            2.', ucfirst($product->getTypeId()), $rowNumber);
            return false;
        }

        $productName = $product->getName();
        $productId = $product->getId();
        $price = $product->getPrice();
        if (is_array($matches) && count($matches) > 0) {
            if ($product->getTypeId() != 'bundle') {
                $newprice = $product->getPrice() - ($product->getPrice() * ($matches[1] / 100));
            } else {
                if ($matches[1] < 0 || $matches[1] > 100) {
                    $this->_importErrors[] = __(
                        'Invalid New Price "%1" in the row #%
                    2.Percentage should be greater than 0 and less or equals than 100.',
                        $newprice,
                        $rowNumber
                    );

                    return false;
                } else {
                    $newprice = $matches[1];
                }
            }
        } else {
            if ($product->getTypeId() == 'bundle') {
                if ($newprice < 0 || $newprice > 100) {
                    $this->_importErrors[] = __(
                        'Invalid New Price "%1" in the row #%
                    2.Percentage should be greater than 0 and less or equals than 100.',
                        $newprice,
                        $rowNumber
                    );

                    return false;
                }
            }
        }

        return [
            'customer_id' => $customerId, // Customer Id
            'customer_name' => $customer->getName(), // Customer Name
            'customer_email' => $email, // customer email
            'product_id' => $productId, // Product Id
            'product_name' => $productName, // Product Name
            'price' => $price, // Price
            'log_price' => ($product->getTypeId() != 'bundle') ? $logprice : str_replace('%', '', $logprice),
            'new_price' => ($product->getTypeId() != 'bundle') ? $newprice : str_replace('%', '', $newprice), // New price for customer
            'qty' => $qty,           // Qty
            'website_id' => $website_id,
            'expiry_date' => $date //date
        ];
    }
}
