{if $tinymce}
	<script type="text/javascript" src="{$PS_BASE_URI}js/tiny_mce/tiny_mce.js"></script>
	<script type="text/javascript" src="{$PS_BASE_URI}js/tinymce.inc.js"></script>	
	<script type="text/javascript">
		var iso = '{$iso_tiny_mce}';
		var pathCSS = '{$smarty.const._THEME_CSS_DIR_}';
		var ad = '{$ad}';
	</script>	
{/if}
 
<script type="text/javascript">

$(document).ready(function(){
	   
    tinySetup({
     editor_selector :"autoload_rte",
	 theme_advanced_buttons1 : "newdocument,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,styleselect,formatselect,fontselect,fontsizeselect",
	 theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,cleanup,help,code,,|,forecolor,backcolor",
	 theme_advanced_buttons3 : "tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,media,|,ltr,rtl,|,fullscreen",
	 theme_advanced_buttons4 : "styleprops,|,cite,abbr,acronym,del,ins,attribs,pagebreak", 
    });

});

	var noTax = '{$noTax}';
	var taxesArray = '{$taxesArray}';
	
	
function addressInvoiceChange(id_customer, id_address)
{
	if(id_customer == 0)
		var hrefCust = "#";
	else
		var hrefCust = "?tab=AdminCustomers&id_customer="+id_customer+"&viewcustomer&token={getAdminToken tab='AdminCustomers'}";
	$.ajax({
	  url: "../modules/devisperso/ajaxdevis.php",
	  cache: false,
	  data: "ajaxAddress=1&id_customer="+id_customer+"&id_address="+id_address,
	  success: function(html){
		$("#id_address_invoice").html(html);
		$("a[rel=fiche]").attr({
			title: document.getElementById("id_customer").options[document.getElementById("id_customer").selectedIndex].innerHTML,
			href: hrefCust
		});
		visuAddress(id_address, '_invoice');
	  },
	  error: function(XMLHttpRequest, textStatus, errorThrown){
		alert('Adresse ajax error: '+textStatus);
	  }
	});	
		
	$("select.adInvoice").fadeOut("slow");
	$("select.adInvoice").fadeIn("slow");
}
function addressDeliveryChange(id_customer, id_address)
{
	$.ajax({
	  url: "../modules/devisperso/ajaxdevis.php",
	  cache: false,
	  data: "ajaxAddress=1&id_customer="+id_customer+"&id_address="+id_address,
	  success: function(html){
		$("#id_address_delivery").html(html);
		visuAddress(id_address, '_delivery');
	  },
	  error: function(XMLHttpRequest, textStatus, errorThrown){
		alert('Adresse ajax error: '+textStatus);
	  }
	});		

	$("select.adDelivery").fadeOut("slow");
	$("select.adDelivery").fadeIn("slow");
}
function visuAddress(id_address, chp_address)
{
	var adSelect = document.getElementById("id_address"+chp_address).options[document.getElementById("id_address"+chp_address).selectedIndex].innerHTML;
	
	if(id_address == 0)
		var hrefAd = "#";
	else
		var hrefAd = "?tab=AdminAddresses&id_address="+id_address+"&updateaddress&token={getAdminToken tab='AdminAddresses'}";
      
      document.getElementById(chp_address).href = hrefAd;
      document.getElementById(chp_address).title = adSelect;
	
}
function taxFill(id_address_delivery, id_lang, id_tax_devis)
{
	$.ajax({
	  url: "../modules/devisperso/ajaxdevis.php",
	  cache: false,
	  data: "ajaxTax=1&id_address_delivery="+id_address_delivery+"&id_lang="+id_lang+"&id_tax_devis="+id_tax_devis,
	  success: function(html){
		$("#id_tax").html(html);
		calculTotal();
	  },
	  error: function(XMLHttpRequest, textStatus, errorThrown){
		alert('Tax ajax error: '+textStatus);
	  }
	});		

	$("select.tax").fadeOut("slow");
	$("select.tax").fadeIn("slow");
}
function shippingChange(id_carrier, id_devis, total, poids)
{
	var freeshipp = document.getElementById("free_shipp");
	if (freeshipp.checked == false)
	{
		$.ajax({
		  url: "../modules/devisperso/ajaxdevis.php",
		  cache: false,
		  data: "ajaxShipping=1&id_carrier="+id_carrier+"&id_devis="+id_devis+"&total="+total+"&poids="+poids,
		  success: function(html){
		  	$("div.shipp").html(html);
		  	calculTotal();
		  },
		  error: function(XMLHttpRequest, textStatus, errorThrown){
			alert('Shipping ajax error: '+textStatus);
		  }
		});		

		$("div.shipp").fadeOut("fast");
		$("div.shipp").fadeIn("fast");
	}
}
function getTax()
{
	if ({$noTax})
		return 0;

	var taxesArray = [
	              	{foreach from=$taxesArray item=tax}
						"{$tax}",
	              	{/foreach}
	              	];
	var selectedTax = document.getElementById("id_tax");
	var taxId = selectedTax.options[selectedTax.selectedIndex].value;

	return taxesArray[taxId];
}
function calculTotal ()
{
	var tax = getTax();
	
	var tot_out_ht = parseFloat(document.getElementById("total_out_shipp").value);
	var tot_ttc = tot_out_ht * ((tax/100) + 1);
	
	var shipp = parseFloat(document.getElementById("total_shipping").value);
	var total = tot_ttc + shipp >= 0 ? Math.round(total = (tot_ttc + shipp)*100)/100   : 0.00;
	
	document.getElementById("tot_ttc").innerHTML = Math.round(tot_ttc*100)/100;
	document.getElementById("total_devis").value = total;

}
function shippingNull ()
{
	document.getElementById("total_shipping").value = 0;
	calculTotal();
}

