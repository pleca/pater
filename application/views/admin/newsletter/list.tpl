<div id="pageTitle">
   <h1>{$pageTitle}</h1>
</div>

<div id="pageInfo">
   {include file="newsletter/menu-small.tpl"}
</div>

<div id="pageContent">

   {foreach from=$aItems item=v name=n key=k}
      {if $smarty.foreach.n.first}
         <table class="tableList" border="0" cellspacing="0" cellpadding="0">
            <tr class="tableListHeader">
               <td class="w70">{$lang.lp}</td>
               <td>{$lang.title}</td>
               <td>{$lang.desc_short}</td>
               <td class="w100">{$lang.date_add}</td>
               <td class="w100">{$lang.date_mod}</td>
               <td class="w70">{$lang.edit}</td>
               <td class="w70">{$lang.delete}</td>
            </tr>
         {/if}

         <tr class="{if isset($smarty.request.id) AND $smarty.request.id==$v.id}tableListSelect{else}tableListNormal{/if}">
            <td>{$interval+$k+1}</td>
            <td>{$v.title}</td>
            <td>{$v.desc_short}</td>
            <td class="date">{$v.date_add}</td>
            <td class="date">{$v.date_mod}</td>
            <td class="center">
               <a href="?id={$v.id}&amp;action=edit" title="{$lang.edit}">
                  <img src="{$smarty.const.TPL_URL}/img/admin/btnEdit.png" alt="{$lang.edit}" title="{$lang.edit}" />
               </a>
            </td>
            <td class="center">
               <a href="?id={$v.id}&amp;action=delete" title="{$lang.delete}" onclick="return confirmDelete();">
                  <img src="{$smarty.const.TPL_URL}/img/admin/btnDelete.png" alt="{$lang.delete}" title="{$lang.delete}" />
               </a>
            </td>
         </tr>

         {if $smarty.foreach.n.last}</table>{/if}
      {/foreach}

   {include file="other/pages.tpl"}

</div>