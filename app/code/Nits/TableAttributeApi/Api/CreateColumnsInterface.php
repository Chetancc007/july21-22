<?php 
namespace Nits\TableAttributeApi\Api;
 
interface CreateColumnsInterface
{
    /**
    * Create Columns
    * @param string $attribute_code
    * @param mixed $column_values
    * @return string
    **/
    public function createColumns($attribute_code, $column_values);
}