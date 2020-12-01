<?php

namespace Magecomp\S3Amazon\Model\ResourceModel\Title;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected $_idFieldName = 'title_id';

    public function _construct()
    {
        $this->_init("Magecomp\S3Amazon\Model\Title", "Magecomp\S3Amazon\Model\ResourceModel\Title");
    }
}