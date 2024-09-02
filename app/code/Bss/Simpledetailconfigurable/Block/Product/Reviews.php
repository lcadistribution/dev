<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_Simpledetailconfigurable
 * @author     Extension Team
 * @copyright  Copyright (c) 2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\Simpledetailconfigurable\Block\Product;

use Magento\Framework\Exception\NoSuchEntityException;

class Reviews extends \Magento\Review\Block\Product\ReviewRenderer
{
    /**
     * @var $storage
     */
    private $storage;

    /**
     * Get ratings summary
     *
     * @return int
     * @throws NoSuchEntityException
     */
    public function getRatingSummary()
    {
        return $this->getManageRatingReview()['summary'];
    }

    /**
     * Get count of reviews
     *
     * @return int
     * @throws NoSuchEntityException
     */
    public function getReviewsCount()
    {
        return $this->getManageRatingReview()['reviews'];
    }

    /**
     * Storage data
     *
     * @return array
     * @throws NoSuchEntityException
     */
    public function getManageRatingReview()
    {
        $product = $this->getProduct();
        if (!isset($this->storage[$product->getId()])) {
            if (!isset($this->storage[$product->getId()][$this->_storeManager->getStore()->getId()])) {
                $this->_reviewFactory->create()->getEntitySummary($product, $this->_storeManager->getStore()->getId());
                $manage = $product->getRatingSummary();
                $this->storage[$product->getId()][$this->_storeManager->getStore()->getId()] = [
                    'summary' => (int)$manage->getRatingSummary(),
                    'reviews' => (int)$manage->getReviewsCount()
                ];
            }
        }
        return $this->storage[$product->getId()][$this->_storeManager->getStore()->getId()];
    }
}
