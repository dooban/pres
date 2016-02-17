<?php 

class devispersodefaultModuleFrontController extends ModuleFrontController 
{

	public function __construct()
	{
		$this->display_column_left = false;
		parent::__construct();

		$this->context = Context::getContext();

		include_once($this->module->getLocalPath().'models/Devisclass.php');
	}
	
	public function initContent() {

		parent :: initContent();

		if ($this->context->customer->isLogged())
		{
			$devis = new Devisclass(Tools::getValue('id_devis'));
		    if (Tools::isSubmit('approve') || Tools::isSubmit('pay'))
		    {	
		    	if ($devis->total_out_shipp == 0)
		    	{
		    		$this->errors = 'Total = 0';
		    	}
		    	else 
		    	{
		    		$devis->id_statut = 2; //'Approuved';
		    		$this->displayPayment($devis);
		    	}
		    }
		    elseif (Tools::isSubmit('cancel'))
		    {
		    	$devis->id_statut = 3;//'Cancelled';
		    	$devis->update();
		    	
		    	// + mail admin si client annule son devis (dans la langue par defaut)
		    	$lang 		= new Language((int)($this->context->language->id));
		    	$customer 	= new Customer($devis->id_customer);
				$dev_trad 	= new DevisPerso();
				$objet  	= $dev_trad->getL('A quote request has been cancelled !');
		    	Mail::Send(
		    		Configuration::get('PS_LANG_DEFAULT'), 
		    		'devis_annulation', 
		    		$objet,
					array(	'{id_devis}' 	=> $devis->id,
							'{color}'		=> ($color=Tools::safeOutput(Configuration::get('PS_MAIL_COLOR')) != null ? $color : '#db3484')), 
					strval(Configuration::get('PS_SHOP_EMAIL')), 
					strval(Configuration::get('PS_SHOP_NAME')),
					strval(Configuration::get('PS_SHOP_EMAIL')), 
					strval(Configuration::get('PS_SHOP_NAME')), 
					NULL, NULL, _PS_MODULE_DIR_.'devisperso/mails/');
		    }
		    elseif (Tools::getValue('page')=='formdevis')
		    {
		    	Tools::redirect($this->context->link->getModuleLink('devisperso', 'formdevis', array('id_devis'=>$devis->id))); //redirige vers formdevis.php
		    }
		
			//Récupère les devis du client pour les afficher en liste
			$list_devis = Devisclass::getCustomerDevis((int)($this->context->customer->id));
			foreach ($list_devis as $dev)
			{
				// si le devis à été approuvé sans etre payé
				//mise à jour d'un devis terminé dans le hook orderConfirmation
				//et plus à l'affciahge des devis : laisse ici au cas ou l'install ne s'est pas faire ds le hook orderConf....
				if ($dev['id_cart'] != 0)
				{
					$cart = new Cart($dev['id_cart']);
					if ($cart->orderExists() && $dev['id_statut'] != '5')
					{
						$devis_paid = new Devisclass((int)($dev['id_devis']));
						$devis_paid->id_statut = '5';//'Terminé';
						$devis_paid->updateQteProduct();
						$devis_paid->update();
						
					}
				}
				//mise à jour des devis expirés à l'affichage de la liste
				// si le devis est expiré et statut différent de "terminé" et expiré + envoie de mail à admin 
				if (($dev['date_expiration'] <= date('Y-m-d H:i:s')) && $dev['id_statut'] != 5 && $dev['id_statut'] != 4)
	      		{
	      			$dev_statut = new Devisclass((int)($dev['id_devis']));
	      			$dev_statut->id_statut = 4;
	      			$dev_statut->update();
	
	      			// + envoie de mail admin (dans la langue par défaut)
	      			$lang 		= new Language($cookie->id_lang);
	      			$customer 	= new Customer($dev['id_customer']);
					$dev_trad 	= new DevisPerso();
					$objet  	= $dev_trad->getL('A quote request has expired !');
	      			Mail::Send(
	      				Configuration::get('PS_LANG_DEFAULT'),
	      			 	'devis_expiration', 
	      				$objet, 
						array(
							'{id_devis}' 	=> $dev['id_devis'], 
							'{shop_name}' 	=> strval(Configuration::get('PS_SHOP_NAME')),
							'{color}'		=> ($color=Tools::safeOutput(Configuration::get('PS_MAIL_COLOR')) != null ? $color : '#db3484')), 
						strval(Configuration::get('PS_SHOP_EMAIL')), 
						strval(Configuration::get('PS_SHOP_NAME')),
						strval(Configuration::get('PS_SHOP_EMAIL')), 
						strval(Configuration::get('PS_SHOP_NAME')),
						NULL, NULL, _PS_MODULE_DIR_.'devisperso/mails/');
	      		}
			}
			//on recharge la liste avec les mises à jour
			$list_devis = Devisclass::getCustomerDevis((int)($this->context->customer->id));

			//efface la trace de la vente pour ne pas voir le produit Devis dans les meilleurs ventes 
			//Fait dans le hook orderConfirmation : mais la ligne est ajouté à product_sale lors du changement de statut de la commane ds le BO 
			ProductSale::removeProductSale(Configuration::get('DEVIS_PROD'));
			
			//efface des produit deja vu si le client est allé sur la fiche prod devis
			$tab_prod_view = explode(',',$this->context->cookie->viewed);
			foreach($tab_prod_view as $id_prod_view)
			{
				if($id_prod_view == Configuration::get('DEVIS_PROD'))
				{
					$key = array_search($id_prod_view, $tab_prod_view);
					unset($tab_prod_view[$key]);
				}
			}
			$list_id = implode(',',$tab_prod_view);
			$this->context->cookie->viewed = $list_id;
			
			// récup les statuts dans un tableau
			$statut = Devisclass::getStatut((int)($this->context->language->id));
			$tab_stat = array();
			foreach ($statut as $v)
				$tab_stat[$v['id_statut']] = $v['statut_desc'];
			
			//verif l'existance des icones pour affichage
			$img_account 	= false;
			$img_home 		= false;
			
			if(file_exists(_PS_THEME_DIR_.'img/icon/my-account.gif'))
				$img_account = true;
			
			if(file_exists(_PS_THEME_DIR_.'img/icon/home.gif'))
				$img_home = true;
				
			
			$this->context->smarty->assign(array(
					'devis' 	=> $list_devis,
					'statut'	=> $tab_stat,
					'errors' 	=> $this->errors,
					'token'		=> Tools::getToken(false),
					'img_account'=> $img_account,
					'img_home'	=> $img_home
				)
			);
	
			$this->setTemplate('listdevis.tpl');
		}		
		else
		{
			$url = urlencode($this->context->link->getModuleLink('devisperso', 'default'));
			if (version_compare(_PS_VERSION_,'1.5.6.1','<'))
				$url = str_replace('index', 'index.php', $url); //.php bien présent dans $url mais est supprimé par Tools::redirect
			//a partir de 1.5.5 le back ne fonctionne plus
			if (version_compare(_PS_VERSION_,'1.5.5','>'))
				Tools::redirect('index.php?controller=authentication');
			else
				Tools::redirect('index.php?controller=authentication&back='.$url);
		}
				//manque index.php dans le getModule link du context !!! BUG PS
	}
	
