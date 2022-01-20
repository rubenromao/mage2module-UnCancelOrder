<?php
/**
 * @package RubenRomao_UnCancelOrder
 * @author  Ruben Romao <rubenromao@gmail.com>
 * @created 2021-04-12
 * @copyright Copyright (c) 2020 Ruben Romao.
 */
declare(strict_types=1);

namespace RubenRomao\UnCancelOrder\Controller\Adminhtml\Order;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use RubenRomao\UnCancelOrder\Model\OrderFactory;

class Uncancel extends Action
{
    /**
     * @var OrderFactory
     */
    private $demoFactory;

    /**
     * Initialize Group Controller
     *
     * @param Context $context
     * @param OrderFactory $orderFactory
     */
    public function __construct(
        Context $context,
        OrderFactory $orderFactory
    ) {
        $this->orderFactory = $orderFactory;
        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed(): bool
    {
        return $this->authorization->isAllowed('RubenRomao_UnCancelOrder::uncancel');
    }

    /**
     * Delete action
     *
     * @return Redirect
     */
    public function execute(): Redirect
    {
        // check if we know what should be deleted
        $id = $this->getRequest()->getParam('order_id');

        if ($id) {
            $order = $this->orderFactory->create()->load($id);
            try {
                // Uncancel order
                $order->uncancel();
                // Save order details
                $order->save();
                // display success message
                $this->messageManager->addSuccess(__('You un-canceled the order.'));
            } catch (\Exception $e) {
                // display error message
                $this->messageManager->addError($e->getMessage());
            }
        } else {
            // display error message
            $this->messageManager->addError(__('Unable to un-cancel the order!'));
        }

        // go to order view page
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('sales/order/view', ['order_id' => $id]);
    }
}
