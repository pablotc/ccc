<?php
$useSSL = true;
include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/../../init.php');

include_once(_PS_MODULE_DIR_.'/dpay/dpay.php');


if (!$cookie->isLogged()){
	//Tools::redirect('authentication.php?back=order.php');
}

$currency = $cookie->id_currency;
$total = floatval(number_format($cart->getOrderTotal(true, 3), 2, '.', ''));


$dPayObject = new DPay();

$dPayObject->validateOrder($cart->id,  _PS_OS_PREPARATION_, $total, $dPayObject->displayName, NULL, array(), NULL, false, $cart->secure_key);

echo $dPayObject->execPayment($cart);