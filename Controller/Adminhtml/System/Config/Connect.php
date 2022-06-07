<?php

namespace Smarttshipping\Shipping\Controller\Adminhtml\System\Config;

use Magento\Catalog\Model\Product\Visibility;
use Magento\Framework\Controller\ResultFactory;
use Smarttshipping\Shipping\Helper\Data;

class Connect extends \Magento\Backend\App\Action
{
    
    protected $_api;
    protected $_helper;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Smarttshipping\Shipping\Model\Api\Smartapi $api
    ) {
        $this->_api = $api;
        parent::__construct($context);
    }

    public function execute()
    {
         $result = [];
        try {
            $connection = $this->_api->getSyncAddress();
           
            if (isset($connection['errors'])) {
                $data = $connection['errors'];
                $detail = isset($data[0]['detail'])?$data[0]['detail']:'';
                $result = ['error'=>$data[0]['title'].', '.$detail];
            } else {
                $result = ['success'=>1];
            }
        } catch (\Exception $e) {
                $result = ['error'=>$e->getMessage()];
        }
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($connection);
        return $resultJson;
    }
}
