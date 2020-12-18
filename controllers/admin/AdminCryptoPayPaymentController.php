<?php
/*
* 2018-2020 Foris Limited ("Crypto.com")
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
*  @copyright  2018-2020 Foris Limited ("Crypto.com")
*  @license    http://www.apache.org/licenses/LICENSE-2.0  Apache License, Version 2.0
*/

require_once _PS_MODULE_DIR_.'cryptopay/classes/cryptopaytransaction.php';
require_once _PS_MODULE_DIR_.'cryptopay/classes/cryptopayhelper.php';

class AdminCryptoPayPaymentController extends ModuleAdminController
{
    public function __construct()
    {
        $this->bootstrap = true;
        $this->table = 'crypto_pay_transactions';
        $this->identifier = 'id_crypto_pay';
        $this->_orderBy = 'id_crypto_pay';
        $this->_orderWay = 'DESC';
        $this->lang = false;
        $this->list_no_link = true;
        $this->addRowAction('delete');
        $this->className = 'CryptoPayTransactions';
        parent::__construct();
        $this->fields_list = array(
            'id_crypto_pay' => array(
                'title' => $this->l('ID'),
                'type' => 'text',
            ),
            'id' => array(
                'title' => $this->l('Crypto.com Payment ID'),
                'type' => 'text',
            ),
            'id_cart' => array(
                'title' => $this->l('Cart ID'),
                'type' => 'text',
            ),
            'id_order' => array(
                'title' => $this->l('Order ID'),
                'type' => 'text',
                'class' => 'text-center',
                'callback' => 'viewStoreOrder'
            ),
            'crypto_amount' => array(
                'title' => $this->l('Amount'),
                'type' => 'text',
            ),
            'crypto_currency' => array(
                'title' => $this->l('Currency'),
                'type' => 'text',
            ),
            'created' => array(
                'title' => $this->l('Created'),
                'type' => 'datetime',
            ),
            'status' => array(
                'title' => $this->l('Status'),
                'type' => 'text',
                'class' => 'text-center',
                'callback' => 'viewCancelPaymentButton'
            ),
        );
        $this->bulk_actions = array(
            'delete' => array(
                'text' => $this->module->l('Delete', 'AdminCedAmazonAccountController'),
                'confirm' => $this->module->l('Delete Selected Account(s) ?', 'AdminCedAmazonAccountController'),
                'icon' => 'icon-trash'
            )
        );
        if (Tools::getIsset('cancelpayment') && Tools::getValue('cancelpayment')) {
            $cryptopayhelper = new CryptoPayHelper();
            $cryptopaytransaction = new CryptoPayTransactions();
            $response = $cryptopayhelper->cancelPayment(Tools::getValue('cancelpayment'));
            if (!empty($response) && !isset($response['error'])) {
                $cryptopaytransaction->updateTransaction($response);
            } elseif (isset($response['error']['code'])) {
                $this->errors[] = $response['error']['code'];
            } else {
                $this->errors[] = $this->l('Failed to Cancel Payment.');
            }
        }
    }
    public function viewStoreOrder($field_data, $rowdata)
    {
        if ($rowdata['id_order']!='') {
            $link = new LinkCore();
            $order_url = $link->getAdminLink('AdminOrders').'&vieworder=&id_order='.$rowdata['id_order'];
            $this->context->smarty->assign(
                array(
                    'order_url' => $order_url,
                    'id_order' => $rowdata['id_order'],
                )
            );
            return $this->context->smarty->fetch(
                _PS_MODULE_DIR_ . 'cryptopay/views/templates/admin/view_store_order.tpl'
            );
        } else {
            return $field_data;
        }
    }
    public function viewCancelPaymentButton($field_data, $rowdata)
    {
        if ($rowdata['status']=='pending') {
            $link = new LinkCore();
            $order_url = $link->getAdminLink('AdminCryptoPayPayment').'&cancelpayment='.$rowdata['id'];
            $this->context->smarty->assign(
                array(
                    'order_url' => $order_url,
                    'id_crypto_pay' => $rowdata['id_crypto_pay'],
                    'status' => $rowdata['status'],
                )
            );
            return $this->context->smarty->fetch(
                _PS_MODULE_DIR_ . 'cryptopay/views/templates/admin/cancel_order.tpl'
            );
        } else {
            return $field_data;
        }
    }
    public function ajaxProcessRefundOrder()
    {
        $params = Tools::getAllValues();
        if (isset($params['crypto_payment_id'])
            && $params['crypto_payment_id']
            && isset($params['amount']) && $params['amount']
        ) {
            $cryptopayHelper = new CryptoPayHelper();
            $refund_data = array(
                'payment_id' => trim($params['crypto_payment_id']),
                'amount'     => $params['amount']*100
            );
            if (isset($params['reason']) && $params['reason']) {
                $refund_data['reason'] = trim($params['reason']);
            }
            if (isset($params['description']) && $params['description']) {
                $refund_data['description'] = trim($params['description']);
            }
            $response = $cryptopayHelper->makeRefund($refund_data);
            if (!isset($response['error']) && isset($response['id'])) {
                $cryptoPayTransactions = new CryptoPayTransactions();
                $cryptoPayTransactions->addRefund($response);
                $response = array('success' => true, 'message' => 'Refund Created With '.$response['id']);
            } else {
                $response = array('success' => false, 'message' => $response['error']['code']);
            }
            die(Tools::jsonEncode($response));
        } else {
            $response = array('success' => false, 'message' => 'Invalid Amount');
            die(Tools::jsonEncode($response));
        }
    }
}
