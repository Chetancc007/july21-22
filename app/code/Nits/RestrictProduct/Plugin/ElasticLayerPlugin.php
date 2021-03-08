<?php
namespace Nits\RestrictProduct\Plugin;

use Magento\Elasticsearch6\Model\Client\Elasticsearch;

class ElasticLayerPlugin
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     */
    public function __construct(
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
    ) {
        $this->customerSession = $customerSession;
        $this->productCollectionFactory = $productCollectionFactory;
    }

    public function beforeQuery($subject,$query) {


        $filteredIds = $this->productCollectionFactory->create()
            ->addAttributeToSelect('*')
            ->addAttributeToFilter('status', ['eq' => 1])
            ->getAllIds();


        if(!$filteredIds || count($filteredIds) < 1)  {

            return [$query];
        }

        $query['body']['query']['bool']['filter'] = ['ids' => [ 'values' => $filteredIds]];


        return [$query];

    }
}
