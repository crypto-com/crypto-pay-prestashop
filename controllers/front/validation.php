<?php
/*
* Copyright 2024 Crypto.com
*
* NOTICE OF LICENSE
*
* Licensed under the Apache License, Version 2.0 (the "License");
* you may not use this file except in compliance with the License.
* You may obtain a copy of the License at
*
* http://www.apache.org/licenses/LICENSE-2.0
*
* Unless required by applicable law or agreed to in writing, software
* distributed under the License is distributed on an "AS IS" BASIS,
* WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
* See the License for the specific language governing permissions and
* limitations under the License.
*
*  @author     Crypto.com <pay@crypto.com>
*  @copyright  2024 Crypto.com
*  @license    http://www.apache.org/licenses/LICENSE-2.0  Apache License, Version 2.0
*/

/**
 * @since 1.5.0
 */

require_once _PS_MODULE_DIR_ . 'cryptopay/classes/cryptopayhelper.php';
require_once _PS_MODULE_DIR_ . 'cryptopay/classes/cryptopaytransaction.php';

class CryptoPayValidationModuleFrontController extends ModuleFrontController
{
    public $ssl = true;

    /**
     * Initialize cart controller.
     *
     * @see FrontController::init()
     */
    public function init()
    {
        parent::init();
    }

    /**
     * @see FrontController::initContent()
     */
    public function initContent()
    {
        $paymentId = Tools::getValue('paymentId');
        if (!$paymentId) {
            parent::initContent();
            if (Configuration::isCatalogMode() && Tools::getValue('action') === 'show') {
                Tools::redirect('index.php');
            }
            $cart = $this->context->cart;
            if (($cart->id_customer == 0)
                || ($cart->id_address_delivery == 0)
                || ($cart->id_address_invoice == 0)
                || (!$this->module->active)
            ) {
                Tools::redirect('index.php?controller=order&step=1');
            }
            $authorized = false;
            foreach (Module::getPaymentModules() as $module) {
                if ($module['name'] == 'cryptopay') {
                    $authorized = true;
                    break;
                }
            }
            if (!$authorized) {
                die($this->module->l('This payment method is not available.', 'validation'));
            }
            $customer = new Customer($cart->id_customer);
            if (!Validate::isLoadedObject($customer)) {
                Tools::redirect('index.php?controller=order&step=1');
            }
            $currency = $this->context->currency;
            $total = (float)$cart->getOrderTotal(true, Cart::BOTH);
            if (!$this->module->active) {
                return;
            }
            $cryptopayhelper = new CryptoPayHelper();
            $publishable_key = $cryptopayhelper->getPublishableKey();
            if ($total) {
                $payment = CryptoPayTransactions::getTransactionByCartId((int)$cart->id);
                if (!empty($payment) && ($payment['amount'] == (number_format($total, 2) * 100))) {
                    $response = $payment;
                } else {
                    $currency = new Currency($currency->id);
                    $zero_currencies = array(
                        'BIF',
                        'CLP',
                        'DJF',
                        'GNF',
                        'JPY',
                        'KMF',
                        'KRW',
                        'PYG',
                        'RWF',
                        'UGX',
                        'VND',
                        'VUV',
                        'XAF',
                        'XOF',
                        'XPF'
                    );
                    if (in_array($currency->iso_code, $zero_currencies)) {
                        $total = number_format($total, 0, '.', '');
                    } else {
                        $total = number_format($total, 2, '.', '') * 100;
                    }
                    $payment_data = [
                        'amount' => $total,
                        'currency' => $currency->iso_code,
                        'description' => 'Cart ID #' . $cart->id,
                        'customer_id' => $customer->id,
                        'order_id' => $cart->id,
                        'metadata' => [
                            'prestashop_cart_id' => $cart->id,
                            'shop_name' => $this->context->shop->name,
                            'plugin_name' => 'prestashop',
                            'customer_name' => $customer->firstname . ' ' . $customer->lastname,
                        ],
                    ];

                    $response = $cryptopayhelper->createPayment($payment_data);
                }

                if (!empty($response) && isset($response['id'])) {
                    $response['id_shop'] = $this->context->shop->id;
                    $response['id_cart'] = $this->context->cart->id;
                    $response['id_order'] = 0;
                    $cryptopaytransactions = new CryptoPayTransactions();
                    $cryptopaytransactions->addTransaction($response);
                    $this->context->smarty->assign(array(
                        'crypto_paypemt_status' => 'success',
                        'total_to_pay' => Tools::displayPrice(
                            $total,
                            new Currency($currency->id),
                            false
                        ),
                        'shop_name' => $this->context->shop->name,
                        'publishable_key' => $publishable_key,
                        'payment_id' => $response['id'],
                        'prestashop_cart_id' => $cart->id,
                        'cart_detailed' => $this->render('checkout/_partials/cart-detailed'),
                        'cart_detailed_totals' => $this->render('checkout/_partials/cart-detailed-totals'),
                        'cart_summary_items_subtotal' => $this->render(
                            'checkout/_partials/cart-summary-items-subtotal'
                        ),
                        'cart_summary_subtotals_container' => $this->render(
                            'checkout/_partials/cart-summary-subtotals'
                        ),
                        'cart_summary_totals' => $this->render(
                            'checkout/_partials/cart-summary-totals'
                        ),
                        'cart_detailed_actions' => $this->render('checkout/_partials/cart-detailed-actions'),
                        'cart_voucher' => $this->render('checkout/_partials/cart-voucher'),
                    ));
                    $this->setTemplate('module:cryptopay/views/templates/front/payment_return.tpl');
                } elseif (isset($response['error'])) {
                    $this->context->smarty->assign(
                        array(
                            'crypto_paypemt_status' => 'failed',
                            'error' => $response['error'],
                        )
                    );
                    CryptoPayHelper::log(
                        'Payment create Error',
                        json_encode($response),
                        'Error',
                        'Payment Object'
                    );
                    $this->setTemplate('module:cryptopay/views/templates/front/payment_error.tpl');
                }
            } else {
                $this->context->smarty->assign(array('crypto_paypemt_status' => 'failed'));
                CryptoPayHelper::log(
                    'Payment create Error',
                    json_encode(array('Payment not needed.')),
                    'Error',
                    'Payment Object'
                );
                $this->setTemplate('module:cryptopay/views/templates/front/payment_error.tpl');
            }
        }
    }

