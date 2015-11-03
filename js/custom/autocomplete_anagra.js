$(function() {
	$( "#search_clfoco" ).autocomplete({
		source: "../../modules/root/search.php",
		minLength: 2,
	});
	$( "#search_id_customer" ).autocomplete({
		source: "../../modules/root/search.php",
		minLength: 2,
	});
});
