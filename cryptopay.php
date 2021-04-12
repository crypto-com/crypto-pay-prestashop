<?php
/*
* 2018-2021 Foris Limited ("Crypto.com")
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
*  @copyright  2018-2021 Foris Limited ("Crypto.com")
*  @license    http://www.apache.org/licenses/LICENSE-2.0  Apache License, Version 2.0
*/

require_once _PS_MODULE_DIR_.'cryptopay/classes/cryptopayhelper.php';
require_once _PS_MODULE_DIR_.'cryptopay/classes/cryptopaytransaction.php';

use PrestaShop\PrestaShop\Core\Payment\PaymentOption;

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once _PS_MODULE_DIR_.'cryptopay/classes/cryptopayhelper.php';

class Cryptopay extends PaymentModule
{
    public $details;
    public $owner;
    public $address;
    public $extra_mail_vars;

    public function __construct()
    {
        $this->name = 'cryptopay';
        $this->tab = 'payments_gateways';
        $this->version = '1.0.0';
        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
        $this->author = 'Crypto.com';
        $this->controllers = array('validation');
        $this->is_eu_compatible = 1;
        $this->currencies = true;
        $this->module_key = '7a10b1c0440ed9b43192fd15f8188f31';
        $this->currencies_mode = 'checkbox';
        $this->bootstrap = true;
        parent::__construct();
        $this->displayName = $this->l('Crypto.com Pay');
        $this->description = $this->l(
            'Crypto.com Pay Payment is a Module of Crypto.com Pay,
            which utilises Crypto.com Chain as a high performing native blockchain solution.
            This enables the transaction flows between crypto users and merchants seamless, cost-efficient and secure.'
        );
        if (!count(Currency::checkPaymentCurrencies($this->id))) {
            $this->warning = $this->l('No currency has been set for this module.');
        }
    }

    public function install()
    {
        if (Shop::isFeatureActive()) {
            try {
                Shop::setContext(Shop::CONTEXT_ALL);
            } catch (PrestaShopException $e) {
            }
        }
        if (file_exists(dirname(__FILE__) . '/sql/install.php')) {
            include(dirname(__FILE__) . '/sql/install.php');
        }
        if (!parent::install()
            || !$this->registerHook('paymentOptions')
            || !$this->registerHook('hookDisplayPaymentEU')
            || !$this->registerHook('paymentReturn')
            || !$this->registerHook('orderConfirmation')
            || !$this->registerHook('adminOrder')
            || !$this->installOrderState()
            || !$this->installTab('AdminCryptoPay', 'Crypto.com Pay', 0)
            || !$this->installTab(
                'AdminCryptoPayPayment',
                'Payment(s)',
                (int)Tab::getIdFromClassName('AdminCryptoPay')
            )
            || !$this->installTab(
                'AdminCryptoPayLog',
                'Log(s)',
                (int)Tab::getIdFromClassName('AdminCryptoPay')
            )
            || !$this->installTab(
                'AdminCryptoPaySetting',
                'Setting(s)',
                (int)Tab::getIdFromClassName('AdminCryptoPay')
            )
        ) {
            return false;
        }
        return true;
    }

    public function installTab($class_name, $tab_name, $parent, $active = 1)
    {
        $tab = new Tab();
        $tab->active = $active;
        $tab->class_name = $class_name;
        $tab->name = array();
        foreach (Language::getLanguages(true) as $lang) {
            $tab->name[$lang['id_lang']] = $tab_name;
        }
        if ($parent == 0 && _PS_VERSION_ >= '1.7') {
            $tab->id_parent = (int)Tab::getIdFromClassName('SELL');
            $tab->icon = 'account_balance_wallet';
        } else {
            $tab->id_parent = $parent;
        }
        $tab->module = $this->name;
        return $tab->add();
    }

    /**
     * uninstall tabs created by module
     * @throws PrestaShopException
     */
    public function uninstallTab($class_name)
    {
        $id_tab = (int)Tab::getIdFromClassName($class_name);
        if ($id_tab) {
            $tab = new Tab($id_tab);
            return $tab->delete();
        } else {
            return false;
        }
    }

