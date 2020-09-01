<?php
/**
 * payment.php
 *
 * Copyright (c) 2017 PayDunya
 *
 * LICENSE:
 *
 * This payment module is free software; you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published
 * by the Free Software Foundation; either version 3 of the License, or (at
 * your option) any later version.
 *
 * This payment module is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY
 * or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser General Public
 * License for more details.
 *
 * @copyright 2017 PayDunya
 * @license   http://www.gnu.org/licenses/gpl-2.0.html
 * @link      https://paydunya.com
 */


class PaydunyaPaymentModuleFrontController extends ModuleFrontController
{
    public $ssl = true;


    public function initContent()
    {
        // Call parent init content method
        parent::initContent();

        // Check if currency is accepted
        if (!$this->checkCurrency())
            Tools::redirect('index.php?controller=order');

        // Check if cart exists and all fields are set
        $cart = $this->context->cart;
        if ($cart->id_customer == 0 || $cart->id_address_delivery == 0 || $cart->id_address_invoice == 0 || !$this->module->active)
            Tools::redirect('index.php?controller=order&step=1');

        // Check if module is enabled
        $authorized = false;
        foreach (Module::getPaymentModules() as $module)
            if ($module['name'] == $this->module->name)
                $authorized = true;
        if (!$authorized)
            die('This payment method is not available.');


        // Check if customer exists
        $customer = new Customer($cart->id_customer);
        session_start();
        $_SESSION["id_customer"] = $cart->id_customer;

        if (!Validate::isLoadedObject($customer))
            Tools::redirect('index.php?controller=order&step=1');

        Tools::redirect(Tools::getHttpHost( true ).__PS_BASE_URI__."commande?order_id");

//        global $smarty;
//        $smarty->tpl_vars['base_dir_ssl']->value;

        // Will use the file modules/cheque/views/templates/front/test.tpl
//        $this->setTemplate('module:paydunya/views/templates/front/test.tpl');
    }



    private function checkCurrency()
    {
        // Get cart currency and enabled currencies for this module
        $currency_order = new Currency($this->context->cart->id_currency);
        $currencies_module = $this->module->getCurrency($this->context->cart->id_currency);

        // Check if cart currency is one of the enabled currencies
        if (is_array($currencies_module))
            foreach ($currencies_module as $currency_module)
                if ($currency_order->id == $currency_module['id_currency'])
                    return true;

        // Return false otherwise
        return false;
    }



    private function get_paydunya_args($customer) {
        $cart = $this->context->cart;
        $order_cart_id = $cart->id;
        $order_total_amount = $cart->getOrderTotal(true, Cart::BOTH);
        $ttx = $order_total_amount - $cart->getOrderTotal(false);
        $order_total_tax_amount = $ttx < 0 ? : $ttx;
        $order_cart_secure_key = $cart->secure_key;
        $order_items = $cart->getProducts(true);
        $order_total_shipping_amount = $cart->getOrderTotal(false, Cart::ONLY_SHIPPING);
        $order_return_url = $this->context->link->getPageLink('order-confirmation', null, null, 'key='.$cart->secure_key.'&id_cart='.$cart->id.'&id_module='.$this->module->id);

        $items = $order_items;
        $paydunya_items = array();
        foreach ($items as $item) {
            $paydunya_items[] = array(
                "name" => $item['name'],
                "quantity" => $item['cart_quantity'],
                "unit_price" => number_format((float)$item['price'], 2, '.', ''),
                "total_price" => number_format((float)$item['total'], 2, '.', ''),
                "description" => strip_tags($item['description_short'])
            );
        }

        $paydunya_args = array(
            "invoice" => array(
                "items" => $paydunya_items,
                "taxes" => [
                    "tax_0" => [
                        "name" => "Frais de livraison",
                        "amount" => $order_total_shipping_amount
                    ],
                    "tax_1" => [
                        "name" => "Taxes",
                        "amount" => $order_total_tax_amount
                    ]
                ],
                "total_amount" => $order_total_amount,
                "description" => "Paiement de " . $order_total_amount . " FCFA pour article(s) achetés sur " . Configuration::get('PS_SHOP_NAME')
            ), "store" => array(
                "name" => Configuration::get('PS_SHOP_NAME'),
                "website_url" => Tools::getHttpHost( true ).__PS_BASE_URI__
            ), "actions" => array(
                "cancel_url" => Tools::getHttpHost( true ).__PS_BASE_URI__,
                "callback_url" => $this->context->link->getModuleLink('paydunya', 'validationipn'),
                "return_url" => $order_return_url
            ), "custom_data" => array(
                "cart_id" => $order_cart_id,
                "order_id" => $this->module->currentOrder,
                "cart_secure_key" => $order_cart_secure_key,
            )
        );

        return $paydunya_args;
    }



}