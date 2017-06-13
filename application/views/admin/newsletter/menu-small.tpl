{*<a class="btnInfo" href="?action=addForm" title="{$lang.newsletter_add_template}">
   <span><img src="{$smarty.const.TPL_URL}/img/admin/icoAdd.png" alt="{$lang.newsletter_add_template}" />{$lang.newsletter_add_template}</span>
</a>
<a class="btnInfo" href="?" title="{$lang.newsletter_list_templates}">
   <span><img src="{$smarty.const.TPL_URL}/img/admin/icoList.png" alt="{$lang.newsletter_list_templates}" />{$lang.newsletter_list_templates}</span>
</a>
<a class="btnInfo" href="?action=sendNewsletter" title="{$lang.newsletter}">
   <span><img src="{$smarty.const.TPL_URL}/img/admin/icoMail.png" alt="{$lang.newsletter_send}" />{$lang.newsletter_send}</span>
</a>*}

{if $smarty.get.action == 'addUserForm' OR ($smarty.get.action=='editUser' AND !$smarty.post.action)}  
   <a class="btnInfo" href="/admin/newsletter.php?action=listUsers" title="{$lang.back}">
      <span><img src="{$smarty.const.TPL_URL}/img/admin/icoBack.png" alt="{$lang.back}" />{$lang.back}</span>
   </a>    
{else}
    <a class="btnInfo" href="?action=addUserForm" title="{$lang.newsletter_add_user}">
       <span><img src="{$smarty.const.TPL_URL}/img/admin/icoAdd.png" alt="{$lang.newsletter_add_user}" />{$lang.newsletter_add_user}</span>
    </a>     
{/if}
 
{*<a class="btnInfo" href="?action=listUsers" title="{$lang.newsletter_list_users}">
   <span><img src="{$smarty.const.TPL_URL}/img/admin/icoList.png" alt="{$lang.newsletter_list_user}" />{$lang.newsletter_list_user}</span>
</a>*}