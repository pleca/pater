{include file="customer/top.tpl"}

<div id="pageContent">
   {include file="other/notify.tpl"}

   {include file="customer/menu-top.tpl"}

   <div class="cpContent">

      {foreach from=$aComments item=v name=n}
         {if $smarty.foreach.n.first}
            <table class="tableList font11" cellpadding="0" cellspacing="0" border="0">
               <tr class="center">
                  <th class="w30" height="25">Lp.</th>
                  <th>{$lang.comments_desc}</th>
                  <th class="w100">{$lang.comments_date}</th>
               </tr>
            {/if}

            <tr>
               <td height="25">{$smarty.foreach.n.iteration}.</td>
               <td><a class="black" href="{$v.url}#comments" title="{$lang.comments_desc}">{$v.desc}</a></td>
               <td class="right">{$v.date_add}</td>
            </tr>

            {if $smarty.foreach.n.last}</table>{/if}
         {foreachelse}
         <div class="center red info">Brak komentarzy</div>
      {/foreach}
   </div>

   {include file="customer/bottom.tpl"}       
</div>