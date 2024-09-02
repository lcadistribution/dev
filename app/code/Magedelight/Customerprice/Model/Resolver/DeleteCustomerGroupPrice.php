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
use Magedelight\Customerprice\Api\CustomerGroupPriceRepositoryInterface;
use Magedelight\Customerprice\Api\Data\CustomerGroupPriceInterface;

class DeleteCustomerGroupPrice implements ResolverInterface
{
    /**
     * @var CustomerGroupPriceRepositoryInterface
     */
    private $customerGroupPriceRepository;
    
    /**
     * @var CustomerGroupPriceInterface
     */
    private $customerGroupPrice;

    

    /**
     *
     * @param CustomerGroupPriceRepositoryInterface $customerGroupPriceRepository
     * @param CustomerGroupPriceInterface $customerGroupPrice
     */
    public function __construct(
        CustomerGroupPriceRepositoryInterface $customerGroupPriceRepository,
        CustomerGroupPriceInterface $customerGroupPrice
    ) {
        $this->customerGroupPriceRepository = $customerGroupPriceRepository;
        $this->customerGroupPrice = $customerGroupPrice;
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
        if (!isset($args['customergroupprice_id'])) {
            throw new GraphQlInputException(__('Specify the "customergroupprice_id" value.'));
        }

        //$id = $this->customerGroupPriceRepository->getById($args['customerprice_id']);
        $id = $this->customerGroupPrice->load($args['customergroupprice_id']);
        ;
        if (empty($id->getCustomergrouppriceId())) {
            throw new GraphQlNoSuchEntityException(
                __('Could not find a customer group price with id : %1', $args['customergroupprice_id'])
            );
        }

        return ['result' => $this->customerGroupPriceRepository->deleteById($args['customergroupprice_id'])];
    }
}
