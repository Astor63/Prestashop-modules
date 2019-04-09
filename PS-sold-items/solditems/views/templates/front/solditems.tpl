{if $productsi > $pric}

<div id="container">
  <div id="short_description_blockm" class="arrow_box">
<table width="100%" border="0" cellspacing="0px">
  <td>
<div id="solditems">
				{l s='This item has already been sold' mod='solditems'}: 
</div> 
<div id="sold"> 
            	<span><strong>{$productsi|escape:'html'}{l s='x' mod='solditems'}</strong></span>
 				</div>
  </td>
</table>


</div> 
</div>
   
    {else}{/if}