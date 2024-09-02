<?php

namespace Magedelight\Customerprice\Plugin\Block\Product\View\Type;

/**
 * @package Magedelight_Customerprice for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */

use Magedelight\Customerprice\Helper\Data;
use Magedelight\Customerprice\Api\CustomerpriceRepositoryInterface;

class Configurable
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
    public function afterGetJsonConfig(\Magento\ConfigurableProduct\Block\Product\View\Type\Configurable $subject,$result){

        $isLoggedIn = $this->httpContext->getValue(\Magento\Customer\Model\Context::CONTEXT_AUTH);

        if ($isLoggedIn && $this->helper->isEnabled()) {

            $jsonResult = json_decode($result, true);
            $jsonResult['date'] = [];

            foreach ($subject->getAllowProducts() as $simpleProduct) {

                $date = $this->customerPriceRepository->getCustomerPriceValidDate($simpleProduct->getId(), $this->helper->getUserId(), $this->helper->getCurrentWebsiteId());
                $date = ($date) ? __('( Valid till %1 )',$date) : "";
                $jsonResult['date'][$simpleProduct->getId()] = $date;
            }

            $result = json_encode($jsonResult);
        }
        
        return $result;
    }

    
}
