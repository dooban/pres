<?php
	if (!defined('_PS_VERSION_'))
		exit;
  
    class DevisPerso extends Module
    {
		private $html = '';
		
		/* Constructeur du Module */
    	public function __construct()
        {
        	$this->name 		= 'devisperso';
			$this->version 		= '1.5.2';
			$this->module_key 	= '6cf3d0cb3f331f8ac9afa3987d187009';
			$this->tab 			= 'pricing_promotion';
			$this->author 		= 'MH';
			
			$this->displayName 	= $this->l('Customized Quote');
			$this->description 	= $this->l('Suggest customized quote request system to your customers');
			
			parent::__construct();
        }
    	
		/* Installation du module dans le BO */
		public function install()
		{
			//tab 
			$tab_admin = array();
			
			//recup les langues
			$lang 		= Language::getLanguages(false);
			foreach($lang as $language)
			{
				if ($language['iso_code'] == 'fr')
				{
					$tab_admin[$language['id_lang']] = 'Devis';
				}
				elseif ($language['iso_code'] == 'en')
				{
					$tab_admin[$language['id_lang']] = 'Quote';
				}
				elseif ($language['iso_code'] == 'es')
				{
					$tab_admin[$language['id_lang']] = 'Presupuesto';
				}
				else
				{
					$tab_admin[$language['id_lang']] = 'Quote';
				} 
			}
			
			$id_tab = Tab::getIdFromClassName("AdminOrders");
			
			if (!parent::install() 
				OR !$this->registerHook('displayCustomerAccount')
				OR !$this->registerHook('displayRightColumn')
				OR !$this->registerHook('displayHeader')
				OR !Configuration::updateValue('DEVIS_DESCRIPTION_FR', NULL)
				OR !Configuration::updateValue('DEVIS_DESCRIPTION_EN', NULL)
				OR !Configuration::updateValue('DEVIS_DESCRIPTION_ES', NULL) 
				OR !Configuration::updateValue('DEVIS_PROD', NULL)
				OR !Configuration::updateValue('DEVIS_EXP_AUTO', NULL)
				OR !Configuration::updateValue('DEVIS_NB_JOUR_EXP', NULL)
				OR !Configuration::updateValue('DEVIS_CAT', NULL)
				OR !Configuration::updateValue('DEVIS_DISCOUNT', NULL)
				OR !Configuration::updateValue('DEVIS_PROD_LINK', 1)
				OR !$this->installModuleTab(	'AdminDevis', 
												$tab_admin,
											 	$id_tab)
				)
				return false;
			
			$this->registerHook('displayMyAccountBlock');
			$this->registerHook('displayPDFInvoice');
			$this->registerHook('displayOrderConfirmation');
			$this->registerHook('displayProductButtons');
			
			 // Cr�ation de la table "Devis"
			$createTable = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'devis` (
								`id_devis` int(10) NOT NULL AUTO_INCREMENT,
								`id_carrier` int(10) NOT NULL,
								`id_lang` int(10) NOT NULL,
								`id_currency` int(10) NOT NULL,
								`id_customer` int(10) NOT NULL,
								`id_address_delivery` int(10) NOT NULL,
								`id_address_invoice` int(10) NOT NULL,
								`id_cart` int(10),
								`id_order` int(10),
								`id_statut` int(10),
								`id_tax` int(10),
								`poids_devis` float,
								`total_devis` decimal(17,2), 
								`total_out_shipp` decimal(17,2),
								`total_shipping` decimal(17,2),
								`free_shipp` bool,
								`date_demande` datetime,
								`date_reponse` datetime NULL,
								`date_expiration` datetime,
								`demande` text NOT NULL,
								`reponse` text,
								`desc_invoice` text,
								PRIMARY KEY  (`id_devis`)
								) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8';

			// Cr�ation de la table "StatutDevis"
			$createTableStatut = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'devis_statut` (
								`id_statut` int(10) NOT NULL,
								`id_lang` int(10) NOT NULL,
								`statut_desc` text NOT NULL,
								`color` varchar(32) DEFAULT NULL,
								PRIMARY KEY (`id_statut`, `id_lang`)
								) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8';
			
			$insertStatut = 'INSERT INTO `'._DB_PREFIX_.'devis_statut` 
								(`id_statut`, `id_lang`, `statut_desc`, `color`) VALUES ';
			foreach($lang as $language)
			{
				if ($language['iso_code'] == 'fr')
				{
					$insertStatut .= '(1, '.$language['id_lang'].', \'Attente\', \'lightblue\'),
										(2, '.$language['id_lang'].', \'Approuvé\', \'#FFDD99\'),
										(3, '.$language['id_lang'].', \'Annulé\', \'#FFFFBB\'),
										(4, '.$language['id_lang'].', \'Expiré\', \'#FFDFDF\'),
										(5, '.$language['id_lang'].', \'Terminé\', \'#DDFFAA\'),';
				}
				elseif ($language['iso_code'] == 'en')
				{
					$insertStatut .= '(1, '.$language['id_lang'].', \'Wait\', \'lightblue\'),
										(2, '.$language['id_lang'].', \'Approved\', \'#FFDD99\'),
										(3, '.$language['id_lang'].', \'Cancelled\', \'#FFFFBB\'),
										(4, '.$language['id_lang'].', \'Expired\', \'#FFDFDF\'),
										(5, '.$language['id_lang'].', \'Done\', \'#DDFFAA\'),';
				}
				elseif ($language['iso_code'] == 'es')
				{
					$insertStatut .= '(1, '.$language['id_lang'].', \'Espera\', \'lightblue\'),
										(2, '.$language['id_lang'].', \'Aprobado\', \'#FFDD99\'),
										(3, '.$language['id_lang'].', \'Cancelado\', \'#FFFFBB\'),
										(4, '.$language['id_lang'].', \'Caducado\', \'#FFDFDF\'),
										(5, '.$language['id_lang'].', \'Terminado\', \'#DDFFAA\'),';
				}
				else
				{
					$insertStatut .= '(1, '.$language['id_lang'].', \'Wait\', \'lightblue\'),
										(2, '.$language['id_lang'].', \'Approved\', \'#FFDD99\'),
										(3, '.$language['id_lang'].', \'Cancelled\', \'#FFFFBB\'),
										(4, '.$language['id_lang'].', \'Expired\', \'#FFDFDF\'),
										(5, '.$language['id_lang'].', \'Done\', \'#DDFFAA\'),';
				} 
			}

			//on enleve le dernier caractere de la requete qui est une virgule
			$insertStatut= substr($insertStatut, 0, -1);
			
			if (!Db::getInstance()->execute($createTable))
				return false;
				
			if (!Db::getInstance()->execute($createTableStatut))
				return false;
				
			if (!Db::getInstance()->execute($insertStatut))
				return false;
			
			//ajout de la catégorie 
			$cat_devis 	= new Category();
			foreach ($lang as $language) 
			{
				if ($language['iso_code'] == 'fr')
				{
					$cat_devis->name[$language['id_lang']] = 'DevisCat';
					$cat_devis->link_rewrite[$language['id_lang']] = 'deviscat';
				}
				elseif ($language['iso_code'] == 'en')
				{
					$cat_devis->name[$language['id_lang']] = 'QuoteCat';
					$cat_devis->link_rewrite[$language['id_lang']] = 'deviscat';
				}
				elseif ($language['iso_code'] == 'es')
				{
					$cat_devis->name[$language['id_lang']] = 'PresupuestoCat';
					$cat_devis->link_rewrite[$language['id_lang']] = 'deviscat';
				}
				else
				{
					$cat_devis->name[$language['id_lang']] = 'QuoteCat';
					$cat_devis->link_rewrite[$language['id_lang']] = 'deviscat';
				}
			}
			$cat_devis->id_parent = 0;
			$cat_devis->level_depth = 0;
			$cat_devis->active = 0;
			$cat_devis->nleft = 0;
			$cat_devis->nright = 0;
			$cat_devis->position = 0;

			$cat_devis->add();
			Configuration::updateValue('DEVIS_CAT', (int)($cat_devis->id));
		
			//ajout du produit
			$prod_devis 	= new Product();
			foreach ($lang as $language) 
			{
				if ($language['iso_code'] == 'fr')
				{
					$prod_devis->name[$language['id_lang']] = 'Devis';
					$prod_devis->link_rewrite[$language['id_lang']] = 'devis';
				}
				elseif ($language['iso_code'] == 'en')
				{
					$prod_devis->name[$language['id_lang']] = 'Quote';
					$prod_devis->link_rewrite[$language['id_lang']] = 'devis';
				}
				elseif ($language['iso_code'] == 'es')
				{
					$prod_devis->name[$language['id_lang']] = 'Presupuesto';
					$prod_devis->link_rewrite[$language['id_lang']] = 'devis';
				}
				else 
				{
					$prod_devis->name[$language['id_lang']] = 'Quote';
					$prod_devis->link_rewrite[$language['id_lang']] = 'devis';
				}
			}
			
			
			$prod_devis->price = 1;
			$prod_devis->id_category_default = (int)($cat_devis->id);
			$prod_devis->active = 1;
			$prod_devis->weight = 1;
			$prod_devis->visibility = 'none';
			$prod_devis->id_tax_rules_group = 0;
			$prod_devis->date_add = date('Y-m-d H:i:s');
			$prod_devis->add(false);
			StockAvailable::setProductOutOfStock($prod_devis->id, 1);//par defaut accepte les commande si pas de stock
			StockAvailable::setQuantity($prod_devis->id, 0, 1000);
			Configuration::updateValue('DEVIS_PROD', (int)($prod_devis->id));
			
			//ajout des images du produit
			$insertImage = 'INSERT INTO `'._DB_PREFIX_.'image` 
								(`id_product`, `position`, `cover`) VALUES
								('.$prod_devis->id.', 1, 1)';
			Db::getInstance()->execute($insertImage);
			$res 		= Db::getInstance()->executeS('SELECT LAST_INSERT_ID() AS last_id');
			$id_img 	= $res[0]['last_id'];
			$tab_dir 	= str_split($id_img);
			$dir_img 	= "";
			foreach($tab_dir as $dir)
				$dir_img .= $dir."/";
				
			$file 		= _PS_MODULE_DIR_.'devisperso/img/img_p.jpg';
			$newfile 	= _PS_PROD_IMG_DIR_.$dir_img.$id_img.'.jpg';
			$newfile_home = _PS_PROD_IMG_DIR_.$dir_img.$id_img.'-home_default.jpg';
			$newfile_large = _PS_PROD_IMG_DIR_.$dir_img.$id_img.'-large_default.jpg';
			$newfile_medium = _PS_PROD_IMG_DIR_.$dir_img.$id_img.'-medium_default.jpg';
			$newfile_small = _PS_PROD_IMG_DIR_.$dir_img.$id_img.'-small_default.jpg';
			$newfile_thickbox = _PS_PROD_IMG_DIR_.$dir_img.$id_img.'-thickbox_default.jpg';
			
			$tab_img = array("$newfile_home"=>124, "$newfile_large"=>264, "$newfile_medium"=>58, "$newfile_small"=>45);
			
			if(!is_dir(_PS_PROD_IMG_DIR_.$dir_img))
				mkdir(_PS_PROD_IMG_DIR_.$dir_img);
			if (copy($file, $newfile) && copy($file, $newfile_thickbox)) 
			{
				chdir(_PS_PROD_IMG_DIR_.$dir_img);
				
				foreach($tab_img as $nom=>$format)
				{
					// Chargement
					$thumb 	= imagecreatetruecolor($format, $format);
					$source = imagecreatefromjpeg($file);
					
					// Redimensionnement
					imagecopyresized($thumb, $source, 0, 0, 0, 0, $format, $format, 600, 600);
					imagejpeg($thumb, $nom);
				}
			}
			$insert_img_shop = 'INSERT INTO `'._DB_PREFIX_.'image_shop` 
								(`id_image`, `id_shop`, `cover`) VALUES 
								('.$id_img.', 1, 1)';
			Db::getInstance()->execute($insert_img_shop);
			
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
				else
				{
					$tab_lang[] = $language['id_lang'];
				} 
			}
			foreach ($tab_lang as $id_lang)
			{
				$insert_img_lang = 'INSERT INTO `'._DB_PREFIX_.'image_lang` 
								(`id_image`, `id_lang`) VALUES 
								('.$id_img.', '.$id_lang.')';
				Db::getInstance()->execute($insert_img_lang);
			}
			
			
			//ajoute une ligne dans la table category_product 
			$insertCatProd = 'INSERT INTO `'._DB_PREFIX_.'category_product` 
								(`id_category`, `id_product`, `position`) VALUES
								('.$cat_devis->id.', '.$prod_devis->id.', 0) ';
			if (!Db::getInstance()->execute($insertCatProd))
				return false;
			
			//ajoute le bon de réduction DEVIS
			$discount 	= new CartRule();
			$discount->id_customer 		= 0;
			$discount->date_from 		= date('Y-m-d H:i:s');
			$discount->date_to 			= "2035-12-01";
			$discount->quantity 		= 1000;
			$discount->quantity_per_user= 1000;
			$discount->code				= 'DEVIS';
			$discount->free_shipping 	= true;
			$discount->description		= 'Free shipping cost for personalized quote module / Frais de port offerts pour le module devis personnalise';
			
			$lang = Language::getLanguages(false);
			foreach ($lang as $language) 
			{
				if ($language['iso_code'] == 'fr')
				{
					$discount->name[$language['id_lang']] = 'Frais de port offerts';
				}
				elseif ($language['iso_code'] == 'en')
				{
					$discount->name[$language['id_lang']] = 'Free shipping cost';
				}
				elseif ($language['iso_code'] == 'es')
				{
					$discount->name[$language['id_lang']] = 'Gastos de env&#237;o gratis'; //�
				}
				else
				{
					$discount->name[$language['id_lang']] = 'Free shipping cost';
				}
			}
			$discount->add();
			
			Configuration::updateValue('DEVIS_DISCOUNT',(int)$discount->id);
			
			Configuration::updateValue('DEVIS_EXP_AUTO', '1');
			Configuration::updateValue('DEVIS_NB_JOUR_EXP', '30');
			
			return true;
		}
		
		
		public function uninstall()
		{
			$dev_cat 		= Configuration::get('DEVIS_CAT');
			$dev_prod 		= Configuration::get('DEVIS_PROD');
			$dev_discount 	= Configuration::get('DEVIS_DISCOUNT');
			if (!parent::uninstall() 
				OR !Db::getInstance()->execute('DROP TABLE IF EXISTS`'._DB_PREFIX_.'devis`')
				OR !Db::getInstance()->execute('DROP TABLE IF EXISTS`'._DB_PREFIX_.'devis_statut`')
				OR !$this->unregisterHook('displayCustomerAccount') 
				OR !$this->unregisterHook('displayRightColumn')
				OR !Configuration::deleteByName('DEVIS_DESCRIPTION_FR')
				OR !Configuration::deleteByName('DEVIS_DESCRIPTION_EN')
				OR !Configuration::deleteByName('DEVIS_DESCRIPTION_ES')
				OR !Configuration::deleteByName('DEVIS_PROD')
				OR !Configuration::deleteByName('DEVIS_EXP_AUTO')
				OR !Configuration::deleteByName('DEVIS_NB_JOUR_EXP')
				OR !Configuration::deleteByName('DEVIS_CAT')
				OR !Configuration::deleteByName('DEVIS_DISCOUNT')
				OR !Configuration::deleteByName('DEVIS_PROD_LINK')
				OR !$this->uninstallModuleTab('AdminDevis'))
				return false;
			
			$this->unregisterHook('displayMyAccountBlock');
			$this->unregisterHook('displayPDFInvoice');
			$this->unregisterHook('displayOrderConfirmation');
			$this->unregisterHook('displayProductButtons');
			
			// Suppression de la catégorie "Devis"
			$cat_devis = new Category($dev_cat);
			$cat_devis->delete();
			// Suppression du produit "Devis"
			$prod_devis = new Product($dev_prod);
			$prod_devis->delete();
			// Suppression du discount
			$discount = new CartRule($dev_discount);
			$discount->delete();
			
			return true;
		}
		
		private function installModuleTab($tabClass, $tabName, $idTabParent)
		{
		  $tab 	= new Tab();
		  $tab->name 		= $tabName;
		  $tab->class_name 	= $tabClass;
		  $tab->module 		= $this->name;
		  $tab->id_parent 	= $idTabParent;
		  if(!$tab->save())
			return false;
		  return true;
		}
		private function uninstallModuleTab($tabClass)
		{
		  $idTab = Tab::getIdFromClassName($tabClass);
		  if($idTab != 0)
		  {
			$tab = new Tab($idTab);
			$tab->delete();
			return true;
		  }
		  $tabClass->delete();
		  return false;
		}
		
		
		/* Interface BO >>configurer */
		public function getContent()
		{
			$this->html .= '<h2><img src="'.$this->_path.'logo.png" alt="" />  '.$this->displayName.'</h2>';
			
			if (Tools::isSubmit('submitDevis'))
			{
				Configuration::updateValue('DEVIS_PROD_LINK', Tools::getValue('devis_prod_link'));
				Configuration::updateValue('DEVIS_DESCRIPTION_FR', nl2br(Tools::getValue('devis_desc_fr')));
				Configuration::updateValue('DEVIS_DESCRIPTION_EN', nl2br(Tools::getValue('devis_desc_en')));
				Configuration::updateValue('DEVIS_DESCRIPTION_ES', nl2br(Tools::getValue('devis_desc_es')));
				$this->html .= $this->displayConfirmation($this->l('Settings updated'));
			}
			
			return $this->html.$this->displayForm();
		}
		
		public function displayForm()
		{
			$html = '
				<form action="'.$_SERVER['REQUEST_URI'].'" method="post">
				<fieldset>
					<legend><img src="../img/admin/cog.gif" alt="" title="" />'.$this->l('Settings').'</legend>';
					
					if($this->isRegisteredInHook('displayProductButtons'))
					{
						$value = Configuration::get('DEVIS_PROD_LINK');
						$html .= '
						<label>'.$this->l('Button on the product sheet').'</label>
						<div class="margin-form">
							<p class="clear">'.$this->l('This option allows you to add a "request for a quote" button on each product sheet in the front office.').'
								<br />'.$this->l('This button will appear under the "add to cart" button and will allow a redirection of the customer on the quote form with the description of the product automatically added to the request.').'
							</p>
							<label for="DEVIS_PROD_LINK_on" class="t">
								<img title="'.$this->l('Enabled').'" alt="'.$this->l('Enabled').'" src="../img/admin/enabled.gif">
							</label>
							<input type="radio" value="1" name="devis_prod_link" '.($value==1 ? 'checked="checked"' : '').' />
							<label class="t" for="DEVIS_PROD_LINK_yes">'.$this->l('Enabled').'</label>
							&nbsp;&nbsp;
							<label for="DEVIS_PROD_LINK_off" class="t">
								<img title="'.$this->l('Disabled').'" alt="'.$this->l('Disabled').'" src="../img/admin/disabled.gif">
							</label>
							<input type="radio" value="0" name="devis_prod_link" '.($value==0 ? 'checked="checked"' : '').' />
							<label class="t" for="DEVIS_PROD_LINK_off">'.$this->l('Disabled').'</label>
							<br />
						</div>';
					}
					$html .= '
					<label>'.$this->l('Quote description').'</label>
					<div class="margin-form">
						<br /><br />
						<p class="clear">'.$this->l('Set a brief description of your quote request system.').'
							<br />'.$this->l('This description will appear on the quote form to help your customers.').'
							<br />'.$this->l('All languages are not required.').'
						</p>
						<br />
						<h3>Fran&ccedil;ais</h3>
						<textarea rows="5" cols="70" name="devis_desc_fr" value="'.Configuration::get('DEVIS_DESCRIPTION_FR').'" >'.htmlspecialchars_decode(Configuration::get('DEVIS_DESCRIPTION_FR')).'</textarea>
					</div>
					<div class="margin-form">
						<h3>English</h3>
						<textarea rows="5" cols="70" name="devis_desc_en" value="'.Configuration::get('DEVIS_DESCRIPTION_EN').'" >'.htmlspecialchars_decode(Configuration::get('DEVIS_DESCRIPTION_EN')).'</textarea>
					</div>
					<div class="margin-form">
						<h3>Espa&ntilde;ol</h3>
						<textarea rows="5" cols="70" name="devis_desc_es" value="'.Configuration::get('DEVIS_DESCRIPTION_ES').'" >'.htmlspecialchars_decode(Configuration::get('DEVIS_DESCRIPTION_ES')).'</textarea>
					</div>
					

					<center><input type="submit" name="submitDevis" value="'.$this->l('Save').'" class="button" /></center>
	
				</fieldset>
				</form>
				';
			//echo $html;	
			return $html;
		}
		
		
		/* Accroche du bloc aux "hook" désirés */
		function hookDisplayOrderConfirmation($params)
		{
			include_once($this->getLocalPath().'models/Devisclass.php');
			//recup le devis du client à partir de l'id_cart
			$devis_order = Devisclass::getDevisByCart($params['objOrder']->id_customer, $params['objOrder']->id_cart);
			//si le devis existe bien
			if($devis_order && !empty($devis_order))
			{
				$dev = new Devisclass(intval($devis_order['id_devis']));
				$dev->id_order = $params['objOrder']->id;
				$dev->id_statut = 5;
				$dev->updateQteProduct();
				$dev->update();
				
				//efface la trace de la vente pour ne pas voir le produit Devis dans les meilleurs ventes
				//ici supprime les précédentes meilleures ventes 
				ProductSale::removeProductSale(Configuration::get('DEVIS_PROD'));
			}
		}
    	function hookDisplayPDFInvoice($params)
		{
			include_once($this->getLocalPath().'models/Devisclass.php');
			//recup le devis du client à partir de id_order
			$desc_devis = Devisclass::getDevisByOrder($params['object']->id_order);
			//si il y a une description renseign�e par l'admin on l'affiche sinon rien
			if($desc_devis)
			{
				$this->context->smarty->assign('desc_devis', $desc_devis);
				return $this->display(__FILE__, 'devisperso-invoice.tpl');
			}
			//efface la trace de la vente pour ne pas voir le produit Devis dans les meilleurs ventes
			//l'ajout au meilleures ventes se fait lors du changment de statut de la commande...
			ProductSale::removeProductSale(Configuration::get('DEVIS_PROD'));
		}
		function hookDisplayProductButtons($params)
		{
			$this->context->smarty->assign('productActions', true);
			$this->context->smarty->assign('prod_link', Configuration::get('DEVIS_PROD_LINK'));
			$this->context->smarty->assign('id_prod', $_GET['id_product']);

			//MGM 02/02/16 AÑADIDA ESTA CONDICION PARA QUE SÓLO MUESTRE EL BOTÓN DE PRESUPUESTO CUANDO SEA PARA UN PRODUCTO QUE NO ESTÁ DISPONIBLE PARA LA VENTA
			$product = new Product((int)Tools::getValue('id_product'));

			if ($product->available_for_order == 0)
			 	return $this->display(__FILE__, 'devisperso.tpl');
			//FIN MGM

		}
		function hookDisplayMyAccountBlock($params)
		{
			$this->context->smarty->assign('myAccount', false);
			return $this->display(__FILE__, 'devisperso-myAccount.tpl'); 
		}
		function hookDisplayCustomerAccount($params)
		{
			$this->context->smarty->assign('myAccount', true);
			return $this->display(__FILE__, 'devisperso-myAccount.tpl'); 
		}
		function hookDisplayLeftColumn($params)
		{
			return $this->hookDisplayRightColumn($params);			
		}
		function hookDisplayRightColumn($params)
		{
			$this->context->smarty->assign('productActions', false);
			return $this->display(__FILE__, 'devisperso.tpl'); 
		}
	    public function hookDisplayHeader($params)
		{
			$this->context->controller->addCSS(($this->_path).'css/style_devis.css', 'all');
		}
		
		//on ne peut plus forcer la traduction dans un $id_lang : pas pris en compte par fonction l
	    public function getL($key)
		{
			$trad = array (
				'No Tax' 								=> $this->l('No Tax'),
				'A quote request has expired !' 		=> $this->l('A quote request has expired !'),
				'An answer for your quote request !' 	=> $this->l('An answer for your quote request !'),
				'A quote request has been cancelled !' 	=> $this->l('A quote request has been cancelled !'),
				'A new quote request !'					=> $this->l('A new quote request !')
			);
			
			return (array_key_exists($key, $trad)) ? $trad[$key] : $key;
		}
    }

?>