    public function uninstall()
    {
        Configuration::deleteByName('CRYPTO_PAY_API_MODE');
        $this->unregisterHook('paymentOptions');
        $this->unregisterHook('hookDisplayPaymentEU');
        $this->unregisterHook('paymentReturn');
        $this->unregisterHook('orderConfirmation');
        $this->unregisterHook('adminOrder');
        try {
            $this->uninstallTab('AdminCryptoPay');
            $this->uninstallTab('AdminCryptoPayPayment');
            $this->uninstallTab('AdminCryptoPayLog');
            $this->uninstallTab('AdminCryptoPaySetting');
        } catch (PrestaShopException $e) {
            return false;
        }
        return parent::uninstall();
    }

    /**
     * Load the configuration form
     */
    public function getContent()
    {
        /**
         * If values have been submitted in the form, process.
         */
        $output  = '';
        if (((bool)Tools::isSubmit('submitCryptoPayModule')) == true) {
            $this->postProcess();
            $output .= $this->displayConfirmation("Setting Saved Successfully.");
        }

        $this->context->smarty->assign('module_dir', $this->_path);

        $output .= $this->context->smarty->fetch($this->local_path.'views/templates/admin/configure.tpl');

        return $output.$this->renderForm();
    }

    /**
     * Create the form that will be displayed in the configuration of your module.
     */
    protected function renderForm()
    {
        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitCryptoPayModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(), /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm($this->getConfigForm());
    }

    /**
     * Create the structure of your form.
     */
    protected function getConfigForm()
    {

        $this->context->smarty->assign(array(
            'base_url' => Context::getContext()->shop->getBaseURL(true),
            'is_rewrite_enabled' => Configuration::get('PS_REWRITING_SETTINGS')
        ));
        $webhook_html = $this->display(
            __FILE__,
            'views/templates/admin/webhook.tpl'
        );
        $fields_form = array();
        $fields_form[0]['form'] = [
            'legend' => [
                'title' => $this->l('Payment Configuration'),
            ],
            'description' => $this->l('You must first sign up for an account and get below details ')
                . $this->getHelpLink("https://merchant.crypto.com/", $this->l("Crypto Pay")),
            'input' => [
                [
                    'type' => 'select',
                    'label' => $this->l('API mode'),
                    'desc' => $this->l(
                        'Here you can set Environment of module in which you want to use like for test or in live.'
                    ),
                    'name' => 'CRYPTO_PAY_API_MODE',
                    'required' => false,
                    'default_value' => 'test',
                    'options' => [
                        'query' => [
                            [
                                'id' => 'test',
                                'value' => 'Test',
                                'label' => $this->l('Test')
                            ],
                            [
                                'id' => 'live',
                                'value' => 'Live',
                                'label' => $this->l('Live')
                            ]
                        ],
                        'id' => 'id',
                        'name' => 'value',
                    ]
                ],
                [
                    'type' => 'password',
                    'label' => $this->l('Live Secret Key'),
                    'desc' => $this->l(
                        'You can manage your API keys within the Crypto Pay Developers tab  '
                    ) . $this->getHelpLink(
                        "https://merchant.crypto.com/developers/api_keys",
                        $this->l("Get Keys")
                    ) ,
                    'name' => 'CRYPTO_PAY_LIVE_SECRET_KEY',
                    'required' => false
                ],
                [
                    'type' => 'text',
                    'label' => $this->l('Live Publishable Key'),
                    'desc' => $this->l(
                        'You can manage your API keys within the Crypto Pay Developers tab  '
                    ) . $this->getHelpLink(
                        "https://merchant.crypto.com/developers/api_keys",
                        $this->l("Get Keys")
                    ) ,
                    'name' => 'CRYPTO_PAY_LIVE_PUBLISHABLE_KEY',
                    'required' => false
                ],
                [
                    'type' => 'password',
                    'label' => $this->l('Live Signature Secret '),
                    'desc' => $this->l('This is use to verify the webhooks authenticity.'),
                    'name' => 'CRYPTO_PAY_LIVE_SIGNATURE_SECRET',
                    'required' => false
                ],
                [
                    'type' => 'password',
                    'label' => $this->l('Test Secret Key'),
                    'desc' => $this->l(
                        'You can manage your API keys within the Crypto Pay Developers tab  '
                    ) . $this->getHelpLink(
                        "https://merchant.crypto.com/developers/api_keys",
                        $this->l("Get Keys")
                    ) ,
                    'name' => 'CRYPTO_PAY_TEST_SECRET_KEY',
                    'required' => false
                ],
                [
                    'type' => 'text',
                    'label' => $this->l('Test Publishable Key'),
                    'desc' => $this->l(
                        'You can manage your API keys within the Crypto Pay Developers tab  '
                    ) . $this->getHelpLink(
                        "https://merchant.crypto.com/developers/api_keys",
                        $this->l("Get Keys")
                    ) ,
                    'name' => 'CRYPTO_PAY_TEST_PUBLISHABLE_KEY',
                    'required' => false
                ],
                [
                    'type' => 'password',
                    'label' => $this->l('Test Signature Secret '),
                    'desc' => $this->l('This is use to verify the webhooks authenticity.'),
                    'name' => 'CRYPTO_PAY_TEST_SIGNATURE_SECRET',
                    'required' => false
                ],
                [
                    'type' => 'textarea',
                    'label' => $this->l('Payment Description Text'),
                    'desc' => $this->l('This text will be shown when payment method selected on checkout.'),
                    'name' => 'CRYPTO_PAY_PAYMENT_DESCRIPTION',
                    'required' => false
                ],
                [
                    'type' => 'select',
                    'label' => $this->l('Debug Mode'),
                    'desc' => $this->l(
                        'This is for developers and user to log data and see if any abnormal behaviour.'
                    ),
                    'name' => 'CRYPTO_PAY_API_DEBUG_MODE',
                    'required' => false,
                    'default_value' => 'test',
                    'options' => [
                        'query' => [
                            [
                                'id' => '0',
                                'value' => 'No',
                                'label' => $this->l('No')
                            ],
                            [
                                'id' => '1',
                                'value' => 'Yes',
                                'label' => $this->l('Yes')
                            ]
                        ],
                        'id' => 'id',
                        'name' => 'value',
                    ]
                ],
                [
                    'col' => 8,
                    'type' => 'html',
                    'label' => $this->l('Webhook Url'),
                    'name' => $webhook_html,
                ],
            ],
            'submit' => [
                'title' => $this->l('Save'),
                'class' => 'btn btn-default pull-right'
            ]
        ];
        return $fields_form;
    }

