<?php
/**
 * @package Magedelight_Customerprice for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */

namespace Magedelight\Customerprice\Controller\CustomerSpecialprice;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magedelight\Customerprice\Helper\Data;
use Magedelight\Customerprice\Api\Data\CustomerpriceSpecialpriceInterface;
use Magedelight\Customerprice\Api\CustomerpriceSpecialpriceRepositoryInterface;

class Save extends Action
{

    /**
     * @var JsonFactory
     */
    private $jsonFactory;

    /**
     * @var Data
     */
    private $helper;

    /**
     * @var CustomerpriceSpecialpriceInterface
     */
    private $customerSpecialpriceInterface;

    /**
     * @var CustomerpriceSpecialpriceRepositoryInterface
     */
    private $customerSpecialpriceRepositoryInterface;


    public function __construct(
        Context $context,
        JsonFactory $jsonFactory,
        Data $helper,
        CustomerpriceSpecialpriceInterface $customerSpecialpriceInterface,
        CustomerpriceSpecialpriceRepositoryInterface $customerSpecialpriceRepositoryInterface
    ) {
        $this->jsonFactory = $jsonFactory;
        $this->helper = $helper;
        $this->customerSpecialpriceInterface = $customerSpecialpriceInterface;
        $this->customerSpecialpriceRepositoryInterface = $customerSpecialpriceRepositoryInterface;
        
        parent::__construct($context);
    }

    /**
     * Execute action based on request and return result
     *
     * @return array
     */
    public function execute()
    {
        $result = $this->jsonFactory->create();
        $response = [];
        if ($this->helper->isEnabled() && $this->helper->specialPriceButton()) {
            $data = $this->getRequest()->getPostValue();
            $pid = isset($data['pid']) ? $data['pid'] : null;
            if ($pid) {
                try {
                    $this->customerSpecialpriceInterface = $this->customerSpecialpriceInterface->setData($data);
                    $this->customerSpecialpriceRepositoryInterface->save($this->customerSpecialpriceInterface);
                    $response = ['status' => 1, 'message' => "Your form has submitted Successfully."];
                                        
                } catch (Exception $e) {
                    $response = ['status' => 0, 'message' => $e->getMessage()];
                }
            }
        }
        $result->setData($response);
        return $result;
    }
}
