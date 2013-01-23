<?php
/**
 * Octagono Ecommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.octagonoecommerce.com.br/eula-licenca-usuario-final.html
 *
 *
 * @category   Bradesco
 * @package    Octagono_Bradesco
 * @copyright  Copyright (c) 2009-2011 - Octagono Ecommerce - www.octagonoecommerce.com.br
 * @license    http://www.octagonoecommerce.com.br/eula-licenca-usuario-final.html
 */
class Octagono_Bradesco_Model_Standard extends Mage_Payment_Model_Method_Abstract
{
    //changing the payment to different from cc payment type and Bradesco payment type
    const PAYMENT_TYPE_AUTH = 'AUTHORIZATION';
    const PAYMENT_TYPE_SALE = 'SALE';

	protected $_modulo = 'BRADESCO';
    protected $_code  = 'bradesco_standard';
    protected $_formBlockType = 'bradesco/standard_form';
    protected $_infoBlockType = 'bradesco/standard_info';
    protected $_allowCurrencyCode = array('BRL');

	/**
     * Availability options
     */
    protected $_isGateway               = true;
    protected $_canAuthorize            = true;
    protected $_canCapture              = true;
    protected $_canCapturePartial       = false;
    protected $_canRefund               = false;
    protected $_canVoid                 = true;
    protected $_canUseInternal          = true;
    protected $_canUseCheckout          = true;
    protected $_canUseForMultishipping  = true;
    protected $_canSaveCc = false;

    /**
     * Get Paypal API Model
     *
     * @return Mage_Paypal_Model_Api_Nvp
     */
    public function getApi()
    {
        return Mage::getSingleton('bradesco/api_nvp');
    }

	/**
     * Get Bradesco session namespace
     *
     * @return Bradesco_Model_Session
     */
    public function getSession()
    {
        return Mage::getSingleton('bradesco/session');
    }

    /**
     * Get checkout session namespace
     *
     * @return Mage_Checkout_Model_Session
     */
    public function getCheckout()
    {
        return Mage::getSingleton('checkout/session');
    }

    /**
     * Using internal pages for input payment data
     *
     * @return bool
     */
    public function canUseInternal()
    {
        return $this->_canUseInternal;
    }

    /**
     * Using for multiple shipping address
     *
     * @return bool
     */
    public function canUseForMultishipping()
    {
        return true;
    }

    public function createFormBlock($name)
    {
        $block = $this->getLayout()->createBlock('bradesco/standard_form', $name)
            ->setMethod('bradesco_standard')
            ->setPayment($this->getPayment())
            ->setTemplate('bradesco/standard/form.phtml');

        return $block;
    }

    /*validate the currency code is avaialable to use for Bradesco or not*/
    public function validate()
    {
        parent::validate();
        $currency_code = $this->getQuote()->getBaseCurrencyCode();
        if (!in_array($currency_code,$this->_allowCurrencyCode)) {
            Mage::throwException(Mage::helper('bradesco')->__('Selected currency code ('.$currency_code.') is not compatabile with Bradesco'));
        }
        return $this;
    }

    public function onOrderValidate(Mage_Sales_Model_Order_Payment $payment)
    {
       return $this;
    }

    public function onInvoiceCreate(Mage_Sales_Model_Invoice_Payment $payment)
    {

    }

    public function canCapture()
    {
        return $this->_canCapture;
    }

    public function getSkinUrl($file=null, array $params=array())
    {
        return Mage::getDesign()->getSkinUrl($file, $params);
    }

    public function getOrderPlaceRedirectUrl()
    {
          return Mage::getUrl('bradesco/standard/redirect', array('_secure' => true));
    }

	public function getUrlRetorno() {
		  return Mage::getBaseUrl() . 'Bradesco/standard/autoriza';
	}

	public function getUrlSuccess() {
		  return Mage::getBaseUrl() . 'Bradesco/standard/success';
	}

