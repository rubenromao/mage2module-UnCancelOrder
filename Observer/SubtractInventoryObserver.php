<?php
/**
 * @package RubenRomao_UnCancelOrder
 * @author  Ruben Romao <rubenromao@gmail.com>
 * @created 2020-04-12
 * @copyright Copyright (c) 2020 Ruben Romao.
 */
declare(strict_types=1);

namespace RubenRomao\UnCancelOrder\Observer;

use Magento\CatalogInventory\Observer\ItemsForReindex;
use Magento\Framework\Event\ObserverInterface;
use Magento\CatalogInventory\Api\StockManagementInterface;
use Magento\CatalogInventory\Model\Indexer\Stock\Processor as StockProcessor;
use Magento\Framework\Event\Observer as EventObserver;
use Psr\Log\LoggerInterface;

class SubtractInventoryObserver implements ObserverInterface
{
    /**
     * @var StockManagementInterface
     */
    private $stockManagement;

    /**
     * @var ItemsForReindex
     */
    private $itemsForReindex;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * SubtractInventoryObserver constructor.
     * @param StockManagementInterface $stockManagement
     * @param StockProcessor $stockIndexerProcessor
     * @param LoggerInterface $logger
     */
    public function __construct(
        StockManagementInterface $stockManagement,
        StockProcessor $stockIndexerProcessor,
        LoggerInterface $logger
    ) {
        $this->stockManagement = $stockManagement;
        $this->stockIndexerProcessor = $stockIndexerProcessor;
        $this->logger = $logger;
    }

    /**
     * Subtract items qtys from stock related with uncancel products.
     *
     * @param EventObserver $observer
     * @return $this
     */
    public function execute(EventObserver $observer)
    {
        $order = $observer->getEvent()->getOrder();
        $productQty = $observer->getEvent()->getProductQty();

        if ($order->getInventoryProcessed()) {
            return $this;
        }

        /**
         * Reindex items
         */
        $itemsForReindex = $this->stockManagement->registerProductsSale(
            $productQty,
            $order->getStore()->getWebsiteId()
        );
        $productIds = [];
        foreach ($itemsForReindex as $item) {
            $item->save();
            $productIds[] = $item->getProductId();
        }
        if (!empty($productIds)) {
            $this->stockIndexerProcessor->reindexList($productIds);
        }

        $order->setInventoryProcessed(true);
        return $this;
    }
}
