<?php
	
class Devisclass extends ObjectModel
{
	public 		$id_devis;
	public 		$id_carrier;
	public 		$id_lang;
	public 		$id_currency;
	public 		$id_customer;
	public 		$id_address_delivery;
	public 		$id_address_invoice;
	public 		$id_cart;
	public 		$id_order;
	public 		$id_statut;
	public 		$id_tax;
	public 		$poids_devis;
	public 		$total_devis;
	public 		$total_out_shipp;
	public 		$total_shipping;
	public 		$free_shipp = 1;
	public 		$date_demande;
	public 		$date_reponse;
	public 		$date_expiration;
	public 		$demande;
	public 		$reponse;
	public 		$desc_invoice;
	
	protected static $fieldsRequiredDatabase = array(
												'id_address_invoice', 
												'id_address_delivery', 
												'id_carrier', 
												'demande'
												);
	
	public static $definition = array(
		'table' 	=> 'devis',
		'primary' 	=> 'id_devis',
		'multilang' => false,
		'fields' 	=> array(
			'id_devis' 	=> array(
				'type' 		=> ObjectModel :: TYPE_INT
			),
			'id_carrier'=> array(
				'type' 		=> ObjectModel :: TYPE_INT,
				'required' 	=> true,
				'validate' 	=> 'isUnsignedId'
			),
			'id_lang' 	=> array(
				'type' 		=> ObjectModel :: TYPE_INT,
				'validate' 	=> 'isUnsignedId'
			),
			'id_currency'=> array(
				'type' 		=> ObjectModel :: TYPE_INT,
				'validate' 	=> 'isUnsignedId'
			),
			'id_customer'=> array(
				'type' 		=> ObjectModel :: TYPE_INT,
				'required' 	=> true,
				'validate' 	=> 'isUnsignedId'
			),
			'id_address_delivery' => array(
				'type' 		=> ObjectModel :: TYPE_INT,
				'required' 	=> true,
				'validate' 	=> 'isUnsignedId'
			),
			'id_address_invoice' => array(
				'type' 		=> ObjectModel :: TYPE_INT,
				'required' 	=> true,
				'validate' 	=> 'isUnsignedId'
			),
			'id_cart' 	=> array(
				'type'		=> ObjectModel :: TYPE_INT,
				'validate' 	=> 'isUnsignedId'
			),
			'id_order' 	=> array(
				'type' 		=> ObjectModel :: TYPE_INT,
				'validate' 	=> 'isUnsignedId'
			),
			'id_statut' => array(
				'type' 		=> ObjectModel :: TYPE_INT,
				'validate' 	=> 'isUnsignedId'
			),
			'id_tax' 	=> array(
				'type' 		=> ObjectModel :: TYPE_INT,
				'validate' 	=> 'isUnsignedId'
			),
			'poids_devis' => array(
				'type' 		=> ObjectModel :: TYPE_FLOAT,
				'validate' 	=> 'isFloat'
			),
			'total_devis' => array(
				'type' 		=> ObjectModel :: TYPE_FLOAT,
				'validate' 	=> 'isPrice'
			),
			'total_out_shipp' => array(
				'type' 		=> ObjectModel :: TYPE_FLOAT,
				'validate' 	=> 'isPrice'
			),
			'total_shipping' => array(
				'type' 		=> ObjectModel :: TYPE_FLOAT,
				'validate' 	=> 'isPrice'
			),
			'free_shipp' => array(
				'type' 		=> ObjectModel :: TYPE_BOOL,
				'validate' 	=> 'isBool'
			),
			'date_demande' => array(
				'type' 		=> ObjectModel :: TYPE_DATE
			),
			'date_reponse' => array(
				'type' 		=> ObjectModel :: TYPE_DATE
			),
			'date_expiration' => array(
				'type' 		=> ObjectModel :: TYPE_DATE
			),
			'demande' 	=> array(
				'type' 		=> ObjectModel :: TYPE_STRING,
				'required' 	=> true,
				'size' 		=> 65000,
				'validate' 	=> 'isMessage'
			),
			'reponse' 	=> array(
				'type' 		=> ObjectModel :: TYPE_STRING,
				'size' 		=> 65000,
				'validate' 	=> 'isString'
			),
			'desc_invoice' => array(
				'type' 		=> ObjectModel :: TYPE_STRING,
				'validate' 	=> 'isString'
			)
	   )
	);
	
