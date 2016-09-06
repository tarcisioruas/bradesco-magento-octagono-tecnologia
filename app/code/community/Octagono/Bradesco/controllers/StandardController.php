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
class Octagono_Bradesco_StandardController extends Mage_Core_Controller_Front_Action
{
    /**
     * Order instance
     */
    protected $_order;

    /**
     *  Get order
     *
     *  @param    none
     *  @return	  Mage_Sales_Model_Order
     */
    public function getOrder()
    {
        if ($this->_order == null) {
        }
        return $this->_order;
    }

    protected function _expireAjax()
    {
        if (!Mage::getSingleton('checkout/session')->getQuote()->hasItems()) {
            $this->getResponse()->setHeader('HTTP/1.1','403 Session Expired');
            exit;
        }
    }

    /**
     * Get singleton with Bradesco strandard order transaction information
     *
     * @return Octagono_Bradesco_Model_Standard
     */
    public function getStandard()
    {
        return Mage::getSingleton('bradesco/standard');
    }

    /**
     * When a customer chooses Bradesco on Checkout/Payment page
     *
     */
    public function pedidoAction()
    {
		$this->getResponse()->setHeader('Content-Type', 'text/html; charset=ISO-9660-1')->setHeader('Vary', 'Accept');
        $this->getResponse()->setBody($this->getLayout()->createBlock('bradesco/standard_pedido')->toHtml());
    }

    public function falhaAction()
    {
        $this->getResponse()->setBody($this->getLayout()->createBlock('bradesco/standard_falha')->toHtml());
    }

	public function autorizaAction()
    {
        $this->getResponse()->setBody($this->getLayout()->createBlock('bradesco/standard_autoriza')->toHtml());
    }

    /**
     * When a customer chooses Bradesco on Checkout/Payment page
     *
     */
    public function redirectAction()
    {
        $session = Mage::getSingleton('checkout/session');
        $session->setBradescoStandardQuoteId($session->getQuoteId());

        /** set the quote as inactive after back from paypal    */
        $session->getQuote()->setIsActive(false)->save();

		$this->getStandard()->getOrder()->sendNewOrderEmail();

        Mage::dispatchEvent('checkout_onepage_controller_success_action');

		$this->getResponse()->setBody($this->getLayout()->createBlock('bradesco/standard_redirect')->toHtml());

        $session->unsQuoteId();
        $session->clear();
    }

    public function viewAction() {
		$this->getResponse()->setBody($this->getLayout()->createBlock('bradesco/standard_redirect')->toHtml());
    }

	 public function sendmailAction() {
        $this->getResponse()->setBody($this->getLayout()->createBlock('bradesco/standard_sendMail')->toHtml());
	}

    public function captureAction()
    {
        $this->getResponse()->setBody($this->getLayout()->createBlock('bradesco/standard_capture')->toHtml());
    }

    /**
     * When a customer cancel payment from Bradesco.
     */
    public function cancelAction()
    {
        $session = Mage::getSingleton('checkout/session');
        $session->setQuoteId($session->getBradescoStandardQuoteId(true));

        // cancel order
        if ($session->getLastRealOrderId()) {
            $order = Mage::getModel('sales/order')->loadByIncrementId($session->getLastRealOrderId());
            if ($order->getId()) {
                $order->cancel()->save();
            }
        }

        /*we are calling getBradescoStandardQuoteId with true parameter, the session object will reset the session if parameter is true.
        so we don't need to manually unset the session*/
        //$session->unsBradescoStandardQuoteId();

        //need to save quote as active again if the user click on cacanl payment from Bradesco
        //Mage::getSingleton('checkout/session')->getQuote()->setIsActive(true)->save();
        //and then redirect to checkout one page
        $this->_redirect('checkout/cart');
     }

    /**
     * when Bradesco returns
     * The order information at this point is in POST
     * variables.  However, you don't want to "process" the order until you
     * get validation from the IPN.
     */
    public function  successAction()
    {
        $this->_redirect('checkout/onepage/success', array('_secure'=>true));
    }

    /**
     * when Bradesco returns via ipn
     * cannot have any output here
     * validate IPN data
     * if data is valid need to update the database that the user has
     */
    public function ipnAction()
    {
        if (!$this->getRequest()->isPost()) {
            $this->_redirect('');
            return;
        }

        if($this->getStandard()->getDebug()){
            $debug = Mage::getModel('bradesco/api_debug')
                ->setApiEndpoint($this->getStandard()->getBradescoUrl())
                ->setRequestBody(print_r($this->getRequest()->getPost(),1))
                ->save();
        }

        $this->getStandard()->setIpnFormData($this->getRequest()->getPost());
        $this->getStandard()->ipnPostSubmit();
    }
}

