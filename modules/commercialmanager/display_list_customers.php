<?php

include(dirname(__FILE__) . '/../../config/config.inc.php');
require_once(dirname(__FILE__) . '/../../init.php');

if (!defined('_PS_VERSION_'))
	exit;

if ($search = Tools::getValue('search'))
{
	$shop = Tools::getValue('shop', 'shop');
	$this_shop = Tools::getValue('this_shop');
	$context = Context::getContext();

	$customer = new Customer($context->cookie->id_customer);
	$id_lang = $context->language->id;
	$join = (version_compare(_PS_VERSION_, '1.5.0.0', '>=')) ? 'JOIN `' . _DB_PREFIX_ . 'shop` s ON(s.`id_shop` = c.`id_shop`)' : '';
	$join .= ' INNER JOIN '._DB_PREFIX_.'commercialmanager cm ON cm.id_customer = c.id_customer AND cm.id_commercialmanager = '. (int)$context->cookie->id_customer;

	$customers = array();
	$sql = 'SELECT c.`id_customer`, c.`firstname`, c.`lastname`, c.`email`, c.`company`  
			FROM `' . _DB_PREFIX_ . 'customer` c 
			' . $join . '
			WHERE (
				c.`firstname` LIKE \'%' . $search . '%\' 
				OR c.`lastname` LIKE \'%' . $search . '%\' 
				OR c.`email` LIKE \'%' . $search . '%\' 
				)
				' . ((version_compare(_PS_VERSION_, '1.5.0.0', '>=') AND $this_shop AND $this_shop > 0) ? 'AND c.`id_shop`=' . $this_shop : '') . '  
			ORDER BY c.`firstname`, c.`lastname` ASC
    ';
	
	$customers = Db::getInstance()->ExecuteS($sql);
	if ($customers AND ! empty($customers))
	{
		echo '<select name="id_customer">';
		foreach ($customers as $customer)
			echo '<option value="' . $customer['id_customer'] . '">' . $customer['firstname'] . ' ' . $customer['lastname'] . ' ' . $customer['company'] . ' (' . $customer['email'].')</option>';
		echo '</select>';
	}
	else
		echo 'No result';
}