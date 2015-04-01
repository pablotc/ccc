<?php
if (!defined('_PS_VERSION_'))
  exit;
 
class DPay extends PaymentModule
{
	public function __construct(){
		$this->name = 'dpay';
		$this->tab = 'payments_gateways';
		$this->version = '1.0.0';
		$this->author = 'Joao da Silva';
		$this->need_instance = 0;
		$this->currencies = true;
		$this->currencies_mode = 'checkbox';
		$this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_); 
		$this->bootstrap = true;

		parent::__construct();

		$this->displayName = $this->l('D Pay');
		$this->description = $this->l('Payment gateway for D Pay Chile.');

		$this->confirmUninstall = $this->l('Are you sure you want to uninstall?');

		if (!Configuration::get('MYMODULE_NAME'))      
			$this->warning = $this->l('No name provided');
	}
	
	public function install(){
		if (!parent::install()
		|| !$this->registerHook('payment')
		|| !$this->registerHook('paymentReturn')
		|| !$this->registerHook('invoice'))
			return false;
		return true;
	}
	
	public function uninstall(){
		if (!parent::uninstall())
			return false;
		return true;
	}
	
	public function hookPayment($params)
	{
		if (!$this->active)
			return;
			
		$this->smarty->assign(array(
					'this_path' => $this->_path,
					'this_path_bw' => $this->_path,
					'this_path_ssl' => Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'modules/'.$this->name.'/'
				));			

		return $this->display(__FILE__, 'views/templates/hook/payment.tpl');
	}	

	
	function hookInvoice($params){
		$id_order = $params['id_order'];
 
			global $smarty;
			$paymentCarddetails = $this->readPaymentcarddetails($id_order);
 
			$smarty->assign(array(
			    'cardHoldername'  	        => $paymentCarddetails['cardholdername'],
				'cardNumber' 		        => $paymentCarddetails['cardnumber'],
				'id_order'					=> $id_order,
				'this_page'					=> $_SERVER['REQUEST_URI'],
				'this_path' 				=> $this->_path,
            	'this_path_ssl' 			=> Configuration::get('PS_FO_PROTOCOL').$_SERVER['HTTP_HOST'].__PS_BASE_URI__."modules/{$this->name}/"));
			return $this->display(__FILE__, 'invoice_block.tpl');
 
	}
	

	public function execPayment($cart){
		if (!$this->active)
			return "";
		global $cookie, $smarty;
		
		/*
		$smarty->assign(array(
			'this_path' => $this->_path,
			'this_path_ssl' => (Configuration::get('PS_SSL_ENABLED') ? 'https://' : 'http://').htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').__PS_BASE_URI__.'modules/'.$this->name.'/'
		));
		*/ 
		 
		//esto ya funciona pero lo hardcodee... necesito probar con pesos shilenos
		//$total = floatval(number_format($cart->getOrderTotal(true, 3), 2, '.', ''));
		$total = 5990;
		$total_string = "${total}00";
		
		$TBK_ORDEN_COMPRA = $cart->id;
		$TBK_ID_SESION = date("Ymdhis");
		
		$fields = array(
			'TBK_ORDEN_COMPRA'     => $TBK_ORDEN_COMPRA,
			'TBK_TIPO_TRANSACCION' => 'TR_NORMAL',
			'TBK_MONTO'            => $total_string,
			'TBK_ID_SESION'        => $TBK_ID_SESION,
			//REVISAR URL EXITO
			'TBK_URL_EXITO'        => "pago_exito.php",
			//REVISAR URL FRACASO
			'TBK_URL_FRACASO'      => "pago_fracaso",
		);
		
		$data = http_build_query($fields);
		
		/***RUTA DONDE SE GUARDA EL LOG - DEBE ESTAR CHMOD 777 carpeta cierre***/
		/***chmod 777 -R cierre***/
		//Revisar Ruta
		$myPath = "/var/www/html/cgi-bin/cierre/dato".$TBK_ID_SESION.".log";
		
		//Grabado de datos en archivo de transaccion
		$fic = fopen($myPath, "w+");
		$linea="$total_string;$TBK_ORDEN_COMPRA";
		fwrite ($fic,$linea);
		fclose($fic);
		
		/*** CURL DEBE ESTAR INSTALADO EN EL SERVIDOR***/
		/*** apt-get install curl libcurl3 libcurl3-dev php5-curl***/
		/*** SE DEBE REINICIAR APACHE2 ***/
		/*** service apache2 restart ***/
		
		//REVISAR RUTA
		$url = 'sportapp.cl/cgi-bin/tbk_bp_pago.cgi';
		$ch = curl_init($url);

		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		$response = curl_exec($ch);
		curl_close($ch);
		echo $response;

		//Este último paso nos debería redirigir a webpay
		
	}
	
	public function hookPaymentReturn($params)
	{
		if (!$this->active)
			return;

		$state = $params['objOrder']->getCurrentState();

		//if ($state == Configuration::get('PS_OS_BANKWIRE') || $state == Configuration::get('PS_OS_OUTOFSTOCK'))
		if ($state == Configuration::get('PS_OS_PREPARATION'))
		{
			$this->smarty->assign(array(
				'total_to_pay' => Tools::displayPrice($params['total_to_pay'], $params['currencyObj'], false),
				'bankwireDetails' => Tools::nl2br($this->details),
				'bankwireAddress' => Tools::nl2br($this->address),
				'bankwireOwner' => $this->owner,
				'status' => 'ok',
				'id_order' => $params['objOrder']->id
			));
			if (isset($params['objOrder']->reference) && !empty($params['objOrder']->reference))
				$this->smarty->assign('reference', $params['objOrder']->reference);
		}
		else
			$this->smarty->assign('status', 'failed');
		return $this->display(__FILE__, 'payment_return.tpl');
	}	
	
	
}