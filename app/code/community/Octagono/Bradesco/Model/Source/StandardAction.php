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
class Octagono_Bradesco_Model_Source_StandardAction
{
    public function toOptionArray()
    {
        return array(
            array('value' => Octagono_Bradesco_Model_Standard::PAYMENT_TYPE_AUTH, 'label' => Mage::helper('Bradesco')->__('Authorization')),
            array('value' => Octagono_Bradesco_Model_Standard::PAYMENT_TYPE_SALE, 'label' => Mage::helper('Bradesco')->__('Sale')),
        );
    }
}

