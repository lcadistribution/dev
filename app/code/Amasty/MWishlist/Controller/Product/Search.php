<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Multiple Wishlist for Magento 2
 */

namespace Amasty\MWishlist\Controller\Product;

use Amasty\MWishlist\Controller\AbstractIndexInterface;
use Amasty\MWishlist\Model\Product\Search as SearchModel;
use Exception;
use InvalidArgumentException;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;

class Search extends Action implements AbstractIndexInterface
{
    /**
     * @var SearchModel
     */
    private $search;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        SearchModel $search,
        Context $context,
        LoggerInterface $logger
    ) {
        $this->search = $search;
        $this->logger = $logger;
        parent::__construct($context);
    }

    /**
     * @return ResultInterface
     */
    public function execute()
    {
        /** @var Json $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        try {
            $resultData = $this->getSearchModel()->search($this->getRequest()->getParam('q', ''));
            $resultJson->setData(['items' => $resultData]);
        } catch (InvalidArgumentException | LocalizedException $e) {
            $resultJson->setData(['error' => $e->getMessage()]);
        } catch (Exception $e) {
            $resultJson->setData(['error' => 'Something is wrong']);
            $this->logger->error($e->getMessage());
        }

        return $resultJson;
    }

    /**
     * @return SearchModel
     */
    private function getSearchModel(): SearchModel
    {
        return $this->search;
    }
}
