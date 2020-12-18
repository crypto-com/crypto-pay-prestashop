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
<script type="text/javascript">
    $("document").ready(function() {
        $('select[name="CRYPTO_PAY_API_MODE"]').change(function(e) {
            if ($(this).val()=='live') {
                $('input[name="CRYPTO_PAY_LIVE_PUBLISHABLE_KEY"]').parent().parent().show();
                $('input[name="CRYPTO_PAY_LIVE_SECRET_KEY"]').parent().parent().parent().show();
                $('input[name="CRYPTO_PAY_LIVE_SIGNATURE_SECRET"]').parent().parent().parent().show();
                $('input[name="CRYPTO_PAY_TEST_SECRET_KEY"]').parent().parent().parent().hide();
                $('input[name="CRYPTO_PAY_TEST_PUBLISHABLE_KEY"]').parent().parent().hide();
                $('input[name="CRYPTO_PAY_TEST_SIGNATURE_SECRET"]').parent().parent().parent().hide();
            } else {
                 $('input[name="CRYPTO_PAY_TEST_SECRET_KEY"]').parent().parent().parent().show();
                $('input[name="CRYPTO_PAY_TEST_PUBLISHABLE_KEY"]').parent().parent().show();
                $('input[name="CRYPTO_PAY_TEST_SIGNATURE_SECRET"]').parent().parent().parent().show();
                $('input[name="CRYPTO_PAY_LIVE_PUBLISHABLE_KEY"]').parent().parent().hide();
                $('input[name="CRYPTO_PAY_LIVE_SECRET_KEY"]').parent().parent().parent().hide();
                $('input[name="CRYPTO_PAY_LIVE_SIGNATURE_SECRET"]').parent().parent().parent().hide();
            }
        });

        if($('select[name="CRYPTO_PAY_API_MODE"]') && $('select[name="CRYPTO_PAY_API_MODE"]').val()){
            if ($('select[name="CRYPTO_PAY_API_MODE"]').val()=='live') {
                $('input[name="CRYPTO_PAY_LIVE_PUBLISHABLE_KEY"]').parent().parent().show();
                $('input[name="CRYPTO_PAY_LIVE_SECRET_KEY"]').parent().parent().parent().show();
                $('input[name="CRYPTO_PAY_LIVE_SIGNATURE_SECRET"]').parent().parent().parent().show();
                $('input[name="CRYPTO_PAY_TEST_SECRET_KEY"]').parent().parent().parent().hide();
                $('input[name="CRYPTO_PAY_TEST_PUBLISHABLE_KEY"]').parent().parent().hide();
                $('input[name="CRYPTO_PAY_TEST_SIGNATURE_SECRET"]').parent().parent().parent().hide();
            } else {
                $('input[name="CRYPTO_PAY_TEST_SECRET_KEY"]').parent().parent().parent().show();
                $('input[name="CRYPTO_PAY_TEST_PUBLISHABLE_KEY"]').parent().parent().show();
                $('input[name="CRYPTO_PAY_TEST_SIGNATURE_SECRET"]').parent().parent().parent().show();
                $('input[name="CRYPTO_PAY_LIVE_PUBLISHABLE_KEY"]').parent().parent().hide();
                $('input[name="CRYPTO_PAY_LIVE_SECRET_KEY"]').parent().parent().parent().hide();
                $('input[name="CRYPTO_PAY_LIVE_SIGNATURE_SECRET"]').parent().parent().parent().hide();
            }
        }
        $('input[type="password"]').parent().parent().style.width="100% !important";
    });
</script>
