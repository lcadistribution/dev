<?php

/**
 * @package Magedelight_Customerprice for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */

namespace Magedelight\Customerprice\Model\Layer;

class Resolver extends \Magento\Catalog\Model\Layer\Resolver
{

    /**
     * @var \Magedelight\Customerprice\Model\Layer
     */
    protected $layer;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;


    /**
     * Resolver constructor.
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magedelight\Customerprice\Model\Layer $layer
     * @param array $layersPool
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magedelight\Customerprice\Model\Layer $layer,
        \Magento\Framework\App\RequestInterface $request,
        array $layersPool
    ) {
        $this->layer = $layer;
        $this->request = $request;
        parent::__construct($objectManager, $layersPool);
    }

    /**
     * @param string $layerType
     */
    public function create($layerType)
    {
        if (isset($this->layer)) {
            throw new \RuntimeException('Catalog Layer has been already created');
        }
        if (!isset($this->layersPool[$layerType])) {
            throw new \InvalidArgumentException($layerType . ' does not belong to any registered layer');
        }
        $this->layer = $this->objectManager->create($this->layersPool[$layerType]);
    }

    /* Get current Catalog Layer
    *
    * @return \Magento\Catalog\Model\Layer
    */
    public function get()
    {
        if (!isset($this->layer)) {
            $this->layer = $this->objectManager->create($this->layersPool[self::CATALOG_LAYER_CATEGORY]);
        }

        if ($this->request->getActionName() == 'offers' &&
            $this->request->getModuleName() == 'md_customerprice'
        ) {
            $this->layer = $this->objectManager->create($this->layersPool['mdlayer']);
        }

        return $this->layer;
    }
}
