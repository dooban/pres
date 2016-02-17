<?php
/*
* 2007-2015 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author Armando Salvador <asalvador@ecomm360.es>
*  @copyright  2007-2015 eComm360 SL
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_'))
	exit;

class PaymentIn90days extends PaymentModule
{
	private $_html = '';
	private $_postErrors = array();

	public $paymentName;
	public $address;
	public $extra_mail_vars;

	public function __construct()
	{
		$this->name = 'paymentin90days';
		$this->tab = 'payments_gateways';
		$this->version = '1.0.0';
		$this->author = 'eComm360 SL';
		$this->controllers = array('payment', 'validation');
		$this->is_eu_compatible = 1;

		$this->currencies = true;
		$this->currencies_mode = 'checkbox';

		$config = Configuration::getMultiple(array('PAYMENTIN90days_90days'));
		if (isset($config['PAYMENTIN90days_90days']))
			$this->paymentName = $config['PAYMENTIN90days_90days'];
		
		$this->bootstrap = true;
		parent::__construct();

		$this->displayName = $this->l('Payments in 90 days');
		$this->description = $this->l('This module allows you to accept payments in 90 days.');
		$this->confirmUninstall = $this->l('Are you sure you want to delete these details?');

		if (!count(Currency::checkPaymentCurrencies($this->id)))
			$this->warning = $this->l('No currency has been set for this module.');

		$this->extra_mail_vars = array(
											'{paymentin90days_name}' => Configuration::get('PAYMENTIN90days_NAME'),
											'{paymentin90days_address}' => Configuration::get('PAYMENTIN90days_ADDRESS'),
											'{paymentin90days_address_html}' => str_replace("\n", '<br />', Configuration::get('PAYMENTIN90days_ADDRESS'))
											);
	}

	public function install()
	{
		Configuration::updateValue('PAYMENTIN90days_90days', 90);
		$this->createOrderState();
		if (!parent::install() || !$this->registerHook('payment') || ! $this->registerHook('displayPaymentEU') || !$this->registerHook('paymentReturn'))
			return false;
		return true;
	}

	public function uninstall()
	{
		if (!Configuration::deleteByName('PAYMENTIN90days_90days') 
				|| !parent::uninstall())
			return false;
		return true;
	}

	private function _postValidation()
	{
		if (Tools::isSubmit('btnSubmit'))
		{
			if (!Tools::getValue('PAYMENTIN90days_90days'))
				$this->_postErrors[] = $this->l('The days for option 1 field is required.');
		}
	}

	private function _postProcess()
	{
		if (Tools::isSubmit('btnSubmit'))
		{
			Configuration::updateValue('PAYMENTIN90days_90days', Tools::getValue('PAYMENTIN90days_90days'));
			
		}
		$this->_html .= $this->displayConfirmation($this->l('Settings updated'));
	}

	private function _displayPaymentIn90days()
	{
		return $this->display(__FILE__, 'infos.tpl');
	}

	public function getContent()
	{
		$this->_html = '';

		if (Tools::isSubmit('btnSubmit'))
		{
			$this->_postValidation();
			if (!count($this->_postErrors))
				$this->_postProcess();
			else
				foreach ($this->_postErrors as $err)
					$this->_html .= $this->displayError($err);
		}

		$this->_html .= $this->_displayPaymentIn90days();
		$this->_html .= $this->renderForm();

		return $this->_html;
	}

	public function hookPayment($params)
	{
		
		if (!$this->active)
			return;
		if (!$this->checkCurrency($params['cart']))
			return;

		$this->smarty->assign(array(
			'this_path' => $this->_path,
			'this_path_payment' => $this->_path,
			'this_path_ssl' => Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'modules/'.$this->name.'/'
		));
		
		return $this->display(__FILE__, 'payment.tpl');
	}

	public function hookDisplayPaymentEU($params)
	{
		if (!$this->active)
			return;
		if (!$this->checkCurrency($params['cart']))
			return;

		$payment_options = array(
			'cta_text' => $this->l('Pay by Check'),
			'logo' => Media::getMediaPath(dirname(__FILE__).'/paymentin90days.jpg'),
			'action' => $this->context->link->getModuleLink($this->name, 'validation', array(), true)
		);

		return $payment_options;
	}

	public function hookPaymentReturn($params)
	{
		if (!$this->active)
			return;

		$state = $params['objOrder']->getCurrentState();
		if (in_array($state, array(Configuration::get('PS_OS_PAYMENTIN90days'), Configuration::get('PS_OS_OUTOFSTOCK'), Configuration::get('PS_OS_OUTOFSTOCK_UNPAID'))))
		{
			$this->smarty->assign(array(
				'total_to_pay' => Tools::displayPrice($params['total_to_pay'], $params['currencyObj'], false),
				'paymentName' => $this->paymentName,
				'paymentAddress' => Tools::nl2br($this->address),
				'status' => 'ok',
				'id_order' => $params['objOrder']->id
			));
			if (isset($params['objOrder']->reference) && !empty($params['objOrder']->reference))
				$this->smarty->assign('reference', $params['objOrder']->reference);
		}
		else
			$this->smarty->assign('status', 'failed');
		return $this->display(__FILE__, 'payment_return.tpl');
	}

	public function checkCurrency($cart)
	{
		$currency_order = new Currency((int)($cart->id_currency));
		$currencies_module = $this->getCurrency((int)$cart->id_currency);

		if (is_array($currencies_module))
			foreach ($currencies_module as $currency_module)
				if ($currency_order->id == $currency_module['id_currency'])
					return true;
		return false;
	}

	public function renderForm()
	{
		$fields_form = array(
			'form' => array(
				'legend' => array(
					'title' => $this->l('Contact details'),
					'icon' => 'icon-envelope'
				),
				'input' => array(
					array(
						'type' => 'text',
						'label' => $this->l('Days for payment option 1 (zero for disable)'),
						'name' => 'PAYMENTIN90days_90days',
						'required' => true
					),
				),
				'submit' => array(
					'title' => $this->l('Save'),
				)
			),
		);

		$helper = new HelperForm();
		$helper->show_toolbar = false;
		$helper->table = $this->table;
		$lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
		$helper->default_form_language = $lang->id;
		$helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
		$this->fields_form = array();
		$helper->id = (int)Tools::getValue('id_carrier');
		$helper->identifier = $this->identifier;
		$helper->submit_action = 'btnSubmit';
		$helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
		$helper->token = Tools::getAdminTokenLite('AdminModules');
		$helper->tpl_vars = array(
			'fields_value' => $this->getConfigFieldsValues(),
			'languages' => $this->context->controller->getLanguages(),
			'id_language' => $this->context->language->id
		);

		return $helper->generateForm(array($fields_form));
	}

	public function getConfigFieldsValues()
	{
		return array(
			'PAYMENTIN90days_90days' => Tools::getValue('PAYMENTIN90days_90days', Configuration::get('PAYMENTIN90days_90days')),
		);
	}
	
	/**
	 * Create a new order state
	 */
	public function createOrderState()
	{
		if (!Configuration::get('PS_OS_PAYMENTIN90days'))
		{
			$order_state = new OrderState();
			$order_state->name = array();

			foreach (Language::getLanguages() as $language)
			{
				if (Tools::strtolower($language['iso_code']) == 'es')
					$order_state->name[$language['id_lang']] = 'Pago en '.Configuration::get('PAYMENTIN90days_90days') .' días';
				else
					$order_state->name[$language['id_lang']] = 'Payment in '.Configuration::get('PAYMENTIN90days_90days') .' days';
			}

			$order_state->send_email = false;
			$order_state->color = '#DDEEFF';
			$order_state->hidden = false;
			$order_state->delivery = false;
			$order_state->logable = true;
			$order_state->invoice = true;

			if ($order_state->add())
			{
				$source = dirname(__FILE__).'/logo.gif';
				$destination = dirname(__FILE__).'/../../img/os/'.(int)$order_state->id.'.gif';
				copy($source, $destination);
			}
			Configuration::updateValue('PS_OS_PAYMENTIN90days', (int)$order_state->id);
		}
	}
}