<script type="text/javascript">
   $(document).ready(function () {
      $("#loadingPayment").fadeIn();
      document.frmLaunchDirect.submit();
   });
</script>
<form name="frmLaunchDirect" method="Post" action="{$URLS}/order.html">
   <input type="hidden" name="action" value="website_payments_direct">
   <noscript> 
   <div class="center"> 
      <div class="red"> 
         <h2>Processing your Payer Authentication Transaction</h2> 
         <h3>JavaScript is currently disabled or is not supported by your browser.<br></h3> 
         <h4>Please click Submit to continue the processing of your transaction.</h4> 
      </div>
   </div>
   <input type="submit" value="Proceed with my order" style="float:right;" class="inpSubmit" /> 
   </noscript>
</form>
<div style="display:block;width:42px;height:42px;margin:0 auto;">
   <img id="loadingPayment" src="{$smarty.const.CMS_URL}/files/paypal/icon_animated_prog_dkgy_42wx42h.gif" style="display:block;display:none;" />
</div>
{* 
<div style="text-align:center;padding-top:50px;">
<h1>Your payment has been accepted. Order is in progress now and waiting for dispatch. Thank you for your shopping.</h1>
<br />
<br />
<h3>Please <a href="{$URL}/contact.html" title="contact us">contact us</a> in any case.</h3>
<br />
<br />
<h3><a href="{$URL}" title="Go to main page">Go to main page</a></h3>
</div>
*}