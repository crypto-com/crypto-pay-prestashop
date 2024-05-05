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
<span class="btn-group-action">
    <button style="display:none;" type="button" class="btn btn-info btn-lg" data-toggle="modal"
            data-target="#module-modal-cryptopay{$id|escape:'htmlall':'UTF-8'}"
            id="module-modal-cryptopay-button{$id|escape:'htmlall':'UTF-8'}">
				<i class="material-icons">edit</i>
    </button>
    <button type="button" class="btn btn-success btn-sx"
            onclick="prettyPrint('{$id|escape:'htmlall':'UTF-8'}');">
                    <i class="icon-eye-open"></i>
    </button>
</span>

<div id="module-modal-cryptopay{$id|escape:'htmlall':'UTF-8'}" class="modal modal-vcenter fade" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title module-modal-title">Log Data</h4>
                <button type="button" class="close" data-dismiss="modal">X</button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <textarea rows="20" name="feed_content{$id|escape:'htmlall':'UTF-8'}" class="form-control"
                                      id="feed_content{$id|escape:'htmlall':'UTF-8'}">{$data|escape:'quotes': 'UTF-8'}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    function prettyPrint(row_id) {
        var ugly = document.getElementById('feed_content' + row_id).value;
        var obj = JSON.parse(ugly);
        var pretty = JSON.stringify(obj, undefined, 4);
        document.getElementById('feed_content' + row_id).value = pretty;
        document.getElementById('module-modal-cryptopay-button' + row_id).click();
    }
</script>
