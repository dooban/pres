<?php

class devispersoformdevisModuleFrontController extends ModuleFrontController 
{

	public function __construct()
	{
		$this->display_column_left = false;
		parent::__construct();

		$this->context = Context::getContext();

		include_once($this->module->getLocalPath().'models/Devisclass.php');
		include_once($this->module->getLocalPath().'devisperso.php');
	}
	
	public function initContent() {

		parent :: initContent();

		if ($this->context->customer->isLogged())
		{
			// en cas d'erreur de saisie on recup les valeurs entrées pour les réafficher
			$selectedCarrier 			= (Tools::getValue('id_carrier') ?  Tools::getValue('id_carrier') : null);
			$selectedAddressInvoice 	= (Tools::getValue('id_address_invoice') ? Tools::getValue('id_address_invoice') : null);
			$selectedAddressDelivery 	= (Tools::getValue('id_address_delivery') ?  Tools::getValue('id_address_delivery') : null);
			
			// action pour création du devis
			if (Tools::isSubmit('submitDevis'))
			{
				$devis 	= new Devisclass();
				$this->errors = $devis->validateController();
				
				if ($this->context->customer->isLogged() && !$this->isTokenValid())
						$this->errors[] = Tools::displayError('invalid token');
				
				
				if (!sizeof($this->errors))
				{
					$devis->id_customer 		= (int)($this->context->customer->id);
					$devis->id_address_invoice 	= (int)Tools::getValue('id_address_invoice');
					$devis->id_address_delivery = (int)Tools::getValue('id_address_delivery');
					$devis->id_carrier 			= (int)Tools::getValue('id_carrier');
					$devis->demande 			= Tools::getValue('demande');
					$devis->date_demande 		= date('Y-m-d H:i:s'); 
					$devis->id_statut 			= 1; //wait
					
					$today 		= getdate();
					$date 		= array($today['year'], $today['mon'], $today['mday']);
					$dateExp 	= $devis->getDateExp($date);
					
					$devis->date_expiration 	= $dateExp;
					$devis->id_lang 			= (int)($this->context->language->id);
					$devis->id_currency 		= (int)($this->context->currency->id); //récup la monnaie ds laquelle a été faite la demande
					
					$devis->save();
					
					// envoie de mail a l'admin dans la langue par defaut en cas de nouvelle demande mais pas en cas de modification (dans la langue dans laquelle a été faite la demande)
					$customer 		= new Customer($devis->id_customer);
					
					$invoice 		= new Address((int)($devis->id_address_invoice));
					$delivery 		= new Address((int)($devis->id_address_delivery));
					$carrier 		= new Carrier((int)($devis->id_carrier), $devis->id_lang);
					$delivery_state = $delivery->id_state ? new State((int)($delivery->id_state)) : false;
					$invoice_state 	= $invoice->id_state ? new State((int)($invoice->id_state)) : false;
			
					$dev_trad 	= new DevisPerso();
					$objet  	= $dev_trad->getL('A new quote request !');
					Mail::Send(
						Configuration::get('PS_LANG_DEFAULT'),
					 	'devis_creation', 
						$objet, 
						array(
							'{date_demande}' 		=> Tools::displayDate($devis->date_demande, (int)($devis->id_lang), 1),
							'{demande}'	 			=> $devis->demande, 
							'{firstname}' 			=> $customer->firstname, 
							'{lastname}' 			=> $customer->lastname,
							'{carrier}' 			=> $carrier->name,
							'{delivery_company}' 	=> $delivery->company,
							'{delivery_firstname}' 	=> $delivery->firstname,
							'{delivery_lastname}' 	=> $delivery->lastname,
							'{delivery_address1}' 	=> $delivery->address1,
							'{delivery_address2}' 	=> $delivery->address2,
							'{delivery_city}' 		=> $delivery->city,
							'{delivery_postal_code}'=> $delivery->postcode,
							'{delivery_country}' 	=> $delivery->country,
							'{delivery_state}' 		=> $delivery->id_state ? $delivery_state->name : '',
							'{delivery_phone}' 		=> ($delivery->phone) ? $delivery->phone : $delivery->phone_mobile,
							'{delivery_other}' 		=> $delivery->other,
							'{invoice_company}' 	=> $invoice->company,
							'{invoice_vat_number}' 	=> $invoice->vat_number,
							'{invoice_firstname}' 	=> $invoice->firstname,
							'{invoice_lastname}'	=> $invoice->lastname,
							'{invoice_address1}' 	=> $invoice->address1,
							'{invoice_address2}' 	=> $invoice->address2,
							'{invoice_city}' 		=> $invoice->city,
							'{invoice_postal_code}' => $invoice->postcode,
							'{invoice_country}' 	=> $invoice->country,
							'{invoice_state}' 		=> $invoice->id_state ? $invoice_state->name : '',
							'{invoice_phone}' 		=> ($invoice->phone) ? $invoice->phone : $invoice->phone_mobile,
							'{invoice_other}' 		=> $invoice->other,
							'{color}'				=> ($color=Tools::safeOutput(Configuration::get('PS_MAIL_COLOR')) != null ? $color : '#db3484')),  
						strval(Configuration::get('PS_SHOP_EMAIL')), 
						strval(Configuration::get('PS_SHOP_NAME')),
						strval(Configuration::get('PS_SHOP_EMAIL')), 
						strval(Configuration::get('PS_SHOP_NAME')), 
						NULL, NULL, _PS_MODULE_DIR_.'devisperso/mails/');
				
					Tools::redirect($this->context->link->getModuleLink('devisperso', 'default'));	
				}
			}
			
			// action pour modification d'un devis
			if (Tools::isSubmit('modifDevis'))
			{
				if ($id = Tools::getValue('id_modif'))
				{
					$devis 	= new Devisclass($id);
					$this->errors = $devis->validateController();
					
					if ($this->context->customer->isLogged() && !$this->isTokenValid())
						$this->errors[] = Tools::displayError('invalid token');
					
					
					if (!sizeof($this->errors))
					{
						$devis->id_address_invoice 		= Tools::getValue('id_address_invoice');
						$devis->id_address_delivery 	= Tools::getValue('id_address_delivery');
						$devis->id_carrier 				= Tools::getValue('id_carrier');
						$devis->demande 				= Tools::getValue('demande');
						
						$devis->update();
						Tools::redirect($this->context->link->getModuleLink('devisperso', 'default'));	
					}
				}
			}
			// si ouvre un devis depuis listdevis (modif ou visu)
			if ($id = Tools::getValue('id_devis')) //POST ou GET
			{
				$devis = new Devisclass($id);
				if($this->context->customer->id == $devis->id_customer)
				{
					$selectedCarrier 			= $devis->id_carrier;
					$selectedAddressInvoice 	= $devis->id_address_invoice;
					$selectedAddressDelivery 	= $devis->id_address_delivery;
					$this->context->smarty->assign('devis', $devis);
				}
				else 
					$this->errors[] = Tools::displayError('invalid token');
				
			}
		
			// recup l'intro
			$intro 		= nl2br(Configuration::get('DEVIS_DESCRIPTION_'.strtoupper($this->context->language->iso_code)));
			
			// recup les adresses du client connecte
			$addresses 				= $this->context->customer->getAddresses($this->context->language->id);
			$addressInvoiceList 	= '';
			$addressDeliveryList 	= '';
			foreach ($addresses as $address)
			{
				$addressInvoiceList 	.= '<option value="'.(int)($address['id_address']).'" '.($address['id_address'] == $selectedAddressInvoice ? 'selected="selected"' : '').'>'.htmlentities($address['alias'], ENT_COMPAT, 'UTF-8').'</option>';
				$addressDeliveryList 	.= '<option value="'.(int)($address['id_address']).'" '.($address['id_address'] == $selectedAddressDelivery ? 'selected="selected"' : '').'>'.htmlentities($address['alias'], ENT_COMPAT, 'UTF-8').'</option>';	
			}
			
			// recup les transporteurs
			$carriers = Carrier::getCarriers((int)($this->context->language->id), true);
			$carrierList = '';
			foreach ($carriers as $carrier)
			{
				$carrierList .= '<option value="'.(int)($carrier['id_carrier']).'" '.($carrier['id_carrier'] == $selectedCarrier ? 'selected="selected"' : '').'>'.htmlentities($carrier['name'], ENT_COMPAT, 'UTF-8').'</option>';
			}
			
			//si on vient d'une fiche produit
			$desc_prod = null;
			if($id_prod = Tools::getValue('id_product'))
			{
				$prod = new Product($id_prod);
				$desc_prod = $prod->name[$this->context->language->id].' : '.$prod->description_short[$this->context->language->id];
			}
			
			//verif l'existance des icones pour affichage
			$img_account 	= false;
			$img_home 		= false;
			
			if(file_exists(_PS_THEME_DIR_.'img/icon/my-account.gif'))
				$img_account = true;
			
			if(file_exists(_PS_THEME_DIR_.'img/icon/home.gif'))
				$img_home = true;
			
			$this->context->smarty->assign(array(
					'intro'					=> $intro,
					'address_invoice_list' 	=> $addressInvoiceList,
					'address_delivery_list' => $addressDeliveryList,
					'carrier_list'			=> $carrierList,
					'id_customer' 			=> $this->context->customer->id,
					'id_statut' 			=> 1,
					'desc_prod' 			=> strip_tags($desc_prod), //supprime les balise html avant affichage ds textarea
					'errors' 				=> $this->errors,
					'token'					=> Tools::getToken(false),
					'img_account'			=> $img_account,
					'img_home'				=> $img_home
				)
			);
			
	
			$this->setTemplate('formdevis.tpl');
			
		}		
		else
		{
			$url = urlencode($this->context->link->getModuleLink('devisperso', 'default'));
			$url = str_replace('index', 'index.php', $url); //.php bien présent dans $url mais est supprimé par Tools::redirect
			Tools::redirect('index.php?controller=authentication&back='.$url);
		}
			//Tools::redirect('index.php?controller=authentication&back='.urlencode($this->context->link->getModuleLink('devisperso', 'default')));
				//manque index.php dans le getModule link du context !!! BUG PS
			
	}
}