    /**
     * Set values for the inputs.
     */
    protected function getConfigFormValues()
    {
        return array(
            'CRYPTO_PAY_API_MODE' => Configuration::get('CRYPTO_PAY_API_MODE'),
            'CRYPTO_PAY_LIVE_SECRET_KEY' => Configuration::get('CRYPTO_PAY_LIVE_SECRET_KEY'),
            'CRYPTO_PAY_LIVE_PUBLISHABLE_KEY' => Configuration::get('CRYPTO_PAY_LIVE_PUBLISHABLE_KEY'),
            'CRYPTO_PAY_TEST_SECRET_KEY' => Configuration::get('CRYPTO_PAY_TEST_SECRET_KEY'),
            'CRYPTO_PAY_TEST_PUBLISHABLE_KEY' => Configuration::get('CRYPTO_PAY_TEST_PUBLISHABLE_KEY'),
            'CRYPTO_PAY_API_DEBUG_MODE' => Configuration::get('CRYPTO_PAY_API_DEBUG_MODE'),
            'CRYPTO_PAY_PAYMENT_DESCRIPTION' => Configuration::get('CRYPTO_PAY_PAYMENT_DESCRIPTION'),
            'CRYPTO_PAY_LIVE_SIGNATURE_SECRET' => Configuration::get('CRYPTO_PAY_LIVE_SIGNATURE_SECRET'),
            'CRYPTO_PAY_TEST_SIGNATURE_SECRET' => Configuration::get('CRYPTO_PAY_TEST_SIGNATURE_SECRET'),
        );
    }

    /**
     * Save form data.
     */
    protected function postProcess()
    {
        $password_values = [
            'CRYPTO_PAY_TEST_SECRET_KEY',
            'CRYPTO_PAY_TEST_SIGNATURE_SECRET',
            'CRYPTO_PAY_LIVE_SECRET_KEY',
            'CRYPTO_PAY_LIVE_SIGNATURE_SECRET'
        ];
        $form_values = $this->getConfigFormValues();
        foreach (array_keys($form_values) as $key) {
            $current_value = trim(Tools::getValue($key));
            if (in_array($key, $password_values)
                && (trim(Tools::getValue($key))=='')
                && (isset($form_values[$key]) && $form_values[$key])
            ) {
                $current_value = $form_values[$key];
            }
            Configuration::updateValue($key, $current_value);
        }
    }

