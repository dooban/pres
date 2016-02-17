<?php

if (!defined('_PS_VERSION_'))
{
	exit;
}

class QuickOrderForm15 extends Module
{
	/* Title associated to the image */

	public $qof_title;

	/* Name of the image without extension */
	public $qof_imgname;

	/* Image path with extension */
	public $qof_img;

	public function __construct()
	{
		$this->name = 'quickorderform15';
		$this->tab = 'checkout';
		$this->version = '1.3';
		$this->module_key = '9021e2d24ac6c563b7204df1c388fc3d';
		$this->need_instance = 0;
		$this->author = 'Ixycom';

		$this->controllers = array('actions', 'default');

		parent::__construct();

		$this->displayName = $this->l('Quick Order Form');
		$this->description = $this->l('Adds a form to order quicker.');

		$this->initialize();
	}

	/*
	 * Set the properties of the module, like the link to the image and the title (contextual to the current shop context)
	 */

	protected function initialize()
	{
		$this->qof_imgname = 'quickorderform';
		if ((Shop::getContext() == Shop::CONTEXT_GROUP || Shop::getContext() == Shop::CONTEXT_SHOP) && file_exists(_PS_MODULE_DIR_ . $this->name . '/' . $this->qof_imgname . '-g' . $this->context->shop->getContextShopGroupID() . '.' . Configuration::get('BLOCKQUICKORDER_IMG_EXT')))
		{
			$this->qof_imgname .= '-g' . $this->context->shop->getContextShopGroupID();
		}
		if (Shop::getContext() == Shop::CONTEXT_SHOP && file_exists(_PS_MODULE_DIR_ . $this->name . '/' . $this->qof_imgname . '-s' . $this->context->shop->getContextShopID() . '.' . Configuration::get('BLOCKQUICKORDER_IMG_EXT')))
		{
			$this->qof_imgname .= '-s' . $this->context->shop->getContextShopID();
		}
		$this->qof_img = Tools::getMediaServer($this->name) . _MODULE_DIR_ . $this->name . '/' . $this->qof_imgname . '.' . Configuration::get('BLOCKQUICKORDER_IMG_EXT');
		$this->qof_title = htmlentities(Configuration::get('BLOCKQUICKORDER_TITLE'), ENT_QUOTES, 'UTF-8');
	}

	public function install()
	{
		Configuration::updateGlobalValue('BLOCKQUICKORDER_TITLE', $this->l('Quick order form'));
		// Try to update with the extension of the image that exists in the module directory
		foreach (scandir(_PS_MODULE_DIR_ . $this->name) as $file)
			if (in_array($file, array('quickorderform.jpg', 'quickorderform.gif', 'quickorderform.png')))
			{
				Configuration::updateGlobalValue('BLOCKQUICKORDER_IMG_EXT', substr($file, strrpos($file, '.') + 1));
			}

		return (parent::install() && $this->registerHook('displayMyAccountBlock') && $this->registerHook('displayLeftColumn') && $this->registerHook('displayCustomerAccount') && $this->registerHook('displayHeader')
				);
	}

	public function uninstall()
	{
		Configuration::deleteByName('BLOCKQUICKORDER_TITLE');
		Configuration::deleteByName('BLOCKQUICKORDER_IMG_EXT');
		return (parent::uninstall());
	}

	/**
	 * _deleteCurrentImg delete current image, (so this will use default image)
	 *
	 * @return void
	 */
	private function _deleteCurrentImg()
	{
		// Delete the image file
		if ($this->qof_imgname != 'quickorderform' && file_exists(_PS_MODULE_DIR_ . $this->name . '/' . $this->qof_imgname . '.' . Configuration::get('BLOCKQUICKORDER_IMG_EXT')))
		{
			unlink(_PS_MODULE_DIR_ . $this->name . '/' . $this->qof_imgname . '.' . Configuration::get('BLOCKQUICKORDER_IMG_EXT'));
		}

		// Update the extension to the global value or the shop group value if available
		Configuration::deleteFromContext('BLOCKQUICKORDER_IMG_EXT');
		Configuration::updateValue('BLOCKQUICKORDER_IMG_EXT', 'jpg');

		// Reset the properties of the module
		$this->initialize();
	}

