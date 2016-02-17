<?php

class QuickOrderForm15DefaultModuleFrontController extends ModuleFrontController {

	public $ssl = true;

	public function initContent()
	{
		parent::initContent();

		$this->context->smarty->assign('search_ssl',(int)Tools::usingSecureMode());

		$this->setTemplate('quickorderform-form.tpl');
	}
}