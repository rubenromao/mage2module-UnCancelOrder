<?php
/**
 * @package RubenRomao_UnCancelOrder
 * @author  Ruben Romao <rubenromao@gmail.com>
 * @created 2020-04-12
 * @copyright Copyright (c) 2020 Ruben Romao.
 */
declare(strict_types=1);

namespace RubenRomao\UnCancelOrder\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Model\Order as CoreOrder;

/**
 * Extending Magento Core Order Model
 * to Add the ability to un-cancel a cancelled order.
 */
class Order extends CoreOrder
{
    /**
     * @param string $comment
     * @param bool $graceful
     *
     * @return $this
     * @throws LocalizedException
     */
    public function uncancel($comment = '', $graceful = true): Order
    {
        if ($this->isCanceled()) {
            $state = self::STATE_PROCESSING;
            $productStockQty = [];
            foreach ($this->getAllVisibleItems() as $item) {
                $productStockQty[$item->getProductId()] = $item->getQtyCanceled();
                foreach ($item->getChildrenItems() as $child) {
                    $productStockQty[$child->getProductId()] = $item->getQtyCanceled();
                    $child->setQtyCanceled(0);
                    $child->setTaxCanceled(0);
                    $child->setDiscountTaxCompensationCanceled(0);
                }
                $item->setQtyCanceled(0);
                $item->setTaxCanceled(0);
                $item->setDiscountTaxCompensationCanceled(0);
                $this->eventManager->dispatch('sales_order_item_uncancel', ['item' => $item]);
            }
            $this->eventManager->dispatch(
                'sales_order_uncancel_inventory',
                [
                    'order' => $this,
                    'product_qty' => $productStockQty
                ]
            );

            $this->setSubtotalCanceled(0);
            $this->setBaseSubtotalCanceled(0);

            $this->setTaxCanceled(0);
            $this->setBaseTaxCanceled(0);

            $this->setShippingCanceled(0);
            $this->setBaseShippingCanceled(0);

            $this->setDiscountCanceled(0);
            $this->setBaseDiscountCanceled(0);

            $this->setTotalCanceled(0);
            $this->setBaseTotalCanceled(0);

            $this->setState($state)
                ->setStatus($this->getConfig()->getStateDefaultStatus($state));
            if (!empty($comment)) {
                $this->addStatusHistoryComment($comment, false);
            }

            $this->eventManager->dispatch('order_uncancel_after', ['order' => $this]);
        } elseif (!$graceful) {
            throw new LocalizedException(__('We cannot un-cancel this order.'));
        }

        return $this;
    }
}
