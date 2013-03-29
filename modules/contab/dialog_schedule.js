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
        allFields = $( [] ).add( descri ).add( expiry ).add( amount ),
        tips = $( ".validateTips" );

    function updateForm() {
		$( "#openitem"+ nrow + " tbody tr" ).remove();
		$( "#openitem"+ nrow + " tbody" ).replaceWith('<tbody> <tr id="pm_header_'+ nrow + '">' +
			'<td class="ui-widget ui-widget-content " >Descrizione</td>' +
			'<td class="ui-widget ui-widget-content " >Scadenza</td>' +
			'<td class="ui-widget-right ui-widget-content ">Importo</td>' + 
			'<td class="ui-widget-right ui-widget-content "><button id="add_expiry'+ nrow + '" value="' + nrow +'">'+
			'<img src="../../library/images/add.png" /></button></td></tr></tbody>');
			$( "#pm_post_container_"+ nrow + " div" ).each(function(i,v) {
				var valore = $(v).attr('id').split('_');
				var id_sub = valore[2];
				var ex = $('input[id=paymov_' + nrow + '_' + id_sub + '_expiry]:first',v).focus().attr('value');
				var am = $('input[id=paymov_' + nrow + '_' + id_sub + '_amount]:first',v).focus().attr('value');
				$( "#openitem"+ nrow + " tbody" ).append( '<tr id="pm_form_'+id_sub+'">' +
					'<td></td><td class="ui-widget-right ui-widget-content " ><input type="text" name="paymov[' + nrow + '][' + id_sub + '][expiry]" value="' + ex + '" /></td>' +
					'<td class="ui-widget-right ui-widget-content " ><input style="text-align:right;" type="text" name="paymov[' + nrow + '][' + id_sub + '][amount]" value="' + am + '" /></td>' +
					'<td class="ui-widget-right ui-widget-content " ><button id="btn_' + id_sub + '"><img src="../../library/images/x.gif" /></button></td>' +
					"</tr>" );
				$( "#btn_"+id_sub ).click(function() { 
					$("#pm_form_"+id_sub ).remove();
					$("#pm_post_"+id_sub ).remove();
				});

			});
	}

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
       tips.text( t ).addClass( "ui-state-highlight" );
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
	  position: "top",	  
	  open: function(){ updateForm(); },
      buttons: {
        "Chiudi (Esc)":function(){ $(this).dialog( "close" );}
      },
      close: function() {
			allFields.val( "" ).removeClass( "ui-state-error" );
			$( "#db-contain"+ nrow + " tbody").remove();
			$( "#db-contain"+ nrow ).append("<tbody></tbody>");
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

    $("#add_expiry"+nrow).click(function() {
				var id_btn = new Date().valueOf().toString();
				$( "#pm_post_container_"+ nrow ).append( '<div id="pm_post_' + id_btn + '">'+
					'<input type="hidden" id="paymov_' + nrow + '_' + id_btn + '_expiry" name="paymov[' + nrow + '][' + id_btn + '][expiry]" value="" />'+
					'<input type="hidden" id="paymov_' + nrow + '_' + id_btn + '_amount" name="paymov[' + nrow + '][' + id_btn + '][amount]" value="" />'+
					'</div>');
                $( "#openitem"+ nrow + " tbody" ).append( '<tr id="pm_form_'+id_btn+'">' +
                   '<td></td><td class="ui-widget-right ui-widget-content " ><input type="text" name="paymov[' + nrow + '][' + id_btn + '][expiry]" value="" /></td>' +
                   '<td class="ui-widget-right ui-widget-content " ><input style="text-align:right;" type="text" name="paymov[' + nrow + '][' + id_btn + '][amount]" value="" /></td>' +
                   '<td class="ui-widget-right ui-widget-content " ><button id="btn_' + id_btn + '"><img src="../../library/images/x.gif" /></button></td>' +
                   "</tr>" );
     			$( "#btn_"+id_btn ).click(function(){
					$("#pm_form_"+id_btn).remove();
					$("#pm_post_"+id_btn).remove();
				});
			}
	);

}