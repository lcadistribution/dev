<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Multiple Wishlist for Magento 2
 */

namespace Amasty\MWishlist\Model\Action;

use Amasty\MWishlist\Model\ConfigProvider;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Escaper;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Message\ManagerInterface as MessageManager;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\DesignLoader;
use Psr\Log\LoggerInterface;

class Context
{
    /**
     * @var Validator
     */
    private $formKeyValidator;

    /**
     * @var Escaper
     */
    private $escaper;

    /**
     * @var ResultFactory
     */
    private $resultFactory;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var DesignLoader
     */
    private $designLoader;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var MessageManager
     */
    private $messageManager;

    /**
     * @var ManagerInterface
     */
    private $eventManager;

    public function __construct(
        ConfigProvider $configProvider,
        Validator $formKeyValidator,
        Escaper $escaper,
        ResultFactory $resultFactory,
        RequestInterface $request,
        DesignLoader $designLoader,
        UrlInterface $urlBuilder,
        MessageManager $messageManager,
        ManagerInterface $eventManager,
        LoggerInterface $logger
    ) {
        $this->formKeyValidator = $formKeyValidator;
        $this->escaper = $escaper;
        $this->resultFactory = $resultFactory;
        $this->request = $request;
        $this->designLoader = $designLoader;
        $this->logger = $logger;
        $this->urlBuilder = $urlBuilder;
        $this->configProvider = $configProvider;
        $this->messageManager = $messageManager;
        $this->eventManager = $eventManager;
    }

    /**
     * @return RequestInterface
     */
    public function getRequest(): RequestInterface
    {
        return $this->request;
    }

    /**
     * @return ResultFactory
     */
    public function getResultFactory(): ResultFactory
    {
        return $this->resultFactory;
    }

    /**
     * @return Escaper
     */
    public function getEscaper(): Escaper
    {
        return $this->escaper;
    }

    /**
     * @return Validator
     */
    public function getFormKeyValidator(): Validator
    {
        return $this->formKeyValidator;
    }

    /**
     * @return DesignLoader
     */
    public function getDesignLoader(): DesignLoader
    {
        return $this->designLoader;
    }

    /**
     * @return LoggerInterface
     */
    public function getLogger(): LoggerInterface
    {
        return $this->logger;
    }

    /**
     * @return UrlInterface
     */
    public function getUrlBuilder(): UrlInterface
    {
        return $this->urlBuilder;
    }

    /**
     * @return ConfigProvider
     */
    public function getConfigProvider(): ConfigProvider
    {
        return $this->configProvider;
    }

    /**
     * @return MessageManager
     */
    public function getMessageManager(): MessageManager
    {
        return $this->messageManager;
    }

    /**
     * @return ManagerInterface
     */
    public function getEventManager(): ManagerInterface
    {
        return $this->eventManager;
    }
}
