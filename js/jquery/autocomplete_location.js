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
				//$( "#country opt.value='"+val( ui.item.country )+"'").attr('selected','selected');  
				/*    pensavo che si facesse così ma la scarsa conoscenza di jquery non mi permette di riuscire nell'intendo...
				        se qualcuno lo sa fare o vuole impegnarsi in tal senso me lo faccia sapere
				*/
				return false;
			}
		})
});