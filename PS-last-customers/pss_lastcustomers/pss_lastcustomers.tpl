<!-- Block PSS / Last Customers -->
<style type="text/css">
{literal}
#pss_block_last_customers ul li{
	white-space:nowrap;
	overflow:hidden;
	-o-text-overflow: ellipsis; /* pour Opera 9 */
    text-overflow: ellipsis; /* pour le reste du monde */
	padding-left:26px; 
}
#pss_block_last_customers ul{
	padding-top:3px;
}
{/literal}
</style>
<div id="pss_block_last_customers" class="block">
	<h4 class="title_block">{l s='Last customers' mod='pss_lastcustomers'}</h4>
	<div class="last_customer_container">
		<ul class="block_content bullet">
		{foreach from=$customers item=customer}
			{if $displayFlags==1}
				{if $ps15x=='false'}
					<li title="{$customer.country}" style="background: transparent url('{$absoluteUrl}img/{$customer.country_iso_code}.png') no-repeat 0px 3px;">{$customer.display}</li>
				{else}
					<li title="{$customer.country}" style="background: transparent url('{$absoluteUrl}img/{$customer.country_iso_code}.png') no-repeat 0px 4px; padding:3px 21px;">{$customer.display}</li>
				{/if}
			{else}
				<li>padding-top:3px; padding-left:21px;{$customer.display}</li>
			{/if}
		{/foreach}
		</ul>
	</div>
</div>
<!-- /Block PSS / Last Customers -->
