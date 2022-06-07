<?php
namespace Smarttshipping\Shipping\Block\Adminhtml\Order;

/**
 * Class ModalBox
 *
 * @package Smarttshipping\Shipping\Block\Adminhtml\Order
 */
class ModalBox extends \Magento\Backend\Block\Template
{

    protected $source;
    /**
     * Constructor
     *
     * @param \Magento\Backend\Block\Template\Context  $context
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Smarttshipping\Shipping\Model\Config\Source\Options $source,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->source = $source;
    }

    public function getFormUrl()
    {
        return $this->getUrl('getrates/order/order');
    }

    public function getSaveFormUrl()
    {
        return $this->getUrl('saverates/order/saveRequote');
    }

    public function getCurrentOrderId()
    {
        $orderId = false;
        if ($this->hasData('order')) {
            $orderId = $this->getOrder()->getId();
        }

        return $orderId;
    }

    public function getPackageId()
    {

        $getPackageId = $this->source->getAllOptions();
        if ($getPackageId) {
            return $getPackageId;
        }
        return false;
    }
}