	public function updateQteProduct()
	{
		$product = new Product(Configuration::get('DEVIS_PROD'));
		StockAvailable::setProductOutOfStock($product->id, 1);
		StockAvailable::setQuantity($product->id, 0, 1000);
		$product->price 	= 0;
		$product->weight 	= 0;
		$product->update();
		
		$discount = new CartRule(Configuration::get('DEVIS_DISCOUNT'));
		$discount->quantity 			= 1000;
		$discount->quantity_per_user 	= 1000;
		$discount->update();
		
	}
	public function getFields()
	{
		parent::validateFields();
		if (isset($this->id))
			$fields['id_devis'] = intval($this->id);
		$fields['id_carrier'] = intval($this->id_carrier);
		$fields['id_lang'] = intval($this->id_lang);
		$fields['id_currency'] = intval($this->id_currency);
		$fields['id_customer'] = intval($this->id_customer);
		$fields['id_address_delivery'] = intval($this->id_address_delivery);
		$fields['id_address_invoice'] = intval($this->id_address_invoice);
		$fields['id_cart'] = intval($this->id_cart);
		$fields['id_order'] = intval($this->id_order);
		$fields['id_statut'] = intval($this->id_statut);
		$fields['id_tax'] = intval($this->id_tax);
		$fields['poids_devis'] = floatval($this->poids_devis);
		$fields['total_devis'] = floatval($this->total_devis);
		$fields['total_out_shipp'] = floatval($this->total_out_shipp);
		$fields['total_shipping'] = floatval($this->total_shipping);
		$fields['free_shipp'] = floatval($this->free_shipp);
		$fields['date_demande'] = pSQL($this->date_demande);
		if (!empty($this->date_reponse))
			$fields['date_reponse'] = pSQL($this->date_reponse);
		$fields['date_expiration'] = pSQL($this->date_expiration);
		$fields['demande'] = pSQL($this->demande);
		$fields['reponse'] = pSQL($this->reponse, true);
		$fields['desc_invoice'] = pSQL($this->desc_invoice, true);
		return $fields;
	}
	