    public function hookPaymentOptions($params)
    {
        try {
            if (!$this->active) {
                return;
            }

            if (!$this->checkCurrency($params['cart'])) {
                return;
            }

            $cryptoPayment = new PaymentOption();
            $cryptoPayment->setCallToActionText($this->l('Crypto.com Pay'))
                ->setAction($this->context->link->getModuleLink($this->name, 'validation', array(), true))
                ->setInputs([])
                ->setAdditionalInformation(
                    $this->context->smarty->fetch(
                        'module:cryptopay/views/templates/front/payment_description.tpl'
                    )
                )
                ->setLogo(Media::getMediaPath(_PS_MODULE_DIR_.$this->name.'/views/img/icon.svg'));
            if (Configuration::get('CRYPTO_PAY_PAYMENT_DESCRIPTION')) {
                $cryptoPayment->setAdditionalInformation(Configuration::get('CRYPTO_PAY_PAYMENT_DESCRIPTION'));
            }
            return [
                $cryptoPayment,
            ];
        } catch (Exception $exception) {
            CryptoPayHelper::log(
                "Hook Exception",
                $exception->getMessage(),
                'Exception',
                'hookPaymentOptions'
            );
        }
    }

    public function hookDisplayPaymentEU($params)
    {
        try {
            if (!$this->active || !$this->checkCurrency($params['cart'])) {
                return;
            }
            return array(
                'cta_text' => $this->l('Crypto.com Pay'),
                'action' => $this->context->link->getModuleLink($this->name, 'validation', array(), true)
            );
        } catch (Exception $exception) {
            CryptoPayHelper::log(
                "Hook Exception",
                $exception->getMessage(),
                'Exception',
                'hookDisplayPaymentEU'
            );
        }
    }

    public function hookOrderConfirmation($params)
    {
        try {
            $order = $params['order'];
            if ($order) {
                if ($order->module === $this->name) {
                    $payments = $order->getOrderPayments();
                    if (count($payments) >= 1) {
                        $payment = $payments[0];
                        $paymentId = $payment->transaction_id;
                        $response = (array)CryptoPayTransactions::getTransaction($paymentId);
                        $this->smarty->assign(
                            array(
                                'total_to_pay' => Tools::displayPrice(
                                    $params['order']->getOrdersTotalPaid(),
                                    new Currency($params['order']->id_currency),
                                    false
                                ),
                                'shop_name' => $this->context->shop->name,
                                'id_order' => $params['order']->id,
                                'reference' => $params['order']->reference,
                                'status' => $response['status'],
                                'crypto_amount' => $response['crypto_amount'],
                                'recipient' => $response['recipient'],
                                'crypto_currency' => $response['crypto_currency'],
                                'paymentId' => $paymentId
                            )
                        );
                        return $this->fetch('module:cryptopay/views/templates/hook/payment_return.tpl');
                    } else {
                        $this->smarty->assign(
                            array(
                                'order_reference' => $order->reference
                            )
                        );
                        return $this->fetch('module:cryptopay/views/templates/hook/payment_error.tpl');
                    }
                    return;
                }
            }
        } catch (Exception $exception) {
            CryptoPayHelper::log(
                "Hook Exception",
                $exception->getMessage(),
                'Exception',
                'hookOrderConfirmation'
            );
        }
    }

    public function hookPaymentReturn($params)
    {
        try {
            if (!$this->active) {
                return;
            }
            $state = $params['order']->getCurrentState();
            if (in_array(
                $state,
                array(
                    Configuration::get('CRYPTO_PAY_OS_WAITING'),
                    Configuration::get('PS_OS_PAYMENT'),
                    Configuration::get('PS_OS_OUTOFSTOCK'),
                    Configuration::get('PS_OS_OUTOFSTOCK_UNPAID')
                )
            )
            ) {
                $order = $params['order'];
                if ($order->module === $this->name) {
                    $payments = $order->getOrderPayments();
                    if (count($payments) >= 1) {
                        $payment = $payments[0];
                        $paymentId = $payment->transaction_id;
                        $response = CryptoPayTransactions::getTransaction($paymentId);
                        $this->smarty->assign(
                            array(
                                'total_to_pay' => Tools::displayPrice(
                                    $params['order']->getOrdersTotalPaid(),
                                    new Currency($params['order']->id_currency),
                                    false
                                ),
                                'shop_name' => $this->context->shop->name,
                                'id_order' => $params['order']->id,
                                'reference' => $params['order']->reference,
                                'status' => $response['status'],
                                'crypto_amount' => $response['crypto_amount'],
                                'recipient' => $response['recipient'],
                                'crypto_currency' => $response['crypto_currency']
                            )
                        );
                    }
                }
            } else {
                $this->smarty->assign('status', 'failed');
            }
            return $this->fetch('module:cryptopay/views/templates/hook/payment_return.tpl');
        } catch (Exception $exception) {
            CryptoPayHelper::log(
                "Hook Exception",
                $exception->getMessage(),
                'Exception',
                'hookOrderConfirmation'
            );
        }
    }

