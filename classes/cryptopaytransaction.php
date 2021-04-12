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

class CryptoPayTransactions extends ObjectModel
{
    protected $refund_fields = array(
        'id_crypto_refund',
        'id',
        'currency',
        'amount',
        'debit_currency',
        'debit_amount',
        'created',
        'reason',
        'description',
        'payment_id',
        'status',
        'id_order'
    );
    public static $definition = array(
        'table' => 'crypto_pay_transactions',
        'primary' => 'id_crypto_pay',
        'multilang' => false,
        'fields' => array(
            'id_crypto_pay' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'id' => array('type' => self::TYPE_STRING, 'db_type' => 'text'),
            'amount' => array('type' => self::TYPE_FLOAT, 'db_type' => 'float'),
            'amount_refunded' => array('type' => self::TYPE_FLOAT, 'db_type' => 'float'),
            'created' => array('type' => self::TYPE_STRING, 'db_type' => 'text'),
            'crypto_currency' => array('type' => self::TYPE_STRING, 'db_type' => 'text'),
            'crypto_amount' => array('type' => self::TYPE_FLOAT, 'db_type' => 'float'),
            'currency' => array('type' => self::TYPE_STRING, 'db_type' => 'text'),
            'customer_id' => array('type' => self::TYPE_STRING, 'db_type' => 'text'),
            'data_url' => array('type' => self::TYPE_STRING, 'db_type' => 'text'),
            'description' => array('type' => self::TYPE_STRING, 'db_type' => 'text'),
            'live_mode' => array('type' => self::TYPE_STRING, 'db_type' => 'text'),
            'metadata' => array('type' => self::TYPE_STRING, 'db_type' => 'text'),
            'order_id' => array('type' => self::TYPE_STRING, 'db_type' => 'text'),
            'recipient' => array('type' => self::TYPE_STRING, 'db_type' => 'text'),
            'status' => array('type' => self::TYPE_STRING, 'db_type' => 'text'),
            'refunded' => array('type' => self::TYPE_STRING, 'db_type' => 'text'),
            'time_window' => array('type' => self::TYPE_INT, 'db_type' => 'int'),
            'id_cart' => array('type' => self::TYPE_INT, 'db_type' => 'int'),
            'id_shop' => array('type' => self::TYPE_INT, 'db_type' => 'int'),
            'id_order' => array('type' => self::TYPE_INT, 'db_type' => 'int'),
        ),
    );

    public $id_crypto_pay;
    public $id;
    public $amount;
    public $amount_refunded;
    public $created;
    public $crypto_currency;
    public $crypto_amount;
    public $currency;
    public $customer_id;
    public $data_url;
    public $description;
    public $live_mode;
    public $metadata;
    public $order_id;
    public $recipient;
    public $status;
    public $refunded;
    public $time_window;
    public $id_cart;
    public $id_shop;
    public $id_order;

    public function addTransaction($payment_data)
    {
        if (!isset($payment_data['id_cart'])) {
            $payment_data['id_cart'] = 0;
        }
        $payment = self::getTransactionByCartId($payment_data['id_cart']);
        if ($payment && isset($payment['id']) && $payment['id']) {
            $payment_data['id_crypto_pay'] = $payment['id_crypto_pay'];
            $this->updateTransaction($payment_data);
        } else {
            foreach ($payment_data as $key => $resp) {
                if (is_array($resp)) {
                    $resp = json_encode($resp);
                }
                if (in_array($key, array_keys(self::$definition['fields']))) {
                    if ($key=='created') {
                        $resp = pSQL(date("Y-m-d H:i:s", $resp));
                    }
                    $this->{$key} = pSQL($resp);
                }
            }
            $this->force_id = true;
            $this->add();
        }
    }
    public function getRefunds($payment_id)
    {
        $sql = 'SELECT * FROM `' ._DB_PREFIX_ . 'crypto_pay_refund` WHERE payment_id LIKE "'.pSQL($payment_id).'"';
        try {
            $result = Db::getInstance()->executeS($sql);
            if (!empty($result) && !$result['0']) {
                $result = array();
            }
            return $result;
        } catch (PrestaShopDatabaseException $e) {
            return array();
        }
    }
    public function addRefund($refund_data)
    {
        $refund = $this->getRefunds($refund_data['id']);
        if ($refund && $refund['id']) {
            $this->updateRefund($refund);
        } else {
            $sql = 'INSERT INTO `' ._DB_PREFIX_ . 'crypto_pay_refund` SET ';
            foreach ($this->refund_fields as $value) {
                if (isset($refund_data[$value]) && is_array($refund_data[$value])) {
                    $refund_data[$value] = json_encode($refund_data[$value]);
                }
                if (isset($refund_data[$value])) {
                    if ($value=='created') {
                        $refund_data[$value] = pSQL(date("Y-m-d H:i:s", $refund_data[$value]));
                    }
                    $sql .= '`' . bqSQL($value) . "` = '".pSQL($refund_data[$value])."',";
                }
            }
            $sql = rtrim($sql, ', ');
            Db::getInstance()->execute($sql);
        }
    }

