<?php
namespace Dtn\AutoChangeCustomerGroup\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;

class OrderSucess implements ObserverInterface {

    const XML_AUTO_CHANGE_CUSTOMER_GROUP_ENABLE = 'dtn_autochangecustomergroup/config/enabled';
    const XML_CUSTOMER_GROUP_FROM = 'dtn_autochangecustomergroup/config/from_group';
    const XML_CUSTOMER_GROUP_TO = 'dtn_autochangecustomergroup/config/to_group';
    
    protected $logger;
    protected $_customer;
    protected $customerModel;
    protected $scopeConfig;
    private $customerSession;

    public function __construct(\Psr\Log\LoggerInterface $loggerInterface,
                                \Magento\Customer\Model\Customer $customerModel,
                                \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
                                \Magento\Customer\Model\Session $customerSession) {
        $this->logger = $loggerInterface;
        $this->customerModel= $customerModel;
        $this->customerSession = $customerSession;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     *
     * @param Observer $observer
     */
    public function execute(Observer $observer) {

        try {
            if ($this->scopeConfig->getValue(self::XML_AUTO_CHANGE_CUSTOMER_GROUP_ENABLE)) {
                    $from = $this->scopeConfig->getValue(self::XML_CUSTOMER_GROUP_FROM);
                    $from_array = array_filter(explode(',', $from));
                    $to = $this->scopeConfig->getValue(self::XML_CUSTOMER_GROUP_TO);
                    $group_current = $this->customerSession->getCustomerGroupId();
                    $customerId = $this->customerSession->getCustomerId();
                    if ($group_current != $to && in_array($group_current,$from_array) && $customerId != null && $to != 0) {
                        $customer = $this->customerModel->load($customerId);
                        $customer->setGroupId($to)->save();
                        $this->customerSession->setCustomerGroupId($to)->save();
                    }
            }
        }catch (\Exception $e){
            return;
        }

    }
}