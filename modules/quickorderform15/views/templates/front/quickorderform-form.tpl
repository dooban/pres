{capture name=path}<a href="{$link->getPageLink('my-account', true)}">{l s='My account' mod='quickorderform15'}</a><span class="navigation-pipe">{$navigationPipe}</span>{l s='Quick form to buy' mod='quickorderform15'}{/capture}


<!-- MODULE QuickOrderForm 15 -->
<div class="quick_order_content">
	<div class="default_lines_container">
		<table>
			<tbody>
			<tr>
				<td class="selector"></td>
				<td class="row_number">0</td>
				<td class="ref"><input type="text" value="" maxlength="10" size="10" title="{l s='Please enter reference or name' mod='quickorderform15'}"></td>
				<td class="name"><p class="title"></p><input type="hidden" class="id_product"/></td>
				<td class="decli"><select disabled=""></select></td>
				<td class="quantity">
					<input type="text" />
					<span class="min"></span>
				</td>
				<td class="remove"><a class="del_button" title="{l s='Remove this product' mod='quickorderform15'}"><img src="{$img_dir}icon/delete.gif" alt="{l s='Remove' mod='quickorderform15'}"/></a>
				</td>
			</tr>
			</tbody>
		</table>
	</div>
	<table class="quick_order_table" cellspacing="0">
		<tbody>
		<tr class="head">
			<th class="selector"></th>
			<th class="row_number"></th>
			<th class="ref">{l s='Ref.' mod='quickorderform15'}</th>
			<th class="name">{l s='Name' mod='quickorderform15'}</th>
			<th class="decli">{l s='Combinaison' mod='quickorderform15'}</th>
			<th class="quantity">{l s='Quantity' mod='quickorderform15'}</th>
			<th class="remove"></th>
		</tr>
		<tr class="">
			<td class="selector"></td>
			<td class="row_number">1</td>
			<td class="ref"><input type="text" value="" maxlength="10" size="25" title="{l s='Please enter reference or name' mod='quickorderform15'}"></td>
			<td class="name"><p class="title"></p><input type="hidden" class="id_product"/></td>
			<td class="decli"><select disabled=""></select></td>
			<td class="quantity">
				<input type="text" />
				<span class="min"></span>
			</td>
			<td class="remove"><a class="del_button" title="{l s='Remove this product' mod='quickorderform15'}"><img src="{$img_dir}icon/delete.gif" alt="{l s='Remove' mod='quickorderform15'}"/></a>
			</td>
		</tr>
		<tr class="alternate">
			<td class="selector"></td>
			<td class="row_number">2</td>
			<td class="ref"><input type="text" value="" maxlength="10" size="25" title="{l s='Please enter reference or name' mod='quickorderform15'}"></td>
			<td class="name"><p class="title"></p><input type="hidden" class="id_product"/></td>
			<td class="decli"><select disabled=""></select></td>
			<td class="quantity">
				<input type="text" />
				<span class="min"></span>
			</td>
			<td class="remove"><a class="del_button" title="{l s='Remove this product' mod='quickorderform15'}"><img src="{$img_dir}icon/delete.gif" alt="{l s='Remove' mod='quickorderform15'}"/></a>
			</td>
		</tr>
		<tr class="">
			<td class="selector"></td>
			<td class="row_number">3</td>
			<td class="ref"><input type="text" value="" maxlength="10" size="25" title="{l s='Please enter reference or name' mod='quickorderform15'}"></td>
			<td class="name"><p class="title"></p><input type="hidden" class="id_product"/></td>
			<td class="decli"><select disabled=""></select></td>
			<td class="quantity">
				<input type="text" />
				<span class="min"></span>
			</td>
			<td class="remove"><a class="del_button" title="{l s='Remove this product' mod='quickorderform15'}"><img src="{$img_dir}icon/delete.gif" alt="{l s='Remove' mod='quickorderform15'}"/></a>
			</td>
		</tr>
		<tr class="alternate">
			<td class="selector"></td>
			<td class="row_number">4</td>
			<td class="ref"><input type="text" value="" maxlength="10" size="25" title="{l s='Please enter reference or name' mod='quickorderform15'}"></td>
			<td class="name"><p class="title"></p><input type="hidden" class="id_product"/></td>
			<td class="decli"><select disabled=""></select></td>
			<td class="quantity">
				<input type="text" />
				<span class="min"></span>
			</td>
			<td class="remove"><a class="del_button" title="{l s='Remove this product' mod='quickorderform15'}"><img src="{$img_dir}icon/delete.gif" alt="{l s='Remove' mod='quickorderform15'}"/></a>
			</td>
		</tr>
		<tr class="">
			<td class="selector"></td>
			<td class="row_number">5</td>
			<td class="ref"><input type="text" value="" maxlength="10" size="25" title="{l s='Please enter reference or name' mod='quickorderform15'}"></td>
			<td class="name"><p class="title"></p><input type="hidden" class="id_product"/></td>
			<td class="decli"><select disabled=""></select></td>
			<td class="quantity">
				<input type="text" />
				<span class="min"></span>
			</td>
			<td class="remove"><a class="del_button" title="{l s='Remove this product' mod='quickorderform15'}"><img src="{$img_dir}icon/delete.gif" alt="{l s='Remove' mod='quickorderform15'}"/></a>
			</td>
		</tr>
		<tr class="last">
			<td colspan="5" class="add_lines">
				<a class="add_lines" href="javascript:AddNewLines(1);" title="{l s='Add 5 new lines' mod='quickorderform15'}">
					<i class="icon-plus-square"></i> {l s='Add new line' mod='quickorderform15'}
				</a>
			</td>
			<td colspan="2">
				<p id="add_to_cart_fix" class="buttons_bottom_block">
					<input type="submit" name="Submit" value="{l s='Add to cart' mod='quickorderform15'}" class="exclusive"/>
				</p>
			</td>
		</tr>
		</tbody>
	</table>
