<script type="text/javascript">
   {literal}
      $(function () {
         $('#to1').click(function () {
            $('#emailDiv').show('slow');
         });
         $('#to2').click(function () {
            $('#emailDiv').hide('slow');
         });
      });
   {/literal}
</script>

<div id="pageTitle">
   <h1>{$pageTitle}</h1>
</div>

<div id="pageInfo">
   {include file="newsletter/menu-small.tpl"}
</div>

<div id="pageContent">
   <div class="center" style="height: 50px;">
      <strong>Wybierz szablon:</strong>
      {foreach from=$templatesSelect item=v name=n}
         {if $smarty.foreach.n.first}
            <form style="display: inline-table;" method="get" action="{$smarty.server.PHP_SELF}">
               <select name="template_id" onchange="this.form.submit()"><option value="">{$lang.select}</option>{/if}
                  <option value="{$v.id}" {if $aItem.id==$v.id}selected="true"{/if}>{$v.title}</option>
                  {if $smarty.foreach.n.last}
                  </select>
                  <input type="hidden" name="action" value="sendNewsletter" />
               </form>
            {/if}
            {/foreach}
            </div>

            {if $aItem.id}
               <form method="post" action="{$smarty.server.PHP_SELF}">
                  W systemie znajduje się {$aUsers.all} adresów e-mail, w tym <strong>{$aUsers.active}</strong> aktywnych.<br /><br />
                  Wyślij do:<br /><br />
                  <input type="radio" id="to1" name="to" value="1" />
                  <label for="to1">testowy email</label>
                  <div id="emailDiv" style="display:none;"><input class="inpText w200" type="text" name="email" value="" /></div>
                  <br /><br />
                  <input type="radio" id="to2" name="to" value="2" />
                  <label for="to2">do wszystkich</label><br/>

                  <input type="hidden" name="template_id" value="{$aItem.id}" />
                  <input type="hidden" name="action" value="send" />
                  <input type="image" src="{$smarty.const.CMS_URL}/public/img/admin/{$language}/btnSend.png" onclick="return confirmSend();" />
               </form>

               <br /><hr /><br />
               <div><strong>Podgląd wysyłanego newslettera:</strong></div>
               <br /><hr /><br />
               <div><strong>{$aItem.title}</strong></div>
               <div style="width: 800px; margin: 5px 0; padding: 10px; border: 1px solid #b6b6b6;">{$aItem.desc}</div>
            {/if}


         </div>