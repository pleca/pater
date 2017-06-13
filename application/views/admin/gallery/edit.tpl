<div id="pageTitle">
   <h1>{$pageTitle}</h1>
</div>

<div id="pageInfo">
   <a class="btnInfo" href="?" title="{$lang.back}">
      <span><img src="{$smarty.const.TPL_URL}/img/admin/icoBack.png" alt="{$lang.back}" />{$lang.back}</span>
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
               <img src="{$smarty.const.TPL_URL}/img/flags/{$v.lang_code}.gif" alt="{$v.lang_name}" />
               {$v.lang_code}
            </a>
         {/foreach}
      </div>
      <div class="break clear"></div>
      {foreach from=$aDesc item=v}
         <div id="divEdit{$v.lang_id}" class="lang" style="display:none;">
            <table class="tableEdit" border="0" cellspacing="0" cellpadding="0">
               <tr>
                  <td class="vertical w150"><img src="{$smarty.const.TPL_URL}/img/flags/{$v.lang_code}.gif" alt="{$v.lang_name}" /> {$lang.title}:</td>
                  <td><input class="inpText w600" type="text" name="title[{$v.lang_id}]" value="{$v.title}" /></td>
               </tr>
               <tr>
                  <td class="vertical"><img src="{$smarty.const.TPL_URL}/img/flags/{$v.lang_code}.gif" alt="{$v.lang_name}" /> {$lang.desc_short}:</td>
                  <td><textarea class="w600 descShort" name="desc_short[{$v.lang_id}]">{$v.desc_short}</textarea></td>
               </tr>
               <tr>
                  <td class="vertical"><img src="{$smarty.const.TPL_URL}/img/flags/{$v.lang_code}.gif" alt="{$v.lang_name}" /> {$lang.tags}:</td>
                  <td>
                     <input class="inpText w200" type="text" name="tag1[{$v.lang_id}]" value="{$v.tag1}" />
                     <input class="inpText w200" type="text" name="tag2[{$v.lang_id}]" value="{$v.tag2}" />
                     <input class="inpText w200" type="text" name="tag3[{$v.lang_id}]" value="{$v.tag3}" />
                  </td>
               </tr>
               <tr>
                  <td class="vertical"><img src="{$smarty.const.TPL_URL}/img/flags/{$v.lang_code}.gif" alt="{$v.lang_name}" /> {$lang.desc}:</td>
                  <td><textarea id="edytor{$v.lang_id}" class="edytor" name="desc[{$v.lang_id}]">{$v.desc}</textarea></td>
               </tr>
            </table>
         </div>
      {/foreach}

      <div class="break"></div>
      <table class="tableEdit" border="0" cellspacing="0" cellpadding="0">
         <tr>
            <td class="vertical w150">{$lang.option}:</td>
            <td>
               <input type="checkbox" name="active" id ="active" value="1" {if $aItem.active==1 OR $aItem.active==''}checked="true"{/if} />
               <label for="active">{$lang.show_gal}</label><br />
               <input type="checkbox" name="view" id ="view" value="1" {if $aItem.view==1 OR $aItem.view==''}checked="true"{/if} />
               <label for="view">{$lang.view_gal}</label>
            </td>
         </tr>
         <tr>
            <td class="vertical">{$lang.signature}:</td>
            <td><input class="inpText w200" type="text" name="signature" value="{$aItem.signature}" /></td>
         </tr>
         <tr>
            <td></td>
            <td class="center">
               <input type="hidden" id="action" name="action" value="" />
               <input type="hidden" name="id" value="{$aItem.id}" />
               <input type="hidden" name="title_url_old" value="{$aItem.title_url}" />
               <input type="image" src="{$smarty.const.CMS_URL}/public/img/admin/{$language}/btnSavePublish.png" onclick="setAction('savePublish');" />
               <input type="image" src="{$smarty.const.CMS_URL}/public/img/admin/{$language}/btnSaveContinue.png" onclick="setAction('saveContinue');" />
               <input type="image" src="{$smarty.const.CMS_URL}/public/img/admin/{$language}/btnCancel.png" onclick="setAction('');" />
            </td>
         </tr>
      </table>
   </form>
</div>