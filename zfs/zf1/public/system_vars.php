<?php
$protocol = isset($_SERVER["HTTPS"]) ? 'https' : 'http';
//for live 
// define("BASE_URL", "".$protocol."://" . $_SERVER['HTTP_HOST'] . str_replace('/','',dirname($_SERVER['PHP_SELF'])));

//for local 
define("BASE_URL", "".$protocol."://" . $_SERVER['HTTP_HOST'] .'/'. str_replace('/','',dirname($_SERVER['PHP_SELF'])));

define("ACCESS_IP", $_SERVER['REMOTE_ADDR']);
define ("IMAGE_LINK",BASE_URL."/public/admin_images");
define ("Bizcourier_Images",BASE_URL."/public/bizcourier_images");
define ("CSS_LINK",BASE_URL."/public/css");
define ("JS_LINK",BASE_URL."/public/js");
define ("SERVICE_ICON",BASE_URL."/public/service_img/");
define ("FORWARDER_ICON",BASE_URL."/public/forwarder_icon/");

define("LOGO_UPLODE_LINK",ROOT_PATH."/public/headerlogo");
define("LOGO_LINK",BASE_URL."/public/headerlogo");






//Open Label Link
define("API_OPEN_LABEL", BASE_URL.'/label/');
define("PRINT_OPEN_LABEL", BASE_URL.'/label/print/');

//Save Label Link
define("API_SAVE_LABEL", ROOT_PATH.'/label/');
define("PRINT_SAVE_LABEL", ROOT_PATH.'/label/print/');

//Edi
define("EDI_SAVE", ROOT_PATH.'/private/EDI/'.date('Y_m'));
define("EDI_OPEN", BASE_URL.'private/EDI/');

//Save Invoice
define("INVOICE_SAVE", ROOT_PATH.'/private/INVOICE/');
define("INVOICE_OPEN", BASE_URL.'/private/INVOICE/');

//EDI MAnifest
define("FORWARDER_MANIFEST_SAVE", ROOT_PATH.'/private/EDI_MANIFEST/');
define("FORWARDER_MANIFEST_OPEN", BASE_URL.'/private/EDI_MANIFEST/');
//Driver Manifest
define("DRIVER_MANIFEST_SAVE", ROOT_PATH.'/private/DRIVER_MANIFEST/');
define("DRIVER_MANIFEST_OPEN", BASE_URL.'/private/DRIVER_MANIFEST/');
//Performa Invoice
define("PERFORMA_INVOICE_SAVE", ROOT_PATH.'/private/PERFORMA_INVOICE/');
define("PERFORMA_INVOICE_OPEN", BASE_URL.'/private/PERFORMA_INVOICE/');

//Help Desk
define ("HELPDESK_DOC",BASE_URL."/public/help_desk/");
//Claim file
define("CLAIM_OPEN", BASE_URL.'/public/claim_file/');
define("CLAIM_SAVE", ROOT_PATH.'/public/claim_file/');
define("ADDITIONAL_DOC_OPEN", BASE_URL.'/custom_docs/');
define("ADDITIONAL_DOC_SAVE", ROOT_PATH.'/custom_docs/');


define("WEBSHOP_IMG",ROOT_PATH."/public/webshop_product/");

define("DRIVER_SIGNATURE_UPLODE_LINK",ROOT_PATH."/driverappv2/delivery_signature/");
define("DRIVER_SIGNATURE_LINK",BASE_URL."/driverappv2/delivery_signature/");