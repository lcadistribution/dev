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

class Exportcategory extends \Magento\Config\Controller\Adminhtml\System\AbstractConfig
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
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Config\Model\Config\Structure $configStructure
     * @param ConfigSectionChecker $sectionChecker
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magedelight\Customerprice\Api\Data\CustomerpriceCategoryInterface $categoryPrice
     * @param \Magento\Catalog\Model\CategoryFactory $category
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Config\Model\Config\Structure $configStructure,
        ConfigSectionChecker $sectionChecker,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magedelight\Customerprice\Api\Data\CustomerpriceCategoryInterface $categoryPrice,
        \Magento\Catalog\Model\CategoryFactory $category
    ) {
        $this->storeManager = $storeManager;
        $this->fileFactory = $fileFactory;
        $this->customerFactory = $customerFactory;
        $this->categoryPrice = $categoryPrice;
        $this->category = $category;
        parent::__construct($context, $configStructure, $sectionChecker);
    }

    /**
     * Export category price in csv format.
     *
     * @return ResponseInterface
     */
    public function execute()
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
            $data[] = ($_pricePerCustomer->getExpiryDate()) ? date("Y-m-d",strtotime($_pricePerCustomer->getExpiryDate())) : "";
            $content .= implode(',', $data)."\n";
        }

        return $this->fileFactory->create($fileNamePass, $content, DirectoryList::VAR_DIR);
    }
}
