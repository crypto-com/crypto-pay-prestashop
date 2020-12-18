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
    <div class="col-sm-12">
        <table class="table">
            <tbody>
            <tr>
                <td style="width: 90%;">
                    {if $is_rewrite_enabled && 0}
                        <input readonly type="text" class="form-control" id="webhook_url"
                               value="{$base_url|escape:'htmlall':'UTF-8'}cryptopay/webhook" />
                    {else}
                        <input readonly type="text" class="form-control" id="webhook_url"
                               value="{$base_url|escape:'htmlall':'UTF-8'}index.php?fc=module&module=cryptopay&controller=webhook" />
                    {/if}

                </td>
                <td>
                    <button title="Copy Webhook URL"
                            type="button"
                            class="btn btn-primary"
                            onclick="copyWebhookUrl('webhook_url');"
                    ><i class="material-icons">content_copy</i></button>
                </td>
            </tr>
            </tbody>
        </table>
        <script>
            function copyWebhookUrl(WehhookElementID) {
                var WehhookInputElement = document.getElementById(WehhookElementID);
                WehhookInputElement.select();
                document.execCommand("copy");
            }
        </script>
    </div>
</div>
