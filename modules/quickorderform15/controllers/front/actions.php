<?php

class QuickOrderForm15ActionsModuleFrontController extends ModuleFrontController
{

	public $id_product;

	public function init()
	{
		parent::init();

		$this->id_product = (int) Tools::getValue('id_product');
		$this->id_product_attribute = (int) Tools::getValue('id_product_attribute');
	}

	public function postProcess()
	{
		if (Tools::getValue('process') == 'getDecli')
			$this->processGetDecli();
		else if (Tools::getValue('process') == 'getMin')
			$this->processGetMin();
		exit;
	}

	/**
	 * Get product declinaisons
	 */
	public function processGetDecli()
	{
		// check if product exists
		$monproduct = new Product($this->id_product, true, (int) Context::getContext()->cookie->id_lang);
		if (!Validate::isLoadedObject($monproduct))
			echo false;

		$attributesGroups = $monproduct->getAttributesGroups((int) Context::getContext()->cookie->id_lang);

		if (is_array($attributesGroups) AND $attributesGroups)
		{
			foreach ($attributesGroups AS $k => $row)
			{
				$combinations[$row['id_product_attribute']]['attributes_values'][$row['id_attribute_group']] = $row['attribute_name'];
				$combinations[$row['id_product_attribute']]['minimal_quantity'] = $row['minimal_quantity'];
				$combinations[$row['id_product_attribute']]['default_on'] = ($row['default_on'] > 0) ? 1 : 0;
			}
		}
		else
		{
			echo "ProdSansDecli##" . $monproduct->minimal_quantity;
			exit;
		}

		$return = '';
		foreach ($combinations as $kCombination => $vCombination)
		{
			$mastring = $kCombination . '#';
			foreach ($vCombination["attributes_values"] as $composant)
			{
				$mastring .= $composant . ' - ';
			}
			$mastring = rtrim($mastring, ' - ');
			$mastring .= '#' . $vCombination["minimal_quantity"];
			$mastring .= '#' . $vCombination["default_on"];
			$return .= $mastring . "##";
		}
		$return = rtrim($return, '##');

		echo ( $return );
	}

	/**
	 * Get Minimal Quantity
	 */
	public function processGetMin()
	{
		$monproduct = new Product($this->id_product, true, (int) Context::getContext()->cookie->id_lang);

		// check if product exists
		if (!Validate::isLoadedObject($monproduct))
			echo false;

		if (Attribute::getAttributeMinimalQty($this->id_product_attribute) > 1)
			echo Attribute::getAttributeMinimalQty($this->id_product_attribute);
		else
			echo 1;
	}

}