	public function getUrlStore() {
		  return Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);
	}

	public function getUrlCapture($orderId = '') {
		$vars = '';

		/*if (!empty($tid)) {
			  $vars  = '?identificacao=' . $this->getConfigData('codigo_gateway');
			  $vars .= '&ambiente='	. $this->getConfigData('ambiente');
			  $vars .= '&modulo=' . 'VISAVBV';
			  $vars .= '&operacao=' . 'Captura';
			  $vars .= '&tid=' . $tid;
		}*/

		if (!empty($orderId)) {
			$vars = '?order_id=' . $orderId;
		}

		return Mage::getBaseUrl() . 'Bradesco/standard/capture' . $vars;
	}

	public function getUrlFailure() {
		  return Mage::getBaseUrl() . 'checkout/onepage/failure';
	}

    /**
     * Get current quote
     *
     * @return Mage_Sales_Model_Quote
     */
    public function getQuote($quote_id = null) {
		if (!empty($quote_id)) {
			return Mage::getModel('sales/quote')->load($quote_id);
		}
		else {
			return $this->getCheckout()->getQuote();
		}
    }

	public function getOrder($order_id = null) {
		if (empty($order_id) || $order_id == '') {
			$order = Mage::registry('current_order');
		}
		else {
			$order = Mage::getModel('sales/order')->load($order_id);
		}

		if (empty($order)) {
			$order_id = Mage::getSingleton('checkout/session')->getLastOrderId();
			$order = Mage::getModel('sales/order')->load($order_id);
		}

		return($order);
	}

function getNumEndereco($endereco) {
    	$numEndereco = '';

    	//procura por vírgula ou traço para achar o final do logradouro
    	$posSeparador = $this->getPosSeparador($endereco, false);
    	if ($posSeparador !== false) {
	    $numEndereco = trim(substr($endereco, $posSeparador + 1));
	}

    	//procura por vírgula, traço ou espaço para achar o final do número da residência
      	$posComplemento = $this->getPosSeparador($numEndereco, true);
	if ($posComplemento !== false) {
	    $numEndereco = trim(substr($numEndereco, 0, $posComplemento));
	}

	if ($numEndereco == '') {
	    $numEndereco = '?';
	}

	return($numEndereco);
}

