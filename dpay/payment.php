<?php

$useSSL = true;
include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/../../init.php');

include_once(_PS_MODULE_DIR_.'/dpay/DPay.php');

if (!$cookie->isLogged())
    Tools::redirect('authentication.php?back=order.php');
 
$dPayObject = new DPay();

echo $dPayObject->execPayment($cart);