<?php
/**
 * Plumrocket Inc.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End-user License Agreement
 * that is available through the world-wide-web at this URL:
 * http://wiki.plumrocket.net/wiki/EULA
 * If you are unable to obtain it through the world-wide-web, please
 * send an email to support@plumrocket.com so we can send you a copy immediately.
 *
 * @package     Plumrocket RMA v2.x.x
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */

namespace Plumrocket\RMA\Setup;

use Magento\Cms\Model\Block;
use Magento\Cms\Model\BlockFactory;
use Magento\Config\Model\ConfigFactory;
use Magento\Customer\Model\Group;
use Magento\Framework\App\State;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Store\Model\System\Store as SystemStore;
use Plumrocket\RMA\Helper\Config as ConfigHelper;
use Plumrocket\RMA\Helper\Data as DataHelper;
use Plumrocket\RMA\Helper\DataFactory as DataHelperFactory;
use Plumrocket\RMA\Model as RmaModel;
use Plumrocket\RMA\Model\Config\Source\Status;

/**
 * @codeCoverageIgnore
 */
class InstallData implements InstallDataInterface
{
    /**
     * Reason Factory
     *
     * @var RmaModel\ReasonFactory
     */
    protected $reasonFactory;

    /**
     * Condition factory
     *
     * @var RmaModel\ConditionFactory
     */
    protected $conditionFactory;

    /**
     * Resolution factory
     *
     * @var RmaModel\ResolutionFactory
     */
    protected $resolutionFactory;

    /**
     * Return rule factory
     *
     * @var RmaModel\ReturnruleFactory
     */
    protected $returnruleFactory;

    /**
     * Return rule factory
     *
     * @var RmaModel\ResponseFactory
     */
    protected $responseFactory;

    /**
     * Cms block factory
     *
     * @var BlockFactory
     */
    protected $blockFactory;

    /**
     * Return rule factory
     *
     * @var SystemStore
     */
    protected $systemStore;

    /**
     * System config
     *
     * @var ConfigFactory
     */
    protected $configFactory;

    /**
     * @var DataHelperFactory
     */
    protected $dataHelperFactory;

    /**
     * @var ConfigHelper
     */
    protected $configHelper;

    /**
     * @param RmaModel\ReasonFactory     $reasonFactory
     * @param RmaModel\ConditionFactory  $conditionFactory
     * @param RmaModel\ResolutionFactory $resolutionFactory
     * @param RmaModel\ReturnruleFactory $returnruleFactory
     * @param RmaModel\ResponseFactory   $responseFactory
     * @param BlockFactory               $blockFactory
     * @param SystemStore                $systemStore
     * @param ConfigFactory              $configFactory
     * @param DataHelperFactory          $dataHelperFactory
     * @param ConfigHelper               $configHelper
     * @param State                      $state
     */
    public function __construct(
        RmaModel\ReasonFactory $reasonFactory,
        RmaModel\ConditionFactory $conditionFactory,
        RmaModel\ResolutionFactory $resolutionFactory,
        RmaModel\ReturnruleFactory $returnruleFactory,
        RmaModel\ResponseFactory $responseFactory,
        BlockFactory $blockFactory,
        SystemStore $systemStore,
        ConfigFactory $configFactory,
        DataHelperFactory $dataHelperFactory,
        ConfigHelper $configHelper,
        State $state
    ) {
        $this->reasonFactory = $reasonFactory;
        $this->conditionFactory = $conditionFactory;
        $this->resolutionFactory = $resolutionFactory;
        $this->returnruleFactory = $returnruleFactory;
        $this->responseFactory = $responseFactory;
        $this->blockFactory = $blockFactory;
        $this->systemStore = $systemStore;
        $this->configFactory = $configFactory;
        $this->dataHelperFactory = $dataHelperFactory;
        $this->configHelper = $configHelper;

        try {
            $state->setAreaCode('adminhtml');
        } catch (\Exception $e) {}
    }

