{*
* Copyright 2024 Crypto.com
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
*  @copyright  2024 Crypto.com
*  @license    http://www.apache.org/licenses/LICENSE-2.0  Apache License, Version 2.0
*}
{extends "$layout"}
{block name="content"}
    <section class="card" id="cryptopay_container">
        <div class="card card-block">
            <div class="row">
                <p>{l s='Your Order Reference is' mod='cryptopay'}<code>{$reference|escape:'htmlall':'UTF-8'}</code></p>
               <p>There is some error please contact admin regarding this order. </p>
            </div>
        </div>
    </section>
{/block}
