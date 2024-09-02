<?php

/**
 * @package Magedelight_Customerprice for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */

namespace Magedelight\Customerprice\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magedelight\Customerprice\Api\CustomerpriceSpecialpriceRepositoryInterface;
use Magedelight\Customerprice\Api\Data\CustomerpriceSpecialpriceInterface;

class DeleteCustomerSpecialPrice implements ResolverInterface
{
    /**
     * @var CustomerpriceSpecialpriceRepositoryInterface
     */
    private $customerSpecialPriceRepository;
    
    /**
     * @var CustomerpriceSpecialpriceInterface
     */
    private $customerSpecialPrice;

    

    /**
     *
     * @param CustomerpriceSpecialpriceRepositoryInterface $customerSpecialPriceRepository
     * @param CustomerpriceSpecialpriceInterface $customerSpecialPrice
     */
    public function __construct(
        CustomerpriceSpecialpriceRepositoryInterface $customerSpecialPriceRepository,
        CustomerpriceSpecialpriceInterface $customerSpecialPrice
    ) {
        $this->customerSpecialPriceRepository = $customerSpecialPriceRepository;
        $this->customerSpecialPrice = $customerSpecialPrice;
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        if (!isset($args['customerspecialprice_id'])) {
            throw new GraphQlInputException(__('Specify the "customerspecialprice_id" value.'));
        }

        //$id = $this->customerSpecialPriceRepository->getById($args['customerprice_id']);
        $id = $this->customerSpecialPrice->load($args['customerspecialprice_id']);
        ;
        if (empty($id->getCustomerspecialpriceId())) {
            throw new GraphQlNoSuchEntityException(
                __('Could not find a customer special price with id : %1', $args['customerspecialprice_id'])
            );
        }

        return ['result' => $this->customerSpecialPriceRepository->deleteById($args['customerspecialprice_id'])];
    }
}
