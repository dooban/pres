<!-- Interface du bloc dans le FO -->

{if !$productActions}
<div id="devis_block_right" class="block">
	<h4 class="title_block">{l s='Customized Quote' mod='devisperso'}</h4>
    <div class="block_content">
    	<p>
        	<a href="{$link->getModuleLink('devisperso')}" title="{l s='Request a quote' mod='devisperso'}">
            	{l s='Request a quote' mod='devisperso'}
	        </a>
		</p>
    </div>
</div>

<!-- Lien produit dans le FO -->
{elseif $prod_link == 1}
	<p class="buttons_bottom_block">
		<a class="button_large" href="{$link->getModuleLink('devisperso','formdevis',['id_product'=>$id_prod])}">{l s='Request a quote' mod='devisperso'}</a>
	</p>
{/if}
