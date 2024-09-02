<?php
/**
 * @package Magedelight_Customerprice for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */

namespace Magedelight\Customerprice\Model\Config\Backend;

use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Value;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Module\ResourceInterface;
use Magento\Framework\Registry;

/**
 * Backend system config
 * Render current extension version
 */
class Version extends Value
{
    const MODULE = 'Magedelight_Customerprice';

    /**
     * @var ResourceInterface
     */
    private $moduleResource;
    /**
     * @var Context
     */
    private $context;
    /**
     * @var Registry
     */
    private $registry;
    /**
     * @var ScopeConfigInterface
     */
    private $config;
    /**
     * @var AbstractResource|null
     */
    private $resource;
    /**
     * @var AbstractDb|null
     */
    private $resourceCollection;
    /**
     * @var array
     */
    private $data;

    /**
     * Version constructor.
     * @param Context $context
     * @param Registry $registry
     * @param ScopeConfigInterface $config
     * @param TypeListInterface $cacheTypeList
     * @param ResourceInterface $moduleResource
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        ResourceInterface $moduleResource,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->moduleResource = $moduleResource;
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
        $this->context = $context;
        $this->registry = $registry;
        $this->config = $config;
        $this->cacheTypeList = $cacheTypeList;
        $this->resource = $resource;
        $this->resourceCollection = $resourceCollection;
        $this->data = $data;
    }

    /**
     * Inject current installed module version as the config value.
     *
     * @return void
     */
    public function afterLoad()
    {
        $version = $this->moduleResource->getDbVersion(self::MODULE);
        $this->setValue($version);
    }
}
