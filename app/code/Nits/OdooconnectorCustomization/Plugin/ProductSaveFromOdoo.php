<?php

namespace Nits\OdooconnectorCustomization\Plugin;

class ProductSaveFromOdoo
{

	public function beforeSaveProduct(\Webkul\Odoomagentoconnect\Model\MobRepository $subject, $product, $saveOptions = false)
	{
		$product->setStockData(['is_in_stock' => true]);
		return [$product, $saveOptions];
	}

}