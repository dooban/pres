<script type="text/javascript">
	var Annul = '{l s='Are you sure you want to cancel your quote request?' mod='devisperso'}';
</script>
{literal}
<script type="text/javascript">
//<![CDATA[ 
	function confirmAnnule() {
			return confirm(Annul);
		}
//]]> 
</script>
{/literal}


{capture name=path}<a href="{$link->getPageLink('my-account', true)}">{l s='My account' mod='devisperso'}</a><span class="navigation-pipe">{$navigationPipe}</span>{l s='Quotes requests history' mod='devisperso'}{/capture}
{*{include file="$tpl_dir./breadcrumb.tpl"}
*}
<h1>{l s='Quotes requests history' mod='devisperso'}</h1>
<p>{l s='Here are the quotes you have requested since the creation of your account' mod='devisperso'}.</p>
<p><a href="{$link->getModuleLink('devisperso', 'default', ['page'=>'formdevis'])}" class="button_large">{l s='I request a quote' mod='devisperso'}</a></p>
<div class="block-center" id="block-history">
<input type="hidden" name="token" value="{$token}" />
{include file="$tpl_dir./errors.tpl"}
	{if $devis && count($devis)}
	<table class="std">
		<thead>
			<tr>
				<th class="first_item">{l s='Quote' mod='devisperso'}</th>
				<th class="item">{l s='Date' mod='devisperso'}</th>
				<th class="item">{l s='Exp. Date' mod='devisperso'}</th>
				<th class="item">{l s='Total price' mod='devisperso'}</th>
				<th class="item">{l s='Status' mod='devisperso'}</th>
				<th class="last_item">{l s='Actions' mod='devisperso'}</th>
			</tr>
		</thead>
 
		<tbody>
		{foreach from=$devis item=devi name=myLoop}
			
			<tr class="{if $smarty.foreach.myLoop.first}first_item{elseif $smarty.foreach.myLoop.last}last_item{else}item{/if} {if $smarty.foreach.myLoop.index % 2}alternate_item{/if}">
				<td class="history_link bold">
					<a class="color-myaccount" href="{$link->getModuleLink('devisperso', 'default', ['page'=>'formdevis','id_devis'=>$devi.id_devis])}">{l s='#' mod='devisperso'}{$devi.id_devis|string_format:"%06d"}</a>
				</td>
				<td class="history_date bold">{dateFormat date=$devi.date_demande full=0}</td>
				<td class="history_date bold">{dateFormat date=$devi.date_expiration full=0}</td>
				<td class="history_price"><span class="price">{if $devi.total_devis != 0}{displayPrice price= $devi.total_devis currency=$devi.id_currency no_utf8=false convert=false}  {else}---{/if}</span></td>
				
				<td class="history_state">	{if $devi.id_statut==1}{$statut.1|escape:'htmlall':'UTF-8'}{/if}
											{if $devi.id_statut==2}{$statut.2|escape:'htmlall':'UTF-8'}{/if}
											{if $devi.id_statut==3}{$statut.3|escape:'htmlall':'UTF-8'}{/if}
											{if $devi.id_statut==4}{$statut.4|escape:'htmlall':'UTF-8'}{/if}
											{if $devi.id_statut==5}{$statut.5|escape:'htmlall':'UTF-8'}{/if}
				</td>
				
				<td class="history_method">
					<form name="modif_statut" action="{$link->getModuleLink('devisperso', 'default')}" method="post">
						{if $devi.id_statut == 1  && $devi.total_devis != 0}
						<input type="submit" class="lien_devis" name="approve" value="{l s='Approve' mod='devisperso'}">**
						/ <input type="submit" class="lien_devis" name="cancel" value="{l s='Cancel' mod='devisperso'}" onclick="return confirmAnnule()">
						<input type="hidden" name="id_devis" value="{$devi.id_devis|intval}">
						
						{elseif $devi.id_statut == 2  && $devi.id_cart}
						<input type="submit" class="lien_devis" name="pay" value="{l s='Pay' mod='devisperso'}">**
						/ <input type="submit" class="lien_devis" name="cancel" value="{l s='Cancel' mod='devisperso'}" onclick="return confirmAnnule()">
						<input type="hidden" name="id_devis" value="{$devi.id_devis|intval}">
						
						{/if}
					</form>
				</td>
			</tr>
			
		{/foreach}
		</tbody>
	</table>
	{foreach from=$devis item=devi name=myLoop}
		{if $devi.id_statut==2 || ($devi.id_statut==1 && $devi.total_devis != 0)}
			<p><sup>**</sup>{l s='Implies that I agree the Terms of service ' mod='devisperso'}</p>
			{break}
		{/if}
	{/foreach}
	<div id="block-order-detail" class="hidden">&nbsp;</div>
	{else}
		<p class="warning">{l s='You have not requested any quotes.' mod='devisperso'}</p>
	{/if}
</div>

<p><a href="{$link->getModuleLink('devisperso', 'default', ['page'=>'formdevis'])}" class="button_large">{l s='I request a quote' mod='devisperso'}</a></p>

<ul class="footer_links clearfix">
<li><a class="btn btn-default button button-small" href="{$link->getPageLink('my-account', true)}" title="{l s='Back to your account' mod='devisperso'}"><span><i class="icon-chevron-left"></i> {l s='Back to your account' mod='devisperso'}</span></a></li>
</ul>