function getPosSeparador($endereco, $procuraEspaco = false) {
  	  $posSeparador = strpos($endereco, ',');
      	  if ($posSeparador === false) {
	      $posSeparador = strpos($endereco, '-');
	  }

	  if ($procuraEspaco) {
	      if ($posSeparador === false) {
	          $posSeparador = strrpos($endereco, ' ');
	      }
	  }

	  return($posSeparador);
}

	public function getStandardCheckoutFormFields($order_id) {
		if($this->getDebug()) {
			$writer = new Zend_Log_Writer_Stream($this->getLogPath());
			$logger = new Zend_Log($writer);
			$logger->info("Bradesco - entrando em captura()");
		}

		$order = $this->getOrder($order_id);
		$order_id = $order->getId(); //sempre recupera o order_id a partir do order
		$quote = $this->getQuote($order->getQuoteId());

		$a = $order->getBillingAddress();
		$currency_code = $order->getBaseCurrencyCode();

		$html  = '<BEGIN_ORDER_DESCRIPTION>';
		$html .= "<orderid>=($order_id)";

		$items = $order->getAllItems();
		if ($items) {
			$i = 1;
			foreach($items as $item) {
				$qty = $item->getQtyOrdered();
				$qty = number_format($qty, 2, ',', '');
				if ($qty == '') {
					$qty = '1';
				}

				$itemPrice = ($item->getPrice() - $item->getBaseDiscountAmount());

				$valor = $itemPrice * $qty;
				$valor = number_format($valor, 2, '', '');

				$descritivo = $this->preparaCampo($item->getName());

				$html .= '<descritivo>=(' . $descritivo . ')';
				$html .= '<quantidade>=(' . $qty . ')';
				$html .= '<unidade>=(un)';
				$html .= '<valor>=(' .  $valor . ')';
			}
		}

		//inclui a descrição do frete no pedido
        $frete = $quote->getShippingAddress()->getBaseShippingAmount();
		if ($frete > 0) {
			$frete = number_format($frete, 2, '', '');
			$html .= '<adicional>=(frete)';
			$html .= '<valorAdicional>=(' . $frete . ')';
		}

		$dataVencimento = date("d/m/Y", time() + ($this->getConfigData('prazo_pagamento') * 86400));
		$total = $order->getGrandTotal();
		$total = number_format($total, 2, ',', '.');


		if ($this->getConfigData('ambiente') == 'teste') {
			$agencia = '0001';
			$conta = '1234567';
		}
		else {
			$agencia = $this->getConfigData('agencia');
			$conta = $this->getConfigData('conta');
		}

		$uf = $a->getState();
		if ($uf == '' || strlen($uf)>2) {
			$uf = 'BR';
		}

		$cep = substr(eregi_replace ("[^0-9]", "", $a->getPostcode()).'00000000',0,8);

		$cedente = $this->preparaCampo($this->getConfigData('cedente'));
		$cidadeSacado = $this->preparaCampo($a->getCity());

		$instrucoes1 = $this->preparaCampo($this->getConfigData('instrucoes1'));
		$instrucoes2 = $this->preparaCampo($this->getConfigData('instrucoes2'));
		$instrucoes3 = $this->preparaCampo($this->getConfigData('instrucoes3'));
		$instrucoes4 = $this->preparaCampo($this->getConfigData('instrucoes4'));

		$html .= '<END_ORDER_DESCRIPTION>';

		$html .= '<BEGIN_BOLETO_DESCRIPTION>';
		$html .= '<CEDENTE>=(' . $cedente . ')';
		$html .= '<BANCO>=(237)';
		$html .= '<NUMEROAGENCIA>=(' . $agencia . ')';
		$html .= '<NUMEROCONTA>=(' . $conta . ')';
		$html .= '<ASSINATURA>=(' . $this->getConfigData('assinatura') . ')';
		$html .= '<DATAEMISSAO>=(' . date("d/m/Y", time()) . ')';
		$html .= '<DATAPROCESSAMENTO>=(' . date("d/m/Y", time()) . ')';
		$html .= '<DATAVENCIMENTO>=(' . $dataVencimento . ')';
		$html .= '<NOMESACADO>=(' . $this->preparaCampo($a->getFirstname().' '.$a->getLastname()) . ')';
		$html .= '<ENDERECOSACADO>=(' . $this->preparaCampo($a->getStreet(1)) . ')';
		$html .= '<CIDADESACADO>=(' . $cidadeSacado . ')';
		$html .= '<UFSACADO>=(' . $this->preparaCampo($uf) . ')';
		$html .= '<CEPSACADO>=(' . $cep . ')';
		$html .= '<CPFSACADO>=(11111111111)';
		$html .= '<NUMEROPEDIDO>=(' . $order_id . ')';
		$html .= '<VALORDOCUMENTOFORMATADO>=(R$' . $total. ')';
		$html .= '<SHOPPINGID>=(0)';
		$html .= '<NUMDOC>=(' . $order->getRealOrderId() . ')';
		$html .= '<CARTEIRA>=(' . $this->getConfigData('carteira') . ')';
		$html .= '<INSTRUCAO1>=(' . $instrucoes1 . ')';
		$html .= '<INSTRUCAO2>=(' . $instrucoes2 . ')';
		$html .= '<INSTRUCAO3>=(' . $instrucoes3 . ')';
		$html .= '<INSTRUCAO4>=(' . $instrucoes4 . ')';
		$html .= '<END_BOLETO_DESCRIPTION>';

		if($this->getDebug()) {
			$logger->info('getStandardCheckoutFormFields: ' . $html);
		}

		return $html;
	}

    //define a url do Bradesco
    public function getBradescoUrl($order_id) {
		$url = '';
        $codigo_bradesco = $this->getConfigData('codigo_bradesco');

        if ($this->getConfigData('ambiente') == 'producao') {
            $url = 'https://mup.comercioeletronico.com.br/sepsBoleto/' . $codigo_bradesco . '/prepara_pagto.asp?MerchantId=' . $codigo_bradesco . '&OrderId=' . $order_id;
        }
        else {
            $url = 'http://mupteste.comercioeletronico.com.br/sepsBoleto/' . $codigo_bradesco . '/prepara_pagto.asp?MerchantId=' . $codigo_bradesco . '&OrderId=' . $order_id;
        }

        return $url;
    }

    public function preparaCampo($campo) {
		$campo = utf8_decode($campo); //converte para ISO-8859-1

		$campo = str_replace("\n", '', $campo); //retira quebras de linha

		$campo = htmlentities($campo); //codifica entidades HTML. Isso evita que o usuário digite algum comando HTML que interfira na página

		$campo = str_replace(')', '&#41;', $campo); //codifica parentesis. O sistema do bradesco usa parentesis nos comandos

		$campo = str_replace('(', '&#40;', $campo); //codifica parentesis. O sistema do bradesco usa parentesis nos comandos

		return($campo);
	}

	//  define a url do Bradesco
    public function getGatewayUrl()
    {
		$url = $this->getConfigData('url_gateway');

       return $url;
    }

	public function getDebug()
	{
		$ret = Mage::getStoreConfig('payment/bradesco/debug');
		if (!$ret) {
			$ret = $this->getConfigData('debug');
		}
		return $ret;
	}

	public function getLogPath()
	{
		return Mage::getBaseDir() . '/var/log/bradesco.log';
	}

    public function captura($orderId, $tid, $responseCode, $message, $amount) {
		$ret = '';

		if($this->getDebug()) {
			$writer = new Zend_Log_Writer_Stream($this->getLogPath());
			$logger = new Zend_Log($writer);
			$logger->info("Bradesco - entrando em captura()");
		}

		$textoCaptura = "Valor Capturado: $amount. Código da Transação: $tid. Código de Resposta da Transação: $responseCode. Mensagem: $message.";

        $order = $this->getOrder($orderId);

		if($this->getDebug()) {
			$logger->info('order: ' . $order->getData());
		}

		if ($responseCode=='0' || $responseCode=='3') {
			if (!$order->canInvoice()) {
				//when order cannot create invoice, need to have some logic to take care
				$ret = 'Este pedido não pode gerar Fatura. Verifique se a fatura já foi criada.';

				$order->addStatusToHistory(
					$order->getStatus(),//continue setting current order status
					$ret . ' - ' . $textoCaptura
				);

				$order->save();
			}
			else {
				//need to save transaction id
				$order->getPayment()->setTransactionId($tid);

				//need to convert from order into invoice
				if($this->getDebug()) {
					$logger->info('$order->prepareInvoice()');
				}
				$invoice = $order->prepareInvoice();

				//capture
				if($this->getDebug()) {
					$logger->info('$invoice->register()->capture()');
				}
				$invoice->register()->capture();

				//save
				if($this->getDebug()) {
					$logger->info('addObject->save()');
				}
				Mage::getModel('core/resource_transaction')
				   ->addObject($invoice)
				   ->addObject($invoice->getOrder())
				   ->save();

				$ret = 'Fatura ' . $invoice->getIncrementId() . ' foi criada com sucesso.';
				$order->addStatusToHistory(
					'Processing',//update order status to processing after creating an invoice
					$ret . ' - ' . $textoCaptura
				);

				$order->save();
            }
        }
		else if ($responseCode=='1' || $responseCode=='2' || $responseCode=='4') {
			$ret = 'Falha ao capturar o pagamento. ';
			$order->addStatusToHistory(
				$order->getStatus(),//continue setting current order status
				$ret . ' - ' . $textoCaptura
			);
			$order->save();
		}

		return($ret);
    }

	    /**
     * Send authorize request to gateway
     *
     * @param   Varien_Object $payment
     * @param   decimal $amount
     * @return  Mage_Paygate_Model_Authorizenet
     */
    public function autoriza($orderId, $tid, $responseCode, $authCode, $message, $amount=0) {
		$error = false;

        if ($this->getDebug()) {
        }

		$textoAutorizacao = "Valor: $amount. Código da Transação: $tid. Código de Resposta da Transação: $responseCode. Código de Autorização: $authCode. Mensagem: $message.";

		$order = $this->getOrder($orderId);
		$payment = $order->getPayment();

		/*if ($amount != $order->getGrandTotal()) {
			//when grand total does not equal, need to have some logic to take care
			$order->addStatusToHistory(
				$order->getStatus(),//continue setting current order status
				'Total do pedido não confere com o valor aprovado pela Bradesco. ' . $textoAutorizacao
			);
		}*/
		if ($responseCode == 0 || $responseCode == 1) { //autorizou
		   $order->addStatusToHistory(
					$order->getStatus(),
					'Pagamento autorizado pela Bradesco. ' . $textoAutorizacao
			);

		   if ($payment) {
			   //need to save transaction id
			   $payment->setTransactionId($tid);
			   $payment->setAnetTransType('AUTH_ONLY');
			   if ($amount == 0) {
				   $payment->setAmount($amount);
			   }

			   $payment->setStatus(self::STATUS_APPROVED)
						->setCcApproval($authCode)
						->setLastTransId($tid)
						->setCcTransId($tid)
						->setCcAvsStatus($message)
						->setCcCidStatus($responseCode);
			}
		}
		else { //se a transação não foi aprovada
			$order->addStatusToHistory(
				'Cancelado', //$order->getStatus(),//continue setting current order status
				'Transação não aprovada. ' . $textoAutorizacao
			);
		}

		if ($error !== false) {
			Mage::throwException($error);
		}
		else {
			//save order
			//$order->sendNewOrderEmail();
			$order->save();
		}

		return $this;
	}

	/*
	public function capture(Varien_Object $payment, $amount)
    {
		if($this->getDebug()) {
			$writer = new Zend_Log_Writer_Stream($this->getLogPath());
			$logger = new Zend_Log($writer);
			$logger->info("Bradesco - Redirecionando");
		}

		if ($payment->getCcTransId()) {
			$this->setRedirectUrl($this->getUrlCapture());
            return false;
		}
		else {
			Mage::throwException('Código da transação não identificado');
		}
		return $this;
	}*/

    /*public function capture(Varien_Object $payment, $amount)
    {
		if($this->getDebug()) {
			$writer = new Zend_Log_Writer_Stream($this->getLogPath());
			$logger = new Zend_Log($writer);
			$logger->info("Bradesco - entrando em capture()");
		}

		if ($payment->getCcTransId()) {
				$this->setAmount($amount)
				->setPayment($payment);

			$result = $this->_callCapture($payment);

			if($this->getDebug()) { $logger->info(var_export($result, TRUE)); }

			if($result === false)
			{
				$e = $this->getError();
				if (isset($e['message'])) {
					$message = Mage::helper('dps')->__('There has been an error processing your payment.') . $e['message'];
				} else {
					$message = Mage::helper('dps')->__('There has been an error processing your payment. Please try later or contact us for help.');
				}
				Mage::throwException($message);
			}
			else
			{
				if ($result['lr'] == '0' || $result['lr'] == 0)
				{
					$payment->setStatus(self::STATUS_APPROVED)
						->setLastTransId($result['tid']);
				}
				else if ($result['lr'] == '1' || $result['lr'] == 1)
				{
					Mage::throwException('Captura negada: ' . $result['ars'] . '. LR: ' . $result['lr'] . '. Cap: ' . $result['cap']);
				}
				else if ($result['lr'] == '2' || $result['lr'] == 2)
				{
					Mage::throwException('Falha na captura. Informação inconsistente: ' . $result['ars'] . '. LR: ' . $result['lr'] . '. Cap: ' . $result['cap']);
				}
				else if ($result['lr'] == '3' || $result['lr'] == 3)
				{
					Mage::throwException('Captura já efetuada: ' . $result['ars'] . '. LR: ' . $result['lr'] . '. Cap: ' . $result['cap']);
				}
				else
				{
					Mage::throwException('Falha na captura: ' . $result['ars'] . '. LR: ' . $result['lr'] . '. Cap: ' . $result['cap']);
				}
			}
		}
		else {
			Mage::throwException('Código da transação não identificado');
		}
		return $this;
    }*/

	/**
	 *
	 */
	protected function _callCapture(Varien_Object $payment)
	{
		if($this->getDebug())
		{
			$writer = new Zend_Log_Writer_Stream($this->getLogPath());
			$logger = new Zend_Log($writer);
			$logger->info('entering _call()');
			$logger->info('identificacao: ' . $this->getConfigData('codigo_gateway'));
			$logger->info('modulo: ' . $this->_modulo);
			$logger->info('ambiente: ' . $this->getConfigData('ambiente'));
			$logger->info('tid: ' . $payment->getCcTransId());
		}

		// Generate any needed values
		$nvpArr = array(
			'identificacao' => $this->getConfigData('codigo_gateway'),
            'operacao'  	=> 'Captura',
            'modulo'   		=> $this->_modulo,
            'ambiente'      => $this->getConfigData('ambiente'),
			'tid'			=> $payment->getCcTransId()
        );

		if($this->getDebug())
		{
			$logger->info(var_export($payment->getOrder()->getData(), TRUE));
		}

		$nvpReq = '';
        foreach ($nvpArr as $k=>$v) {
            $nvpReq .= '&'.$k.'='.urlencode($v);
        }
        $nvpReq = substr($nvpReq, 1);

		// DEBUG
		if($this->getDebug()) { $logger->info($nvpReq); }

		// Send the data via HTTP POST and get the response
		$http = new Varien_Http_Adapter_Curl();
		$http->setConfig(array('timeout' => 30));

		$http->write(Zend_Http_Client::POST, $this->getGatewayUrl(), '1.1', array(), $nvpReq);

		$response = $http->read();

		if ($http->getErrno()) {
			$http->close();
			$this->setError(array(
				'message' => $http->getError()
			));
			return false;
		}

		// DEBUG
		if($this->getDebug()) {
			$logger->info($response);
		}

        $http->close();

		// Strip out header tags
        $response = preg_split('/^\r?$/m', $response, 2);
        $response = trim($response[1]);

		// Parse the XML object
		$xmlObj = simplexml_load_string($response);

		// Build an associative array with returned values
		$result = array();

		$xpath = $xmlObj->xpath('/ars');
		$result['ars'] = ($xpath !== FALSE) ? $xpath[0] : '';

		$xpath = $xmlObj->xpath('/tid');
		$result['tid'] = ($xpath !== FALSE) ? $xpath[0] : '';

		$xpath = $xmlObj->xpath('/lr');
		$result['lr'] = ($xpath !== FALSE) ? $xpath[0] : '';

		$xpath = $xmlObj->xpath('/cap');
		$result['cap'] = ($xpath !== FALSE) ? $xpath[0] : '';

		$xpath = $xmlObj->xpath('/free');
		$result['free'] = ($xpath !== FALSE) ? $xpath[0] : '';

		return $result;
	}

	public function sendHTMLemail($message, $from='', $to='', $subject)
	{
		if ($to == '') { //recupera o e-mail do cliente
			$to = $this->getQuote()->getShippingAddress()->getEmail();
		}

		// To send the HTML mail we need to set the Content-type header.
		$headers = "MIME-Version: 1.0\r\n";
		$headers .= "Content-type: text/html; charset=UTF-8\r\n";
		$headers  .= "From: $from\r\n";
		//options to send to cc+bcc
		//$headers .= "Cc: [email]maa@p-i-s.cXom[/email]";
		//$headers .= "Bcc: [email]email@maaking.cXom[/email]";

	    return(mail($to,$subject,$message,$headers));
	}
}

