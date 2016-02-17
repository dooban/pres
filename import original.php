<?php

/* 
 *  2007-2014 PrestaShop
 * 
 *  NOTICE OF LICENSE
 * 
 *  This source file is subject to the Academic Free License (AFL 3.0)
 *  that is bundled with this package in the file LICENSE.txt.
 *  It is also available through the world-wide-web at this URL:
 *  http://opensource.org/licenses/afl-3.0.php
 *  If you did not receive a copy of the license and are unable to
 *  obtain it through the world-wide-web, please send an email
 *  to license@prestashop.com so we can send you a copy immediately.
 * 
 *  DISCLAIMER
 * 
 *  Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 *  versions in the future. If you wish to customize PrestaShop for your
 *  needs please refer to http://www.prestashop.com for more information.
 * 
 *   @author    PrestaShop SA <contact@prestashop.com>
 *   @copyright 2007-2014 PrestaShop SA
 *   @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *   International Registered Trademark & Property of PrestaShop SA
 * /
 */
set_time_limit(0);

if(php_sapi_name() === 'cli'){
    //params to run with php cli
    $_SERVER['HTTP_HOST'] = 'dentalmarket.ecomm360.net';
    $_SERVER['REQUEST_URI'] = '/import.php';
}

include_once __DIR__.'/config/config.inc.php';
include_once __DIR__.'/init.php';



include_once __DIR__.'/controllers/admin/AdminImportController.php';

Context::getContext()->employee = new Employee(1);


$import = New AdminImportController();


//switch ($_GET['entity']) {
//    case 0:
//        loadCategoriesPost();
//        $import->categoryImport();
//    break;

//    case 1:
        echo "importando productos<br>";
        loadProductsPost();
        echo "carga de campos<br>";
        $import->productImport();        
      
//    break;

//    case 2:
//        echo "importando atributos<br>";
//        loadAtributosPost();
//        echo "carga de atributos<br>";
//        $import->attributeImport();
//     
//    break;
//
//    default:
//        die();
//        break;
//}



echo "FINALIZADA LA IMPORTACION<BR>";
exit();

function loadCategoriesPost() {
    $_POST = array (
    'tab' => 'AdminImport',
    'skip' => '0',
    'csv' => 'categorias.csv',
    'convert' => '',
    'entity' => '0',
    'separator' => ';',
    'multiple_value_separator' => ',',
    'import' => 'Importar datos CSV',
    'type_value' =>
    array (
        0 => 'id',
        1 => 'active',
        2 => 'name',
        3 => 'parent',
        4 => 'description',
        5 => 'meta_title',
        6 => 'meta_keywords',
        7 => 'meta_description',
        8 => 'link_rewrite',
        9 => 'image',
        ),
    );
}

function loadProductsPost() {
    $_POST = array (
    'tab' => 'AdminImport',
    'skip' => '1',
    'csv' => 'EXPORT_PRODUCTOS.CSV',
    'convert' => 'on',
    'entity' => '1',
    'separator' => ';',
    'iso_lang' => 'es',
    'multiple_value_separator' => ',',
    'import' => 'Importar datos CSV', 
    'forceIDs' => '1',
    'type_value' =>
    array (
        0 => 'id',
        1 => 'active',        
        2 => 'name',
        3 => 'category',
        4 => 'price_tex',
        5 => 'id_tax_rules_group',
        6 => 'reference',
        7 => 'supplier_reference',
        8 => 'supplier',
        9 => 'manufacturer',
        10 => 'show_price',      
        11 => 'description',
        12 => 'reduction_percent',
        13 => 'on_sale',
        14 => 'image',     
        
        ),
    );
}


function loadAtributosPost() {
    $_POST = array (
        'tab' => 'AdminImport',
        'skip' => '1',
        'csv' => 'combinaciones.csv',
        'convert' => '',
        'entity' => '2',
        'separator' => ';',
        'iso_lang' => 'es',
        'multiple_value_separator' => ',',
        'import' => 'Importar datos CSV',
        'type_value' =>
        array (
            0 => 'id_product',
            1 => 'reference',
            2 => 'group',
            3 => 'attribute',
            4 => 'price',
            5 => 'quantity',
            6 => 'default_on',
            7 => 'shop',
        ),
    );
}

?>