<img id="bigpic" style="display:none;visibility:hidden" />
</div>


<script type="text/javascript">
// <![CDATA[
	{literal}
		var selectedRow = null;

		$(document).ready( function() {
			$(".quick_order_content .ref input")
				.autocomplete(
					'{/literal}{if $search_ssl == 1}{$link->getPageLink('search.php', true)}{else}{$link->getPageLink('search.php')}{/if}{literal}', {
						minChars: 3,
						max: 10,
						width: 500,
						cacheLength : 1,
						selectFirst: false,
						scroll: false,
						dataType: "json",
						formatItem: function(data, i, max, value, term) {
							return value;
						},
						parse: function(data) {
							var mytab = new Array();
							for (var i = 0; i < data.length; i++)
								mytab[mytab.length] = { data: data[i], value: data[i].cname + ' > ' + data[i].pname };
							return mytab;
						},
						extraParams: {
							ajaxSearch: 1,
							id_lang: {/literal}{$cookie->id_lang}{literal}
						}
					}
				)
				.result(function (event, data, formatted) {
					$(this).val(data.preference);
					$(this).parent().parent().find('.name .id_product').val(data.id_product);
					if (data.pname.length > 42)
						$(this).parent().parent().find('.name .title').html(data.pname.slice(0, 42 - 3) + "...");
					else
						$(this).parent().parent().find('.name .title').html(data.pname);
					lesdeclis(data.id_product, $(this));
				});

				$(".quick_order_content .decli select").change(function () {
					majMin($(this));
				});

				$(".quick_order_content tr[class!=last][class!=head][class!=separation]").click(function () {
					ClickToSelect($(this));
				});

				$(".quick_order_content .del_button").click(function () {
					emptyLine($(this).parent().parent());
				});

				$('body .quick_order_content p#add_to_cart_fix input').unbind('click').click(function(){
					$('.quick_order_content table.quick_order_table tr[class!=last][class!=head][class!=separation]').each(function()
					{
						if ($(this).find('.quantity input').val() > 0)
						{
							var currentRow=$(this);
							ajaxCart.add( currentRow.find('.name .id_product').val(), currentRow.find('.decli select').val(), false, this, currentRow.find('.quantity input').val(), null);
						}
					});
					return false;
				});

				BindRowSelection($('table.quick_order_table tr[class!=last][class!=head][class!=separation] td,table.quick_order_table tr[class!=last][class!=head][class!=separation] td.ref a'));
				BindFocusToSelectRow($('table.quick_order_table input,table.quick_order_table select'));

				var firstRow = $($('table.quick_order_table tr').get(1));
				firstRow.addClass("selected");
				InitRow(firstRow);
				selectedRow = firstRow;
		});

		function InitRow(row) {
			selectedRow = row;
		}

		function lesdeclis(lidproduct, maligne) {
		$.ajax({
				url: "{/literal}{$link->getModuleLink('quickorderform15', 'actions', ['process' => 'getDecli'], true)}{literal}",
				type:"POST",
				data: {
					'id_product' : lidproduct
				},
				success:function (response) {
					var qty_min_default = 0;
					maligne.parent().parent().find('.decli select').empty();
					mesreponses = response.split('##');
					console.log(mesreponses);
					if (mesreponses[0] == 'ProdSansDecli') {
						maligne.parent().parent().find('.decli select').hide();
						qty_min_default = mesreponses[1];
						if (maligne.parent().parent().find('.name .title').html().length > 20)
						{
							maligne.parent().parent().find('.decli').hide();
							maligne.parent().parent().find('.name').attr('colspan',2);
						}
						else
						{
							if (maligne.parent().parent().find('.name').attr('colspan') == 2)
							{
								maligne.parent().parent().find('.decli').css('display', 'table-cell');
								maligne.parent().parent().find('.name').removeAttr('colspan');
							}
						}
					}
					else {
						for (i = 0; i < mesreponses.length; i++) {
							mareponse = mesreponses[i].split('#');
							if (mareponse[3] == 1) {
								maligne.parent().parent().find('.decli select').append('<option value="' + mareponse[0] + '" selected>' + mareponse[1] + '</option>');
								qty_min_default = mareponse[2];
							}
							else {
								maligne.parent().parent().find('.decli select').append('<option value="' + mareponse[0] + '">' + mareponse[1] + '</option>');
							}
						}
						maligne.parent().parent().find('.decli').show();
						maligne.parent().parent().find('.decli select').css('display', 'inline-block');
						maligne.parent().parent().find('.name').removeAttr('colspan');
					}

					maligne.parent().parent().find('.decli select').removeAttr('disabled');
					maligne.parent().parent().find('.quantity input').val(qty_min_default);
					maligne.parent().parent().find('.quantity .min').html(qty_min_default);
				},
				error:function (XMLHttpRequest, textStatus, errorThrown) {
					alert("TECHNICAL ERROR: unable to refresh the cart.\n\nDetails:\nError thrown: " + XMLHttpRequest + "\n" + 'Text status: ' + textStatus);
				}
			});
		}

		function emptyLine(linetoempty) {
			linetoempty.find('.name .title').html('');
			linetoempty.find('.name').removeAttr('colspan');
			linetoempty.find('.ref input').val('');
			linetoempty.find('.decli').show();
			linetoempty.find('.decli select').empty();
			linetoempty.find('.decli select').css('display', 'inline-block');
			linetoempty.find('.decli select').attr('disabled', 'disabled') ;
			linetoempty.find('.quantity .min').empty();
			linetoempty.find('.quantity input').val('');
			linetoempty.find('.quantity input').attr('disabled', 'disabled');
		}

		function majMin(myObject) {
			var idprod = myObject.parent().parent().find('.name .id_product').val();
			var idprodattrib = myObject.find('option:selected').val();
			$.ajax({
				type:"POST",
				url: "{/literal}{$link->getModuleLink('quickorderform15', 'actions', ['process' => 'getMin'], true)}{literal}",
				data: {
					'id_product' : idprod,
					'id_product_attribute' : idprodattrib
				},
				success:function (response) {
					myObject.parent().parent().find('.quantity .min').html(response);
					myObject.parent().parent().find('.quantity input').val(response);
				},
				error:function (XMLHttpRequest, textStatus, errorThrown) {
					alert("TECHNICAL ERROR: unable to retrieve minimal quantity");
				}
			});
		}

		function AddNewLines(lineNumberToAdd) {
			var tmpSelection = $('table.quick_order_table tr');
			var lastLine = $('table.quick_order_table tr.last');
			for (var i = 0; i < lineNumberToAdd; i++) {
				var newRow = $('.default_lines_container tr').clone();
				lastLine.before(newRow);
				BindRowSelection(newRow.find('td'));
				BindAutocompletionFunction(newRow.find('.ref input'));
				BindDeleteFunction(newRow.find('.del_button'));
				BindMajMin(newRow.find('.decli select'));
				BindClickToSelect(newRow);
			}
			var intRowCount = 1;
			$('table.quick_order_table tr[class!=last][class!=head][class!=separation]').each(function () {
				var currentRow = $(this);
				currentRow.find('.row_number').html(intRowCount);
				currentRow.removeClass('alternate');
				if (intRowCount % 2 == 0) {
					currentRow.addClass('alternate');
				}
				intRowCount++;
			});
		}

		function BindMajMin(myObject) {
			$(myObject).bind("change", function (e) {
				majMin($(this));
			});
		}

		function BindDeleteFunction(myObject) {
			$(myObject).bind("click", function (e) {
				emptyLine(myObject.parent().parent());
			});
		}

		function BindFocusToSelectRow(jQueryObject) {
			jQueryObject.bind('focus change', function () {
				var newRow = $(this).parents('tr');
				if (newRow.attr('class').indexOf('selected') != -1) {
					return;
				}
				if ( (!newRow.attr('class')) || (newRow.attr('class').indexOf('selected') == -1) ) {
					newRow.addClass('selected');
					selectedRow.removeClass('selected');
					InitRow(newRow);
				}
			});
		}

		function BindClickToSelect(jQueryObject) {
			$(jQueryObject).bind("click", function (e) {
				ClickToSelect($(this))
			});
		}

		function ClickToSelect(jQueryObject) {
			jQueryObject.click(function () {
				$(this).addClass('selected');
			});
		}

		function BindRowSelection(jQueryObject) {
			jQueryObject.click(function () {
				var newRow = $(this).parents('tr');
				if ( (!newRow.attr('class')) || (newRow.attr('class').indexOf('selected') == -1) ) {
					newRow.addClass('selected');
					var ref = '';
					var numberOfCars = 0;
					selectedRow.find('.ref input').each(function () {
						numberOfCars += $(this).val().length;
						ref += $(this).val();
					});
					selectedRow.removeClass('selected');
					InitRow(newRow);
				}
			});
		}

		function BindAutocompletionFunction(jQueryObject) {
			jQueryObject.bind("focus change", function (event) {
				jQueryObject
				.autocomplete(
					'{/literal}{if $search_ssl == 1}{$link->getPageLink('search.php', true)}{else}{$link->getPageLink('search.php')}{/if}{literal}', {
						minChars: 3,
						max: 10,
						width: 500,
						selectFirst: false,
						cacheLength : 1,
						scroll: false,
						dataType: "json",
						formatItem: function(data, i, max, value, term) {
							return value;
						},
						parse: function(data) {
							var mytab = new Array();
							for (var i = 0; i < data.length; i++)
								mytab[mytab.length] = { data: data[i], value: data[i].cname + ' > ' + data[i].pname };
							return mytab;
						},
						extraParams: {
							ajaxSearch: 1,
							id_lang: {/literal}{$cookie->id_lang}{literal}
						}
					}
				)
				.result(function (event, data, formatted) {
					$(this).val(data.preference);
					$(this).parent().parent().find('.name .id_product').val(data.id_product);
					if (data.pname.length > 42)
						$(this).parent().parent().find('.name .title').html(data.pname.slice(0, 42 - 3) + "...");
					else
						$(this).parent().parent().find('.name .title').html(data.pname);
					lesdeclis(data.id_product, $(this));
				});
			});
		}
	{/literal}
	// ]]>
	</script>
<!-- /MODULE QuickOrderForm 15 -->
