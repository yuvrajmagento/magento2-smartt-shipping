<?php
namespace Smarttshipping\Shipping\Plugin;

use Closure;

class QuoteToOrderItem
{
    /**
     * @param \Magento\Quote\Model\Quote\Item\ToOrderItem $subject
     * @param callable $proceed
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $item
     * @param array $additional
     * @return \Magento\Sales\Model\Order\Item
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundConvert(
        \Magento\Quote\Model\Quote\Item\ToOrderItem $subject,
        Closure $proceed,
        \Magento\Quote\Model\Quote\Item\AbstractItem $item,
        $additional = []
    ) {
        /** @var $orderItem \Magento\Sales\Model\Order\Item */
        $orderItem = $proceed($item, $additional);
        $orderItem->setShippingHeight($item->getShippingHeight());
        $orderItem->setShippingLength($item->getShippingLength());
        $orderItem->setShippingWidth($item->getShippingWidth());
        $orderItem->setShippingWeight($item->getShippingWeight());
        $orderItem->setPackageId($item->getPackageId());
        $orderItem->setRecalculateQty($item->getRecalculateQty());
        return $orderItem;
    }
}
