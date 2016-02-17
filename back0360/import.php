<?php 

error_reporting(0);

// definimos la cabecera y el juego de caracteres
header("Content-Type: text/html;charset=utf-8");

// colocamos el directorio de adminxxxxx en la variable cogiendo el directorio donde está este script con getcwd
define('_PS_ADMIN_DIR_', getcwd());

// incluimos los parametros que se definieron en la instalación
include(_PS_ADMIN_DIR_.'/../config/config.inc.php');

// incluimos las funciones php de Prestashop que vamos a usar
include(_PS_ADMIN_DIR_.'/functions.php');

// incluimos el script php que hace las importaciones de CSV de Prestashop
include_once '../controllers/admin/AdminImportController.php';

// Configura bien los parametros:
//
// skip = numero de lineas a saltar, 1 para saltar los títulos de las columnas del csv
// forceIDs = 0 si queremos que los ponga Prestashop (recomendado) 1 si ponemos una columna con los números de id
// match_ref = 0 no usar la referencia como clave o 1 usarla como clave y hacer la referencia clave unica en mysql
// convert = lo dejamos vacio para usar el juego utf-8, el csv debe estar en este formato.
// entity = los dejamos a 1 , que es importar productos para Prestashop, 0 es importar categorías... etc.
// separator = el caracter de separacion de columnas del fichero.csv en nuestro caso ;
// multiple_value_separator' = el caracter de separacion de varios valores dentro de una columna: ,
// iso_lang = el idioma del csv que vamos a importar en formato iso_lang, para español : es
// ***
// En el array vamos poniendo por orden las columnas que tenemos en el CSV
// la 1 es las categorias, la 2 es el nombre, etc.. y asociamos el nombre de la variable de Prestashop
// para decirle que hay es ese numero de columna, es lo mismo que sale en la importacion desde el Back Office
// 
// la lista de las variables:
// 
// 'no' = 'Ignore this column'
// 'id' = 'ID'
// 'active' = 'Active (0/1)'
// 'name' = 'Name'
// 'category' = Categories (x,y,z...)
// 'price_tex' = Price tax excluded      Aquí con poner uno de los 2 price basta, si lo ponemos sin iva incluido 
// 'price_tin' = Price tax included      calcula este que es con IVA incluido.
// 'id_tax_rules_group' = Tax rules ID
// 'wholesale_price' = Wholesale price
// 'on_sale' = 'On sale (0/1)
// 'reduction_price' = Discount amount
// 'reduction_percent' = Discount percent
// 'reduction_from' = Discount from (yyyy-mm-dd)
// 'reduction_to' = Discount to (yyyy-mm-dd)
// 'reference' = Reference
// 'supplier_reference' = Supplier reference
// 'supplier' = Supplier
// 'manufacturer' = Manufacturer
// 'ean13' = EAN13
// 'upc' = UPC
// 'ecotax' = Ecotax
// 'width' = Width
// 'height' = Height
// 'depth' = Depth
// 'weight' = Weight
// 'quantity' = Quantity
// 'minimal_quantity' = Minimal quantity
// 'visibility' = Visibility
// 'additional_shipping_cost' = Additional shipping cost
// 'unity' = Unit for the unit price
// 'unit_price' = Unit price
// 'description_short' = Short description
// 'description' = Description
// 'tags' = Tags (x,y,z...)
// 'meta_title' = Meta title
// 'meta_keywords' = Meta keywords
// 'meta_description' = Meta description
// 'link_rewrite' = URL rewritten
// 'available_now' = Text when in stock
// 'available_later' = Text when backorder allowed
// 'available_for_order' = Available for order (0 = No, 1 = Yes)
// 'available_date' = Product availability date
// 'date_add' = Product creation date
// 'show_price' = Show price (0 = No, 1 = Yes)
// 'image' =  Image URLs (x,y,z...)
// 'delete_existing_images' = Delete existing images (0 = No, 1 = Yes)')
// 'features' = Feature (Name:Value:Position:Customized)
// 'online_only' = Available online only (0 = No, 1 = Yes)
// 'condition' = Condition
// 'customizable' = Customizable (0 = No, 1 = Yes)
// 'uploadable_files' = Uploadable files (0 = No, 1 = Yes)
// 'text_fields' = Text fields (0 = No, 1 = Yes)
// 'out_of_stock' = Action when out of stock
// 'shop' = tienda
// 
// Puedes encontrarlas todas mirando el fichero AdminImportController.php 
// vas añadiendo el numero de columna que tengas en tu csv y la variable que describe esa columna y pones
// todos los campos que quieras en el array, para el ejemplo CSV de arriba quedaría como vez abajo 0 => Categorias etc..
// 

function loadProductsPost() {
$_POST = array (
'tab' => 'AdminImport',
'skip' => '0',
'csv' => 'EXPORT_PRODUCTOS.CSV',
'forceIDs' => '1',
'match_ref' => '0',
'convert' => '',
'entity' => '1',
'separator' => ';',
'multiple_value_separator' => ',',
'iso_lang' => 'es',
'import' => 'Importar datos CSV',
'type_value' =>
    array (

//Subcat,categoria;Nombre;descripcion;referencia;EAN13;peso;preciocoste;precioventa;ecotasa;fabricante;img
//piensos,Animales;Pienso de primera;El pienso de bla bla...;010;1234567890123;0.2;9.01;10.50;0;Fab;/dir/img.jpg
// 0 => 'category',
// 1 => 'name',
// 2 => 'description_short',
// 3 => 'reference',
// 4 => 'ean13',
// 5 => 'weight',
// 6 => 'wholesale_price',
// 7 => 'price_tex',
// 8 => 'ecotax',
// 9 => 'manufacturer',
// 10 => 'image',

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
        15 => 'available_for_order',
        16 => 'delete_existing_images',
        17 => 'desoferta',


    ),
  );
}


function loadAtributosPost() {
    $_POST = array (
        'tab' => 'AdminImport',
        'skip' => '0',
        'csv' => 'EXPORT_COMBINACIONES.CSV',
        'convert' => '',
        'entity' => '2',
        'separator' => ';',
        'iso_lang' => 'es',
        'multiple_value_separator' => ',',
        'import' => 'Importar datos CSV',
        'type_value' =>
        array (
            0 => 'id_product',
            1 => 'group',
            2 => 'attribute',
            3 => 'supplier_reference',
            4 => 'reference',
            5 => 'delete_existing_images',
            6 => 'image_url',
        ),
    );
}




$import = New AdminImportController(); 

    // comprobamos que haya escrito algo en los ficheros
    if (filesize("/var/www/html/prestashop/back0360/import/EXPORT_PRODUCTOS.CSV") > 0)
    {
        loadProductsPost();
        $import->productImport();
    }
    rename("/var/www/html/prestashop/back0360/import/EXPORT_PRODUCTOS.CSV", "/var/www/html/prestashop/back0360/import/".date('Ymdhis')."EXPORT_PRODUCTOS.CSV");


    if (filesize("/var/www/html/prestashop/back0360/import/EXPORT_COMBINACIONES.CSV") > 0)
    {
        loadAtributosPost();
        $import->attributeImport();
    }
    rename("/var/www/html/prestashop/back0360/import/EXPORT_COMBINACIONES.CSV", "/var/www/html/prestashop/back0360/import/".date('Ymdhis')."EXPORT_COMBINACIONES.CSV");

?>
