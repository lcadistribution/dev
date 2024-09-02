<?php
/**
 * @package Magedelight_Customerprice for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */

namespace Magedelight\Customerprice\Model\Calculation\Calculator;

use Magedelight\Customerprice\Model\Calculation\Calculator\AbstractCalculator;
use Magento\Customer\Api\AccountManagementInterface as CustomerAccountManagement;
use Magento\Framework\Exception\NoSuchEntityException;
use Magedelight\Customerprice\Helper\Data as Helper;
use Magento\Framework\App\Http\Context;
use Magento\Customer\Model\SessionFactory;
use Magedelight\Customerprice\Api\Data\CustomerpriceDiscountInterface as DiscountModel;
use Magedelight\Customerprice\Api\Data\CustomerpriceCategoryInterface as CategoryDiscountModel;
use Magento\Sales\Model\AdminOrder\Create  as OrderCreate;
use Magedelight\Customerprice\Model\Customer\Context as CustomerContex;

class GlobalDiscountCalculator extends AbstractCalculator
{

    /**
     * @var Context
     */
    protected $context;

    /**
     * @var SessionFactory
     */
    protected $customerSession;

    /**
     * @var DiscountModel
     */
    protected $discountModel;

    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @var CategoryDiscountModel
     */
    protected $categoryDiscountModel;

    /**
     * @var OrderCreate
     */
    protected $orderCreate;

    /**
     * @var \Magento\Framework\App\State
     */
    protected $state;

    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;

    /**
     * @var \Magedelight\Customerprice\Api\Data\CustomerGroupPriceInterface
     */
    protected $customerGroupPrice;

     /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    protected $productmetadata;

    /**
     * @var \Magento\Catalog\Model\ProductRepository
     */
    protected $productRepository;

    /**
     * AbstractCalculation constructor.
     *
     * @param Context $context
     * @param SessionFactory $customerSession
     * @param DiscountModel $discountModel
     * @param Helper $helper
     * @param CategoryDiscountModel $categoryDiscountModel
     * @param OrderCreate $orderCreate
     * @param \Magento\Framework\App\State $state
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param \Magedelight\Customerprice\Api\Data\CustomerGroupPriceInterface $customerGroupPrice
     * @param \Magento\Framework\App\ProductMetadataInterface $productmetadata
     * @param \Magento\Catalog\Model\ProductRepository $productRepository
     */
    public function __construct(
        Context $context,
        SessionFactory $customerSession,
        DiscountModel $discountModel,
        Helper $helper,
        CategoryDiscountModel $categoryDiscountModel,
        OrderCreate $orderCreate,
        \Magento\Framework\App\State $state,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magedelight\Customerprice\Api\Data\CustomerGroupPriceInterface $customerGroupPrice,
        \Magento\Framework\App\ProductMetadataInterface $productmetadata,
        \Magento\Catalog\Model\ProductRepository $productRepository
    ) {
        $this->context = $context;
        $this->customerSession = $customerSession;
        $this->discountModel = $discountModel;
        $this->categoryDiscountModel = $categoryDiscountModel;
        $this->orderCreate = $orderCreate;
        $this->state = $state;
        $this->httpContext = $httpContext;
        $this->customerGroupPrice = $customerGroupPrice;
        $this->productmetadata = $productmetadata;
        $this->productRepository = $productRepository;
        parent::__construct($helper);
    }
    /**
     * {@inheritdoc}
     */
    public function calculate($price, $product, $customerId = null)
    {
        if ($this->helper->isEnabled()) {
            $discount = 0.00;
            $gPrice = 0.00;
            $productId = $product->getId();

            if ($this->getMagentoEdition()!=='Community' && empty($product->getCategoryIds())) {
               $product = $this->productRepository->getById($productId);
            }

            if (!$customerId) {
                if ($this->state->getAreaCode() == 'adminhtml') {
                    $customerId = $this->orderCreate->getQuote()->getCustomer()->getId();
                } else {
                    $customerId = $this->getCustomerId();
                }
            }
            if ($customerId) {
                if($this->helper->getConfig('customerprice/general/enable_customer_groupprice')){
            	   $gPrice = $this->getDiscountByCustomerGroup($price);
                }
                $discount = $this->calculateMaximumDiscount($customerId, $product->getCategoryIds());
                if ($discount > 0.00) {
                    $resultPrice = $price - (($price * $discount)/100);
                    if ($gPrice > 0.00) {
                    	if ($gPrice < $resultPrice) {
                    		return $gPrice;
                    	}
                    }
                    return $resultPrice;
                }
                if ($gPrice > 0.00) {
                	return $gPrice;
                }
            }
        }
        return null;
    }

    private function getDiscountByCustomerGroup($price)
    {	
    	$discountPrice = 0.00;
    	$groupId = $this->getGroupId();
        $discount = $this->customerGroupPrice->getCollection()
                ->addFieldToFilter('group_id', ['eq' => $groupId])
                ->getFirstItem();
        if (!empty($discount)) {
            $groupPrice = $discount->getValue();
            $first_character = mb_substr($groupPrice ?? "", 0, 1);
            $groupPrice = substr($groupPrice ?? "", 1);
            $priceType = $discount->getPriceType();

            if ($first_character=='+') {
                if ($priceType == 1) {
                    $discountPrice = $price + (($price * $groupPrice)/100);
                }else{
                    $discountPrice = $price + $groupPrice;
                }
            }elseif ($first_character=='-') {
                if ($priceType == 1) {
                    $discountPrice = $price - (($price * $groupPrice)/100);
                }else{
                    $discountPrice = $price - $groupPrice;
                }
            }
        }

        return $discountPrice;
    }

    private function getDiscountByCustomerId($customerId)
    {
        $discount = $this->discountModel->getCollection()
                ->addFieldToFilter('customer_id', ['eq' => $customerId])
                ->getFirstItem();
        if (!empty($discount)) {
            return $discount->getValue();
        } else {
            return (int)0;
        }
    }
    
    private function getCategoryDiscount($customerId, $categoryIds)
    {
        $categoryDiscounts = $this->categoryDiscountModel
                ->getCollection()
                ->addFieldToSelect('*')
                ->addFieldToFilter('category_id', ['in' => $categoryIds])
                ->addFieldToFilter('customer_id', ['eq' => $customerId]);
        foreach ($categoryDiscounts as $categoryDiscount) {
            $discountArray[] = $categoryDiscount->getDiscount();
        }
        if (!empty($discountArray)) {
            $maxCategoryDiscount = max($discountArray);
            return $maxCategoryDiscount;
        } else {
            return (int)0;
        }
    }
    
    private function calculateMaximumDiscount($customerId, $categoryIds)
    {
        $categoryDiscount = $this->getCategoryDiscount($customerId, $categoryIds);
        $globalDiscount = $this->getDiscountByCustomerId($customerId);
        return max($categoryDiscount, $globalDiscount);
    }
    
    /**
     * @return mixed|null
     */
    private function getCustomerId()
    {
        return $this->customerSession->create()->getCustomerId();
    }

    /**
     * @return mixed|null
     */
    private function getGroupId()
    {
        return $this->customerSession->create()->getCustomerGroupId();
    }

     /**
     * @return mixed|null
     */
    private function getMagentoEdition()
    {
        return $this->productmetadata->getEdition();
    }
}