	/**
	 * postProcess update configuration
	 *
	 * @var string
	 * @return void
	 */
	public function postProcess()
	{
		if (Tools::isSubmit('submitDeleteImgConf'))
			$this->_deleteCurrentImg();

		$errors = '';
		if (Tools::isSubmit('submitAdvConf'))
		{
			if (isset($_FILES['qof_img']) && isset($_FILES['qof_img']['tmp_name']) && !empty($_FILES['qof_img']['tmp_name']))
			{
				if (!ImageManager::isCorrectImageFileExt($_FILES['qof_img']['name']))
					$errors .= $this->l('Error with file extension');
				elseif ($_FILES['qof_img']['size'] > Tools::convertBytes(ini_get('upload_max_filesize')))
					$errors .= $this->l('Error with file size');
				else
				{
					Configuration::updateValue('BLOCKQUICKORDER_IMG_EXT', substr($_FILES['qof_img']['name'], strrpos($_FILES['qof_img']['name'], '.') + 1));

					// Set the image name with a name contextual to the shop context
					$this->qof_imgname = 'quickorderform';
					if (Shop::getContext() == Shop::CONTEXT_GROUP)
						$this->qof_imgname = 'quickorderform-g' . (int) $this->context->shop->getContextShopGroupID();
					elseif (Shop::getContext() == Shop::CONTEXT_SHOP)
						$this->qof_imgname = 'quickorderform-s' . (int) $this->context->shop->getContextShopID();

					// Copy the image in the module directory with its new name
					if (!move_uploaded_file($_FILES['qof_img']['tmp_name'], _PS_MODULE_DIR_ . $this->name . '/' . $this->qof_imgname . '.' . Configuration::get('BLOCKQUICKORDER_IMG_EXT')))
						$errors .= $this->l('Error move uploaded file');
				}
			}

			// If the title is not set, then delete it in order to use the next default value (either the global value or the group value)
			if ($title = Tools::getValue('qof_title'))
				Configuration::updateValue('BLOCKQUICKORDER_TITLE', $title);
			elseif (Shop::getContext() == Shop::CONTEXT_SHOP || Shop::getContext() == Shop::CONTEXT_GROUP)
				Configuration::deleteFromContext('BLOCKQUICKORDER_TITLE');

			// Reset the module properties
			$this->initialize();
		}
		if ($errors)
			echo $this->displayError($errors);
	}

	/**
	 * getContent used to display admin module form
	 *
	 * @return string output
	 */
	public function getContent()
	{
		$this->postProcess();

		$output = '<h2>' . $this->displayName . '</h2>';

		$output .= $this->getImageBlock();

		$output .= $this->getDoc();

		$output .= '<br class="clear" /><a href="http://www.ixycom.com" title="Agence web à Lille" target="_blank"><img style="display: block; margin: 20px auto;" src="http://www.ixycom.com/images/logo-300.jpg" alt="Logo Ixycom" width="300" height="134" /></a>';

		return $output;
	}

