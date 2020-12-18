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
{block name="content"}
  <section>
    {if $status !='failed'}
      <p>{l s='Your Crypo.com Pay Payment Id is' mod='cryptopay'}<code>{$paymentId|escape:'htmlall':'UTF-8'}</code></p>
      <h3>{l s='Crypto.com Payment Details.' mod='cryptopay'}</h3>
      <p>{l s='Recipient.' mod='cryptopay'} : {$recipient|escape:'htmlall':'UTF-8'}</p>
      <p>{l s='Total Paid.' mod='cryptopay'} : {$total_to_pay|escape:'htmlall':'UTF-8'}</p>
      <p>{l s='Amount in Crypto Currency.' mod='cryptopay'} : {$crypto_amount|escape:'htmlall':'UTF-8'}&nbsp;{$crypto_currency|escape:'htmlall':'UTF-8'}</p>
      <p>{l s='Status.' mod='cryptopay'} : {if $status=='succeeded'}
      <span class="btn btn-success">{$status|escape:'htmlall':'UTF-8'}</span>
    {elseif $status=='pending'}
      <span class="btn btn-primary"> {$status|escape:'htmlall':'UTF-8'}</span>
    {elseif $status=='cancelled'}
      <span class="btn btn-danger">{$status|escape:'htmlall':'UTF-8'}</span>
    {else}
      {$status|escape:'htmlall':'UTF-8'}
    {/if}</p>
    {else}
      <p>{l s='There is some issue with payment of your order, please contact .' mod='cryptopay'}</p>
    {/if}
  </section>
{/block}
