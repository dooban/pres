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
*  @author Armando Salvador <info@ecomm360.es>
*  @copyright  2007-2015 eComm360 SL
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_'))
  exit;

class CommercialManager extends Module {

    public $adminTabFilename = '';

    public function __construct() {
		
        $this->name = 'commercialmanager';
        $this->tab = 'front_office_features';
		$this->version = '1.0.0';
		$this->author = 'eComm360 S.L.';
		$this->need_instance = 0;
		$this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_); 
		$this->bootstrap = true;
		
		$this->adminTabFilename = 'AdminCommercialManager';
        $this->page = basename(__FILE__, '.php');

        parent::__construct();

        $this->displayName = $this->l('Commercial Manager');
        $this->description = $this->l('Allow to your Commercial Team to add order in name of his clients.');
		
		$this->confirmUninstall = $this->l('Are you sure you want to uninstall?');
		
    }

    public function install() {
		
		if (!file_exists(dirname(__FILE__).'/sql/install.php'))
			return false;
		else
			include dirname(__FILE__).'/sql/install.php';

		//Adds new tab (TODO FUTURE FEATURE)
		//$this->createTab($this->adminTabFilename, 'Commercial Manager', 'Customers');
        
		//Preasigned Group
		Configuration::updateValue('PS_COMMERCIALMANAGER_GROUP', 4);
		
		if (!parent::install() 
			|| !$this->registerHook('header')
			|| !$this->registerHook('displayMyAccountBlock') 
			|| !$this->registerHook('displayAdminCustomers')
			|| !$this->registerHook('displayCustomerAccount')
        )
            return false;
        return true;
    }

    public function uninstall() {
		
		if (!file_exists(dirname(__FILE__).'/sql/uninstall.php'))
			return false;
		else
			include dirname(__FILE__).'/sql/uninstall.php';

        //Remove tab(TODO FUTURE FEATURE)
		//$this->uninstallModuleTab($this->adminTabFilename);
		//Delete value
		Configuration::deleteByName('PS_COMMERCIALMANAGER_GROUP');
        
		return parent::uninstall();
    }
	
	public function createTab($class_name, $tab_name, $tab_parent_name = false) {
        $tab = new Tab();
        $tab->active = 1;
        $tab->class_name = $class_name;
        $tab->name = array();
        
        foreach (Language::getLanguages(true) as $lang) 
            $tab->name[$lang['id_lang']] = $tab_name;   
        
        if($tab_parent_name) 
            $tab->id_parent = (int)Tab::getIdFromClassName($tab_parent_name);
        else 
            $tab->id_parent = 0;

        $tab->module = $this->name;
        return $tab->add();
    }

    private function uninstallModuleTab($tabClass) {
        $idTab = Tab::getIdFromClassName($tabClass);
        if ($idTab != 0) {
            $tab = new Tab($idTab);
            $tab->delete();
            @unlink(_PS_IMG_DIR_ . 't/' . $tabClass . '.gif');
            return true;
        }
        return false;
    }

    private function postProcess() {
        if (((bool)Tools::isSubmit('submitCommercialManagerModule')) == true) {
			Configuration::updateValue('PS_COMMERCIALMANAGER_GROUP', Tools::getValue('PS_COMMERCIALMANAGER_GROUP', 4));
            return $this->displayConfirmation($this->l('Commercia Group Settings updated'));
        }
    }

    public function getContent() {
		/**
		 * If values have been submitted in the form, process.
		 */
		$output = '';
		$output .= $this->postProcess();
		$output .= $this->context->smarty->fetch($this->local_path.'views/templates/admin/configure.tpl');

		return $output.$this->renderForm();
    }

	/**
	 * Create the form that will be displayed in the configuration of your module.
	 */
	protected function renderForm()
	{
		$helper = new HelperForm();

		$helper->show_toolbar = false;
		$helper->table = $this->table;
		$helper->module = $this;
		$helper->default_form_language = $this->context->language->id;
		$helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

		$helper->identifier = $this->identifier;
		$helper->submit_action = 'submitCommercialManagerModule';
		$helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
				.'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
		$helper->token = Tools::getAdminTokenLite('AdminModules');

		$helper->tpl_vars = array(
			'fields_value' => $this->getConfigFormValues(), /* Add values for your inputs */
			'languages' => $this->context->controller->getLanguages(),
			'id_language' => $this->context->language->id,
		);

		return $helper->generateForm(array($this->getConfigForm()));
	}

	/**
	 * Create the structure of your form.
	 */
	protected function getConfigForm()
	{
		return array(
			'form' => array(
				'legend' => array(
					'title' => $this->l('Settings'),
					'icon' => 'icon-cogs',
				),
				'input' => array(
					array(
						'type' => 'text',
						'label' => $this->l('Id Group for Commercials'),
						'name' => 'PS_COMMERCIALMANAGER_GROUP',
						'hint' => $this->l('Mark all modules that you want to allow access by Google Bot.')
					),
				),
				'submit' => array(
					'title' => $this->l('Save'),
				),
			),
		);
	}
	
	/**
	 * Set values for the inputs.
	 */
	protected function getConfigFormValues()
	{
		// Load current value
		return array('PS_COMMERCIALMANAGER_GROUP' => Configuration::get('PS_COMMERCIALMANAGER_GROUP'));
	}
	
	public function getCommercialManagers() {
		$group = new Group(Configuration::get('PS_COMMERCIALMANAGER_GROUP'));
		return $group->getCustomers();
	}
	
	private function _postSaveAssociation($id_customer) {
		if (Tools::isSubmit('submitCommercialUsers')) {
			$assigned = Tools::getValue('id_commercialmanager');
            
            Db::getInstance()->execute('
					DELETE FROM '._DB_PREFIX_.'commercialmanager 
					WHERE id_customer = '.(int)$id_customer);
            if(!empty($assigned)) 
            {
                foreach ($assigned as $key => $assign)
				Db::getInstance()->execute('
					REPLACE INTO '._DB_PREFIX_.'commercialmanager (`id_commercialmanager`, `id_customer`)
					VALUES (
						"'.(int)$assign.'",
						"'.(int)$id_customer.'"
					)');
            }
		}
	}
	
    public function hookAdminCustomers($params) {
		$html = '';
		$customer = new Customer((int)$params['id_customer']);
		if (!Validate::isLoadedObject($customer))
			die (Tools::displayError());
		
		$this->_postSaveAssociation((int)$params['id_customer']);
		
		if($commercials = $this->getCommercialManagers()) {
			
			$html .= '
			<div class="col-lg-6">
				<div class="panel">
				<form method="post" action="">
					<div class="panel-heading">
						<i class="icon-group"></i> '.$this->l('Commercial Manager').'
					</div>
					<table class="table">
						<thead>
							<tr>
								<th><span class="title_box ">'.$this->l('Commercial asigned').'</span></th>
								<th><span class="title_box ">'.$this->l('Commercial Name').'</span></th>
							</tr>
						</thead>
						<tbody>';
						foreach ($commercials as $comercial) {
							$id_assigned_user = Db::getInstance()->getValue(
									'SELECT id_customer '
									. 'FROM '._DB_PREFIX_.'commercialmanager '
									. 'WHERE id_commercialmanager = '. (int)$comercial['id_customer'].' AND id_customer = '. (int)$params['id_customer']);
							$html .= '
								<tr>
									<td> <input type="checkbox" '.($id_assigned_user > 0 ? 'checked' : '').' name="id_commercialmanager[]" value="'.$comercial['id_customer'].'"></td>
									<td>
										'.$comercial['firstname'].' '.$comercial['lastname'].'
									</td>
								</tr>';
						}
			$html .= '
						</tbody>
					</table>
					<input type="submit" name="submitCommercialUsers" value="' . $this->l('Save Association').'" class="button" />
				</form>
				</div>
			</div>';
		}

		return $html;
    }

    public function hookAdminOrder($param) {
        $this->hookadminCustomers($param);
    }

    /* Hook display on customer account page */
    public function hookCustomerAccount($params) {
        return $this->display(__FILE__, 'my-account.tpl');
    }
    
    public function hookDisplayMyAccountBlock($params)
	{
		return $this->hookCustomerAccount($params);
	}
	
	public function hookHeader()
	{
		$this->context->controller->addCSS($this->_path.'views/css/commercialmanager.css', 'all');
		$this->context->controller->addJS($this->_path.'views/js/commercialmanager.js');
	}

}
