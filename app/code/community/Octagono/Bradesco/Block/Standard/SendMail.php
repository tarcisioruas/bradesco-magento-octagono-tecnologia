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
class Octagono_Bradesco_Block_Standard_Sendmail extends Mage_Core_Block_Template
{
    protected function _construct()
    {
        parent::_construct();
    }

	protected function _toHtml()
    {
		$standard = Mage::getModel('bradesco/standard');
		$order_id = $this->getRequest()->getParam('order_id');
		$order = $standard->getOrder($order_id);

		$subject = Mage::getStoreConfig('system/store/name', $order->getStoreId()) . ' - Segunda via de Boleto';
		$from = Mage::getStoreConfig('trans_email/ident_general/email', $order->getStoreId());
		$to = $order->getCustomerEmail();

		$html = '<html><body>';

        $html .= $this->__('Enviando boleto ao cliente...') . '<br /><br />';
        $html .= 'Assunto: ' . $subject . '<br />';
        $html .= 'De: ' . $from . '<br />';
        $html .= 'Para: ' . $to . '<br />';
		$html .= '<br />';

		$retSend = $this->sendEmail($subject, $from, $to, $order_id, $order);

        if ($retSend == 1) {
			$html .= '<b>' . $this->__('Boleto enviado ao cliente com sucesso.') . '</b>';
		}
		else {
			$html .= '<b>' . $this->__('Falha ao enviar o boleto.') . ' - ' . $retSend . '</b>';
		}

        $html .= '</body></html>';

        return $html;
    }

    public function getPaymentInfoHtml()
    {
        return $this->getChildHtml('payment_info');
    }

	public function sendEmail($subject, $from, $to, $order_id, $order) {
		$html  = 'Caro cliente, <br /><br />';
		$html .= 'Clique no link abaixo para ver e imprimir o boleto. <br /><br />';
		$html .= '<a href="' . Mage::getUrl("Bradesco/standard/view/order_id/$order_id") . '" target="boleto">Segunda via do boleto.</a>';
		$html .= '<br /><br />';
		$html .= 'Atenciosamente, <br /><br />';
		$html .= Mage::getStoreConfig('system/store/name', $order->getStoreId());

		$ret = $this->getStandard()->sendHTMLemail($html, $from, $to, $subject);

		return($ret);
	}

	public function getStandard()
    {
        return Mage::getSingleton('bradesco/standard');
    }

}

