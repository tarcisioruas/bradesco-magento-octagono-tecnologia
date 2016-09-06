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
class Octagono_Bradesco_Block_Standard_Falha extends Mage_Core_Block_Abstract
{
    protected function _construct()
    {
        parent::_construct();
    }

    protected function _toHtml()
    {
		$order_id = $this->getRequest()->getParam('numOrder');
		$merchantid = $this->getRequest()->getParam('merchantid');
		$cod = $this->getRequest()->getParam('cod');
		$errordesc = $this->getRequest()->getParam('errordesc');

        $html  = '<html>';
		$html .= '<head>';
		$html .= '<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />';
		$html .= '</head>';
		$html .= '<body>';
        $html .= '<p>Falha ao processar o pedido</p>';
        $html .= '<p>N&uacute;mero do Pedido: ' . $order_id . '</p>';
        $html .= '<p>N&uacute;mero da Loja: ' . $merchantid . '</p>';
        $html .= '<p>C&oacute;digo do erro: ' . $cod . '</p>';
        $html .= '<p>Descri&ccedil;&atilde;o do erro: ' . utf8_encode($errordesc) . '</p>';

        $html .= '</body></html>';

        return $html;
    }
}

