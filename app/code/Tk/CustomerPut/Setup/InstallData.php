<?php
namespace Tk\CustomerPut\Setup;
 use Magento\Framework\Module\Setup\Migration;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
class InstallData implements InstallDataInterface
{
    /**
     * Customer setup factory
     *
     * @var \Magento\Customer\Setup\CustomerSetupFactory
     */
    private $customerSetupFactory;
    /**
     * Init
     *
     * @param \Magento\Customer\Setup\CustomerSetupFactory $customerSetupFactory
     */
    public function __construct(\Magento\Customer\Setup\CustomerSetupFactory $customerSetupFactory)
    {
        $this->customerSetupFactory = $customerSetupFactory;
    }
    /**
     * Installs DB schema for a module
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
 
        $installer = $setup;
        $installer->startSetup();
        $customerSetup = $this->customerSetupFactory->create(['setup' => $setup]);
        $entityTypeId = $customerSetup->getEntityTypeId(\Magento\Customer\Model\Customer::ENTITY);
        
        $attributesInfo = [
            'notification_phone_number' => [
                'label' => 'Phone Number for Notifications',
                'type' => 'static',
                'input' => 'text',
                'position' => 140,
                'visible' => true,
                'required' => false,
                "note" => "Which phone number do you want leads to be able to reach you on?",
            ],
            'call_capture_area_code' => [
                'label' => 'Desired Area Code for Call Capture Number',
                'type' => 'static',
                'input' => 'text',
                'visible' => true,
                'required' => false,
            ],
            'market_area' => [
                'label' => 'Your Market Area',
                'type' => 'static',
                'input' => 'text',
                'visible' => true,
                'required' => false,
            ],
            'brokerage_name' => [
                'label' => 'Brokerage Name',
                'type' => 'static',
                'input' => 'text',
                'visible' => true,
                'required' => false,
            ],
            'featured_as_author' => [
                'label' => 'Would you like to be featured as an Author?',
                'visible' => true,
                'required' => false,
                'type' => 'static',
                'input' => 'select',
            ],
            'brokerage_info_required' => [
                'label' => 'Are you required to have your brokerage information displayed on your books?',
                'visible' => true,
                'required' => false,
                'type' => 'static',
                'input' => 'select',
            ],
        ];

        foreach ($attributesInfo as $attributeCode => $attributeParams) {
            $customerSetup->addAttribute(\Magento\Customer\Model\Customer::ENTITY, $attributeCode, $attributeParams);
            
            $vatIdAttribute = $customerSetup->getEavConfig()->getAttribute(\Magento\Customer\Model\Customer::ENTITY, $attributeCode);
            $used_in_forms[]="adminhtml_customer";
            $used_in_forms[]="checkout_register";
            $used_in_forms[]="customer_account_create";
            $used_in_forms[]="customer_account_edit";
            $used_in_forms[]="adminhtml_checkout";
            $vatIdAttribute->setData("used_in_forms", $used_in_forms)
                ->setData("is_used_for_customer_segment", true)
                ->setData("is_system", 0)
                ->setData("is_user_defined", 1)
                ->setData("is_visible", 1);
            $vatIdAttribute->save();
        }
        $installer->endSetup();
    }
}