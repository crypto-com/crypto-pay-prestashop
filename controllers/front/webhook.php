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

/**
 * @since 1.5.0
 */

require_once _PS_MODULE_DIR_.'cryptopay/classes/cryptopayhelper.php';
require_once _PS_MODULE_DIR_.'cryptopay/classes/cryptopaytransaction.php';

class CryptoPayWebhookModuleFrontController extends ModuleFrontController
{
    /**
     * @see FrontController::postProcess()
     */
    public function postProcess()
    {
        $cryptopayhelper = new CryptoPayHelper();
        $signature_secret = $cryptopayhelper->getSignatureSecret();
        $signature_secret = trim($signature_secret);
        $signature_secret = trim($signature_secret, "\t");
        $response = Tools::file_get_contents('php://input');
        CryptoPayHelper::log('Webhook Fired', $response, 'info', 'postProcess');
        if ($response && isset($_SERVER['HTTP_PAY_SIGNATURE'])) {
            $signature = $_SERVER['HTTP_PAY_SIGNATURE'];
            $parts = explode(',', $signature);
            $time = false;
            $signature = false;
            CryptoPayHelper::log('Webhook', json_encode($parts), 'info', 'postProcess');
            foreach ($parts as $part) {
                $part = explode('=', $part);
                CryptoPayHelper::log('Webhook', json_encode($part), 'info', 'postProcess');
                if ($part[0] == 't') {
                    $time = $part[1];
                } elseif ($part[0] == 'v1') {
                    $signature = $part[1];
                }
            }
            $string = $time . '.' . $response;
            $hash = hash_hmac('sha256', $string, $signature_secret);
            if (!hash_equals($signature, $hash)) {
                $error = array(
                    'message' => 'Invalid Webhook Request',
                    'signature_secret ' =>  $signature_secret ,
                    'hash' => $hash,
                );
                CryptoPayHelper::log('Webhook', json_encode($error), 'info', 'postProcess');
                exit;
            }
            $response = json_decode($response, true);
            if (!empty($response)) {
                if (isset($response['data']['object']['id']) && $response['data']['object']['id']) {
                    try {
                        if (($response['type'] == 'payment.captured') || ($response['type'] == 'payment.created')) {
                            $cryptopaytransactions = new CryptoPayTransactions();
                            $cryptopaytransactions->updateTransaction($response['data']['object']);
                        }
                    } catch (PrestaShopDatabaseException $e) {
                        CryptoPayHelper::log($e->getMessage());
                        exit;
                    } catch (PrestaShopException $e) {
                        CryptoPayHelper::log($e->getMessage());
                        exit;
                    }
                    if (isset($response['data']['object']) && !empty($response['data']['object'])
                        && ($response['type'] == 'payment.refund_transferred')
                        && ($response['data']['object']['status'] == 'succeeded')
                    ) {
                        $id_order_state = Configuration::get('PS_OS_REFUND');
                        $cryptoPayTransactions = new CryptoPayTransactions();
                        $cryptoPayHelper = new CryptoPayHelper();
                        $cryptoPayTransactions->addRefund($response['data']['object']);
                        $payments = $cryptoPayHelper->getpayments($response['data']['object']['payment_id']);
                        $cryptoPayTransactions->updateTransaction($payments);
        
                        $order = new Order((int)Order::getOrderByCartId((int)$payments['order_id']));
                        CryptoPayHelper::log('$order', json_encode($order), 'Order Data', 'refund_transferred');
                        if ($order->getCurrentState()
                            && ($order->getCurrentState() != $id_order_state)
                            && ($payments['amount_refunded']==$payments['amount'])
                        ) {
                            $history = new OrderHistory();
                            $history->id_order = $order->id;
                            $history->changeIdOrderState((int)$id_order_state, $order->id, true);
                            try {
                                $history->addWithemail();
                            } catch (PrestaShopDatabaseException $e) {
                                CryptoPayHelper::log($e->getMessage());
                                exit;
                            } catch (PrestaShopException $e) {
                                CryptoPayHelper::log($e->getMessage());
                                exit;
                            }
                            exit;
                        }
                    }
                }
            }
        }
        exit;
    }
}
