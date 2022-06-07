<?php
namespace Smarttshipping\Shipping\Block\Adminhtml;

class Shipping extends \Magento\Backend\Block\Template
{
    protected $storeManager;
  
    protected $shippingConfig;
 
    protected $scopeConfig;
          
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Shipping\Model\Config $shippingConfig,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Backend\Model\UrlInterface $backendUrl,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->storeManager = $storeManager;
        $this->shippingConfig = $shippingConfig;
        $this->scopeConfig = $scopeConfig;
        $this->_coreRegistry = $registry;
        $this->_backendUrl = $backendUrl;
        parent::__construct($context, $data);
    }
      
    public function getAllCarriers()
    {
        $allCarriers = $this->shippingConfig->getAllCarriers($this->storeManager->getStore());
 
        $shippingMethodsArray = [];
        foreach ($allCarriers as $shippigCode => $shippingModel) {
            $shippingTitle = $this->scopeConfig->getValue('carriers/'.$shippigCode.'/title');
            $shippingMethodsArray[$shippigCode] = [
                'label' => $shippingTitle,
                'value' => $shippigCode
            ];
        }
        return $shippingMethodsArray;
    }
  
    public function getActiveCarriers()
    {
        $activeCarriers = $this->shippingConfig->getActiveCarriers($this->storeManager->getStore());
 
        $shippingMethodsArray = [];
        foreach ($activeCarriers as $shippigCode => $shippingModel) {
            $shippingTitle = $this->scopeConfig->getValue('carriers/'.$shippigCode.'/title');
            $shippingMethodsArray[$shippigCode] = [
                'label' => $shippingTitle,
                'value' => $shippigCode
            ];
        }
        return $shippingMethodsArray;
    }

    public function getOrder()
    {
        return $this->_coreRegistry->registry('sales_order');
    }

    public function getFormAction()
    {
        $params = ['order_id'=>$this->getOrder()->getId()];
        return $this->_backendUrl->getUrl("smartshipping/shipping/index/", $params);
    }
}
