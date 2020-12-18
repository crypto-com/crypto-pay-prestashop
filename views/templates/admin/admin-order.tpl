{*
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
*}

<div class="row">
    <div class="col-md-7">
        <div class="panel">
            <div class="panel-heading">
                <i class="icon-money"></i>
                Crypto.com Pay Payment
            </div>
            <div class="panel-body">
                <div class="table-responsive">
                <table class="table">
                    <tbody>
                    <tr>
                        <td><b>{l s='Payment ID.' mod='cryptopay'}</b></td>
                        <td><b>{$id|escape:'htmlall':'UTF-8'}</b></td>
                    </tr>
                    <tr>
                        <td><b>{l s='Amount in Crypto Currency.' mod='cryptopay'}</b></td>
                        <td><b>{$crypto_amount|escape:'htmlall':'UTF-8'}&nbsp;{$crypto_currency|escape:'htmlall':'UTF-8'}&nbsp; ({$order_currency|escape:'htmlall':'UTF-8'}&nbsp;{$order_total|escape:'htmlall':'UTF-8'})</b></td>
                    </tr>
                    <tr>
                        <td><b>{l s='Created' mod='cryptopay'}</b></td>
                        <td><b>{$created|escape:'htmlall':'UTF-8'}</b></td>
                    </tr>
                    <tr>
                        <td><b>{l s='Status.' mod='cryptopay'}</b></td>
                        <td>{if $status=='succeeded'}
                                <span class="btn btn-success">{$status|escape:'htmlall':'UTF-8'}</span></p>
                            {elseif $status=='pending'}
                                <span class="btn btn-primary"> {$status|escape:'htmlall':'UTF-8'}</span></p>
                            {elseif $status=='cancelled'}
                                <span class="btn btn-danger">{$status|escape:'htmlall':'UTF-8'}</span></p>
                            {else}
                                {$status|escape:'htmlall':'UTF-8'}
                            {/if}
                        </td>
                    </tr>
                    </tbody>
                </table>
                </div>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                        <tr>
                            <th>Refund ID</th>
                            <th>Amount</th>
                            <th>Created</th>
                            <th>Status</th>
                        </tr>
                        </thead>
                        <tbody>
                        {if !empty($refunds)}
                        {foreach $refunds as $refund}
                        <tr>
                            <td><b>{$refund['id']|escape:'htmlall':'UTF-8'}</b></td>
                            <td><b>{$refund['amount']*0.01|escape:'htmlall':'UTF-8'}&nbsp;{$refund['currency']|escape:'htmlall':'UTF-8'}</b></td>
                            <td><b>{$refund['created']|escape:'htmlall':'UTF-8'}</b></td>
                            <td>{if $refund['status']=='succeeded'}
                                    <span class="btn btn-success">{$refund['status']|escape:'htmlall':'UTF-8'}</span></p>
                                {elseif $refund['status']=='pending'}
                                    <span class="btn btn-primary"> {$refund['status']|escape:'htmlall':'UTF-8'}</span></p>
                                {elseif $refund['status']=='cancelled'}
                                    <span class="btn btn-danger">{$refund['status']|escape:'htmlall':'UTF-8'}</span></p>
                                {else}
                                    {$refund['status']|escape:'htmlall':'UTF-8'}
                                {/if}
                            </td>
                        </tr>
                        {/foreach}
                        {/if}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-5">
        <div class="panel">
            <div class="panel-heading">
                <i class="icon-money"></i>
                Crypto.com Pay Refund
            </div>
            <div class="panel-body">
                {if $status == 'succeeded'}
                    <div id="message" class="form-horizontal">
                        <input type="hidden" id="crypto_amount" name="crypto_amount" value="{$crypto_amount|escape:'htmlall':'UTF-8'}">
                        <input type="hidden" id="crypto_currency" name="crypto_currency" value="{$crypto_currency|escape:'htmlall':'UTF-8'}">
                        <input type="hidden" id="crypto_payment_id" name="crypto_payment_id" value="{$id|escape:'htmlall':'UTF-8'}">
                        <input type="hidden" id="order_currency" name="order_currency" value="{$order_currency|escape:'htmlall':'UTF-8'}">
                        <input type="hidden" id="order_total" name="order_total" value="{$order_total|escape:'htmlall':'UTF-8'}">
                        <div class="form-group">
                            <label class="control-label col-lg-3">Amount</label>
                            <div class="col-lg-9">
                                <input type="text" id="amount" value="{$order_total|escape:'htmlall':'UTF-8'-$refunded_amount|escape:'htmlall':'UTF-8'}" class="text" name="amount" />
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-lg-3">Reason</label>
                            <div class="col-lg-9">
                                <select class="form-control" name="reason" id="reason">
                                    <option value="0" selected="selected">-</option>
                                    <option value="duplicate">Duplicate</option>
                                    <option value="fraudulent">Fraudulent</option>
                                    <option value="requested_by_customer">Requested By Customer</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="control-label col-lg-3">Description</label>
                            <div class="col-lg-9">
                                <textarea id="description" class="textarea-autosize" name="description"></textarea>
                            </div>
                        </div>
                        <button type="button" onclick="shipAmazonOrder(this);" id="submitRefund" class="btn btn-primary pull-left" name="submitRefund">
                            Refund
                        </button>
                    </div>
                    <script>
                        function shipAmazonOrder(buttonObj) {
                            var crypto_amount = $('#crypto_amount').val();
                            var crypto_currency = $('#crypto_currency').val();
                            var amount = $("#amount").val();
                            var reason = $("#reason").val();
                            var crypto_payment_id = $("#crypto_payment_id").val();
                            var order_currency = $("#order_currency").val();
                            var order_total = $("#order_total").val();
                            var description = $('#description').val();

                            if (parseFloat(amount) > parseFloat(order_total)) {
                                alert('Refund can not be greater than '+order_total);
                            } else {
                                $(buttonObj).attr('disabled',true);
                                $.ajax({
                                    type: 'POST',
                                    url: 'ajax-tab.php',
                                    data: {
                                        controller: 'AdminCryptoPayPayment',
                                        ajax: true,
                                        action: 'refundOrder',
                                        crypto_amount: crypto_amount,
                                        crypto_currency: crypto_currency,
                                        reason: reason,
                                        order_total: order_total,
                                        order_currency: order_currency,
                                        amount: amount,
                                        crypto_payment_id: crypto_payment_id,
                                        description: description,
                                        token: '{$token|escape:'htmlall':'UTF-8'}'
                                    },
                                    success: function (res) {
                                        try {
                                            var response = JSON.parse(res);
                                            if(response && response.success) {
                                                alert(response.message);
                                                 location.reload(); 
                                            } else {
                                                alert(response.message);
                                            }
                                            $(buttonObj).attr('disabled',false);
                                        } catch (e) {
                                            console.log(e.message);
                                        }
                                    }
                                });
                            }
                        }
                    </script>
                {/if}
            </div>
        </div>
    </div>
</div>
