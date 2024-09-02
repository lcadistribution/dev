<?php

/**
 * @package Magedelight_Customerprice for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */

namespace Magedelight\Customerprice\Controller\Adminhtml\Config;

use Magento\Framework\App\ResponseInterface;
use Magento\Config\Controller\Adminhtml\System\ConfigSectionChecker;
use Magento\Framework\App\Filesystem\DirectoryList;

class Export extends \Magento\Config\Controller\Adminhtml\System\AbstractConfig
{
    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    protected $fileFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;
    
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
    *
    * @param \Magento\Backend\App\Action\Context $context
    * @param \Magento\Config\Model\Config\Structure $configStructure
    * @param ConfigSectionChecker $sectionChecker
    * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
    * @param \Magento\Customer\Model\CustomerFactory $customerFactory
    * @param \Magento\Store\Model\StoreManagerInterface $storeManager
    * @param \Magedelight\Customerprice\Api\Data\CustomerpriceInterface $customerprice
    * @param \Magento\Catalog\Model\ProductFactory $product
    */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Config\Model\Config\Structure $configStructure,
        ConfigSectionChecker $sectionChecker,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magedelight\Customerprice\Api\Data\CustomerpriceInterface $customerprice,
        \Magento\Catalog\Model\ProductFactory $product
    ) {
        $this->storeManager = $storeManager;
        $this->fileFactory = $fileFactory;
        $this->customerFactory = $customerFactory;
        $this->customerPrice = $customerprice;
        $this->product = $product;
        parent::__construct($context, $configStructure, $sectionChecker);
    }

    /**
     * Export customer price in csv format.
     *
     * @return ResponseInterface
     */
    public function execute()
    {
        $fileNames = 'pricepercustomer.csv';
        $contents = '';
        $_columns = [
            'email', 'sku', 'qty', 'price', 'website_id', 'expiry_date'
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
            $data[] = ($_pricePerCustomer->getExpiryDate()) ? date("Y-m-d",strtotime($_pricePerCustomer->getExpiryDate())) : "";
            //$data[] = trim($_pricePerCustomer->getWebsiteId());

            $contents .= implode(',', $data)."\n";
        }

        return $this->fileFactory->create($fileNames, $contents, DirectoryList::VAR_DIR);
    }
}
