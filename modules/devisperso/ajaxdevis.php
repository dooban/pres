<?php

define('PS_ADMIN_DIR', getcwd());
include(PS_ADMIN_DIR.'/../../config/config.inc.php');

include_once (_PS_MODULE_DIR_.'devisperso/models/Devisclass.php');
include_once (_PS_MODULE_DIR_.'devisperso/devisperso.php');

if (isset($_GET['ajaxAddress']) AND isset($_GET['id_customer']))
{
	$addresses = Db::getInstance()->ExecuteS('
	SELECT a.id_address, a.alias, a.city, c.iso_code
	FROM '._DB_PREFIX_.'address a
	JOIN '._DB_PREFIX_.'country c
	WHERE c.id_country = a.id_country AND a.id_customer = '.intval(Tools::getValue('id_customer')).'
	ORDER BY a.`alias` ASC');
	
	$list = '<option>-----------</option>'."\n";
	foreach ($addresses AS $ad)
	{
		$list .= '<option value="'.intval($ad['id_address']).'"'.((isset($_GET['id_address']) AND $_GET['id_address'] == $ad['id_address']) ? ' selected="selected"' : '').'>'.$ad['alias'].' - '.$ad['city'].' - '.$ad['iso_code'].'</option>'."\n";
	}	
	die($list);
}
if (isset($_GET['ajaxShipping']) AND isset($_GET['id_carrier']))
{
	$dev 		= new Devisclass($_GET['id_devis']);
	
	$carr 		= new Carrier((int)(Tools::getValue('id_carrier')));
	$shipping 	= $dev->getDevisShippingCost($carr, (float)(Tools::getValue('total')), (float)(Tools::getValue('poids')));
	
	$list = '<input class="shipp" type="text" size="11" style="text-align:right; color:grey;" id="total_shipping" name="total_shipping" value="'.$shipping.'" readonly />';

	die($list);
}
if (isset($_GET['ajaxTax']) AND isset($_GET['id_address_delivery']))
{
	$devisperso 	= new Devisperso();
	$dev 			= new Devisclass();
	$ad				= new Address($_GET['id_address_delivery']);
	$country		= new Country($ad->id_country);
	$taxes 			= Tax::getTaxes($_GET['id_lang']);
		
	$taxRule = array();
    foreach ($taxes as $tax)
	{
		$idRuleGp = 0;
		$idRuleGp = $dev->getIdTaxRulesGroup($tax['id_tax'], $ad->id_country);
		if ($idRuleGp!=0)
			$taxRule[$idRuleGp]=$tax;
	}
	
	$list = '<option value="0" '.((isset($_GET['id_tax_devis']) AND $_GET['id_tax_devis'] == 0) ? ' selected="selected"' : '').'>'.$devisperso->getL('No Tax').'</option>'."\n";
	foreach ($taxRule AS $k => $tax)
		$list .= '<option value="'.intval($tax['id_tax']).'" '.((isset($_GET['id_tax_devis']) AND $_GET['id_tax_devis'] == $tax['id_tax']) ? ' selected="selected"' : '').'>'.$tax['name'].' ('.$tax['rate'].'%)</option>'."\n";
	
	die($list);
}	
?>