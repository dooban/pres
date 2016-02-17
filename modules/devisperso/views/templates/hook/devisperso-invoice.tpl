{if isset($desc_devis) && $desc_devis}
<div style="line-height: 1pt">&nbsp;</div>
<table style="width: 100%">
	<tr style="line-height:6px;">
		<td style="text-align: left; background-color: #4D4D4D; color: #FFF; padding-left: 10px; font-weight: bold; width: 100%">{l s='Quote description' mod='devisperso'}</td>
	</tr>
	<tr style="line-height:6px;background-color:#DDD;">
		<td style="text-align: left; width: 100%">{$desc_devis|nl2br}</td>
	</tr>
</table>
{/if}