    public function displayAjaxGetPayment()
    {
        $paymentId = Tools::getValue('paymentId');
        $paymentId = trim($paymentId);
        if ($paymentId) {
            $type = Tools::getValue('type');
            if ($type == 'verify') {
                $cryptoPayHelper = new CryptoPayHelper();
                $payment = $cryptoPayHelper->getPayments($paymentId);
                $store_payment = CryptoPayTransactions::getTransaction($paymentId);
                $payment['id_crypto_pay'] = (int)$store_payment['id_crypto_pay'];
            } else {
                $payment = CryptoPayTransactions::getTransaction($paymentId);
            }

            if (!empty($payment) && isset($payment['id']) && $payment['id']) {
                $payment_status = trim($payment['status']);
                if (in_array(trim($payment_status), array('succeeded', 'cancelled'))) {
                    $total = (float)$this->context->cart->getOrderTotal(true, Cart::BOTH);
                    $currency = $this->context->currency;
                    $customer = $this->context->customer;
                    if ($payment_status == 'succeeded') {
                        $this->module->validateOrder(
                            (int)$this->context->cart->id,
                            (int)Configuration::get('PS_OS_PAYMENT'),
                            $total,
                            $this->module->displayName,
                            null,
                            array('transaction_id' => $paymentId),
                            (int)$currency->id,
                            false,
                            $customer->secure_key
                        );
                    } elseif ($payment_status == 'pending') {
                        $this->module->validateOrder(
                            (int)$this->context->cart->id,
                            (int)Configuration::get('CRYPTO_PAY_OS_WAITING'),
                            $total,
                            $this->module->displayName,
                            null,
                            array('transaction_id' => $paymentId),
                            (int)$currency->id,
                            false,
                            $customer->secure_key
                        );
                    } elseif ($payment_status == 'cancelled') {
                        $this->module->validateOrder(
                            (int)$this->context->cart->id,
                            (int)Configuration::get('PS_OS_CANCELLED'),
                            $total,
                            $this->module->displayName,
                            null,
                            array('transaction_id' => $paymentId),
                            (int)$currency->id,
                            false,
                            $customer->secure_key
                        );
                    }
                    $payment['id_shop'] = (int)$this->context->shop->id;
                    $payment['id_cart'] = (int)$this->context->cart->id;
                    $payment['id_order'] = (int)$this->module->currentOrder;
                    $payment['id_crypto_pay'] = (int)$payment['id_crypto_pay'];
                    $cryptopaytransactions = new CryptoPayTransactions();
                    $cryptopaytransactions->updateTransaction($payment);
                    
                    $url = Context::getContext()->link->getPageLink('order-confirmation');
                    
                    $query = parse_url($url, PHP_URL_QUERY);
                    
                    if ($query) {
                        $url .= '&id_cart=' .
                                    $this->context->cart->id . '&id_module=' . $this->module->id .
                                    '&id_order=' . $this->module->currentOrder . '&key=' . $customer->secure_key;
                    } else {
                        $url .= '?id_cart=' .
                                    $this->context->cart->id . '&id_module=' . $this->module->id .
                                    '&id_order=' . $this->module->currentOrder . '&key=' . $customer->secure_key;
                    }    
                        
                    die(
                        json_encode(
                            array(
                                'redirect' => $url
                            )
                        )
                    );
                }
                die(json_encode(array('waiting' => '5')));
            } else {
                die(json_encode(array('waiting' => '5')));
            }
        } else {
            die(json_encode(array('waiting' => '5 ')));
        }
    }
}
