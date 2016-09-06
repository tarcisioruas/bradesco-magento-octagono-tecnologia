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
class Octagono_Bradesco_Block_Standard_Redirect extends Mage_Core_Block_Abstract
{
    protected function _construct()
    {
        $this->setTemplate('bradesco/standard/redirect.phtml');
        parent::_construct();
    }

    protected function _toHtml()
    {
        $standard = Mage::getModel('bradesco/standard');
		$order_id = $this->getRequest()->getParam('order_id');

        $html  = '<html>';
		$html .= '<head>';
		$html .= '<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />';
		$html .= $this->getScriptCode();
		$html .= '</head>';
		$html .= '<body>';
        $html .= '<p align="center">' . $this->__('Voc&ecirc; ser&aacute; redirecionado para o Bradesco em alguns instantes. . Se isso n&atilde;o acontecer, clique no bot&atilde;o abaixo.') . '</p>';
		$html .= $this->getButtons();

		if ($standard->getConfigData('popup')) {
			$html .= '<script type="text/javascript">abrePopup();</script>';
		}
		else {
			$html .= '<script type="text/javascript">window.location.href="' . $standard->getBradescoUrl($standard->getOrder($order_id)->getId()). '";</script>';
		}
        $html .= '</body></html>';

        return $html;
    }

	private function getFormCode() {
		$standard = Mage::getModel('bradesco/standard');
		$order_id = $this->getRequest()->getParam('order_id');

		// versão popup
		if ($standard->getConfigData('popup')) {
			$form = '<form id = "bradesco_standard_checkout" name="bradesco_standard_checkout" action="' . $standard->getBradescoUrl() . '" method="post" target="mpg_popup" onsubmit="javascript:fabrewin()">';
		}
		else {
			$form = '<form id = "bradesco_standard_checkout" name="bradesco_standard_checkout" action="' . $standard->getBradescoUrl() . '" method="post">';
		}

		foreach ($standard->getStandardCheckoutFormFields($standard->getOrder($order_id)->getId()) as $field=>$value) {
				$form .= '<input type="hidden" name="' . $field . '" value="' . $value . '" />';
		}

		$form .= '</form>';

		return($form);
	}

	private function getButtons() {
		$standard = Mage::getModel('bradesco/standard');

		$form  = '<p align="center">';
		$form .= '<a href="javascript:abrePopup()"><img border="0" src="' . $standard->getSkinUrl('images/bradesco.jpg') . '" /></a><br />';
		$form .= '<button onclick="abrePopup()">Clique aqui para entrar na p&aacute;gina da Bradesco</button>';
		$form .= '</p>';

		return($form);
	}

	private function getScriptCode() {
		$standard = Mage::getModel('bradesco/standard');
		$order_id = $this->getRequest()->getParam('order_id');

		$script = '<script language="JavaScript" type="text/javascript">';
		$script .= 'var retorno;';
		$script .= 'var mpg_popup;';

		$script .= 'window.name="loja";';
		$script .= 'function fabrewin() {';
		$script .= '	if(navigator.appName.indexOf("Netscape") != -1) {';
		$script .= '		mpg_popup = window.open("", "mpg_popup","toolbar=0,location=0,directories=0,status=1,menubar=0,scrollbars=1,resizable=0,screenX=0,screenY=0,left=0,top=0,width=765,height=540");';
		$script .= '	}';
		$script .= '	else {';
		$script .= '		mpg_popup = window.open("", "mpg_popup","toolbar=0,location=0,directories=0,status=1,menubar=0,scrollbars=1,resizable=1,screenX=0,screenY=0,left=0,top=0,width=765,height=540");';
		$script .= '	}';
		$script .= '	return true;';
		$script .= '}';

		$script .= 'function redirect(page, time) {';
		$script .= '	window.setTimeout("window.location.href = \'" + page + "\'", time);';
		$script .= '}';

		$script .= 'function abrePopup() {';
		$script .= '	mpg_popup = window.open("' . $standard->getBradescoUrl($standard->getOrder($order_id)->getId()) . '", "boleto_php","toolbar=0,location=0,directories=0,status=1,menubar=0,scrollbars=1,resizable=1,screenX=0,screenY=0,left=0,top=0,width=765,height=540");';
		$script .= '	if (mpg_popup) {';
		$script .= '		redirect("' . $standard->getUrlSuccess(). '", 500);';
		$script .= '	}';
		$script .= '}';

		$script .= '</script>';

		return($script);
	}
}

