<div id="pageTitle">
    <h1>{$pageTitle}</h1>
</div>

<div id="pageInfo">
    <a class="btnInfo" href="?" title="{$aLang.back}">
        <span><img src="{$smarty.const.TPL_URL}/img/admin/icoBack.png" alt="{$aLang.back}" />{$aLang.back}</span>
    </a>
</div>

<div id="pageContent">

    <form id="form" method="post" action="{$smarty.server.PHP_SELF}" enctype="multipart/form-data">
        <div id="naviLang">
            {foreach from=$aDesc item=v name=n}
                {if $smarty.foreach.n.first}
                    <script type="text/javascript">
                        $(document).ready(function () {
                            $('div#divEdit{$v.lang_id}').show();
                            $('a#link{$v.lang_id}').addClass('activeLang');
                            toggleEditor("edytor{$v.lang_id}");
                        });
                    </script>
                {/if}
                <a id="link{$v.lang_id}" class="btnLang" href="javascript:void(0);" onclick="showDiv('{$v.lang_id}', 'lang');" title="{$v.lang_name}">
                    <img src="{$smarty.const.TPL_URL}/assets/global/img/flags/{$v.lang_code}.png" alt="{$v.lang_name}" />
                    {$v.lang_code}
                </a>
            {/foreach}
        </div>
        <div class="break clear"></div>
        {foreach from=$aDesc item=v}
            <div id="divEdit{$v.lang_id}" class="lang" style="display:none;">
                <table class="tableEdit" border="0" cellspacing="0" cellpadding="0">
                    <tr>
                        <td class="vertical w150"><img src="{$smarty.const.TPL_URL}/assets/global/img/flags/{$v.lang_code}.png" alt="{$v.lang_name}" title="{$v.lang_name}" /> {$aLang.title}:</td>
                        <td><input class="inpText w600" type="text" name="title[{$v.lang_id}]" value="{$v.title}" /></td>
                    </tr>
                    <tr>
                        <td class="vertical"><img src="{$smarty.const.TPL_URL}/assets/global/img/flags/{$v.lang_code}.png" alt="{$v.lang_name}" title="{$v.lang_name}" /> {$aLang.desc_short}:</td>
                        <td><textarea class="w600 descShort" name="desc_short[{$v.lang_id}]">{$v.desc_short}</textarea></td>
                    </tr>
                    <tr>
                        <td class="vertical"><img src="{$smarty.const.TPL_URL}/assets/global/img/flags/{$v.lang_code}.png" alt="{$v.lang_name}" title="{$v.lang_name}" /> {$aLang.tags}:</td>
                        <td>
                            <input class="inpText w200" type="text" name="tag1[{$v.lang_id}]" value="{$v.tag1}" />
                            <input class="inpText w200" type="text" name="tag2[{$v.lang_id}]" value="{$v.tag2}" />
                            <input class="inpText w200" type="text" name="tag3[{$v.lang_id}]" value="{$v.tag3}" />
                        </td>
                    </tr>
                    <tr>
                        <td class="vertical"><img src="{$smarty.const.TPL_URL}/assets/global/img/flags/{$v.lang_code}.png" alt="{$v.lang_name}" title="{$v.lang_name}" /> {$aLang.desc}:</td>
                        <td><textarea id="edytor{$v.lang_id}" class="edytor" name="desc[{$v.lang_id}]">{$v.desc}</textarea></td>
                    </tr>
                </table>
            </div>
        {/foreach}

        <div class="break"></div>
        <table class="tableEdit" border="0" cellspacing="0" cellpadding="0">
            <tr>
                <td class="vertical w150">{$aLang.option}:</td>
                <td>
                    <input type="checkbox" name="active" id ="active" value="1" {if $aItem.active==1 OR $aItem.active==''}checked="true"{/if} />
                    <label for="active">{$aLang.show_art}</label>
                </td>
            </tr>
            <tr>
                <td class="vertical">{$aLang.photo_art}:</td>
                <td><input class="" type="file" name="file" size="96" /></td>
            </tr>
            {if $gallery}
                <tr>
                    <td class="vertical">{$aLang.gallery_select}:</td>
                    <td>
                        {foreach from=$option_gallery item=v name=n2}
                            {if $smarty.foreach.n2.first}<select name="gallery_id"><option value="">{$aLang.select}</option>{/if}
                                <option value="{$v.id}" {if $v.id==$aItem.gallery_id}selected="true"{/if}>{$v.title}</option>
                                {if $smarty.foreach.n2.last}</select>{/if}
                            {/foreach}
                    </td>
                </tr>
            {/if}
            <tr>
                <td></td>
                <td class="center">
                    <input type="hidden" id="action" name="action" value="" />
                    <input type="image" src="{$PATH_URL}/btnSavePublish.png" onclick="setAction('addPublish');" />
                    <input type="image" src="{$PATH_URL}/btnSaveContinue.png" onclick="setAction('addContinue');" />
                    <input type="image" src="{$PATH_URL}/btnCancel.png" onclick="setAction('');" />
                </td>
            </tr>
        </table>
    </form>
</div>