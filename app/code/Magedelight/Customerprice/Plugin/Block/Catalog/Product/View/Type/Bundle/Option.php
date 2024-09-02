<?php

namespace Magedelight\Customerprice\Plugin\Block\Catalog\Product\View\Type\Bundle;

/**
 * @package Magedelight_Customerprice for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */

use Magedelight\Customerprice\Helper\Data;
use Magedelight\Customerprice\Api\CustomerpriceRepositoryInterface;

class Option
{

	/**
     * @var use Magedelight\Customerprice\Helper\Data
     */
    protected $helper;

    /**
    * @var use CustomerpriceRepositoryInterface
    */
    protected $customerPriceRepository;

     /**
     * @var use \Magento\Framework\App\Http\Context
     */
    protected $httpContext;

     /**
     * @param Data $helper
     * @param CustomerpriceRepositoryInterface $customerPriceRepository
     * @param \Magento\Framework\App\Http\Context $httpContext
     *
     */
	public function __construct(
		Data $helper,
		CustomerpriceRepositoryInterface $customerPriceRepository,
	    \Magento\Framework\App\Http\Context $httpContext){
		$this->helper = $helper;
		$this->customerPriceRepository = $customerPriceRepository;
		$this->httpContext = $httpContext;
		
	}

    /**
     * @param $subject
     * @param string $result
     */
    public function afterRenderPriceString(\Magento\Bundle\Block\Catalog\Product\View\Type\Bundle\Option $subject, $result,$selection, $includeContainer = true)
    {
    	$isLoggedIn = $this->httpContext->getValue(\Magento\Customer\Model\Context::CONTEXT_AUTH);

    	if ($isLoggedIn && $this->helper->isEnabled()) {
    		$date = $this->customerPriceRepository->getCustomerPriceValidDate($selection->getId(), $this->helper->getUserId(), $this->helper->getCurrentWebsiteId());
            
                $date = ($date) ? __('( Valid till %1 )',$date) : "";
                return $result.$date;
    	}
        return $result;
    }

    
}
