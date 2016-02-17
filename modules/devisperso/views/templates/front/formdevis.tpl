{capture name=path}
<a href="{$link->getPageLink('my-account', true)}">{l s='My account' mod='devisperso'}</a>
<span class="navigation-pipe">{$navigationPipe}</span>
<a href="{$link->getModuleLink('devisperso', 'default')}">{l s='Quotes requests history' mod='devisperso'}</a>
<span class="navigation-pipe">{$navigationPipe}</span>
{l s='Your quote' mod='devisperso'}
{/capture}

{*{include file="$tpl_dir./breadcrumb.tpl"}
*}

<h1>{if isset($devis)} {l s='Your quote' mod='devisperso'} {l s='#' mod='devisperso'} {$devis->id|string_format:"%06d"} {else} {l s='New quote' mod='devisperso'} {/if}</h1>

<p>{if isset($devis)} {l s='Modify the quote' mod='devisperso'}  
		{if isset($smarty.post.devis)} {l s='#'} {$smarty.post.devis->id|string_format:"%06d"} 
		{else} {l s='#' mod='devisperso'} {$devis->id|string_format:"%06d"}
		{/if} 
	{else} {l s='To request a new quote, please fill out the form below.' mod='devisperso'} 
	{/if}</p>
	
{include file="$tpl_dir./errors.tpl"}

