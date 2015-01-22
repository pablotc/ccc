<?php

include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/../../header.php');
include(dirname(__FILE__).'/DPay.php');
			
$currency = new Currency(intval(isset($_POST['currency_payement']) ? $_POST['currency_payement'] : $cookie->id_currency));
$total = floatval(number_format($cart->getOrderTotal(true, 3), 2, '.', ''));

$dpay = new DPay();
$order = new Order($dpay->currentOrder);

/*
$dpay->validateOrder($cart->id,  _PS_OS_PREPARATION_, $total, $dpay->displayName, NULL, NULL, $currency->id);
//escribe en la BD
//$dpay->writePaymentcarddetails($order->id, $cardholderName, $cardNumber);
	
Tools::redirectLink(__PS_BASE_URI__.'order-confirmation.php?id_cart='.$cart->id.'&id_module='.$dpay->id.'&id_order='.$dpay->currentOrder.'&key='.$order->secure_key);
*/

	//Iniciación de data
	$acepta=false;
	$TBK_RESPUESTA = false;
	$exists = false;

	

	try{
		//rescate de datos de POST.
		$TBK_RESPUESTA = &$_POST["TBK_RESPUESTA"];
		$TBK_ORDEN_COMPRA = &$_POST["TBK_ORDEN_COMPRA"];
		$TBK_MONTO = &$_POST["TBK_MONTO"];
		$TBK_ID_SESION = &$_POST["TBK_ID_SESION"];
		
		/*******************************************************************/
		/****************** REVISAR SI OC YA FUE PAGADA (confirmada) *******/
		/*******************************************************************/
		
		$state = $order->current_state;
		//estado 3 significa que ya pagó... must check... se debería hacer un switch o un >= no sé.
		if($state == 3) $confirmed = true;
		else $confirmed = false;
		
		/******************************************************/
		/****************** FIN REVISION *********************/
		/******************************************************/
		
		if(!$confirmed){
			/******************************************************/
			/****************** CONFIGURAR AQUI *******************/
			/******************************************************/
		
			//REVISAR RUTA
			$cgibin_url = "/var/www/html/cgi-bin/";

			$myPath = $cgibin_url."cierre/dato".$TBK_ID_SESION.".log";
			//GENERA ARCHIVO PARA MAC
			$filename_txt = $cgibin_url."cierre/MAC01Normal".$TBK_ID_SESION.".txt";
			// RUTA CHECK MAC
			$cmdline = $cgibin_url."tbk_check_mac.cgi ".$filename_txt;
			
			/******************************************************/
			/****************** FIN CONFIGURACION *****************/
			/******************************************************/

			//lectura archivo que guardo make_webpay_payment.php
			if ($fic = fopen($myPath, "r")){
				$linea=fgets($fic);
				fclose($fic);
			}
			$detalle=explode(";", $linea);
			
			if (count($detalle)>=1){
				$monto=$detalle[0];
				$ordenCompra=$detalle[1];
			}
			
			
			/**************************************************************************/
			/****************** GUARDAR DATOS POST ENVIADOS POR TRANSBANK *************/
			/**************************************************************************/
			$fp=fopen($cgibin_url."cierre/MAC01Normal".$TBK_ID_SESION.".txt","wt");
			while(list($key, $val)=each($_POST)){
				fwrite($fp, "$key=$val&");
			}
			fclose($fp);
			/**************************************************************************/
			/****************** FIN GUARDADO DATOS ************************************/
			/**************************************************************************/				

			/**************************************************************************/
			/****************** INICIO VALIDACION TRANSBANK****************************/
			/**************************************************************************/
			
			//Validación de respuesta de Transbank, solo si es 0 continua con la pagina de cierre
			if($TBK_RESPUESTA=="0"){
				$acepta=true; 
			} 
			else{ 
				$acepta=false; 
			}
			//validación de monto y Orden de compra
			if ($TBK_MONTO==$monto && $TBK_ORDEN_COMPRA==$ordenCompra && $acepta==true){
				$acepta=true;
			}
			else{ 
				$acepta=false;
			}
			//Validación MAC
			if ($acepta==true){
				exec($cmdline, $result, $retint);
				
				if ($result[0] =="CORRECTO") $acepta=true; else $acepta=false;
			}

			/**************************************************************************/
			/****************** FIN VALIDACION TRANSBANK ******************************/
			/**************************************************************************/			
		}
		else{
			$exists = true;
		}
	}
	catch(Exception $e){
	
	}
	
	
	
	/**************************************/
	/** RESPUESTA AUTOMATICA A TRANSBANK **/
	/**************************************/
	echo "<html>";
	if($exists){
		echo "RECHAZADO";
	}
	else{
		if($acepta==true){
			/************************************************************/
			/** AQUI AGREGA TU CODIGO PARA ACTUALIZAR LA BASE DE DATOS **/
			/************************************************************/
			
			//ACÁ debemos responder RECHAZADO si esq han pasado más de 30 segs --> set_time_limit(30) must check
			
			$dpay->validateOrder($cart->id,  _PS_OS_PREPARATION_, $total, $dpay->displayName, NULL, NULL, $currency->id);

			/********************************************/
			/***FIN CODIGO ACTUALIZACION BASE DE DATOS***/
			/********************************************/
			
			//SI TODO ESTA CORRECTO SE IMPRIME "ACEPTADO"
			echo "ACEPTADO";
		}
		else{
			if($TBK_RESPUESTA != "0"){	
				echo "ACEPTADO";
			}			
			else echo "RECHAZADO";		
		}
	}
	echo "</html>";
	/******************************************/
	/** FIN RESPUESTA AUTOMATICA A TRANSBANK **/
	/******************************************/




?>