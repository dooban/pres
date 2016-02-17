<?php

/*
 * 2007-2013 PrestaShop
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
 *  @author PrestaShop SA <contact@prestashop.com>
 *  @copyright  2007-2013 PrestaShop SA
 *  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

/**
 * @since 1.5.0
 */
class commercialManagerDefaultModuleFrontController extends ModuleFrontController {

    public function __construct() {
        $this->auth = true;
        parent::__construct();

        $this->context = Context::getContext();
    }

    /**
     * @see FrontController::postProcess()
     */
    public function postProcess() {
        
        
    }

    private function loadUserData() {
        
        if (version_compare(_PS_VERSION_, '1.5.0.0', '>=')) {
            $cookie_lifetime = (int) (defined('_PS_ADMIN_DIR_') ? Configuration::get('PS_COOKIE_LIFETIME_BO') : Configuration::get('PS_COOKIE_LIFETIME_FO'));
            $cookie_lifetime = time() + (max($cookie_lifetime, 1) * 3600);
            if (Context::getContext()->shop->getGroup()->share_order)
                $cookie = new Cookie('ps-sg' . Context::getContext()->shop->getGroup()->id, '', $cookie_lifetime, Context::getContext()->shop->getUrlsSharedCart());
            else {
                $domains = null;
                if (Context::getContext()->shop->domain != Context::getContext()->shop->domain_ssl)
                    $domains = array(Context::getContext()->shop->domain_ssl, Context::getContext()->shop->domain);

                $cookie = new Cookie('ps-s' . Context::getContext()->shop->id, '', $cookie_lifetime, $domains);
            }
        }else {
            $cookie = new Cookie('ps');
        }

        if (Tools::isSubmit('submit'.$this->module->name) && Tools::getValue('id_customer')) {
            if ($cookie->logged)
                $cookie->logout();
            Tools::setCookieLanguage();
            Tools::switchLanguage();
            $customer = new Customer(intval(Tools::getValue('id_customer')));
            $cookie->id_customer = intval($customer->id);
            $cookie->customer_lastname = $customer->lastname;
            $cookie->customer_firstname = $customer->firstname;
            $cookie->logged = 1;
            $cookie->passwd = $customer->passwd;
            $cookie->email = $customer->email;
        }
        return $cookie;
    }

    public function contentModule() {
        $html = '';
        $cookie = $this->loadUserData();
        
        $html .= '
            <form action="' . $_SERVER['REQUEST_URI'] . '" method="post">
                <fieldset><legend>'. $this->module->l('Manage Customers', 'default') .'</legend>';
        
        if ($cookie->logged) {
            $id_shop = Db::getInstance()->getValue('SELECT id_shop FROM `' . _DB_PREFIX_ . 'customer` WHERE id_customer=' . $cookie->id_customer . '');
            $multi_store = Db::getInstance()->getValue('SELECT virtual_uri FROM `' . _DB_PREFIX_ . 'shop_url` WHERE id_shop=' . $id_shop . '');
            $html .= '<b>' . $this->module->l('You are', 'default') . ' ' . $cookie->customer_firstname . ' ' . $cookie->customer_lastname .'</b> <br /><br />';
        }

        if (in_array(Configuration::get('PS_COMMERCIALMANAGER_GROUP'), Customer::getGroupsStatic($cookie->id_customer))){
				
			if (version_compare(_PS_VERSION_, '1.5.0.0', '>=')) {
				$list_shops = '<select onchange="display_list_customers()" id="id_shop" name="id_shop">
				<option value="0">' . $this->module->l('All shops', 'default') . '</option>';
				$shops = Db::getInstance()->ExecuteS('SELECT `id_shop`, `name` FROM `' . _DB_PREFIX_ . 'shop` ORDER BY `id_shop` ASC');
				foreach ($shops as $shop)
					$list_shops .= '<option value="' . $shop['id_shop'] . '">' . $shop['name'] . '</option>';
				$list_shops .= '</select>';
			}

			$html .= '
				<label >' . $this->module->l('Search your user by name or email.', 'default') . '</label>
				<div class="margin-form">
					<p><input type="text" id="recherche" name="recherche" required onkeyup="display_list_customers()"/></p>
				</div> <br/>';

				if (version_compare(_PS_VERSION_, '1.5.0.0', '>=') and false)
					$html .= '
					<label >' . $this->module->l('Shop', 'default') . ' : </label>
					<div class="margin-form">
						<p>' . $list_shops . '</p>
					</div> <br/>';
				else
					$html .= '<input type="hidden" id="id_shop" name="id_shop" value="1"/>';

				$html .= ' 
				<label > ' . $this->module->l('Customers founded', 'default') . ': </label>
					<div class="margin-form">
						<div id="display_list_customers">'.$this->module->l('Please start to write in input text.', 'default').'</div>
					</div> 
					<br/>
					<div class="margin-form">
						<input type="submit" name="submit'.$this->module->name.'" value="' . $this->module->l('Login as User', 'default') . '" class="button" />
					</div>';
			} else {
				// $html .= '<span>'. $this->module->l('To continue please clic', 'default').' <a href="'.Context::getContext()->link->getPageLink('index').'">'. $this->module->l('here', 'default') .'</span>';
			}
			
			$html .='
				</fieldset>
			</form>';
        return $html;
    }

    /**
     * @see FrontController::initContent()
     */
    public function initContent() {
        $this->display_column_left = false;
		$this->display_column_right = false;
        
		parent::initContent();
        
        $this->context->smarty->assign(	array(
			'html' => $this->contentModule(),
			'url_commercial_manager' => _MODULE_DIR_ . $this->module->name,
			'shop_text_commercial_manager' => $this->module->l('Shop'),
			'no_result_commercial_manager' => $this->module->l('No hay resultados'),
		));
        $this->setTemplate('list-users.tpl');
    }

}
