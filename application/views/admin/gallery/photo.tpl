<link rel="stylesheet" type="text/css" href="{$smarty.const.TPL_URL}/scripts/uploadify/uploadify.css" />
<script type="text/javascript" src="{$smarty.const.TPL_URL}/scripts/swfobject.js"></script>
<script type="text/javascript" src="{$smarty.const.TPL_URL}/scripts/uploadify/jquery.uploadify.v2.1.0.min.js"></script>

<script type="text/javascript">
   $(document).ready(function () {
      $("#uploadify").uploadify({
         'uploader': '{$smarty.const.TPL_URL}/scripts/uploadify/uploadify.swf',
         'script': '{$smarty.const.TPL_URL}/scripts/uploadify/uploadify.php',
         'cancelImg': '{$smarty.const.TPL_URL}/scripts/uploadify/cancel.png',
         'folder': '{$GALLERY_URL}/files/gallery/{$aItem.id}',
                  'queueID': 'fileQueue',
                  'buttonText': '{$lang.browse}',
                  'auto': true,
                  'multi': true,
                  'onAllComplete': function () {
                     window.location.replace("?id={$aItem.id}&action=photo")
                  }
               });
            });

            function showDiv(id) {
               $('#divDesc' + id).hide();
               $('#divEdit' + id).show();
            }
</script>

<div id="pageTitle">
   <h1>{$pageTitle}</h1>
</div>

<div id="pageInfo">
   <a class="btnInfo" href="?" title="{$lang.back}">
      <span><img src="{$smarty.const.TPL_URL}/img/admin/icoBack.png" alt="{$lang.back}" />{$lang.back}</span>
   </a>
</div>

<div id="pageContent">

   <div><strong>{$lang.gallery_photo}: {$aItem.title}</strong></div>

   {foreach from=$aPhotos item=v name=n}
      {if $smarty.foreach.n.first}<table class="tablePhoto" class="vertical" border="0" cellspacing="5" cellpadding="5"><tr>{/if}
            {if $smarty.foreach.n.index!=0 and $smarty.foreach.n.index%5==0}</tr><tr>{/if}
            <td class="w200 vertical center {if $smarty.request.photo_id==$v.id}tablePhotoSelect{else}tablePhotoNormal{/if}">
               {if $v.photo}<a href="{$v.photo.normal}" title="{$aItem.title}" class="fancybox" rel="fancybox">
                     <img src="{$v.photo.small}" alt="{$aItem.title}" /></a>
               {else}<strong>{$lang.gallery_no_photo}</strong>{/if}
               <br /><br />
               <div id="divDesc{$v.id}" class="left"><small>{$lang.gallery_desc} {$v.desc}<br />{$lang.gallery_alt} {$v.alt}</small></div>
               <div id="divEdit{$v.id}" class="right" style="display:none;">
                  <form method="post" action="{$smarty.server.PHP_SELF}">
                     <small>{$lang.gallery_desc} <input class="inpText w150" type="text" name="desc" value="{$v.desc}" /><br />
                        {$lang.gallery_alt} <input class="inpText w150" type="text" name="alt" value="{$v.alt}" /><br /></small>
                     <input type="hidden" name="action" value="saveDesc" />
                     <input type="hidden" name="photo_id" value="{$v.id}" />
                     <input type="hidden" name="id" value="{$aItem.id}" />
                     <input type="image" src="{$smarty.const.TPL_URL}/img/admin/btnSave.png" />
                  </form>
               </div>

               {if not $smarty.foreach.n.first}<a class="btnSmall btnUp" href="?id={$aItem.id}&amp;photo_id={$v.id}&amp;action=upPhoto" title="{$lang.move_up}">
                     <img src="{$smarty.const.TPL_URL}/img/admin/btnUp.png" alt="{$lang.move_up}" title="{$lang.move_up}" />
                  </a>{/if}
                  {if not $smarty.foreach.n.last}<a class="btnSmall btnDown" href="?id={$aItem.id}&amp;photo_id={$v.id}&amp;action=downPhoto" title="{$lang.move_down}">
                        <img src="{$smarty.const.TPL_URL}/img/admin/btnDown.png" alt="{$lang.move_down}" title="{$lang.move_down}" />
                     </a>{/if}
                     <a href="javascript:void(0);" onclick="showDiv('{$v.id}');" title="{$lang.edit}">
                        <img src="{$smarty.const.TPL_URL}/img/admin/btnEdit.png" alt="{$lang.edit}" title="{$lang.edit}" />
                     </a>
                     <a href="?id={$aItem.id}&amp;photo_id={$v.id}&amp;action=deletePhoto" title="{$lang.delete}" onclick="return confirmDelete();">
                        <img src="{$smarty.const.TPL_URL}/img/admin/btnDelete.png" alt="{$lang.delete}" title="{$lang.delete}" />
                     </a>
                  </td>
                  {if $smarty.foreach.n.last}</tr></table>{/if}
                  {/foreach}

               <br /><hr /><br />
               <div><strong>{$lang.gallery_photos_server}</strong></div>

               {foreach from=$aFiles item=v name=n}
                  {if $smarty.foreach.n.first}
                     <form id="form" method="post" action="{$smarty.server.PHP_SELF}">
                        <table class="tablePhoto" class="vertical" border="0" cellspacing="5" cellpadding="5"><tr>
                           {/if}
                           {if $smarty.foreach.n.index!=0 and $smarty.foreach.n.index%8==0}</tr><tr>{/if}
                           <td class="vertical center tablePhotoNormal">
                              {if $v.src}
                                 <input type="checkbox" name="{$v.id}" value="1" checked="checked" title="{$lang.gallery_add_to}" /><br />
                                 <a href="{$v.src}" title="{$v.name}" class="fancybox" rel="fancybox"><img width="100" src="{$v.src}" alt="{$v.name}" /></a>
                              {else}<strong>{$lang.gallery_no_photo}</strong>{/if}
                              <br />
                              <a href="?id={$aItem.id}&amp;file={$v.name}&amp;action=deleteFile" title="{$lang.delete_server}" onclick="return confirmDelete();">
                                 <img src="{$smarty.const.TPL_URL}/img/admin/btnDelete.png" alt="{$lang.delete}" title="{$lang.delete}" /></a>
                           </td>
                           {if $smarty.foreach.n.last}
                           </tr>
                        </table>
                        <div class="center">
                           <input type="hidden" name="action" value="addPhoto" />
                           <input type="hidden" name="id" value="{$aItem.id}" />
                           <input type="image" src="{$smarty.const.CMS_URL}/public/img/admin/{$language}/btnAddPhoto.png" />
                        </div>
                     </form>
                  {/if}
               {/foreach}

               <br /><hr /><br />
               <div><strong>{$lang.gallery_select_photos}</strong></div>
               <br />
               <div id="fileQueue"></div>
               <input type="file" name="uploadify" id="uploadify" />
               <p><a href="javascript:jQuery('#uploadify').uploadifyClearQueue()" title="{$lang.gallery_cancel_send}">{$lang.gallery_cancel_send}</a></p>

            </div>