    /**
     * Install Data
     * @param  ModuleDataSetupInterface $setup
     * @param  ModuleContextInterface   $context
     * @return void
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        // Add store address to rma config.
        if (! $this->configHelper->getStoreAddress()
            && $address = $this->dataHelperFactory->create()->getStoreAddress()
        ) {
            $config = $this->configFactory->create();
            $config->setDataByPath(DataHelper::SECTION_ID . '/general/store_address', $address);
            $config->save();
        }

        // Return Reasons.
        $reasons = [
            [
                'title' => 'My item was damaged during shipment',
                'store_id' => 0,
                'position' => 1,
                'payer' => 1,
                'status' => Status::STATUS_ENABLED
            ],
            [
                'title' => 'My item was damaged (not during shipment)',
                'store_id' => 0,
                'position' => 2,
                'payer' => 1,
                'status' => Status::STATUS_ENABLED
            ],
            [
                'title' => 'I received the wrong item',
                'store_id' => 0,
                'position' => 3,
                'payer' => 1,
                'status' => Status::STATUS_ENABLED
            ],
            [
                'title' => 'The item I received is different than the description',
                'store_id' => 0,
                'position' => 4,
                'payer' => 1,
                'status' => Status::STATUS_ENABLED
            ],
            [
                'title' => 'I no longer need/want my item',
                'store_id' => 0,
                'position' => 5,
                'payer' => 1,
                'status' => Status::STATUS_ENABLED
            ],
            [
                'title' => "My item has a manufacturer's defect",
                'store_id' => 0,
                'position' => 6,
                'payer' => 1,
                'status' => Status::STATUS_ENABLED
            ],
            [
                'title' => "Other",
                'store_id' => 0,
                'position' => 7,
                'payer' => 1,
                'status' => Status::STATUS_ENABLED
            ]
        ];

        foreach ($reasons as $reason) {
            $this->reasonFactory->create()
                ->setData($reason)
                ->save();
        }

        // Item Conditions.
        $conditions = [
            [
                'title' => 'Unopened',
                'store_id' => 0,
                'position' => 1,
                'status' => Status::STATUS_ENABLED,
            ],
            [
                'title' => 'Opened',
                'store_id' => 0,
                'position' => 2,
                'status' => Status::STATUS_ENABLED,
            ],
            [
                'title' => 'Damaged',
                'store_id' => 0,
                'position' => 3,
                'status' => Status::STATUS_ENABLED,
            ],
        ];

        foreach ($conditions as $condition) {
            $this->conditionFactory->create()
                ->setData($condition)
                ->save();
        }

        // Resolutions.
        $resolutions = [
            [
                'title' => 'Exchange',
                'store_id' => 0,
                'position' => 1,
                'status' => Status::STATUS_ENABLED,
            ],
            [
                'title' => 'Return',
                'store_id' => 0,
                'position' => 2,
                'status' => Status::STATUS_ENABLED,
            ],
            [
                'title' => 'Repair',
                'store_id' => 0,
                'position' => 3,
                'status' => Status::STATUS_ENABLED,
            ],
            [
                'title' => 'Store Credit',
                'store_id' => 0,
                'position' => 4,
                'status' => Status::STATUS_DISABLED,
            ],
        ];

        foreach ($resolutions as $resolution) {
            $this->resolutionFactory->create()
                ->setData($resolution)
                ->save();
        }

        // Return Rules.
        $websiteId = key($this->systemStore->getWebsiteOptionHash());
        $returnRules = [
            [
                'title' => 'Default',
                'status' => Status::STATUS_ENABLED,
                'website_id' => $websiteId,
                'customer_group_id' => join(',', [
                    Group::NOT_LOGGED_IN_ID, 1, 2, 3
                ]),
                'priority' => 1,
                'resolution' => json_encode([
                    1 => '60',
                    2 => '90',
                    3 => '0',
                    4 => '0',
                ])
            ],
            [
                'title' => 'No returns',
                'status' => Status::STATUS_DISABLED,
                'website_id' => $websiteId,
                'customer_group_id' => Group::NOT_LOGGED_IN_ID,
                'priority' => 0,
                'resolution' => json_encode([
                    1 => '0',
                    2 => '0',
                    3 => '0',
                    4 => '0',
                ])
            ],
        ];

        foreach ($returnRules as $returnRule) {
            $this->returnruleFactory->create()
                ->setData($returnRule)
                ->save();
        }

        // Quick Response Templates.
        $responseTemplates = [
            [
                'title' => 'Thank you for request',
                'status' => Status::STATUS_ENABLED,
                'store_id' => 0,
                'message' => '<p>Dear Customer,</p>
<p>Your RMA request has been received and is being reviewed by our customer service team. <br />We will get back to you shortly.</p>
<p>Best Regards, <br />Store Customer Service Team</p>',
            ],
            [
                'title' => 'Return Delivery Instruction',
                'status' => Status::STATUS_ENABLED,
                'store_id' => 0,
                'message' => '<p>Dear Customer,</p>
<p>Your return (<strong>RMA Number: XXX</strong>) was successfully delivered to the returns warehouse on <strong>XX/XX/201X</strong>. Once the returned package is opened, we will notify you via email to inform you of the contents received. Please allow 3-5 business days. Due to item availability, some repair/replacement returns may be delayed or refunded. If you have any questions or need further assistance, please visit our Customer Service Contact Us Page.</p>
<p>Best Regards,</p>
<p>Store Customer Service Team</p>',
            ],
            [
                'title' => 'RMA has been processed',
                'status' => Status::STATUS_ENABLED,
                'store_id' => 0,
                'message' => '<p>Dear Customer,</p>
<p>Your return (<strong>RMA Number: ХХХ</strong>) has been processed, which means that the items have been verified and your return has been approved.</p>
<p>If your return was processed for a refund, you should receive your refund to your original form of payment within 3 - 5 business days.</p>
<p>If your return was processed for a replacement, a replacement order will be processed and set up to be shipped within the next 1-2 business days.</p>
<p>Please let us know, if you need any assitance.</p>
<p>Best Regards, <br />Store Customer Service Team</p>',
            ],
        ];

        foreach ($responseTemplates as $template) {
            $this->responseFactory->create()
                ->setData($template)
                ->save();
        }

        /**
         * CMS Blocks
         */