	//modif le produit devis et l'ajoute au panier puis redirige à la dernière étape de commande
	function displayPayment($dev)
	{
		// pour forcer l'ajout au panier du devis(=produit)
		$this->context->cart->add();
		$this->context->cookie->id_cart 			= (int)$this->context->cart->id; //remplit le panier
		$this->context->cart->id_address_delivery 	= $dev->id_address_delivery;
		$this->context->cart->id_address_invoice 	= $dev->id_address_invoice;
		$this->context->cart->id_carrier 			= $dev->id_carrier;
		$this->context->cart->id_currency 			= $dev->id_currency;
		$this->context->cart->id_lang 				= $dev->id_lang;
		$this->context->cart->id_customer			= $dev->id_customer;
		$this->context->cookie->check_cgv			= 1; //accepte les conditions générales vente
		
		//change le prix du produit devis en total devis sans frais de port et ajoute le prod au panier
		$id_prod 	= Configuration::get('DEVIS_PROD'); 
		$prod 		= new Product($id_prod);
		
		//recup l'id_tax_rule_group en fonction de la taxe sélectionné pour le devis et du pays par defaut
		$idRulesGroup = $dev->getIdTaxRulesGroup($dev->id_tax, $this->context->country->id); //country par defaut
		$prod->id_tax_rules_group = $idRulesGroup;
		
		$prod->price = $dev->total_out_shipp;
		$prod->weight = $dev->poids_devis;
		$prod->update();
		
		//mets a jour la qte dans le panier
		$this->context->cart->updateQty(1,$id_prod);
		
		$orderTotal = Tools::convertPrice($dev->total_devis,$this->context->currency); 
		if ($dev->free_shipp)
			$this->context->cart->addCartRule(Configuration::get('DEVIS_DISCOUNT'));
		
		$this->context->cart->update();
		
		//mis a jour id cart du devis
		$dev->id_cart = $this->context->cart->id;
	    $dev->update();
		   
		Tools::redirect($this->context->link->getPageLink('order',false,null,array('step'=>'3')));
	}

}