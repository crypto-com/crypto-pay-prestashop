<?php
/*
* Copyright 2022 Crypto.com
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
*  @copyright  2022 Crypto.com
*  @license    http://www.apache.org/licenses/LICENSE-2.0  Apache License, Version 2.0
*/

class AdminCryptoPayLogController extends ModuleAdminController
{
    public function __construct()
    {
        $this->bootstrap  = true;
        $this->table      = 'crypto_pay_logs';
        $this->identifier = 'id';
        $this->list_no_link = true;
        $this->_orderBy = 'id';
        $this->_orderWay = 'DESC';
        $this->addRowAction('deletelog');
        parent::__construct();
        $this->fields_list = array(
            'id'       => array(
                'title' => $this->l('ID'),
                'type'  => 'text',
                'align' => 'center',
                'class' => 'fixed-width-xs',
            ),
            'method'  => array(
                'title' => $this->l('ACTION'),
                'type'  => 'text',
                'align' => 'center',
            ),
            'type'     => array(
                'title' => $this->l('TYPE'),
                'type'  => 'text',
                'align' => 'center',
            ),
            'message' => array(
                'title' => $this->l('MESSAGE'),
                'type'  => 'text',
                'align' => 'center',
            ),
            'data' => array(
                'title' => $this->l('RESPONSE'),
                'type'  => 'text',
                'align' => 'center',
                'search' => false,
                'class' => 'fixed-width-xs',
                'callback' => 'viewLogButton'
            ),
            'created_at' => array(
                'title' => $this->l('CREATED AT'),
                'type' => 'datetime',
                'align' => 'center',

            ),
        );
    }

    public function initToolbar()
    {
        $this->toolbar_btn['export'] = array(
            'href' => self::$currentIndex.'&export'.$this->table.'&token='.$this->token,
            'desc' => $this->l('Export')
        );
    }
    public function initPageHeaderToolbar()
    {
        if (empty($this->display)) {
            $this->page_header_toolbar_btn['delete_logs'] = array(
                'href' => $this->context->link->getAdminLink('AdminCryptoPayLog').'&delete_logs',
                'desc' => $this->l('Delete All Logs'),
                'icon' => 'process-icon-eraser'
            );
        }
        parent::initPageHeaderToolbar();
    }
    public function renderList()
    {
        return parent::renderList();
    }

    public function viewLogButton($product_error, $data)
    {
        $data['data'] =  $product_error;
        $data['token'] = $this->token;
        $this->context->smarty->assign(
            $data
        );
        return $this->context->smarty->fetch(
            _PS_MODULE_DIR_ . 'cryptopay/views/templates/admin/log.tpl'
        );
    }

    public function postProcess()
    {
        if (Tools::getIsset('delete_logs')) {
            $result = $this->deleteLogs();
            if (isset($result['success']) && $result['success'] == true) {
                $this->confirmations[] = $result['message'];
            } else {
                $this->errors[] = $result['message'];
            }
        }
        if (Tools::getIsset('deletelog')) {
            $result = $this->deleteLogs(Tools::getValue('id'));
            if (isset($result['success']) && $result['success'] == true) {
                $this->confirmations[] = $result['message'];
            } else {
                $this->errors[] = $result['message'];
            }
        }
        parent::postProcess();
    }

    public function deleteLogs($log_id = '')
    {
        $db = Db::getInstance();
        try {
            if (empty($log_id)) {
                $res = $db->delete(
                    'crypto_pay_logs'
                );
            } else {
                $res = $db->delete(
                    'crypto_pay_logs',
                    'id='.(int)$log_id
                );
            }
            if ($res) {
                return array(
                    'success' => true,
                    'message' => $this->l("Log(s) deleted successfully")
                );
            } else {
                return array(
                    'success' => false,
                    'message' => $this->l("Failed to delete Log(s)")
                );
            }
        } catch (Exception $e) {
            PrestaShopLogger::addLog($e->getMessage());
        }
    }
}
