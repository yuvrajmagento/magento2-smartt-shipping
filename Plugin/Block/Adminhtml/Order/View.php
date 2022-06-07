<?php
namespace Smarttshipping\Shipping\Plugin\Block\Adminhtml\Order;

use Magento\Framework\View\Element\Template;

class View extends Template
{

    /**
     * @var \Smarttshipping\Shipping\Block\Adminhtml\Shipping
     */
    protected $shipping;

    public function __construct(
        Template\Context $context,
        \Smarttshipping\Shipping\Block\Adminhtml\Shipping $shipping,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->shipping = $shipping;
    }


    public function beforeSetLayout(
        \Magento\Sales\Block\Adminhtml\Order\View $subject,
        $layout
    ) {
        $getOrder = $this->shipping->getOrder();

        $getMethod = substr($getOrder->getShippingMethod(), 0, 5);

        $shipmentCollection = $getOrder->getShipmentsCollection();
        $shipmentId = 0;
        
        foreach ($shipmentCollection as $shipment) {
            $shipmentId = $shipment->getId();
        }
        
        if ($shipmentId == 0) {
            //if ($getMethod == 'smart') {
                $subject->addButton(
                    'sendordersms',
                    [
                        'label' => __('Smartt Shipping Requote'),
                        'onclick' => "",
                        'class' => 'action-default action-quote-order',
                    ]
                );
                return [$layout];
            //}
        }
    }

    public function afterToHtml(
        \Magento\Sales\Block\Adminhtml\Order\View $subject,
        $result
    ) {

        if ($subject->getNameInLayout() == 'sales_order_edit') {
            $customBlockHtml = $subject->getLayout()->createBlock(
                \Smarttshipping\Shipping\Block\Adminhtml\Order\ModalBox::class,
                $subject->getNameInLayout().'_modal_box'
            )->setOrder($subject->getOrder())
                ->setTemplate('Smart_Shipping::order/modalbox.phtml')
                ->toHtml();
            return $result.$customBlockHtml;
        }
        return $result;
    }
}