        // Return Policy.
        $block = $this->blockFactory->create();
        $blockIdentifier = 'prrma_policy';
        $block->setStoreId(0)->load($blockIdentifier);

        if (! $block->getId()) {
            $content = <<<TEXT
<p>Items purchased at Yourdomain.com may be returned either to a store or by mail, unless stated otherwise in the list of exceptions below</p>
<p style="color: #ff0000;">Items must be returned in the original manufacturer's packaging. We highly recommend you keep your packaging for at least the first 90 days after purchase.</p>
<p>Items purchased from a Marketplace retailer cannot be returned to Yourdomain.com; they must be returned to their Marketplace Retailer in accordance with their returns policy. Please email the Marketplace retailer directly.</p>
<p><span style="font-size: 1.5em;"><br /></span></p>
<p><span style="font-size: 1.5em;">Returns Policy by Department</span></p>
<p></p>
<h3>Clothing, Shoes and Accessories</h3>
<h4>Must be returned within 90 days</h4>
<ul>
<li>Items purchased at Yourcompany.com can be refunded with a receipt or exchanged within 90 days of purchase.</li>
</ul>
<p></p>
<h3>Sports and Fitness</h3>
<h4>Must be returned within 90 days unless listed below</h4>
<p>Oversized table games and treadmills may be returned to a Yourcompany store or by freight shipping. Under some circumstances, you may be charged for return shipping.</p>
<p>To return an item by freight, please contact Customer Care for assistance. They will also be able to inform you of any return shipping costs.</p>
<p>Autographed sports memorabilia must be returned with the included Certificate of Authenticity.</p>
<p>Swimming pools must be returned within 90 days of receipt.</p>
<p></p>
<h3>Electronics</h3>
<h4>Must be returned within 90 days - with these exceptions:</h4>
<p>The <b>following electronics</b> items must be returned within <b>15 days of receipt</b>:</p>
<ul>
<li>Computers</li>
<li>
<ul>
<li>Computer hardware</li>
<li>Printers (including 3D printers)</li>
<li>3D printing supplies and products</li>
</ul>
</li>
<li>Camcorders</li>
<li>Digital cameras</li>
<li>GPS units</li>
<li>Digital music players</li>
<li>Tablets</li>
<li>E-readers</li>
<li>Portable video players</li>
<li>Drones</li>
</ul>
<h4>Must be returned within 90 days:</h4>
<ul>
<li>Televisions</li>
<li>Computer software must be returned unopened</li>
<li>There are no returns or refunds on prepaid cellular phone cards and electronically fulfilled PINs or minutes.</li>
</ul>
<h4>Must be returned within 14 days and 15 days:</h4>
<ul>
<li>Post-paid cell phones must be returned within 14 days of receipt.</li>
<li>Pre-paid cell phones must be returned within 15 days of receipt.</li>
<li>There are no returns or refunds on prepaid cellular phone cards and electronically fulfilled PINs or minutes.</li>
</ul>
<h4>Not available for return:</h4>
<ul>
<li>No returns for software delivered by email.</li>
</ul>
<p></p>
<h3>Books, Movies, Music and Video Games</h3>
<h4>Must be returned within 90 days if unused, unopened and unmarked</h4>
<ul>
<li>Books must be returned unused and unmarked.</li>
<li>CDs, DVDs, Blu-ray discs, audiotapes, videotapes and video games must be returned unopened. If the item is defective, it can be returned within 90 days with a receipt and the original packaging. Defective items may be exchanged for the same title.</li>
<li>Video on Demand cannot be returned. All sales are final and all charges from those sales are nonrefundable</li>
<li>Video game software, if defective, can be returned within 90 days with a receipt and the original packaging. Defective items may be exchanged for a different title if the same title is not available.</li>
</ul>
<h4>Must be returned within 15 days</h4>
<ul>
<li>Pre-owned (refurbished) video game hardware must be returned within 15 days of receipt</li>
</ul>
<h2>Refund timelines</h2>
<p>Once we accept and process your return, please allow about one to two business days for a merchandise Credit to appear in your Your Store account. If you chose to make your return for a refund, you will see the amount reflected on your original form of payment within seven business days.</p>
<p><span style="font-size: large; background-color: #ffff00; color: #ff0000;"><strong>Please note this is an example of RMA Policy! It should be changed before you start using the RMA functionality!</strong></span></p>
TEXT;

            $blockData = [
                Block::IDENTIFIER   => $blockIdentifier,
                Block::TITLE        => 'RMA Return Policy',
                Block::CONTENT      => $content,
                Block::IS_ACTIVE    => true,
                'stores' => [0],
            ];

            $block->setData($blockData)->save();
        }

