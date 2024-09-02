<?php
/**
 * @package Magedelight_Customerprice for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */

namespace Magedelight\Customerprice\Cron;

use Magento\Framework\App\Filesystem\DirectoryList;

class ExportCustomerPrice
{
    
    /**
     *
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;
    
    /**
     *
     * @var \Magedelight\Customerprice\Api\Data\CustomerpriceInterface
     */
    protected $customerPrice;
    
    /**
     *
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $product;

    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    protected $fileFactory;

    /**
     *
     * @var \Magedelight\Customerprice\Api\Data\CustomerpriceCategoryInterface
     */
    protected $categoryPrice;
    
    /**
     *
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    protected $category;

     /**
     *
     * @var \Magedelight\Customerprice\Helper\Data
     */
    protected $customerPriceHelper;

     /**
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Magedelight\Customerprice\Api\Data\CustomerpriceInterface $customerprice
     * @param \Magento\Catalog\Model\ProductFactory $product
     * @param \Magedelight\Customerprice\Api\Data\CustomerpriceCategoryInterface $categoryPrice
     * @param \Magento\Catalog\Model\CategoryFactory $category
     * @param \Magedelight\Customerprice\Helper\Data $customerPriceHelper
     */
    public function __construct(
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magedelight\Customerprice\Api\Data\CustomerpriceInterface $customerprice,
        \Magento\Catalog\Model\ProductFactory $product,
        \Magedelight\Customerprice\Api\Data\CustomerpriceCategoryInterface $categoryPrice,
        \Magento\Catalog\Model\CategoryFactory $category,
        \Magedelight\Customerprice\Helper\Data $customerPriceHelper
    ) {

        $this->fileFactory = $fileFactory;
        $this->customerFactory = $customerFactory;
        $this->customerPrice = $customerprice;
        $this->product = $product;
        $this->categoryPrice = $categoryPrice;
        $this->category = $category;
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
            if ($this->customerPriceHelper->isExportCronEnabledProduct()) {
                $this->exportProductCsv();
            }
            if ($this->customerPriceHelper->isExportCronEnabledCategory()) {
                $this->exportCategoryCsv();
            }
        }
    }

    protected function exportProductCsv()
    {

        $fileNames = 'pricepercustomer.csv';
          $contents = '';
          $_columns = [
              'email', 'sku', 'qty', 'price', 'website_id','expiry_date'
          ];
          $data = [];
          foreach ($_columns as $column) {
              $data[] = '"'.$column.'"';
          }
          $contents .= implode(',', $data)."\n";
          
          $pricePerCustomer = $this->customerPrice->getCollection();
          foreach ($pricePerCustomer as $_pricePerCustomer) {
              $product = $this->product->create()->load(trim($_pricePerCustomer['product_id']));
              $customer = $this->customerFactory->create()->load(trim($_pricePerCustomer['customer_id']));

              $data = [];
              $data[] = trim($_pricePerCustomer->getCustomerEmail());
              $data[] = trim($product->getSku());
              $data[] = trim($_pricePerCustomer->getQty());
              $data[] = trim($_pricePerCustomer->getLogPrice());
              $data[] = trim($customer->getWebsiteId());
              //$data[] = trim($_pricePerCustomer->getWebsiteId());
              $data[] = trim($_pricePerCustomer->getExpiryDate() ?? "");

              $contents .= implode(',', $data)."\n";
          }
          return $this->fileFactory->create($fileNames, $contents, DirectoryList::VAR_DIR);
    }

    protected function exportCategoryCsv()
    {

        $fileNamePass = 'pricepercategory.csv';
          $content = '';
          $_columnsPass = [
              'customer_email', 'category_id', 'discount', 'website','expiry_date'
          ];
          $data = [];
          foreach ($_columnsPass as $column) {
              $data[] = '"'.$column.'"';
          }
          $content .= implode(',', $data)."\n";
          
          $pricePerCustomer = $this->categoryPrice->getCollection();
          
          foreach ($pricePerCustomer as $_pricePerCustomer) {
              $category = $this->category->create()->load(trim($_pricePerCustomer['category_id']));

              $customer = $this->customerFactory->create()->load(trim($_pricePerCustomer['customer_id']));

              $data = [];
              $data[] = trim($_pricePerCustomer->getCustomerEmail());
              $data[] = trim($_pricePerCustomer->getCategoryId());
              $data[] = trim($_pricePerCustomer->getDiscount());
              $data[] = trim($customer->getWebsiteId());
              $data[] = trim($_pricePerCustomer->getExpiryDate() ?? "");
              $content .= implode(',', $data)."\n";
          }
          return $this->fileFactory->create($fileNamePass, $content, DirectoryList::VAR_DIR);
    }
}
