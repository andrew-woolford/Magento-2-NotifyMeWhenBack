<?php

namespace FunkySquid\NotifyMe\Block;

use Magento\Framework\View\Element\Template;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\CatalogInventory\Api\StockRegistryInterface;

class NotifyMe extends Template
{
    protected $productRepository;
    protected $stockRegistry;
    protected $configurable;

    public function __construct(
        Template\Context $context,
        ProductRepositoryInterface $productRepository,
        StockRegistryInterface $stockRegistry,
        Configurable $configurable,
        array $data = []
    ) {
        $this->productRepository = $productRepository;
        $this->stockRegistry = $stockRegistry;
        $this->configurable = $configurable;
        parent::__construct($context, $data);
    }

    public function getProduct()
    {
        $productId = $this->getRequest()->getParam('id');
        return $this->productRepository->getById($productId);
    }

    public function isProductAvailable()
    {
        $product = $this->getProduct();
        if ($product->getTypeId() == Configurable::TYPE_CODE) {
            $children = $this->configurable->getUsedProducts($product);
            foreach ($children as $child) {
                $stockItem = $this->stockRegistry->getStockItem($child->getId());
                if ($stockItem->getIsInStock()) {
                    return true;
                }
            }
            return false;
        } else {
            return $product->isAvailable();
        }
    }
}
