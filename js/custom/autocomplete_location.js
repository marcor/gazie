$(function() {
		$( "#search_location" ).autocomplete({
			minLength: 3,
			source: "../../modules/root/search_location.php",
			focus: function( event, ui ) {
				$( "#search_location" ).val( ui.item.label );
				return false;
			},
			select: function( event, ui ) {
				$( "#search_location" ).val( ui.item.value );
				$( "#search_location-capspe" ).val( ui.item.capspe );
				$( "#search_location-prospe" ).val( ui.item.prospe );
				$( "#country").val( ui.item.country );  //grazie ad Emanuele Ferrarini
				return false;
			}
		})
});