</script>

{*
{if $show_toolbar}
	{include file="toolbar.tpl" toolbar_btn=$toolbar_btn toolbar_scroll=$toolbar_scroll title=$title}
	<div class="leadin">{block name="leadin"}{/block}</div>
{/if}
*} 
 
<form name="form" id="devis_form" action="{$currentIndex|escape}&token={$token|escape}&adddevis" method="post">
<div class="panel col-lg-12">
	<div class="panel-heading">{l s='Quote' mod='devisperso'}</div>
	<div class="form-group">
		<input type="hidden" name="id_currency" value="{$currency->id}" />
		<input type="hidden" name="id_devis" value="{$obj->id}" />

		<label class="control-label col-lg-3">{l s='Customer:' mod='devisperso'} </label>
		<div class="col-lg-9">
			<div class="input-group">
				<select name="id_customer" id="id_customer" onchange="addressInvoiceChange($(this).val(),0);addressDeliveryChange($(this).val(),0);">
					<option>{l s='select a customer' mod='devisperso'}</option>
					{foreach from=$customers item=cust name=loop}
						<option value="{$cust.id_customer}" {if $obj->id_customer == $cust.id_customer }selected="selected"{/if}>{$cust.lastname} {$cust.firstname}</option>
					{/foreach}
				</select> 
				<br />
				<div class="col-lg-9 col-lg-offset-3">
					<div class="help-block">
						<sup>*</sup>
						<a rel="fiche" target="_blank"> <img src="../img/admin/tab-customers.gif" /> </a>
					</div>
				</div>
			</div>
		</div>

		<label class="control-label col-lg-3">{l s='Status:'}</label>
		<div class="col-lg-9">
			<div class="input-group">
				<select name="id_statut">
				{foreach from=$status_lang item=stat name=loop}
					<option value="{$stat.id_statut}" {if $obj->id_statut == $stat.id_statut }selected="selected"{/if}>{$stat.statut_desc}</option>
				{/foreach}
				</select>
				<div class="col-lg-9 ">
					<div class="help-block">
						<p class="hint clear" >
							<span class="small" name="help_box">
								{l s=' The status change automatically depending on customer\'s choice, modify it exceptionally !' mod='devisperso'}
							</span>
						</p>
					</div>
				</div>
			</div>
		</div>

		<label class="control-label col-lg-3">{l s='Lang:' mod='devisperso'}</label> <!-- affiche la langue dans laquelle a �t� faite la demande -->
		<div class="col-lg-9">
			<div class="input-group">
				<input type="hidden" size="20" name="id_lang" value="{$lang->id}" readonly />
				<input type="text" class="form-control fixed-width-xxl" style="color:grey;" size="32" name="lang" value="{$lang->name}" readonly />
				<div class="col-lg-9 "><div class="help-block"></div></div>
			</div>
		</div>
			
		<label class="control-label col-lg-3">{l s='Exp. Date:' mod='devisperso'}</label>
		<div class="col-lg-9">
			<div class="input-group">
				<input type="hidden" name="date_expiration" value="{$dateExp}" />
				<input type="text" class="form-control fixed-width-xxl" style="color:grey;" size="32" name="date_expiration2" value="{dateFormat date=$dateExp full=false}" readonly />
				<div class="col-lg-9 "><div class="help-block"></div></div>
			</div>
		</div>
		<script type="text/javascript">
			addressInvoiceChange({$obj->id_customer}, {if $obj->id_address_invoice != null}{$obj->id_address_invoice}{else}0{/if});
		</script>

		<label class="control-label col-lg-3">{l s='Invoice Address:' mod='devisperso'}</label>
		<div class="col-lg-9">
			<div class="input-group">
				<select onchange="visuAddress($(this).val(), '_invoice');" class="adInvoice" id="id_address_invoice" name="id_address_invoice" style="width:220px;" {if $obj->id_statut == 5} disabled="disabled" {/if}>
				</select> 
				<div class="col-lg-9 ">
					<div class="help-block">
						<sup>*</sup>
						<a id="_invoice" rel="_invoice" target="_blank" > <img src="../img/admin/details.gif" /> </a>
					</div>
				</div>
			</div>
		</div>
		<script type="text/javascript">
			addressDeliveryChange({$obj->id_customer}, {if $obj->id_address_delivery != null}{$obj->id_address_delivery}{else}0{/if});
		</script>

		<label class="control-label col-lg-3">{l s='Delivery Address:' mod='devisperso'}</label>
		<div class="col-lg-9">
			<div class="input-group">
				<select onchange="visuAddress($(this).val(), '_delivery'); {if $obj->id != null}taxFill($(this).val(), {$lang->id}, {$obj->id_tax});{/if}" class="adDelivery" id="id_address_delivery" name="id_address_delivery"  style="width:220px;"  {if $obj->id_statut == 5} disabled="disabled" {/if}>
				</select> 
				<div class="col-lg-9 ">
					<div class="help-block">
						<sup>*</sup>
						<a rel="_delivery" id="_delivery" target="_blank" > <img src="../img/admin/details.gif" /> </a>
					</div>
				</div>
			</div>
		</div>
		<label class="control-label col-lg-3">{l s='Carrier:' mod='devisperso'}</label>
		<div class="input-group">
			<select name="id_carrier" id="id_carrier" onchange="shippingChange($(this).val(),{$obj->id}, $('input:text[name=total_out_shipp]').val(), $('input:text[name=poids_devis]').val());" {if $obj->id_statut == 5} disabled="disabled" {/if}>
				<option>{l s='select a carrier' mod='devisperso'}</option>
				{foreach from=$carrier item=carr name=loop}
					<option value="{$carr.id_carrier}" {if $obj->id_carrier == $carr.id_carrier }selected="selected"{/if}>{$carr.name}</option>
				{/foreach}
			</select> 
				<div class="col-lg-9 ">
					<div class="help-block">
						<sup>*</sup>
					</div>
				</div>
		</div>

		<label class="control-label col-lg-3">{l s='Request:' mod='devisperso'}</label>
		<div class="col-lg-9">
			<div class="input-group">
				<textarea cols="75" rows="10" name="demande" value="{$obj->demande}" {if $obj->id_statut == 5} readonly {/if} >{$obj->demande}</textarea>
				<br /> 
				<div class="col-lg-9 ">
					<div class="help-block">
						<sup>*</sup>
						<span class="small" name="help_box">{l s='Forbidden characters:' mod='devisperso'} &lt;&gt;{}</span>
					</div>
				</div>
			</div>
		</div>

		{if $obj->id != null}
			<label class="control-label col-lg-3">{l s='Answer:' mod='devisperso'}</label>
			<div class="col-lg-9">
			<div class="input-group">
				<textarea class="autoload_rte" cols="75" rows="15" name="reponse" value="{$obj->reponse|htmlentitiesUTF8}" {if $obj->id_statut == 5} readonly {/if} >{if $obj->reponse != null}{$obj->reponse|htmlentitiesUTF8}{else}{l s='Your proposition...' mod='devisperso'}{/if}</textarea>
				<div class="col-lg-9 ">
				<div class="help-block">
					<sup>*</sup>
					<span class="small" name="help_box">{l s='Forbidden characters:' mod='devisperso'} &lt;&gt;{}</span>
				</div>
				</div>
			</div>
			</div>
			<label class="control-label col-lg-3">{l s='Weight:' mod='devisperso'}</label>
			<div class="col-lg-9">
			<div class="input-group">
				<span class="input-group-addon"> kg </span>
				<input type="text" class="form-control fixed-width-xxl" size="11" style="text-align:right;" id="poids_devis" name="poids_devis" value="{if $obj->poids_devis != null}{$obj->poids_devis}{else}0{/if}" onchange="calculTotal(); shippingChange($('select#id_carrier').val(), {$obj->id}, $('input:text[name=total_out_shipp]').val(), $(this).val());" {if $obj->id_statut == 5} readonly {/if} />
			</div>
				<div class="col-lg-9 ">
					<div class="help-block">
						<img src="../img/admin/warning.gif" />&nbsp;ex : 5.25 &nbsp; <s>5,25</s>
					</div>
				</div>
			</div>
			<label class="control-label col-lg-3">{l s='Free Shipping:' mod='devisperso'}</label>
			<div class="col-lg-9">
				<div class="input-group">
					<div class="col-lg-9">
						<span class="switch prestashop-switch fixed-width-lg">
					<input style="float:left;" type="radio" name="free_shipp" id="free_shipp" value="1" {if $obj->free_shipp == 1} checked="checked" {/if} {if $obj->id_statut == 5} disabled="disabled" {/if} onClick="shippingNull()" />
					<label for="free_shipp" class="t">
						{l s='Enabled' mod='devisperso'}
					</label>
					<input style="float:left;margin-left:25px;" type="radio" name="free_shipp" id="active_off" value="0" {if $obj->free_shipp == 0} checked="checked" {/if} {if $obj->id_statut == 5} disabled="disabled" {/if} onClick="shippingChange($('select#id_carrier').val(), {$obj->id}, $('input:text[name=total_out_shipp]').val(), $('input:text[name=poids_devis]').val());" />
					<label for="active_off" class="t">
						{l s='Disabled' mod='devisperso'}
					</label>
					<a class="slide-button btn"></a>
								</span>
							</div>
					<br class="clear" />
					<div class="col-lg-9 "><div class="help-block"></div></div>
				</div>    
			</div>
			<label class="control-label col-lg-3">{l s='Shipping cost:' mod='devisperso'}</label>
			<div class="col-lg-9">
				<div class="input-group">
					<span class="input-group-addon"> {$currency->sign} {l s='incl. tax' mod='devisperso'}</span>
					<div class="shipp">
						<input type="text" class="form-control fixed-width-xxl"  size="11"  style="text-align:right; color:grey;" id="total_shipping" name="total_shipping" value="{$obj->total_shipping}" readonly />
					</div>
				</div>
			</div>
			<br /><br />

			<label class="control-label col-lg-3">{l s='Total whithout shipp.:' mod='devisperso'}</label>
			<div class="col-lg-9">
				<div class="input-group">
					<span class="input-group-addon"> {$currency->sign} {l s='excl. tax' mod='devisperso'}</span>
					<input type="text" class="form-control fixed-width-xxl" size="11" style="text-align:right" name="total_out_shipp" id="total_out_shipp" value="{$obj->total_out_shipp}" {if $obj->id_statut == 5} readonly {/if} onchange="calculTotal(); shippingChange($('select#id_carrier').val(), {$obj->id}, $(this).val(), $('input:text[name=poids_devis]').val());"  />
				</div>
				<div class="col-lg-9 ">
					<div class="help-block">
						<img src="../img/admin/warning.gif" />&nbsp;ex : 150.25 &nbsp; <s>150,25</s>
					</div>
				</div>
			</div>
			
			<script type="text/javascript">
				taxFill({$obj->id_address_delivery}, {$lang->id}, {$obj->id_tax});
			</script>

			<label class="control-label col-lg-3">{l s='Tax:' mod='devisperso'}</label>
			<div class="col-lg-9">
				<div class="input-group">
					<select onChange="calculTotal();" class="tax" name="id_tax" id="id_tax" {if $no_tax || $obj->id_statut == 5} disabled="disabled" {/if} >
					</select>
					<div class="col-lg-9 ">
					<div class="help-block">
						<p class="hint clear">
							<span class="small" name="help_box">
								{l s=' Taxes displayed are those that have a tax rule in customer\'s country' mod='devisperso'}
							</span>
						</p>
					</div>
					</div>
				</div>
			</div>
			<label class="control-label col-lg-3">{l s='Total:' mod='devisperso'}</label>
			<div class="col-lg-9">
				<div class="input-group">
					<span class="input-group-addon"> {$currency->sign} {l s='incl. tax' mod='devisperso'}	</span>
					<input type="text" class="form-control fixed-width-xxl" size="11" style="text-align:right; color:grey;" name="total_devis" id="total_devis" value="{$obj->total_devis}" readonly />					
				</div>
			</div>
			<label class="control-label col-lg-3">{l s='Description for the invoice:' mod='devisperso'}</label>
			<div class="col-lg-9">
				<div class="input-group">
					<textarea cols="110" rows="5" name="desc_invoice" value="{$obj->desc_invoice}" {if $obj->id_statut == 5} readonly {/if} >{$obj->desc_invoice}</textarea>
					<br /> 
					<div class="col-lg-9 ">
					<div class="help-block">
						<span class="small" name="help_box">{l s='Forbidden characters:' mod='devisperso'} &lt;&gt;{}<br />
							{l s='This description will appear on the invoice if the customer places his order' mod='devisperso'}
						</span>
					</div>
					</div>
				</div>
			</div>
		{/if}	

		<div style="text-align:center">
			<input type="submit" value="{l s='Save'}" class="button" name="submitAdddevis" id="{$table|escape}_form_submit_btn"  />
		</div>
		<div class="small"><sup>*</sup> {l s='Required field' mod='devisperso'}</div>
		</div>
	</div>
 </form>