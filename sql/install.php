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

$sql = array();

$sql[] = 'CREATE TABLE `' . _DB_PREFIX_ . 'crypto_pay_refund` (
  `id_crypto_refund` int(11) NOT NULL AUTO_INCREMENT,
  `id` varchar(150) NOT NULL,
  `currency` varchar(10) NOT NULL,
  `amount` float NOT NULL,
  `debit_currency` varchar(10) NOT NULL,
  `debit_amount` float NOT NULL,
  `created` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `reason` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `payment_id` varchar(150) NOT NULL,
  `status` varchar(50) NOT NULL,
  PRIMARY KEY (`id_crypto_refund`)
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'crypto_pay_logs` (
  `id` int(15) NOT NULL AUTO_INCREMENT,
  `method` text NOT NULL,
  `type` varchar(150) NOT NULL,
  `message` text NULL,
  `created_at` datetime NULL,
  `data` longtext NULL,
  PRIMARY KEY (`id`)
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'crypto_pay_transactions` (
  `id_crypto_pay` int(15) NOT NULL AUTO_INCREMENT,
  `id` varchar(150) DEFAULT NULL,
  `amount` float NOT NULL,
  `amount_refunded` float NOT NULL,
  `created` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `time_window` int(10) DEFAULT NULL,
  `refunded` varchar(10) DEFAULT NULL,
  `recipient` varchar(250) DEFAULT NULL,
  `order_id` varchar(100) DEFAULT NULL,
  `metadata` text DEFAULT NULL,
  `live_mode` varchar(10) DEFAULT NULL,
  `description` varchar(100) DEFAULT NULL,
  `data_url` varchar(100) DEFAULT NULL,
  `customer_id` varchar(100) DEFAULT NULL,
  `currency` varchar(5) DEFAULT NULL,
  `crypto_amount` float DEFAULT NULL,
  `crypto_currency` varchar(50) NOT NULL,
  `status` varchar(50) NOT NULL,
  `id_cart` int(11) DEFAULT NULL,
  `id_shop` int(11) DEFAULT NULL,
  `id_order` int(11) DEFAULT NULL,
  `remaining_time` timestamp NULL,
  `resource_type` text NOT NULL,
  `resource_id` text DEFAULT NULL,
  `resource` text DEFAULT NULL,
  PRIMARY KEY (`id_crypto_pay`)
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

foreach ($sql as $query) {
    Db::getInstance()->execute($query);
}