    /**
     * Hook: AdminOrder details
     */
    public function hookAdminOrder($params)
    {
        try {
            $order_id = Tools::getValue('id_order', 0);
            try {
                $order = new Order($order_id);
            } catch (PrestaShopDatabaseException $e) {
                return '';
            } catch (PrestaShopException $e) {
                return '';
            }
            /* Check if the order was paid with this Addon and display the Transaction details */
            if ($order->module === $this->name) {
                // Retrieve the transaction details
                $transaction = (array)CryptoPayTransactions::getTransactionByOrderId($order_id);
                if (!empty($transaction) && isset($transaction['id']) && $transaction['id']) {
                    $cryptoPayTransactions = new CryptoPayTransactions();
                    $refunds = (array)$cryptoPayTransactions->getRefunds($transaction['id']);
                    $refunded_amount =0;
                    if (!empty($refunds)) {
                        foreach ($refunds as $refund) {
                            $refunded_amount += $refund['amount']*.01;
                        }
                    }

                    $this->context->smarty->assign(
                        array(
                            'id' => $transaction['id'],
                            'status' => $transaction['status'],
                            'created' => $transaction['created'],
                            'order_total' => number_format(
                                $order->getOrdersTotalPaid(),
                                2,
                                '.',
                                ''
                            ),
                            'order_currency' => Currency::getCurrencyInstance($order->id_currency)->iso_code,
                            'refunded_amount' => $refunded_amount,
                            'crypto_amount' => $transaction['crypto_amount'],
                            'recipient' => $transaction['recipient'],
                            'token' => Tools::getAdminTokenLite('AdminCryptoPayPayment'),
                            'crypto_currency' => $transaction['crypto_currency'],
                            'refunds' => $refunds
                        )
                    );
                    return $this->display(__FILE__, 'views/templates/admin/admin-order.tpl');
                }
            }
        } catch (Exception $exception) {
            CryptoPayHelper::log(
                "Hook Exception",
                $exception->getMessage(),
                'Exception',
                'hookOrderConfirmation'
            );
        }
    }

    public function checkCurrency($cart)
    {
        $currency_order = new Currency($cart->id_currency);
        $currencies_module = $this->getCurrency($cart->id_currency);
        if (is_array($currencies_module)) {
            foreach ($currencies_module as $currency_module) {
                if ($currency_order->id == $currency_module['id_currency']) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Create order state
     * @return boolean
     */
    public function installOrderState()
    {
        if (!Configuration::get('CRYPTO_PAY_OS_WAITING')
            || !Validate::isLoadedObject(new OrderState(Configuration::get('CRYPTO_PAY_OS_WAITING')))) {
            $order_state = new OrderState();
            $order_state->name = array();
            foreach (Language::getLanguages() as $language) {
                $order_state->name[$language['id_lang']] = 'Awaiting for Crypto.com payment';
            }
            $order_state->send_email = false;
            $order_state->color = '#011f42';
            $order_state->hidden = false;
            $order_state->delivery = false;
            $order_state->logable = false;
            $order_state->invoice = false;
            if ($order_state->add()) {
                $source = _PS_MODULE_DIR_.$this->name.'/views/img/payment.png';
                $destination = _PS_ROOT_DIR_.'/img/os/'.(int) $order_state->id.'.gif';
                copy($source, $destination);
            }

            Configuration::updateValue('CRYPTO_PAY_OS_WAITING', (int) $order_state->id);
        }
        return true;
    }
    public function getHelpLink($href, $title, $new_tab = true)
    {
        $this->context->smarty->assign(
            array(
                'href' => $href,
                'new_tab' => $new_tab,
                'title' => $title
            )
        );
        return $this->display(__FILE__, 'views/templates/admin/help_link.tpl');
    }
}
