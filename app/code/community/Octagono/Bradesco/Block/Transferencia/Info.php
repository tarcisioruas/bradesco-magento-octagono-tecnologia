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
class Octagono_Bradesco_Block_Transferencia_Info extends Mage_Payment_Block_Info
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('bradesco/transferencia/info.phtml');
    }

    /**
     * Retrieve current order model instance
     *
     * @return Mage_Sales_Model_Order
     */
    public function getOrder()
    {
        return Mage::registry('current_order');
    }

    /**
     * Retrieve current order model instance
     *
     * @return Mage_Sales_Model_Order
     */
    public function getQuote()
    {
        $quote = $this->getTransferencia()->getQuote($this->getOrder()->getQuoteId());
		return $quote;
    }

	public function getTransferencia()
    {
		return Mage::getModel('bradesco/transferencia');
    }

	public function getCcTypeName()
    {
        $ret = '';
		if ($this->getInfo()->getCcType() == 'MA') {
			$ret = 'Mastercard';
		}
		else if ($this->getInfo()->getCcType() == 'DI') {
			$ret = 'Diners';
		}
		else {
			$ret = $this->getInfo()->getCcType();
		}

		return ($ret);
    }

	public function getTransId() {
		return ($this->getInfo()->getCcTransId());
	}

	public function getCcParcelas() {
		return ($this->getInfo()->getData('cc_parcelas'));
	}

	}

