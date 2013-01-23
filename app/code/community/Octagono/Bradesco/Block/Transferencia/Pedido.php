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
class Octagono_Bradesco_Block_Transferencia_Pedido extends Mage_Payment_Block_Form
{
    protected function _construct()
    {
        parent::_construct();
    }

    protected function _prepareLayout()
    {
		parent::_construct();
    }

    protected function _toHtml()
    {
		//header('Content-Type: text/html; charset=ISO-8859-1');

		$order_id = $this->getRequest()->getParam('orderid');
		if ($order_id == '') {
			$order_id = $this->getRequest()->getParam('numOrder');
		}
		if ($order_id == '') {
			$order_id = $this->getRequest()->getParam('OrderId');
		}

		if (empty($order_id)) {
			$html = 'Informe o par&acirc;metro numOrder ou orderid ou OrderId';
		}
		else {
			$html = Mage::getModel('bradesco/transferencia')->getCheckoutFormFields($order_id);
		}

		//$html = print_r($_REQUEST);

        return $html;
    }

	private function getFormCode() {
		$transferencia = Mage::getModel('bradesco/transferencia');

		$orderId = $_REQUEST['NUMPEDIDO'];
		$parcelas = $transferencia->getOrder($orderId)->getQuote()->getPayment()->getData('cc_parcelas');
		$numParcelas = (int)$parcelas;
		if ($numParcelas == 0) {
			$numParcelas = 1;
		}

		$transOrig = '04';
		if ($numParcelas > 1) {
			if ($transferencia->getConfigData('juros') == '1') { //juros do emisor / com juros
				$transOrig = '06';
			}
			else {
				$transOrig = '08'; //juros do lojista / sem juros
			}
		}

		$total = $transferencia->getOrder($orderId)->getQuote()->getGrandTotal();

		$form = '<form id = "bradesco_transferencia_checkout" name="bradesco_transferencia_checkout" action="http://ecommerce.bradesco.com.br/pos_virtual/confirma.asp" method="post">';

		$form .= '<input type="hidden" name="DATA" value="' . $_REQUEST['DATA'] . '" />';
		$form .= '<input type="hidden" name="TRANSACAO" value="203" />';
		$form .= '<input type="hidden" name="TRANSORIG" value="' . $transOrig . '" />';
		$form .= '<input type="hidden" name="PARCELAS" value="' . $numParcelas . '" />';
		$form .= '<input type="hidden" name="FILIACAO" value="' . $transferencia->getConfigData('codigo_bradesco') . '" />';
		$form .= '<input type="hidden" name="TOTAL" value="' . $total . '" />';
		$form .= '<input type="hidden" name="NUMPEDIDO" value="' . $_REQUEST['NUMPEDIDO'] . '" />';
		$form .= '<input type="hidden" name="NUMAUTOR" value="' . $_REQUEST['NUMAUTOR'] . '" />';
		$form .= '<input type="hidden" name="NUMCV" value="' . $_REQUEST['NUMCV'] . '" />';
		$form .= '<input type="hidden" name="NUMSQN" value="' . $_REQUEST['NUMSQN'] . '" />';

		$form .= '</form>';

		return($form);
	}

	private function getValores() {
		$transferencia = Mage::getModel('bradesco/transferencia');

		$orderId = $_REQUEST['NUMPEDIDO'];
		$order = $transferencia->getOrder($orderId);
		$quote = $transferencia->getQuote($order->getQuoteId());

		$parcelas = $quote->getPayment()->getData('cc_parcelas');
		$numParcelas = (int)$parcelas;
		if ($numParcelas == 0) {
			$numParcelas = 1;
		}

		$transOrig = '04';
		if ($numParcelas > 1) {
			if ($transferencia->getConfigData('juros') == '1') { //juros do emisor / com juros
				$transOrig = '06';
			}
			else {
				$transOrig = '08'; //juros do lojista / sem juros
			}
		}

		$total = $order->getGrandTotal();
		$total = number_format($total, 2, '', '');

		$valores = "DATA=" . $_REQUEST['DATA'];
	    $valores = $valores . "&TRANSACAO=203";
		$valores = $valores . "&TRANSORIG=" . $transOrig;
		$valores = $valores . "&PARCELAS=" . $numParcelas;
		$valores = $valores . "&FILIACAO=" . $transferencia->getConfigData('codigo_bradesco');
		$valores = $valores . "&DISTRIBUIDOR="; // este campo deve ser nulo
		$valores = $valores . "&TOTAL=" . $total;
		$valores = $valores . "&NUMPEDIDO=" . $orderId;
		$valores = $valores . "&NUMAUTOR=" . $_REQUEST['NUMAUTOR'];
		$valores = $valores . "&NUMCV=" . $_REQUEST['NUMCV'];
		$valores = $valores . "&NUMSQN=" . $_REQUEST['NUMSQN'];

		return($valores);
	}
}

