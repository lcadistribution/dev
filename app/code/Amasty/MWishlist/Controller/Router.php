<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Multiple Wishlist for Magento 2
 */

namespace Amasty\MWishlist\Controller;

use Amasty\MWishlist\Model\ConfigProvider;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\RouterInterface;

/**
 * 'wishlist/index/index' on EE (Magento_MultipleWishlist)
 * - open controller which redirect on first wishlist ignoring wishlist_id
 *
 * Class Router
 */
class Router implements RouterInterface
{
    public const FORWARDS = [
        'wishlist/index/index' => [
            'mwishlist',
            'wishlist',
            'index'
        ]
    ];

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(ConfigProvider $configProvider)
    {
        $this->configProvider = $configProvider;
    }

    /**
     * @param RequestInterface $request
     */
    public function match(RequestInterface $request)
    {
        $requestUrl = $this->retrieveRoute($request);

        if ($this->configProvider->isEnabled()
            && isset(self::FORWARDS[$requestUrl])
        ) {
            $request->setModuleName(self::FORWARDS[$requestUrl][0]);
            $request->setControllerName(self::FORWARDS[$requestUrl][1]);
            $request->setActionName(self::FORWARDS[$requestUrl][2]);
        }
    }

    /**
     * @param RequestInterface $request
     * @return string
     */
    protected function retrieveRoute(RequestInterface $request): string
    {
        $output = [];

        $path = trim($request->getPathInfo(), '/');

        $params = explode('/', $path ?: '');
        while (count($output) < 3) {
            $output[] = array_shift($params) ?: 'index';
        }

        return implode('/', $output);
    }
}
