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
 * @copyright  Copyright (c) 2017-2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\Simpledetailconfigurable\Controller\Ajax\Product;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\Layout;
use Bss\Simpledetailconfigurable\Controller\Ajax\Product as ProductController;
use Magento\Framework\Controller\ResultFactory;

class ReviewStars extends ProductController
{
    /**
     * Get Reviews Rating
     *
     * @return ResponseInterface|\Magento\Framework\Controller\Result\Json|ResultInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute()
    {
        $product = $this->initProduct();
        $resultJson = $this->resultJsonFactory->create();
        $resultPage = $this->resultPageFactory->create();
        $block = $resultPage->getLayout()
            ->createBlock(\Bss\Simpledetailconfigurable\Block\Product\Reviews::class)
            ->setTemplate('Bss_Simpledetailconfigurable::reviews/summary.phtml')
            ->setProduct($product)
            ->toHtml();

        $resultJson->setData(['reviewsHtml' => $block]);
        return $resultJson;
    }
}
