<?php

include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/../../header.php');
include(dirname(__FILE__).'/DPay.php');
			

/* Gather submitted payment card details */
$cardholderName     = $_POST['cardholderName'];
$cardNumber         = $_POST['cardNumber'];


$currency = new Currency(intval(isset($_POST['currency_payement']) ? $_POST['currency_payement'] : $cookie->id_currency));
$total = floatval(number_format($cart->getOrderTotal(true, 3), 2, '.', ''));

$dpay = new DPay();
$dpay->validateOrder($cart->id,  _PS_OS_PREPARATION_, $total, $dpay->displayName, NULL, NULL, $currency->id);
$order = new Order($dpay->currentOrder);

//escribe en la BD
//$dpay->writePaymentcarddetails($order->id, $cardholderName, $cardNumber);
	
Tools::redirectLink(__PS_BASE_URI__.'order-confirmation.php?id_cart='.$cart->id.'&id_module='.$dpay->id.'&id_order='.$dpay->currentOrder.'&key='.$order->secure_key);

?>