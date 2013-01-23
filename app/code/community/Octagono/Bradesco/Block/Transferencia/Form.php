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
class Octagono_Bradesco_Block_Transferencia_Form extends Octagono_Bradesco_Block_Transferencia_Standard
{
    protected function _construct()
    {
        $this->setTemplate('bradesco/transferencia/form.phtml');
        parent::_construct();
    }

	public function getTransferencia()
    {
		return Mage::getModel('bradesco/transferencia');
    }

	public function getCustomText() {
		return ($this->getTransferencia()->getConfigData('custom_text'));
	}
}

