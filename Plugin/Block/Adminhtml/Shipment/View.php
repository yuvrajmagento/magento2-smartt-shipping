<?php
namespace Smarttshipping\Shipping\Plugin\Block\Adminhtml\Shipment;

class View
{
    public function __construct(
        \Magento\Backend\Model\UrlInterface $backendUrl,
        \Smarttshipping\Shipping\Helper\Data $helperData
    ) {
        $this->_backendUrl = $backendUrl;
        $this->_helperData = $helperData;
    }

    public function beforePushButtons(
        \Magento\Backend\Block\Widget\Button\Toolbar\Interceptor $subject,
        \Magento\Framework\View\Element\AbstractBlock $context,
        \Magento\Backend\Block\Widget\Button\ButtonList $buttonList
    ) {
        $this->_request = $context->getRequest();
        
        if ($this->_request->getFullActionName() == 'adminhtml_order_shipment_view') {
            $backendurl = $this->_backendUrl->getUrl("smartshipping/createdispatch/index/", ['shipment_id' => $this->_request->getParam('shipment_id')]);
            $returnurl = $this->_backendUrl->getUrl("smartshipping/createdispatch/returnshipment/", ['shipment_id' => $this->_request->getParam('shipment_id')]);
            $shipment_id = $this->_request->getParam('shipment_id');
            $isSmartShippingOrder = $this->_helperData->isSmartShippingOrder($shipment_id);
            
            if ($shipment_id && $isSmartShippingOrder) {
                $iscancelled = $this->_helperData->getIsCancelled($shipment_id);
                $pdfurl = $this->_helperData->getPdfUrl($shipment_id, 'magento_shipment_id');
                
                if ($pdfurl) {
                    $url = $pdfurl;
                    $buttonList->add(
                        'smart_shipping_pdf',
                        [
                            'label' => __('Get Smart Shipping PDF'),
                            'class' => 'myclass',
                            'onclick' => 'setLocation(\'' . $url . '\')'
                        ]
                    );
                } else {
                    if ($iscancelled != 2) {
                        $buttonList->add(
                            'smart_shipping_dispatch',
                            [
                                'label' => __('Get Smart Shipping Waybill'),
                                'class' => 'myclass',
                                'onclick' => 'setLocation(\'' . $backendurl . '\')'
                            ]
                        );
                    }
                }
            }
        }
    }
}
