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

class CryptoPayHelper
{
    const PAYMENT_ENDPOINT = 'payments';
    const REFUND_ENDPOINT = 'refunds';

    protected $base_url = 'https://pay.crypto.com/api/';
    protected $secret_key = '';
    protected $mode = 'test';
    protected $publishable_key = '';
    protected $signature_secret = '';

    public function __construct()
    {
        $mode = $this->getPaymentMode();
        if ($mode =='live') {
            $this->setSecretKey(Configuration::get('CRYPTO_PAY_LIVE_SECRET_KEY'));
            $this->setPublishableKey(Configuration::get('CRYPTO_PAY_LIVE_PUBLISHABLE_KEY'));
            $this->setSignatureSecret(Configuration::get('CRYPTO_PAY_LIVE_SIGNATURE_SECRET'));
        } else {
            $this->setSecretKey(Configuration::get('CRYPTO_PAY_TEST_SECRET_KEY'));
            $this->setPublishableKey(Configuration::get('CRYPTO_PAY_TEST_PUBLISHABLE_KEY'));
            $this->setSignatureSecret(Configuration::get('CRYPTO_PAY_TEST_SIGNATURE_SECRET'));
        }
        $this->setMode($mode);
    }

    /**
     * @param string $signature_secret
     */
    public function setSignatureSecret($signature_secret)
    {
        $this->signature_secret = $signature_secret;
    }

    /**
     * @return string
     */
    public function getSignatureSecret()
    {
        return $this->signature_secret;
    }
    /**
     * @return string
     */
    public function getPublishableKey()
    {
        return $this->publishable_key;
    }

    /**
     * @param string $publishable_key
     */
    public function setPublishableKey($publishable_key)
    {
        $this->publishable_key = $publishable_key;
    }
    /**
     * @param string $secret_key
     */
    public function setSecretKey($secret_key)
    {
        $this->secret_key = $secret_key;
    }
    /**
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->base_url;
    }

    /**
     * @return string
     */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * @param string $mode
     */
    public function setMode($mode)
    {
        $this->mode = $mode;
    }
    /**
     * @return string
     */
    public function getSecretKey()
    {
        return $this->secret_key;
    }

    public function getPayments($crypto_payment_id = 0)
    {
        $endpoint = self::PAYMENT_ENDPOINT;
        if ($crypto_payment_id) {
            $endpoint .= '/'.$crypto_payment_id;
        }
        return $this->makeRequest($endpoint);
    }

    public function createPayment($payment_data)
    {
        $response = $this->makeRequest(self::PAYMENT_ENDPOINT, $payment_data);
        return $response;
    }

    public function cancelPayment($crypto_payment_id)
    {
        $endpoint = self::PAYMENT_ENDPOINT;
        if ($crypto_payment_id) {
            $endpoint .= '/'.$crypto_payment_id.'/cancel';
        }
        return $this->makeRequest($endpoint);
    }

    public function makeRefund($refund_data)
    {
        return $this->makeRequest(self::REFUND_ENDPOINT, $refund_data);
    }

    protected function makeRequest($endpoint = self::PAYMENT_ENDPOINT, $params = array())
    {
        $url = $this->getBaseUrl();
        $curl = curl_init();
        if ($endpoint) {
            $url = $this->getBaseUrl().$endpoint;
        } else {
            $url = $this->getBaseUrl().self::PAYMENT_ENDPOINT;
        }
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLINFO_HEADER_OUT, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_FORBID_REUSE, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($curl, CURLOPT_TIMEOUT, 10);
        curl_setopt($curl, CURLOPT_URL, $url);
        // add colon as per instructions in api doc
        curl_setopt($curl, CURLOPT_USERPWD, $this->getSecretKey() . ':');
        if (!empty($params)) {
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($params));
        }
        $response = curl_exec($curl);
        self::log('Crypto Pay Response'."\n".$endpoint, $response, 'info', 'makeRequest');
        return json_decode($response, true);
    }

    public function getPaymentMode()
    {
        return Configuration::get('CRYPTO_PAY_API_MODE');
    }

    public static function log($message = '', $response = '', $type = '', $method = '')
    {
        if (Configuration::get('CRYPTO_PAY_API_DEBUG_MODE')) {
            $db = Db::getInstance();
            $db->insert(
                'crypto_pay_logs',
                array(
                    'method' => pSQL($method),
                    'type' => pSQL($type),
                    'message' => pSQL($message),
                    'data' => pSQL($response, true),
                    'created_at' =>  pSQL(date("Y-m-d H:i:s"))
                )
            );
        }
    }
}