	// Recup le 1er resultat de la requete (getValue)
	// pour avoir l'id_tax_rules_gp correspondant a la tax du devis et au pays par defaut 
	public function getIdTaxRulesGroup($id_tax,$id_country)
	{
		$result = Db::getInstance()->getValue('
			SELECT tr.`id_tax_rules_group`
			FROM `'._DB_PREFIX_.'tax_rule` tr
			LEFT JOIN `'._DB_PREFIX_.'tax_rules_group` rg ON (tr.`id_tax_rules_group` = rg.`id_tax_rules_group`)
			WHERE tr.`id_tax` = '.$id_tax.' AND tr.`id_country` = '.$id_country.' AND rg.`active` = 1');

		if (!$result)
			return false;
		return $result;
	}
	
	public static function getDateExp ($date = null)
	{
		if($date == null)
		{ 
			$today = getdate();
			$date = array($today['year'], $today['mon'], $today['mday']);
		}
			
		// recupere la configuration pour gerer l'expiration auto et date expiration
		Configuration::get('DEVIS_EXP_AUTO') == 1 ? $nbJour = Configuration::get('DEVIS_NB_JOUR_EXP') : $nbJour = 365;
		
		return $dateExp = date('Y-m-d', mktime(0, 0, 0, $date[1], $date[2]+$nbJour,  $date[0]));
	}
	public static function getDevis()
	{
		return Db::getInstance()->ExecuteS('
			SELECT *
			FROM `'._DB_PREFIX_.'devis`');

	}
	public static function getDevisById($id_devis)
	{
		return Db::getInstance()->ExecuteS('
			SELECT *
			FROM `'._DB_PREFIX_.'devis`
			WHERE `id_devis` = '.(int)$id_devis);

	}
	
	public static function getStatut ($id_lang)
	{
		$lang 		= Language::getLanguages(false);
		$tab_lang	= array();
		foreach($lang as $language)
		{
			if ($language['iso_code'] == 'fr')
			{
				$tab_lang[] = $language['id_lang'];
			}
			elseif ($language['iso_code'] == 'en')
			{
				$tab_lang[] = $language['id_lang'];
			}
			elseif ($language['iso_code'] == 'es')
			{
				$tab_lang[] = $language['id_lang'];
			}
		}

		if(!in_array($id_lang, $tab_lang))
			$id_lang = Configuration::get('PS_LANG_DEFAULT'); //par defaut en anglais
			
    	$res = Db::getInstance()->ExecuteS('
			SELECT s.`id_statut`, s.`statut_desc` 
			FROM `'._DB_PREFIX_.'devis_statut` s
			WHERE s.`id_lang` = '.(int)$id_lang //modif
    		);
    	 
    	if (!$res)
			return array();
		return $res;
	}
	
	public static function getCustomerDevis($id_customer)
	{
    	$res = Db::getInstance()->ExecuteS('
        SELECT d.*
        FROM `'._DB_PREFIX_.'devis` d
        WHERE d.`id_customer` = '.(int)($id_customer).'
        ORDER BY d.`id_devis` DESC');
		if (!$res)
			return array();
		return $res;
	}
	//pour hook payment confirmation : pour mettre a jour le id_order du devis qd la commande est confirme
	public static function getDevisByCart($id_customer, $id_cart)
	{
		if((int)($id_cart) == 0)
			return false;
		else 
		{
			$res = Db::getInstance()->ExecuteS('
	        SELECT d.*
	        FROM `'._DB_PREFIX_.'devis` d
	        WHERE d.`id_customer` = '.(int)($id_customer).'
	        	AND d.id_cart = '.(int)($id_cart).'
	        ORDER BY d.`id_devis` DESC');
			if (!$res)
				return false;
			
			return $res[0];
		}
	}
	//pour hook PDFInvoice : pour afficher le desc sur la facture si il y en a un 
	public static function getDevisByOrder($id_order)
	{
		if((int)($id_order) == 0)
			return false;
		else 
		{
			$res = Db::getInstance()->ExecuteS('
	        SELECT d.*
	        FROM `'._DB_PREFIX_.'devis` d
	        WHERE d.id_order = '.(int)($id_order).'
	        ORDER BY d.`id_devis` DESC');
			if (!$res)
				return false;
			
			return $res[0]["desc_invoice"];
		}
	}
	
	// methode de Cart.php
	public function getDevisShippingCost($carrier, $total, $totalWeight, $useTax = true)
	{
		global $defaultCountry;
		
		// Start with shipping cost at 0
        $shipping_cost = 0;	

		// Get id zone
		if (
		      isset($this->id_address_delivery)
		      AND $this->id_address_delivery
		      AND Customer::customerHasAddress($this->id_customer, $this->id_address_delivery)
		    )
			$id_zone = Address::getZoneById((int)($this->id_address_delivery));
		else
			$id_zone = (int)($defaultCountry->id_zone);

		
        if (!$carrier->active)
			return $shipping_cost;
		// Select carrier tax
		if ($useTax AND !Tax::excludeTaxeOption())
			 $carrierTax = Tax::getCarrierTaxRate((int)$carrier->id, (int)$this->{Configuration::get('PS_TAX_ADDRESS_TYPE')});

		$configuration = Configuration::getMultiple(array('PS_SHIPPING_FREE_PRICE', 'PS_SHIPPING_HANDLING', 'PS_SHIPPING_METHOD', 'PS_SHIPPING_FREE_WEIGHT'));
		// Free fees
		$free_fees_price = 0;
		if (isset($configuration['PS_SHIPPING_FREE_PRICE']))
			$free_fees_price = Tools::convertPrice((float)($configuration['PS_SHIPPING_FREE_PRICE']), Currency::getCurrencyInstance((int)($this->id_currency)));
		$orderTotalwithDiscounts = $total;
		if ($orderTotalwithDiscounts >= (float)($free_fees_price) AND (float)($free_fees_price) > 0)
			return $shipping_cost;
		if (isset($configuration['PS_SHIPPING_FREE_WEIGHT']) AND $totalWeight >= (float)($configuration['PS_SHIPPING_FREE_WEIGHT']) AND (float)($configuration['PS_SHIPPING_FREE_WEIGHT']) > 0)
			return $shipping_cost;

		// Get shipping cost using correct method
		if ($carrier->range_behavior)
		{
			// Get id zone
	        if (
	              isset($this->id_address_delivery)
	              AND $this->id_address_delivery
	              AND Customer::customerHasAddress($this->id_customer, $this->id_address_delivery)
	            )
				$id_zone = Address::getZoneById((int)($this->id_address_delivery));
			else
				$id_zone = (int)($defaultCountry->id_zone);
			if (($carrier->getShippingMethod() == Carrier::SHIPPING_METHOD_WEIGHT AND (!Carrier::checkDeliveryPriceByWeight($carrier->id, $totalWeight, $id_zone)))
					OR ($carrier->getShippingMethod() == Carrier::SHIPPING_METHOD_PRICE AND (!Carrier::checkDeliveryPriceByPrice($carrier->id, $total, $id_zone, (int)($this->id_currency)))))
					$shipping_cost += 0;
				else {
						if ($carrier->getShippingMethod() == Carrier::SHIPPING_METHOD_WEIGHT)
							$shipping_cost += $carrier->getDeliveryPriceByWeight($totalWeigh, $id_zone);
						else // by price
							$shipping_cost += $carrier->getDeliveryPriceByPrice($total, $id_zone, (int)($this->id_currency));
					 }
		}
		else
		{
			if ($carrier->getShippingMethod() == Carrier::SHIPPING_METHOD_WEIGHT)
				$shipping_cost += $carrier->getDeliveryPriceByWeight($totalWeight, $id_zone);
			else
				$shipping_cost += $carrier->getDeliveryPriceByPrice($total, $id_zone, (int)($this->id_currency));

		}
		// Adding handling charges
		if (isset($configuration['PS_SHIPPING_HANDLING']) AND $carrier->shipping_handling)
			$shipping_cost += (float)($configuration['PS_SHIPPING_HANDLING']);

		$shipping_cost = Tools::convertPrice($shipping_cost, Currency::getCurrencyInstance((int)($this->id_currency)));

		
		//get external shipping cost from module
		if ($carrier->shipping_external)
		{
			$moduleName = $carrier->external_module_name;
			$module = Module::getInstanceByName($moduleName);
			if (key_exists('id_carrier', $module))
				$module->id_carrier = $carrier->id;
			if($carrier->need_range)
				$shipping_cost = $module->getOrderShippingCost($this, $shipping_cost);
			else
				$shipping_cost = $module->getOrderShippingCostExternal($this);

			// Check if carrier is available
			if ($shipping_cost === false)
				return false;
		}

		// Apply tax
		if (isset($carrierTax))
			$shipping_cost *= 1 + ($carrierTax / 100);

		return (float)(Tools::ps_round((float)($shipping_cost), 2));
		
	}
	
}
	
?>