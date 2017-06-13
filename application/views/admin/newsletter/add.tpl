<div id="pageTitle">
   <h1>{$pageTitle}</h1>
</div>

<div id="pageInfo">
   {include file="newsletter/menu-small.tpl"}
</div>

<div id="pageContent">

   <form id="form" method="post" action="{$smarty.server.PHP_SELF}">
      <table class="tableEdit" border="0" cellspacing="0" cellpadding="0">
         <tr>
            <td class="vertical w150">{$lang.title}:</td>
            <td><input class="inpText w600" type="text" name="title" value="{$aItem.title}" /></td>
         </tr>
         <tr>
            <td class="vertical">{$lang.desc}:</td>
            <td><textarea id="edytor1" class="edytor" name="desc">{$aItem.desc}</textarea></td>
         </tr>
         <tr>
            <td></td>
            <td class="center">
               <input type="hidden" id="action" name="action" value="" />
               <input type="image" src="{$smarty.const.CMS_URL}/public/img/admin/{$language}/btnSavePublish.png" onclick="setAction('addPublish');" />
               <input type="image" src="{$smarty.const.CMS_URL}/public/img/admin/{$language}/btnSaveContinue.png" onclick="setAction('addContinue');" />
               <input type="image" src="{$smarty.const.CMS_URL}/public/img/admin/{$language}/btnCancel.png" onclick="setAction('');" />
            </td>
         </tr>
      </table>
   </form>
</div>
<script type="text/javascript">
   $(document).ready(function () {
      toggleEditor("edytor1");
   });
</script>