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
use Magedelight\Customerprice\Api\CustomerpriceRepositoryInterface;
use Magedelight\Customerprice\Api\Data\CustomerpriceInterface;

class DeleteCustomerprice implements ResolverInterface
{
    /**
     * @var CustomerpriceRepositoryInterface
     */
    private $customerpriceRepository;
    
    /**
     * @var CustomerpriceInterface
     */
    private $customerprice;

    

    /**
     *
     * @param CustomerpriceRepositoryInterface $customerpriceRepository
     */
    public function __construct(
        CustomerpriceRepositoryInterface $customerpriceRepository,
        CustomerpriceInterface $customerprice
    ) {
        $this->customerpriceRepository = $customerpriceRepository;
        $this->customerprice = $customerprice;
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
        if (!isset($args['customerprice_id'])) {
            throw new GraphQlInputException(__('Specify the "customerprice_id" value.'));
        }

        //$id = $this->customerpriceRepository->getById($args['customerprice_id']);
        $id = $this->customerprice->load($args['customerprice_id']);
        ;
        if (empty($id->getCustomerId())) {
            throw new GraphQlNoSuchEntityException(
                __('Could not find a customer price with id : %1', $args['customerprice_id'])
            );
        }

        return ['result' => $this->customerpriceRepository->deleteById($args['customerprice_id'])];
    }
}
