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
class Octagono_Bradesco_Model_Transferencia extends Octagono_Bradesco_Model_Standard
{
    protected $_code  = 'bradesco_transferencia';
    protected $_formBlockType = 'bradesco/transferencia_form';
    protected $_infoBlockType = 'bradesco/transferencia_info';

	public function getOrderPlaceRedirectUrl()
    {
          return Mage::getUrl('bradesco/transferencia/redirect', array('_secure' => true));
    }

	//define a url do Bradesco
    public function getBradescoUrl($order_id) {
		$url = '';
        $codigo_bradesco = $this->getConfigData('codigo_bradesco');

        if ($this->getConfigData('ambiente') == 'producao') {
            $url = 'https://mup.comercioeletronico.com.br/sepsTransfer/' . $codigo_bradesco . '/prepara_pagto.asp?MerchantId=' . $codigo_bradesco . '&OrderId=' . $order_id;
        }
        else {
            $url = 'http://mupteste.comercioeletronico.com.br/sepsTransfer/' . $codigo_bradesco . '/prepara_pagto.asp?MerchantId=' . $codigo_bradesco . '&OrderId=' . $order_id;
        }

        return $url;
    }

	public function getCheckoutFormFields($order_id) {
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

		$html .= '<BEGIN_TRANSFER_DESCRIPTION>';
		$html .= '<BANCO>=(237)';
		$html .= '<NUMEROAGENCIA>=(' . $agencia . ')';
		$html .= '<NUMEROCONTA>=(' . $conta . ')';
		$html .= '<ASSINATURA>=(' . $this->getConfigData('assinatura') . ')';
		$html .= '<END_TRANSFER_DESCRIPTION>';

		if($this->getDebug()) {
			$logger->info('getCheckoutFormFields: ' . $html);
		}

		return $html;
	}
}

