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
 * @package    Bss_SimpledetailconfigurableGraphQl
 * @author     Extension Team
 * @copyright  Copyright (c) 2020-2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
declare(strict_types=1);

namespace Bss\SimpledetailconfigurableGraphQl\Model\Resolver;

use Magento\Review\Model\ResourceModel\Review\CollectionFactory as ReviewCollectionFactory;

class BaseResolver
{
    /**
     * @var ReviewCollectionFactory
     */
    protected $reviewCollectionFactory;

    /**
     * BaseResolver constructor.
     * @param ReviewCollectionFactory $reviewCollectionFactory
     */
    public function __construct(
        ReviewCollectionFactory $reviewCollectionFactory
    ) {
        $this->reviewCollectionFactory = $reviewCollectionFactory;
    }

    /**
     * @param array $preselect
     * @return array
     */
    protected function setPreselect(array $preselect): array
    {
        $dataSelected = $preselect['data'] ?? [];
        $preselectArr = [];
        foreach ($dataSelected as $attrId => $attrValue) {
            $preselectData = [];
            $preselectData['attribute_id'] = (int)$attrId;
            $preselectData['selected_value'] = (int)$attrValue;
            $preselectArr[] = $preselectData;
        }
        return $preselectArr;
    }

    /**
     * @param array $images
     * @return array
     */
    protected function setImages(array $images): array
    {
        $imagesArr = [];
        foreach ($images as $image) {
            $imageData = [];
            $imageData['video_url'] = $image['videoUrl'] ?? '';
            $imageData['caption'] = $image['caption'] ?? '';
            $imageData['full'] = $image['full'] ?? '';
            $imageData['img'] = $image['img'] ?? '';
            $imageData['thumb'] = $image['thumb'] ?? '';
            $imageData['is_main'] = $image['isMain'] ?? false;
            $imageData['type'] = $image['type'] ?? '';
            $imageData['position'] = isset($image['position']) ? (int)$image['position'] : 0;
            $imagesArr[] = $imageData;
        }
        return $imagesArr;
    }

    /**
     * @param array $childItems
     * @param int $storeId
     * @return array
     */
    protected function setChildData(array $childItems, int $storeId = 0): array
    {
        $reviewData = $this->getReviewData(array_keys($childItems), $storeId);
        $childProductDataArr = [];
        foreach ($childItems as $childItem) {
            $childProductData = [];
            if (isset($childItem['entity'])) {
                $childProductData['entity'] = (int)$childItem['entity'];
                $childProductData['sku'] = $childItem['sku'];
                $childProductData['name'] = $childItem['name'];
                $childProductData['desc'] = $childItem['desc'];
                $childMetaDataArr = $childItem['meta_data'] ?? [];
                $childProductData['meta_data'] = [
                    'meta_description' => $childMetaDataArr['meta_description'] ?? '',
                    'meta_keyword' => $childMetaDataArr['meta_keyword'] ?? '',
                    'meta_title' => $childMetaDataArr['meta_title'] ?? ''
                ];
                $childProductData['stock_data'] = [
                    'is_in_stock' => (bool)$childItem['stock_status'] ?? false,
                    'salable_qty' => (float)$childItem['stock_number'] ?? 0
                ];

                $childImagesArr = $childItem['image'] ?? [];
                $childProductData['images'] = $this->setImages($childImagesArr);

                $childProductData['tier_price'] = $this->setTierPrices($childItem['price']['tier_price'] ?? []);
                $childProductData['additional_info'] = $this->setAdditionalInfo($childItem['additional_info'] ?? []);

                // Reviews
                $childProductData['review_count'] = $childItem['review_count'] ?? 0;
                $reviewsAvailable = array_filter($reviewData, function ($reviewArr) use ($childItem) {
                    return isset($reviewArr['entity_pk_value']) &&
                        $reviewArr['entity_pk_value'] == $childItem['entity'];
                });
                $reviews = [];
                if (!empty($reviewsAvailable)) {
                    foreach ($reviewsAvailable as $reviewItemData) {
                        $reviews[] = $reviewItemData;
                    }
                }
                $childProductData['reviews'] = $reviews;

                $childProductDataArr[] = $childProductData;
            }
        }
        return $childProductDataArr;
    }

    /**
     * @param array|int|string $products
     * @param int $storeId
     * @return array
     */
    protected function getReviewData($products, int $storeId = 0): array
    {
        /** @var \Magento\Review\Model\ResourceModel\Review\Collection $reviewCollection */
        $reviewCollection = $this->reviewCollectionFactory->create();
        $reviewCollection->addStoreFilter(
            $storeId
        )->addStatusFilter(
            \Magento\Review\Model\Review::STATUS_APPROVED
        );
        if (is_array($products)) {
            $reviewCollection->addFieldToFilter(
                'entity_pk_value',
                [
                    'in' => $products
                ]
            );
        } else if (is_string($products) || is_numeric($products)) {
            $reviewCollection->addEntityFilter(
                'product',
                $products
            );
        }
        $reviewData = $reviewCollection->getData();
        return $reviewData;
    }

    /**
     * @param array $tiers
     * @return array
     */
    protected function setTierPrices(array $tiers): array
    {
        $tierPrices = [];
        foreach ($tiers as $tier) {
            $tierPrice = [];
            $tierPrice['qty'] = (float)$tier['qty'] ?? 0;
            $tierPrice['final'] = (float)$tier['value'] ?? 0;
            $tierPrice['value'] = (float)$tier['final'] ?? 0;
            $tierPrice['base'] = (float)$tier['base'] ?? 0;
            $tierPrice['final_discount'] = (float)$tier['final_discount'] ?? 0;
            $tierPrice['base_discount'] = (float)$tier['base_discount'] ?? 0;
            $tierPrice['percent'] = (float)$tier['percent'] ?? 0;
            $tierPrices[] = $tierPrice;
        }
        return $tierPrices;
    }

    /**
     * @param array $infoArr
     * @return array
     */
    protected function setAdditionalInfo(array $infoArr): array
    {
        $additionalInfoList = [];
        foreach ($infoArr as $attrCode => $info) {
            $additionalInfo = [];
            $additionalInfo['code'] = $attrCode;
            $additionalInfo['label'] = $info['label'] ?? '';
            $additionalInfo['value'] = $info['value'] ?? '';
            $additionalInfoList[] = $additionalInfo;
        }

        return $additionalInfoList;
    }
}
