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

{extends "$layout"}
{block name="content"}
    <script src="https://js.crypto.com/sdk?publishable-key={$publishable_key|escape:'htmlall':'UTF-8'}"></script>
    <div class="col-sm-3 col-md-3 col-xs-3 col-lg-3"></div>
    <div class="col-sm-6 col-md-6 col-xs-6 col-lg-6">
        <section class="card" id="cryptopay_container">
            <div class="card-block">
                {$crypto_paypemt_status|escape:'htmlall':'UTF-8'}
                <h1>{l s='Please do not close or reload this window while payment, It will refresh automatically once payment done .' mod='cryptopay'}</h1>
                {$cart_summary_items_subtotal|cleanHtml nofilter}
                {$cart_summary_subtotals_container|cleanHtml nofilter}
                {$cart_summary_totals|cleanHtml nofilter}
                <p>{l s='Click on below Button to make payment.' mod='cryptopay'}</p>
                <div class="cryptopay_button_container" id="pay-button" data-payment-id="{$payment_id|escape:'htmlall':'UTF-8'}"></div>
            </div>
            <!-- Trigger the modal with a button when payment approve -->
            <button id="open_loader" style="display: none;" type="button" class="btn btn-info btn-lg" data-toggle="modal" data-target="#myModal"></button>
            <!-- Modal -->
            <div class="modal fade" id="myModal" role="dialog">
                <div class="modal-dialog modal-sm">
                    <div class="modal-content">
                        <div class="modal-body">
                            <p>{l s='Payment Approved. Redirecting to merchant store....'  mod='cryptopay'}</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
    <div class="col-sm-3 col-md-3 col-xs-3 col-lg-3"></div>
    <style>
        {literal}
        #cryptopay_container {
            box-shadow: 2px 2px 8px 0 rgba(0,0,0,.2);
            background: #fff;
        }
        .cryptopay_button_container {
            text-align: center;
        }
        {/literal}
    </style>
    <script>
        try {
            var paymentId = '{$payment_id|escape:'htmlall':'UTF-8'}';
            cryptopay.Button({
                createPayment: function (actions) {
                    return actions.payment.fetch(paymentId)
                },
                onApprove: function (data, actions) {
                    $("#open_loader").click();
                    isPaymentCompletedById(paymentId, 'approved');
                }
            }).render("#pay-button");
        } catch (e) {
            alert('Close Old payment QR Code window then try again.');
        }
        window.onerror = function (msg, url, lineNo, columnNo, error) {
            var string = msg.toLowerCase();
            var substring = "refresh returned status code 400";
            if (string.indexOf(substring) > -1){
                if(paymentId!=''){
                    isPaymentCompletedById(paymentId, 'verify');
                }
                alert('Script Error: See Browser Console for Detail');
            } else {
                alert('Some Error while Making payment, please contact support.');
            }
            return false;
        };

        function isPaymentCompletedById(paymentId,type) {
            $.ajax({
                type: 'POST',
                url: 'validation',
                data: {
                    ajax: true,
                    action: 'getPayment',
                    paymentId: paymentId,
                    type: type,
                    token: $('#back-amazon-order-details').attr('data-token')
                },
                success: function (json) {
                    json = JSON.parse(json);
                    console.log(json);
                    console.log(json.redirect);
                    try {
                        if (json.redirect) {
                            location.href = json.redirect;
                        } else if (json.waiting) {
                            setTimeout(function() {
                                isPaymentCompletedById(paymentId, 'verify');
                            }, 5000);
                        } else {
                            alert(json['error']);
                        }
                    } catch (e) {
                        console.log(e.message);
                    }
                }
            });
        }
    </script>
{/block}