	public function getImageBlock()
	{
		$this->postProcess();
		$output = '<form action="' . Tools::safeOutput($_SERVER['REQUEST_URI']) . '" method="post" enctype="multipart/form-data">
							<fieldset style="float: left;width: 70%;margin-right: 2%;">
								<legend style="line-height: 32px;">' . $this->l('Quick Order Form block configuration') . '</legend>
								';
		if ($this->qof_img)
		{
			$output .= '<div style="margin-right: 10px;width: 250px;float: right; text-align: center;"><img src="' . $this->context->link->protocol_content . $this->qof_img . '"
										alt="' . $this->qof_title . '" title="' . $this->qof_title . '" style="margin: 10px auto;margin-top: 0;display: block;height:163px;width:163px"/>';

			if ($this->qof_imgname == 'quickorderform')
				$output .= $this->l('You cannot delete the default image (but you can change it beside).');
			else
				$output .= '<input class="button" type="submit" name="submitDeleteImgConf" value="' . $this->l('Delete image') . '" style=""/>';
		}
		else
		{
			$output .= '<div style="margin-left: 100px;width:163px;">' . $this->l('no image') . '</div>';
		}

		$output .= '</div>';
		$output .= '
				<label for="qof_img">' . $this->l('Change image') . '&nbsp;&nbsp;</label>
				<div class="margin-form">
					<input id="qof_img" type="file" name="qof_img" />
					<p>' . $this->l('Image will be displayed as 155x163.') . '</p>
				</div>
				<label for="qof_title">' . $this->l('Title') . '</label>
				<div class="margin-form">
					<input id="qof_title" type="text" name="qof_title" value="' . $this->qof_title . '" style="width:250px" />
				</div>
				<div class="margin-form">
					<input class="button" type="submit" name="submitAdvConf" value="' . $this->l('Validate') . '"/>
				</div>
				<br class="clear"/>
			</fieldset>
		</form>';
		return $output;
	}

	public function getDoc()
	{
		global $cookie;

		$moncodeiso = LanguageCore::getIsoById($cookie->id_lang);

		$ladoc = '<fieldset style="float: left; width: 20%;">';
		$ladoc .= '<legend style="line-height: 32px;"><img src="' . _PS_BASE_URL_ . __PS_BASE_URI__ . 'modules/' . $this->name . '/logo.png" alt="" /> ' . $this->l('Documentation') . '</legend>';
		if (isset($moncodeiso) && (!empty($moncodeiso)))
		{
			$ladoc .= '<a href="http://www.ixycom.com/doc-modules/' . $this->name . '/readme_' . $moncodeiso . '.pdf" title="' . $this->l('Documentation') . '" target="_blank">' . $this->l('Documentation') . '</a>';
		}
		else
		{
			$ladoc .= '<a href="http://www.ixycom.com/doc-modules/' . $this->name . '/readme_fr.pdf" title="' . $this->l('Documentation fr') . '" target="_blank">' . $this->l('Documentation fr') . '</a><br />';
			$ladoc .= '<a href="http://www.ixycom.com/doc-modules/' . $this->name . '/readme_en.pdf" title="' . $this->l('Documentation en') . '" target="_blank">' . $this->l('Documentation en') . '</a><br />';
			$ladoc .= '<a href="http://www.ixycom.com/doc-modules/' . $this->name . '/readme_es.pdf" title="' . $this->l('Documentation es') . '" target="_blank">' . $this->l('Documentation es') . '</a>';
		}
		$ladoc .= '</fieldset>';

		return $ladoc;
	}

	public function hookDisplayHeader()
	{
		$this->context->controller->addCSS($this->_path . 'quickorderform.css', 'all');
		$this->context->controller->addCSS(_PS_CSS_DIR_ . 'jquery.autocomplete.css', 'all');
		$this->context->controller->addJS(_PS_JS_DIR_ . 'jquery/jquery.autocomplete.js');
	}

	public function hookDisplayLeftColumn()
	{
		$this->smarty->assign(array('image' => $this->context->link->protocol_content . $this->qof_img,
			'qof_title' => $this->qof_title));

		return $this->display(__FILE__, 'left-column.tpl');
	}

	public function hookDisplayRightColumn($params)
	{
		return $this->hookDisplayLeftColumn($params);
	}

	public function hookDisplayMyAccountBlock($params)
	{
		$this->context->smarty->assign('myAccount', false);
		return $this->display(__FILE__, 'myaccount.tpl');
	}

	public function hookDisplayCustomerAccount($params)
	{
		$this->context->smarty->assign('myAccount', true);
		return $this->display(__FILE__, 'myaccount.tpl');
	}

}
