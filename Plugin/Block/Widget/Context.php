<?php
/**
 * @package RubenRomao_UnCancelOrder
 * @author  Ruben Romao <rubenromao@gmail.com>
 * @created 2020-04-12
 * @copyright Copyright (c) 2020 Ruben Romao.
 */
declare(strict_types=1);

namespace RubenRomao\UnCancelOrder\Plugin\Block\Widget;

use Magento\Backend\Block\Widget\Button\ButtonList;
use Magento\Backend\Block\Widget\Context as CoreBlockWidgetContext;
use Magento\Framework\App\Action\Context as CoreClass;
use Magento\Framework\Registry;
use Magento\Framework\UrlInterface;

/**
 * Add button to Un-Cancel the Cancelled Order.
 */
class Context
{
    /**
     * @var Registry
     */
    protected $coreRegistry = null;

    /**
     * @var CoreClass
     */
    protected $context = null;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @param Registry $coreRegistry
     * @param CoreClass $context
     * @param UrlInterface $urlBuilder
     */
    public function __construct(
        Registry $coreRegistry,
        CoreClass $context,
        UrlInterface $urlBuilder
    ) {
        $this->coreRegistry = $coreRegistry;
        $this->context = $context;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * @param CoreBlockWidgetContext $subject
     * @param ButtonList $buttonList
     * @return ButtonList
     */
    public function afterGetButtonList(
        CoreBlockWidgetContext $subject,
        ButtonList $buttonList
    ): ButtonList {
        $request = $this->context->getRequest();

        if ($request->getFullActionName() == 'sales_order_view') {
            $order = $this->getOrder();
            if ($order && $order->getState()=='canceled') {
                $message = __('Are you sure you want to un-cancel this order?');
                $buttonList->add(
                    'order_uncancel',
                    [
                        'label' => __('Un-Cancel'),
                        'onclick' => "confirmSetLocation('{$message}', '" . $this->getUnCancelUrl() . "')",
                    ]
                );
            }
        }

        return $buttonList;
    }

    /**
     * @return mixed|null
     */
    public function getOrder(): mixed
    {
        return $this->coreRegistry->registry('sales_order');
    }

    /**
     * @return string
     */
    public function getUnCancelUrl(): string
    {
        return $this->urlBuilder->getUrl('*/*/uncancel', ['order_id'=>$this->getOrder()->getId()]);
    }
}