        // Success page.
        $block = $this->blockFactory->create();
        $blockIdentifier = 'prrma_success_page';
        $block->setStoreId(0)->load($blockIdentifier);

        if (! $block->getId()) {
            $content = '<p>Your return has been received and is being processed. Our team will contact you shortly.</p>';

            $blockData = [
                Block::IDENTIFIER   => $blockIdentifier,
                Block::TITLE        => 'RMA Success Page',
                Block::CONTENT      => $content,
                Block::IS_ACTIVE    => true,
                'stores' => [0],
            ];

            $block->setData($blockData)->save();
        }

        // Return Instructions.
        $block = $this->blockFactory->create();
        $blockIdentifier = 'prrma_instructions';
        $block->setStoreId(0)->load($blockIdentifier);

        if (! $block->getId()) {
            $content = <<<TEXT
<div style="background: #fff9e5; border: 1px solid #ded4b2; border-radius: 5px; padding: 8px;">
<h3>Congratulations! Your Return Request is Approved</h3>
<p><strong>If you wish to return an item to yourdomain.com, please follow the instructions below:</strong></p>
<img style="border: 1px solid #e4d6a8; border-radius: 5px; max-width: 100%;" src="{{view url="Plumrocket_RMA::images/instructions_icons.jpg"}}" alt="" />
<p>1. Print the packing slip and shipping label simply by clicking the buttons below. <br />{{block class="Plumrocket\RMA\Block\Returns\Buttons" name="prrma-instructions-buttons" template="Plumrocket_RMA::returns/instructions/buttons.phtml"}}</p>
<p>2. Pack the item(s) securely in the original product packaging, if possible. All items must be returned in good condition to ensure that you receive credit. Before sending your return shipment, please remove all extra labels from the outside of the package. Now add the printed packing slip into your package.</p>
<p>3. Attach the printed shipping label on your package.</p>
<p>4. The package should be shipped pre-paid through a traceable method like UPS or Insured Parcel Post. Please note: Shipping and Handling costs, gift box costs and other charges are non-refundable.</p>
</div>
TEXT;

            $blockData = [
                Block::IDENTIFIER   => $blockIdentifier,
                Block::TITLE        => 'RMA Return Instuctions',
                Block::CONTENT      => $content,
                Block::IS_ACTIVE    => true,
                'stores' => [0],
            ];

            $block->setData($blockData)->save();
        }
    }
}