    public function updateRefund($refund_data)
    {
        $sql = 'UPDATE `' ._DB_PREFIX_ . 'crypto_pay_refund` SET ';
        foreach ($this->refund_fields as $value) {
            if (isset($refund_data[$value]) && is_array($refund_data[$value])) {
                unset($refund_data[$value]);
                continue;
            }
            if (isset($refund_data[$value])) {
                if ($value=='created') {
                    $refund_data[$value] = pSQL(date("Y-m-d H:i:s", $refund_data[$value]));
                }
                $sql .= '`' . bqSQL($value) . '` = "'.pSQL($refund_data[$value]).'", ';
            }
        }
        $sql = rtrim($sql, ', ');
        if (isset($refund_data['id_crypto_refund']) && $refund_data['id_crypto_refund']) {
            $sql .= " WHERE id_crypto_refund = '".(int)$refund_data['id_crypto_refund']."'";
        }
        Db::getInstance()->execute($sql);
    }

    public function updateTransaction($payment_data)
    {
        foreach ($payment_data as $key => $payment) {
            if (is_array($payment)) {
                $payment_data[$key] = json_encode($payment_data[$key]);
            }
            if (in_array($key, array_keys(self::$definition['fields']))) {
                if ($key=='created') {
                    if (is_string($key) && $payment) {
                        $payment_data[$key] = pSQL(date("Y-m-d H:i:s", $payment));
                    }
                }
            } else {
                unset($payment_data[$key]);
            }
        }
        if (!empty($payment_data)) {
            foreach (self::$definition['fields'] as $field => $field_info) {
                if (!empty($field_info) && !isset($payment_data[$field])) {
                    $payment_data[$field] = '';
                }
            }
        }

        if (isset($payment_data['id_crypto_pay']) && $payment_data['id_crypto_pay']) {
            Db::getInstance()->update(
                'crypto_pay_transactions',
                $payment_data,
                ' id_crypto_pay = "'.(int)$payment_data['id_crypto_pay'].'"'
            );
        } elseif (isset($payment_data['id']) && $payment_data['id']) {
            Db::getInstance()->update(
                'crypto_pay_transactions',
                $payment_data,
                ' id LIKE "'.pSQL(trim($payment_data['id'])).'"'
            );
        }
    }


    public static function getTransaction($cryptoPaymentId)
    {
        $sql = new DbQuery();
        $sql->select('*');
        $sql->from('crypto_pay_transactions');
        $sql->where('id LIKE "'.pSQL(trim($cryptoPaymentId)).'"');
        return Db::getInstance()->getRow($sql);
    }

    public static function getTransactionByOrderId($id_order)
    {
        $sql = new DbQuery();
        $sql->select('*');
        $sql->from('crypto_pay_transactions');
        $sql->where('id_order = "'.(int)$id_order.'"');
        return Db::getInstance()->getRow($sql);
    }

    public static function getTransactionByCartId($id_cart)
    {
        $sql = new DbQuery();
        $sql->select('*');
        $sql->from('crypto_pay_transactions');
        $sql->where('id_cart = "'.(int)$id_cart.'"');
        return Db::getInstance()->getRow($sql);
    }
}
