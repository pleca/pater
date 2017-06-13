<div id="pageTitle">
   <h1>{$pageTitle}</h1>
</div>

<div id="pageInfo">
   {include file="newsletter/menu-small.tpl"}
</div>

<div id="pageContent">
   {if $smarty.post.to!=1}
      <strong>Biuletyn został wysłany do wszystkich użytkowników, Dziekujemy</strong>
   {else}
      <strong>Biuletyn został wysłany na testowy email, Dziekujemy</strong>
   {/if}
</div>