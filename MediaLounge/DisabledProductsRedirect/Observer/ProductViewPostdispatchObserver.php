<?php

namespace Magento\MediaLounge\DisabledProductsRedirect\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\CatalogUrlRewrite\Model\CategoryUrlPathGenerator;
use Magento\MediaLounge\DisabledProductsRedirect\Helper\Data;

class ProductViewPostdispatchObserver implements ObserverInterface
{
    /**
     * Tax data
     *
     * @var \Magento\MediaLounge\DisabledProductsRedirect\Helper\Data
     */
    protected $configData;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\CatalogUrlRewrite\Model\CategoryUrlPathGenerator
     */
    protected $categoryPath;

    /**
     * @param \Magento\MediaLounge\DisabledProductsRedirect\Helper\Data $configData
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        Data $configData,
        \Magento\Framework\Registry $registry,
        CategoryUrlPathGenerator $categoryPath
    ) {
        $this->configData = $configData;
        $this->registry = $registry;
        $this->categoryPath = $categoryPath;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $enabledModule = (int) $this->configData->getGeneralConfig('enable');
        if (!$enabledModule) {
            return $this;
        }

        $event = $observer->getEvent();

        $product = $this->registry->registry('current_product');

        if (!$product) {
            return $this;
        }

        if($product->getStatus() != Status::STATUS_ENABLED){
            $categoryIds = $product->getCategoryIds();
            $lastCategory = end($categoryIds);

            $redirectUrl = $this->categoryUrlPathGenerator->getUrlPath($lastCategory);

            $event->_response->setRedirect($redirectUrl, 301);
        }

        return $this;
    }
}
