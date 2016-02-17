function display_list_customers()
{
	var recherche = $("#recherche").val();

	if (recherche.length > 2)
	{
		var this_shop = $("#id_shop").val();
		$.ajax({
			type: "POST",
			headers: {"cache-control": "no-cache"},
			url: url_commercial_manager + "/display_list_customers.php",
			data: {
				"search": recherche,
				"shop": shop_text_commercial_manager,
				"this_shop": this_shop
			},
			success: function (result)
			{
				$("#display_list_customers").html(result);
			},
			error: function (result)
			{
				$("#display_list_customers").html(no_result_commercial_manager);
			}
		});
	}
}		