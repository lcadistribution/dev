<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Multiple Wishlist for Magento 2
 */

namespace Amasty\MWishlist\Controller;

use Amasty\MWishlist\Model\Action\Context;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\LayoutInterface;

abstract class UpdateAction implements ActionInterface, AbstractIndexInterface
{
    private const BLOCK_HANDLES = [
        'customer.wishlist' => ['mwishlist_wishlist_index'],
        'mwishlist.list.contrainer' => ['mwishlist_index_index']
    ];

    public const BLOCK_PARAM = 'block';
    public const COMPONENT_PARAM = 'component';

    /**
     * @var LayoutInterface
     */
    private $layout;

    /**
     * @var Context
     */
    private $context;

    public function __construct(
        Context $context
    ) {
        $this->context = $context;
    }

    /**
     * Return data for json answer.
     * @return array
     */
    abstract protected function action(): array;

    /**
     * @return ResultInterface
     */
    public function execute()
    {
        if (!$this->getContext()->getFormKeyValidator()->validate($this->getContext()->getRequest())) {
            return $this->generateJsonAnswer(['error' => 'Something wrong']);
        }

        $data = $this->action();

        if ($blockName = $this->getContext()->getRequest()->getParam(self::BLOCK_PARAM)) {
            if ($blockToUpdate = $this->getLayout($this->getHandles($blockName))->getBlock($blockName)) {
                $data['blocks'][$blockName] = $blockToUpdate->toHtml();
            }
        }

        return $this->generateJsonAnswer($data);
    }

    /**
     * @param array $data
     * @return Json
     */
    public function generateJsonAnswer(array $data): Json
    {
        /** @var Json $resultJson */
        $resultJson = $this->getContext()->getResultFactory()->create(ResultFactory::TYPE_JSON);
        return $resultJson->setData($data);
    }

    /**
     * @param string $blockName
     * @return string[]
     */
    private function getHandles(string $blockName): array
    {
        return self::BLOCK_HANDLES[$blockName] ?? [];
    }

    /**
     * @param array $handles
     * @return LayoutInterface
     */
    private function getLayout($handles = []): LayoutInterface
    {
        if ($this->layout === null) {
            $this->getContext()->getDesignLoader()->load();
            $page = $this->getContext()->getResultFactory()
                ->create(ResultFactory::TYPE_PAGE, [false, ['isIsolated' => true]]);
            foreach ($handles as $handle) {
                $page->addHandle($handle);
            }
            $this->layout = $page->getLayout();
        }

        return $this->layout;
    }

    /**
     * @return Context
     */
    public function getContext(): Context
    {
        return $this->context;
    }
}
