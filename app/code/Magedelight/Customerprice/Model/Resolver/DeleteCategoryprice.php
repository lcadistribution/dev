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
use Magedelight\Customerprice\Api\CustomerpriceCategoryRepositoryInterface;
use Magedelight\Customerprice\Api\Data\CustomerpriceCategoryInterface;

class DeleteCategoryprice implements ResolverInterface
{
    
    /**
     * @var CustomerpriceCategoryRepositoryInterface
     */
    private $categorypriceRepository;
    
    /**
     * @var CustomerpriceCategoryInterface
     */

    private $categoryprice;

    /**
     *
     * @param CustomerpriceCategoryRepositoryInterface $categorypriceRepository
     * @param CustomerpriceCategoryInterface $categoryprice
     */
    public function __construct(
        CustomerpriceCategoryRepositoryInterface $categorypriceRepository,
        CustomerpriceCategoryInterface $categoryprice
    ) {
        $this->categorypriceRepository = $categorypriceRepository;
        $this->categoryprice = $categoryprice;
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
        if (!isset($args['customerpricecategory_id'])) {
            throw new GraphQlInputException(__('Specify the "customerpricecategory_id" value.'));
        }

        //$id = $this->categorypriceRepository->getById($args['customerpricecategory_id']);
        $id = $this->categoryprice->load($args['customerpricecategory_id']);
        ;
        if (empty($id->getCustomerId())) {
            throw new GraphQlNoSuchEntityException(
                __('Could not find a category price with id : %1', $args['customerpricecategory_id'])
            );
        }
        return ['result' => $this->categorypriceRepository->deleteById($args['customerpricecategory_id'])];
    }
}
