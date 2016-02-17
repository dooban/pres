<?php

	class Search extends SearchCore
	{
		public static function find($id_lang, $expr, $page_number = 1, $page_size = 1, $order_by = 'position',
			$order_way = 'desc', $ajax = false, $use_cookie = true, Context $context = null)
		{
			if (!$context)
				$context = Context::getContext();
			$db = Db::getInstance(_PS_USE_SQL_SLAVE_);

			// Only use cookie if id_customer is not present
			if ($use_cookie)
				$id_customer = $context->customer->id;
			else
				$id_customer = 0;

			// TODO : smart page management
			if ($page_number < 1) $page_number = 1;
			if ($page_size < 1) $page_size = 1;

			if (!Validate::isOrderBy($order_by) || !Validate::isOrderWay($order_way))
				return false;

			$intersect_array = array();
			$score_array = array();
			$words = explode(' ', Search::sanitize($expr, $id_lang));

			foreach ($words as $key => $word)
				if (!empty($word) && strlen($word) >= (int)Configuration::get('PS_SEARCH_MINWORDLEN'))
				{
					$word = str_replace('%', '\\%', $word);
					$word = str_replace('_', '\\_', $word);
					$intersect_array[] = 'SELECT si.id_product
					FROM '._DB_PREFIX_.'search_word sw
					LEFT JOIN '._DB_PREFIX_.'search_index si ON sw.id_word = si.id_word
					WHERE sw.id_lang = '.(int)$id_lang.'
						AND sw.id_shop = '.$context->shop->id.'
						AND sw.word LIKE
					'.($word[0] == '-'
						? ' \''.pSQL(Tools::substr($word, 1, PS_SEARCH_MAX_WORD_LENGTH)).'%\''
						: '\''.pSQL(Tools::substr($word, 0, PS_SEARCH_MAX_WORD_LENGTH)).'%\''
					);

					if ($word[0] != '-')
						$score_array[] = 'sw.word LIKE \''.pSQL(Tools::substr($word, 0, PS_SEARCH_MAX_WORD_LENGTH)).'%\'';
				}
				else
					unset($words[$key]);

			if (!count($words))
				return ($ajax ? array() : array('total' => 0, 'result' => array()));

			$score = '';
			if (count($score_array))
				$score = ',(
				SELECT SUM(weight)
				FROM '._DB_PREFIX_.'search_word sw
				LEFT JOIN '._DB_PREFIX_.'search_index si ON sw.id_word = si.id_word
				WHERE sw.id_lang = '.(int)$id_lang.'
					AND sw.id_shop = '.$context->shop->id.'
					AND si.id_product = p.id_product
					AND ('.implode(' OR ', $score_array).')
			) position';

			$sql = 'SELECT cp.`id_product`
				FROM `'._DB_PREFIX_.'category_group` cg
				INNER JOIN `'._DB_PREFIX_.'category_product` cp ON cp.`id_category` = cg.`id_category`
				INNER JOIN `'._DB_PREFIX_.'category` c ON cp.`id_category` = c.`id_category`
				INNER JOIN `'._DB_PREFIX_.'product` p ON cp.`id_product` = p.`id_product`
				'.Shop::addSqlAssociation('product', 'p', false).'
				WHERE c.`active` = 1
					AND product_shop.`active` = 1
					AND product_shop.`visibility` IN ("both", "search")
					AND product_shop.indexed = 1
					AND cg.`id_group` '.(!$id_customer ?  '= 1' : 'IN (
						SELECT id_group FROM '._DB_PREFIX_.'customer_group
						WHERE id_customer = '.(int)$id_customer.'
					)');

			$results = $db->executeS($sql);

			$eligible_products = array();
			foreach ($results as $row)
				$eligible_products[] = $row['id_product'];
			foreach ($intersect_array as $query)
			{
				$eligible_products2 = array();
				foreach ($db->executeS($query) as $row)
					$eligible_products2[] = $row['id_product'];

				$eligible_products = array_intersect($eligible_products, $eligible_products2);
				if (!count($eligible_products))
					return ($ajax ? array() : array('total' => 0, 'result' => array()));
			}

			$eligible_products = array_unique($eligible_products);

			$product_pool = '';
			foreach ($eligible_products as $id_product)
				if ($id_product)
					$product_pool .= (int)$id_product.',';
			if (empty($product_pool))
				return ($ajax ? array() : array('total' => 0, 'result' => array()));
			$product_pool = ((strpos($product_pool, ',') === false) ? (' = '.(int)$product_pool.' ') : (' IN ('.rtrim($product_pool, ',').') '));

			if ($ajax)
			{
				$sql = 'SELECT DISTINCT p.id_product, p.reference preference, pl.name pname, cl.name cname,
						cl.link_rewrite crewrite, pl.link_rewrite prewrite '.$score.'
					FROM '._DB_PREFIX_.'product p
					INNER JOIN `'._DB_PREFIX_.'product_lang` pl ON (
						p.`id_product` = pl.`id_product`
						AND pl.`id_lang` = '.(int)$id_lang.Shop::addSqlRestrictionOnLang('pl').'
					)
					'.Shop::addSqlAssociation('product', 'p').'
					INNER JOIN `'._DB_PREFIX_.'category_lang` cl ON (
						product_shop.`id_category_default` = cl.`id_category`
						AND cl.`id_lang` = '.(int)$id_lang.Shop::addSqlRestrictionOnLang('cl').'
					)
					WHERE p.`id_product` '.$product_pool.'
					ORDER BY position DESC LIMIT 10';
				return $db->executeS($sql);
			}

			if (strpos($order_by, '.') > 0)
			{
				$order_by = explode('.', $order_by);
				$order_by = pSQL($order_by[0]).'.`'.pSQL($order_by[1]).'`';
			}
			$alias = '';
			if ($order_by == 'price')
				$alias = 'product_shop.';
			$sql = 'SELECT p.*, product_shop.*, stock.out_of_stock, IFNULL(stock.quantity, 0) as quantity,
				pl.`description_short`, pl.`available_now`, pl.`available_later`, pl.`link_rewrite`, pl.`name`,
				tax.`rate`, image_shop.`id_image`, il.`legend`, m.`name` manufacturer_name '.$score.',
				DATEDIFF(
					p.`date_add`,
					DATE_SUB(
						NOW(),
						INTERVAL '.(Validate::isUnsignedInt(Configuration::get('PS_NB_DAYS_NEW_PRODUCT')) ? Configuration::get('PS_NB_DAYS_NEW_PRODUCT') : 20).' DAY
					)
				) > 0 new
				FROM '._DB_PREFIX_.'product p
				'.Shop::addSqlAssociation('product', 'p').'
				INNER JOIN `'._DB_PREFIX_.'product_lang` pl ON (
					p.`id_product` = pl.`id_product`
					AND pl.`id_lang` = '.(int)$id_lang.Shop::addSqlRestrictionOnLang('pl').'
				)
				LEFT JOIN `'._DB_PREFIX_.'tax_rule` tr ON (product_shop.`id_tax_rules_group` = tr.`id_tax_rules_group`
					AND tr.`id_country` = '.(int)$context->country->id.'
					AND tr.`id_state` = 0)
				LEFT JOIN `'._DB_PREFIX_.'tax` tax ON (tax.`id_tax` = tr.`id_tax`)
				LEFT JOIN `'._DB_PREFIX_.'manufacturer` m ON m.`id_manufacturer` = p.`id_manufacturer`
				LEFT JOIN `'._DB_PREFIX_.'image` i ON (i.`id_product` = p.`id_product`)'.
				Shop::addSqlAssociation('image', 'i', false, 'image_shop.cover=1').'
				LEFT JOIN `'._DB_PREFIX_.'image_lang` il ON (i.`id_image` = il.`id_image` AND il.`id_lang` = '.(int)$id_lang.')
				'.Product::sqlStock('p', 0).'
				WHERE p.`id_product` '.$product_pool.'
				AND ((image_shop.id_image IS NOT NULL OR i.id_image IS NULL) OR (image_shop.id_image IS NULL AND i.cover=1))
				'.($order_by ? 'ORDER BY  '.$alias.$order_by : '').($order_way ? ' '.$order_way : '').'
				LIMIT '.(int)(($page_number - 1) * $page_size).','.(int)$page_size;
			$result = $db->executeS($sql);

			$sql = 'SELECT COUNT(*)
				FROM '._DB_PREFIX_.'product p
				'.Shop::addSqlAssociation('product', 'p').'
				INNER JOIN `'._DB_PREFIX_.'product_lang` pl ON (
					p.`id_product` = pl.`id_product`
					AND pl.`id_lang` = '.(int)$id_lang.Shop::addSqlRestrictionOnLang('pl').'
				)
				LEFT JOIN `'._DB_PREFIX_.'tax_rule` tr ON (product_shop.`id_tax_rules_group` = tr.`id_tax_rules_group`
					AND tr.`id_country` = '.(int)Context::getContext()->country->id.'
				AND tr.`id_state` = 0)
				LEFT JOIN `'._DB_PREFIX_.'tax` tax ON (tax.`id_tax` = tr.`id_tax`)
				LEFT JOIN `'._DB_PREFIX_.'manufacturer` m ON m.`id_manufacturer` = p.`id_manufacturer`
				LEFT JOIN `'._DB_PREFIX_.'image` i ON (i.`id_product` = p.`id_product`)'.
				Shop::addSqlAssociation('image', 'i', false, 'image_shop.cover=1').'
				LEFT JOIN `'._DB_PREFIX_.'image_lang` il ON (i.`id_image` = il.`id_image` AND il.`id_lang` = '.(int)$id_lang.')
				WHERE p.`id_product` '.$product_pool.'
				AND ((image_shop.id_image IS NOT NULL OR i.id_image IS NULL) OR (image_shop.id_image IS NULL AND i.cover=1))';
			$total = $db->getValue($sql);

			if (!$result)
				$result_properties = false;
			else
				$result_properties = Product::getProductsProperties((int)$id_lang, $result);

			return array('total' => $total,'result' => $result_properties);
		}

		public static function getAttributes($db, $id_product, $id_lang)
		{
			if (!Combination::isFeatureActive())
				return '';

			$attributes = '';
			$attributesArray = $db->executeS('
		SELECT al.name, pa.reference FROM '._DB_PREFIX_.'product_attribute pa
		INNER JOIN '._DB_PREFIX_.'product_attribute_combination pac ON pa.id_product_attribute = pac.id_product_attribute
		INNER JOIN '._DB_PREFIX_.'attribute_lang al ON (pac.id_attribute = al.id_attribute AND al.id_lang = '.(int)$id_lang.')
		'.Shop::addSqlAssociation('product_attribute', 'pa').'
		WHERE pa.id_product = '.(int)$id_product);
			foreach ($attributesArray as $attribute)
				$attributes .= $attribute['name'].' '.($attribute['reference'] != "" ? $attribute['reference'].' ' : '');
			return $attributes;
		}
	}
