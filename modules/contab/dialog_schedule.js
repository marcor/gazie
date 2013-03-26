function dialogSchedule(paymov) {
 
    var clfoco = paymov.id.substring(6,15),
		nrow = paymov.id.substring(23),
		id_rig = $( "#id_rig_rc"+nrow ).val(),
		tot_amount = $( "#impoRC"+nrow ).val(); //mi servirà per controllare che il totale delle partite sia uguale a questo

	getResults(clfoco,id_rig);

	$.fx.speeds._default = 500;

    var descri = $( "#descri" ),
        expiry = $( "#expiry" ),
        amount = $( "#amount" ),
        remrow = $( "#remrow" ),
        allFields = $( [] ).add( expiry ).add( amount ).add( remrow ),
        tips = $( ".validateTips" );

    function getResults(term_val,excl_val) {
       $.get("expiry.php",
             {clfoco:term_val, id_exc:excl_val},
             function(data) {
                $.each(data, function(i,value){
                       $( "#db-contain" + nrow + " tbody").append( "<tr>" +
                          "<td" + ' class="ui-widget ui-widget-content " > '+ value.descri + " n."
                          + value.numdoc + "/" + value.seziva + " del " + value.datdoc + "</td>" +
                          "<td" + ' class="ui-widget ui-widget-content " >' + value.expiry + "</td>" +
                          "<td" + ' class="ui-widget-right ui-widget-content " >' + value.amount + "</td>" +
                           '<td class="ui-widget-right ui-widget-content " >'+value.darave+'</td>' +
                           '<td class="ui-widget-right ui-widget-content "><A target="NEW" href="admin_movcon.php?id_tes=' + value.id_tes + '&Update"><img src="../../library/images/new.png" width="12"/></A></td>' +
                           "</tr>" );
               });
             },"json"
             );
    }

    function updateTips( t ) {
       tips
       .text( t )
       .addClass( "ui-state-highlight" );
       setTimeout(function() {
            tips.removeClass( "ui-state-highlight", 1500 );
       }, 500 );
    }
	
	function checkDate (o_date, n) {
		var d,day,month,year;
	    d = o_date.val().toString().replace(/\//g,"-").split("-");
		day = d[0] - 0;
		month = d[1]-0;
		year = d[2] - 0;			
		if (month > 0 && month < 13 && year > 2000 && year < 3000 && day > 0 && day <= (new Date(year, month, 0)).getDate()){
            return true;
		} else {
            o_date.addClass( "ui-state-error" );
            updateTips( "Errore !!! "+n);
            return false;
		}
	}
	
    function checkAmount( o, n ) {
		var amou;
        amou = o.val().toString().replace(/\,/g,".");
        if ( amou < 0.01 ) {
            o.addClass( "ui-state-error" );
            updateTips( "Errore !!! " + n );
            return false;
       } else {
            return true;
       }
    }
    
    $( "#dialog"+nrow ).dialog({
      autoOpen: false,
      show: "scale",
      width: 620,
      modal: true,
      buttons: {
        "Chiudi":function(){ $(this).dialog( "close" );}
      },
      close: function() {
        allFields.val( "" ).removeClass( "ui-state-error" );
        $( "#db-contain"+ nrow + " tbody").replaceWith("<tbody></tbody>");
      }
    });

    $("#dialog"+nrow ).dialog( "open" );

    $("#sbmt").click(function() {
            var bValid = true;
            allFields.removeClass( "ui-state-error" );
            bValid = bValid && checkDate( expiry, "La data di Scadenza è sbagliata" );
            bValid = bValid && checkAmount( amount,"L'importo inserito è sbagliato" );
            if ( bValid ) {     
                $( "#openitem"+ nrow + " tbody" ).append( "<tr>" +
                   "<td></td>" +
                   "<td" + ' class="ui-widget ui-widget-content " >' + expiry.val() + "</td>" +
                   "<td" + ' class="ui-widget-right ui-widget-content " >' + amount.val() + "</td>" +
                   '<td class="ui-widget-right ui-widget-content "><button id="'+nrow+'"><img src="../../library/images/x.gif" /></button></td>' +
                   "</tr>" );
                $("#paymov_dial"+nrow+" input" ).append("<input type='hidden' name='paymov[" + nrow + "][][amount]' value='" + amount.val() + "' />");
                $("#paymov_dial"+nrow+" input" ).append("<input type='hidden' name='paymov[" + nrow + "][][expiry]' value='" + expiry.val() + "' />");
                allFields.val( "" ).removeClass( "ui-state-error" );
                updateTips( "" );
            }
    });

    $("#add_expiry").click(function() {
				var id_btn = new Date().valueOf().toString();
     			$( "#openitem"+ nrow + " tbody tr td " ).each(function(){
					$( this ).toggleClass( "ui-state-error" );
				});
                $( "#openitem"+ nrow + " tbody" ).append( '<tr id="'+id_btn+'_row">' +
                   '<td></td><td class="ui-widget-right ui-widget-content " ><input type="text" name="paymov[' + nrow + '][' + id_btn + '][expiry]" value="" /></td>' +
                   '<td class="ui-widget-right ui-widget-content " ><input style="text-align:right;" type="text" name="paymov[' + nrow + '][' + id_btn + '][amount]" value="" /></td>' +
                   '<td class="ui-widget-right ui-widget-content " ><span class="ui-button-text dynamic-button" id="' + id_btn + '"><img src="../../library/images/x.gif" /></span></td>' +
                   "</tr>" );
				$('#' + id_btn).button().click(function() {
					$( '#' + id_btn + '_row').remove();
					    updateTips( "Cancellato: " + id_btn );
				});
                updateTips( "Aggiunto: " + id_btn );
			}
	);

}