<form action="{$link->getModuleLink('devisperso', 'formdevis')}" method="post" class="formulaire">
	<fieldset style="background: #f6f6f6;">
		<h3>{if isset($devis)}{l s='Your quote' mod='devisperso'}{else}{l s='New quote' mod='devisperso'}{/if}</h3>
		
		<input type="hidden" name="token" value="{$token}" />
		<input type="hidden" name="id_devis" value="{if isset($devis)}{$devis->id}{/if}" />
		<input type="hidden" name="id_customer" value="{$id_customer}" />
		<input type="hidden" name="id_statut" value="{$id_statut}" />
		<p id="intro">{$intro}</p>
		<br /><br />
 
 		<p class="gras">{l s='Options for the order' mod='devisperso'}</p>
		<p class="select">
			<label for="id_address_invoice">{l s='Invoice address' mod='devisperso'}&nbsp;</label>
			<select id="id_address_invoice" name="id_address_invoice" style="width: 180px;" {if isset($devis) && ($devis->total_devis !=0 || $devis->reponse)} disabled="disabled" {/if} >{$address_invoice_list}</select>
			<sup class="required">*</sup>&nbsp;
			{if !isset($devis)}
				<a href="{$link->getPageLink('address',false,null,['back'=>'controller=formdevis&fc=module&module=devisperso'])}"><img title="{l s='Add a new address' mod='devisperso'}" alt="{l s='Add a new address' mod='devisperso'}" src="{$base_dir}/img/admin/add.gif"></a>
			{/if}
		</p>
		<p class="select">
			<label for="id_address_delivery">{l s='Delivery address' mod='devisperso'}&nbsp;</label>
			<select id="id_address_delivery" name="id_address_delivery" style="width: 180px;" {if isset($devis) && ($devis->total_devis !=0 || $devis->reponse)} disabled="disabled" {/if} >{$address_delivery_list}</select>
			<sup class="required">*</sup>&nbsp;
			{if !isset($devis)}
				<a href="{$link->getPageLink('address',false,null,['back'=>'controller=formdevis&fc=module&module=devisperso'])}"><img title="{l s='Add a new address' mod='devisperso'}" alt="{l s='Add a new address' mod='devisperso'}" src="{$base_dir}/img/admin/add.gif"></a>
			{/if}
		</p>
		
		<p class="select">
			<label for="id_carrier">{l s='Carrier' mod='devisperso'}&nbsp;</label>
			<select id="id_carrier" name="id_carrier" style="width: 180px;" {if isset($devis) && ($devis->total_devis !=0 || $devis->reponse)} disabled="disabled" {/if}>{$carrier_list}</select>
			<sup class="required">*</sup>
		</p>
		
		<br /><br /><br />
	
		<p class="gras">{l s='Your request' mod='devisperso'}</p>
		<p class="required textarea" id="demande">
			<textarea name="demande" style="width:95%;" rows="12"  {if isset($devis) && ($devis->total_devis !=0 || $devis->reponse)} readonly{/if} >{if isset($desc_prod) && $desc_prod != null}{$desc_prod|escape:'htmlall':'UTF-8'}{elseif isset($smarty.post.demande)}{$smarty.post.demande}{elseif isset($devis)}{$devis->demande|escape:'htmlall':'UTF-8'}{/if}</textarea>
			<br /><sup>*</sup>
			<small>{l s='Forbidden characters:' mod='devisperso'} {literal}<>{} {/literal} </small>
		</p>	
			
		<br />		
		{if isset($devis) && ($devis->total_devis !=0 || $devis->reponse)}
		<p class="gras">{l s='Our answer' mod='devisperso'}</p>
		<table id="reponse">
			<tr>
				<td class="rte">{$devis->reponse}</td>
			</tr>
		</table>
			
		<br /><br />
		
		 <p class="gras">{l s='Total price' mod='devisperso'}</p>	
			<table class="std">
				<tr class="item">
					<td style="text-align:right; border-top:0px;">
					{l s='Total excl.tax: ' mod='devisperso'}</td>
					<td style="border-top:0px;"><span>
					{if isset($devis->total_devis)} {displayPrice price=$devis->total_out_shipp currency=$devis->id_currency no_utf8=false convert=false} {/if}
					</span>
					</td>
				</tr>
				<tr>
					<td style="text-align:right; border-top:0px;">
					{l s='Total shipping: ' mod='devisperso'}</td>
					<td style="border-top:0px;"><span>
					{if isset($devis->total_devis)} {displayPrice price=$devis->total_shipping currency=$devis->id_currency no_utf8=false convert=false} {/if}
					</span></td>
				</tr>
				<tr class="last_item">
					<td style="text-align:right; border-top:0px;">
					<b>{l s='Total: ' mod='devisperso'}</b></td>
					<td style="border-top:0px;"><b><span class="price">
					{if isset($devis->total_devis)} {displayPrice price=$devis->total_devis currency=$devis->id_currency no_utf8=false convert=false} {/if}
					</b></span>
					</td>
				</tr>
			</table>
		{/if}
		<br />
		
	</fieldset>
	<small class="required"><sup>*</sup>{l s='Required field ' mod='devisperso'}</small>
	
		{if !isset($devis) || (!($devis->reponse) && ($devis->total_devis == 0))}
		<p class="submitdevis">
			{if isset($devis)} 
				<input type="hidden" name="id_modif" value="{$devis->id|intval}" />
				<input type="submit" name="modifDevis" id="modifDevis" value="{l s='I modify my request' mod='devisperso'}" class="exclusive_large" />
			{else} 
				<input type="submit" name="submitDevis" id="submitDevis" value="{l s='I confirm my request' mod='devisperso'}" class="exclusive_large" /> 
			{/if}
		{else}
            {if ($devis->id_statut == 1 || $devis->id_statut == 2) && ($devis->total_devis != 0)}  
        </p>
</form>      
			<form class="std" name="modif_statut" action="{$link->getModuleLink('devisperso', 'default')}" method="post">
				<p class="submitdevis">
					<input class="exclusive_large" type="submit" name="approve" value="{l s='I place my order**' mod='devisperso'}" />
				</p>
				<small><sup>**</sup>{l s='Implies that I agree the Terms of service ' mod='devisperso'}</small>
				<input type="hidden" name="id_devis" value="{$devis->id|intval}" />
			</form>
			{/if}
		{/if}
<ul class="footer_links clearfix">
	<li><a class="btn btn-default button button-small" href="{$link->getModuleLink('devisperso', 'default')}" title="{l s='Back to Your Quotes' mod='devisperso'}"><span><i class="icon-chevron-left"></i> {l s='Back to Your Quotes' mod='devisperso'}</span></a></li>
	<li><a class="btn btn-default button button-small" href="{$link->getPageLink('my-account', true)}" title="{l s='Back to your account' mod='devisperso'}"><span><i class="icon-chevron-left"></i> {l s='Back to your account' mod='devisperso'}</span></a></li>
</ul>
