<?php
	
/**
 * Modified by Armando: Updated to versión 1.6 back Office.
 */



   	include_once (_PS_MODULE_DIR_.'devisperso/models/Devisclass.php');
    include_once (_PS_MODULE_DIR_.'devisperso/devisperso.php');
	
    class AdminDevisController extends ModuleAdminController
    {
    	public $module = 'devisperso';
      	// liste des devis dans l'onglet Devis
	    public function __construct()
		{
			$this->table 		= 'devis';
		 	$this->className 	= 'Devisclass';
		 	$this->lang 		= false;
		 	$this->show_toolbar = true;
		 	
			$this->bootstrap = true;
			
		 	$this->addRowAction('edit');
		 	$this->addRowAction('delete');
		 	$this->bulk_actions = array('delete' => array('text' => $this->l('Delete selected'), 'confirm' => $this->l('Delete selected items?')));
		 	$this->colorOnBackground = true;
			
			$this->_listTotal = Db::getInstance()->getValue('SELECT FOUND_ROWS()');
		 	
		 	
		 	$this->_select 	= 'CONCAT(c.`firstname`, \' \', c.`lastname`) as `customer`, s.`statut_desc` as `statut_devis`, s.color ';
		 	$this->_join 	= 'LEFT JOIN `'._DB_PREFIX_.'customer` c ON (c.`id_customer` = a.`id_customer`)
		 						LEFT JOIN `'._DB_PREFIX_.'devis_statut` s ON (s.`id_statut` = a.`id_statut` AND s.`id_lang` = '.(int)Context::getContext()->language->id.' )';
		 	
		 	parent::__construct();
		 	
		 	$statusArray 	= array();
			$status 		= Devisclass::getStatut($this->context->language->id);

			foreach ($status AS $stat)
				$statusArray[$stat['id_statut']] = $stat['statut_desc'];
		 	
		 	$this->fields_list = array(
		 		'statut_devis'	=> array('title' => $this->l('Status'), 'align' =>'center', 'width' => 90, 'type' => 'select', 'list' => $statusArray, 'filter_key' => 's!id_statut'),
				'id_devis'		=> array('title' => 'ID', 'align' => 'center', 'width' => 30),
		 		'id_customer' 	=> array('title' => $this->l('ID Customer'), 'align' => 'center', 'width' => 30, 'filter_key' => 'a!id_customer'),
				'customer'		=> array('title' => $this->l('Customer'), 'left' => 'center', 'width' => 150, 'filter_key' => 'customer', 'tmpTableFilter' => true),
		 		'total_devis'	=> array('title' => $this->l('Total'), 'width' => 70, 'align' => 'right', 'prefix' => '<b>', 'suffix' => '</b>', 'price' => true, 'currency' => true),
		 		'date_demande'	=> array('title' => $this->l('Request Date'), 'align' => 'right', 'widthColumn' => 110, 'width' => 80, 'type' => 'date', 'filter_key' => 'a!date_demande'),
		 		'date_reponse'	=> array('title' => $this->l('Answer Date'), 'align' => 'right', 'widthColumn' => 110, 'width' => 80, 'type' => 'date', 'filter_key' => 'a!date_reponse'),
		 		'date_expiration'=> array('title' => $this->l('Expiration Date'), 'align' => 'right', 'widthColumn' => 110, 'width' => 80, 'type' => 'date',  'filter_key' => 'a!date_expiration')//, 
				);

			
			/* bloc Option en bas de page */
			$this->fields_options = array(
			'general' => array(
				'title' => $this->l('Quote options'),
				'fields' => array(
					'DEVIS_EXP_AUTO' => array(
						'title' => $this->l('Automatic Expiration: '), 
						'desc' => $this->l('The quotes status change automatically to "Expired"'), 
						'cast' => 'intval', 
						'type' => 'bool'),
					'DEVIS_NB_JOUR_EXP' => array(
						'title' => $this->l('Delay before expiration:'), 
						'desc' => $this->l('If "Automatic Expiration" is desactivate, the delay is one year'), 
						'cast' => 'intval', 
						'type' => 'text', 
						'size' => '5', 
						'suffix' => ' '.$this->l('days'))
				),
				'submit' => array()
				)
			);
      	}
	  
      	public function renderForm()
      	{
      		$this->show_toolbar = true;
      		$obj = $this->loadObject(true);
      		
      		// Trie des clients par ordre alphabétique... 
      		$customers = Customer::getCustomers();
	      	function cmp($a, $b)
			{
			    return strcmp($a['lastname'], $b['lastname']);
			}
			usort($customers, 'cmp');
      		
      		//Liste des taxes pour le javascript de form.tpl
      		$taxes 			= Tax::getTaxes($this->context->language->id);
			$taxRule 		= array();
			$taxesArray 	= array();
			$taxesArray[0]	= 0;
			//remplit un tableau avec toutes les taxes
      		foreach ($taxes as $tax)
				$taxRule[$tax['id_tax']] = $tax;
			
			foreach ($taxRule as $k => $tax)
				$taxesArray[$tax['id_tax']] = $tax['rate'];
			
      		$this->context->smarty->assign(array(
      			'show_toolbar' 		=> $this->show_toolbar,
				'toolbar_btn'		=> $this->toolbar_btn,
				'toolbar_scroll' 	=> $this->toolbar_scroll,
				'title' 			=> array($this->l('Quote'), $this->l('Create/Modify quote')),
      			'currentIndex' 		=> self::$currentIndex,
      			'cwd' 				=> getcwd(),
      			'table' 			=> $this->table,
      			'token' 			=> $this->token,
      			'obj' 				=> $obj,
      			'customers' 		=> $customers,
      			'currency' 			=> new Currency(Configuration::get('PS_CURRENCY_DEFAULT')),
      			'status_lang' 		=> Devisclass::getStatut($this->context->language->id),
      			'carrier' 			=> Carrier::getCarriers((int)($this->context->language->id), true),
      			'lang' 				=> ($obj->id_lang ? new Language($obj->id_lang) : $this->context->language),
      			'tinymce'			=> true,
      			'PS_BASE_URI'		=> __PS_BASE_URI__,
      			'max'				=> 65000,
      			'ad'				=> dirname($_SERVER['PHP_SELF']),
      			'iso_tiny_mce'		=> file_exists(_PS_ROOT_DIR_.'/js/tiny_mce/langs/'.$this->context->language->iso_code.'.js') ? $this->context->language->iso_code : 'en',
      			'dateExp' 			=> ($obj->date_expiration ? $obj->date_expiration : Devisclass::getDateExp()),
				'noTax' 			=> Tax::excludeTaxeOption() ? 'true' : 'false',
				'taxRule' 			=> $taxRule,
      			'taxesArray' 		=> $taxesArray,
			));
			
      		$this->content .= $this->createTemplate('form.tpl')->fetch();
      	}
      	
      	public function getList($id_lang, $orderBy = NULL, $orderWay = NULL, $start = 0, $limit = NULL)
      	{	
			$tab_lang  	= array();
			$lang 		= Language::getLanguages(false);
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
      		
      		//on supprime les ventes du produit devis de la base car ça n'est pas un "vrai" produit
      		ProductSale::removeProductSale(Configuration::get('DEVIS_PROD'));
      		
      		$devisperso 	= new Devisperso();
      		$list 			= Devisclass::getDevis();
      		foreach ($list as $k => $liste)
      		{
      			/* si la durée d'expiration est atteinte suite à la demande et que le statut est toujours en attente */
      			if (($liste['date_expiration'] <= date('Y-m-d H:i:s')) && $liste['id_statut'] == 1)
      			{
      				$dev = new Devisclass((int)($liste['id_devis']));
      				$dev->id_statut = 4; //expiré
      				$dev->update();
      				
      				// + mail client (mail dans la langue du devis)
      				$lang 		= new Language($dev->id_lang);
      				$customer 	= new Customer($liste['id_customer']); 
					$id_lang 	= $liste['id_lang'];
					if(!in_array($id_lang, $tab_lang))
						$id_lang = Configuration::get('PS_LANG_DEFAULT');
					$dev_trad 	= new DevisPerso();
					$objet  	= $dev_trad->getL('A quote request has expired !');
					Mail::Send(
      					$id_lang, 
      					'devis_expiration', 
      					$objet, 
						array(	'{id_devis}'	=> $liste['id_devis'],
								'{color}'		=> ($color=Tools::safeOutput(Configuration::get('PS_MAIL_COLOR')) != null ? $color : '#db3484')), 
						$customer->email, 
						$customer->firstname.' '.$customer->lastname, 
						strval(Configuration::get('PS_SHOP_EMAIL')), 
						strval(Configuration::get('PS_SHOP_NAME')), 
						NULL, NULL, _PS_MODULE_DIR_.'devisperso/mails/');
      			}
      		}
  
      		return parent::getList($this->context->language->id, !Tools::getValue($this->table.'Orderby') ? 'date_demande' : NULL, !Tools::getValue($this->table.'Orderway') ? 'DESC' : NULL);
      	}
      	
      	
      	/* validation du formulaire (AdminTab) */ 
		public function postProcess()
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
			// Create or update an object 
			if (Tools::getValue('submitAdddevis'))
			{
				// Checking fields validity 
				$this->validateRules();
				
				
				if (!sizeof($this->errors))
				{
					$id = intval(Tools::getValue('id_'.$this->table));
	
					// Object update 
					if (isset($id) AND !empty($id))
					{
						if ($this->tabAccess['edit'] === '1')
						{
							//si le total HT est =0 : erreur
							if($_POST['total_out_shipp'] == 0)
							{
								$this->errors[] = Tools::displayError($this->l('Total whithout shipping cost is required'));
							}
							else if($_POST['reponse'] == '')
							{
								$this->errors[] = Tools::displayError($this->l('Answer is required'));
							}
							else if (($_POST['reponse'] != '' || $_POST['total_devis'] != 0) && $_POST['id_statut'] == 1)
							{
								//Si on une réponse ou un total et qu'on est en attente : envoi mail au client
						    	$_POST['date_reponse'] = date('Y-m-d H:i:s');
							
								$currency 	= new Currency($_POST['id_currency']);
							    $customer 	= new Customer($_POST['id_customer']);
								$id_lang 	= $_POST['id_lang'];
								if(!in_array($id_lang, $tab_lang))
									$id_lang = Configuration::get('PS_LANG_DEFAULT');
								$dev_trad 	= new DevisPerso();
								$objet  	= $dev_trad->getL('An answer for your quote request !');
								Mail::Send(
					            	$id_lang, 
					            	'devis_proposition', 
					            	$objet, 
					            	array(
					            		'{demande}' 	=> $_POST['demande'], 
					            		'{reponse}' 	=> $_POST['reponse'], 
					            		'{total_devis}' => $_POST['total_devis'], 
					            		'{currency}' 	=> $currency->sign,
					            		'{firstname}'	=> $customer->firstname, 
					            		'{lastname}' 	=> $customer->lastname,
					            		'{id_devis}' 	=> $id,
					            		'{devis_link}'	=> $this->context->link->getModuleLink('devisperso', 'formdevis', array('id_devis'=>$id)),
					            		'{color}'		=> ($color=Tools::safeOutput(Configuration::get('PS_MAIL_COLOR')) != null ? $color : '#db3484')),
					            	$customer->email, 
					            	$customer->firstname.' '.$customer->lastname, 
					            	strval(Configuration::get('PS_SHOP_EMAIL')), 
					            	strval(Configuration::get('PS_SHOP_NAME')), 
					            	NULL, NULL, _PS_MODULE_DIR_.'devisperso/mails/');
							}
							
							// Si le statut a été changé en "annulé" envoie de mail au client (ds la langue du devis) 
							if ($_POST['id_statut'] == 3)
							{
								$lang 		= new Language($_POST['id_lang']);
								$customer 	= new Customer($_POST['id_customer']);
								$id_lang 	= $_POST['id_lang'];
								if(!in_array($id_lang, $tab_lang))
									$id_lang = Configuration::get('PS_LANG_DEFAULT');
								$dev_trad 	= new DevisPerso();
								$objet  	= $dev_trad->getL('A quote request has been cancelled !');
								Mail::Send(
									$id_lang, 
									'devis_annulation', 
									$objet, 
									array(	'{id_devis}' 	=> $id,
											'{color}'		=> ($color=Tools::safeOutput(Configuration::get('PS_MAIL_COLOR')) != null ? $color : '#db3484')), 
									$customer->email, 
									$customer->firstname.' '.$customer->lastname, 
									strval(Configuration::get('PS_SHOP_EMAIL')), 
									strval(Configuration::get('PS_SHOP_NAME')), 
									NULL, NULL, _PS_MODULE_DIR_.'devisperso/mails/');
							}
						}
					}
					// Object creation 
					else
					{
						if ($this->tabAccess['add'] === '1')
						{
							if(!isset($_POST['date_demande']))
								$_POST['date_demande'] = date('Y-m-d H:i:s');
						}
					}
				}
			}
			return parent::postProcess();
		}
    }    
?>