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
class Octagono_Bradesco_Block_Standard_Form extends Mage_Payment_Block_Form
{
    protected function _construct()
    {
        $this->setTemplate('bradesco/standard/form.phtml');
        parent::_construct();
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
     * Get current quote
     *
     * @return Mage_Sales_Model_Quote
     */
    public function getQuote()
    {
        return $this->getCheckout()->getQuote();
    }

	/**
     * Get current quote
     *
     * @return Mage_Sales_Model_Quote
     */
    public function getNumParcelas()
    {
        $ret = $this->getMethod()->getConfigData('parcelas');

		if (empty($ret) || !is_numeric($ret)) {
			$ret = 1;
		}

		return $ret;
    }

		/**
     * Get current quote
     *
     * @return Mage_Sales_Model_Quote
     */
    public function getGrandTotal()
    {
        $ret = $this->getQuote()->getGrandTotal();

		return $ret;
    }

	public function getParcelas() {
	  $parcelas = array();
	  $valorParcela = 0;

	  for ($i=1; $i<=$this->getNumParcelas(); $i++) {
		   $valorParcela = $this->getGrandTotal() / $i;
		   $valorParcela = sprintf('%01.2f', $valorParcela);
		   $valorParcela = str_replace('.', ',', $valorParcela);

		   $parcelas[$i] = "<b>$i</b>x de $valorParcela";
	  }

 	  return($parcelas);
	}

	public function getStandard()
    {
		return Mage::getModel('bradesco/standard');
    }

	public function getCustomText() {
		return ($this->getStandard()->getConfigData('custom_text'));
	}
}

