function dialogSchedule(paymov) {
 
    var clfoco = paymov.id.substring(6,15),
		nrow = paymov.id.substring(23),
		id_rig = $( "#id_rig_rc"+nrow ).val(),
		tesdoc_ref = $("#post_"+nrow+"_0_id_tesdoc_ref").val();
		tot_amount = $( "#impoRC"+nrow ).val(); //mi servirà per controllare che il totale delle partite sia uguale a questo

    var descri = $( "#descri" ),
        expiry = $( "#expiry" ),
        amount = $( "#amount" ),
        allFields = $( [] ).add( descri ).add( expiry ).add( amount ),
        tips = $( ".validateTips" );

    function updateCloseForm() {
		var paymov_op_cl = $("#paymov_op_cl"+nrow).val();
		$( "#pm_form_container_"+ nrow + " tbody tr" ).remove();
		$( "#pm_form_container_"+ nrow + " tbody" ).replaceWith('<tbody> <tr id="pm_header_'+ nrow + '" def="nopost" >' +
			'<td class="ui-widget ui-widget-content " >ID partita </td>' +
			'<td class="ui-widget ui-widget-content " >Data Chiusura</td>' +
			'<td class="ui-widget-right ui-widget-content ">Importo</td>' + 
			'<td class="ui-widget-right ui-widget-content "><button id="addCloseExpiry'+ nrow + '" value="' + nrow +'">'+
			'<img src="../../library/images/add.png" /></button></td></tr></tbody>');
			$( "#pm_post_container_"+ nrow + " div" ).each(function(i,v) {
				var idv = $(v).attr('id').split('_');
				var id_sub = idv[2];
				var id = $('input[id=post_' + nrow + '_' + id_sub + '_id_tesdoc_ref]:first',v).focus().attr('value');
				var ex = $('input[id=post_' + nrow + '_' + id_sub + '_expiry]:first',v).focus().attr('value');
				var am = $('input[id=post_' + nrow + '_' + id_sub + '_amount]:first',v).focus().attr('value');
				$( "#pm_form_container_"+ nrow + " tbody" ).append( '<tr id="pm_form_'+id_sub+'"><td><button id="unlink_' + id_sub + '"><img id="image_unlink_' + id_sub + '" src="../../library/images/link_break.png" width="12" /></button> ' + id +
					'</td><td class="ui-widget-right ui-widget-content " ><input id="form_' + nrow + '_' + id_sub + '_expiry" type="text" name="paymov[' + nrow + '][' + id_sub + '][expiry]" value="' + ex + '" id="post_' + nrow + '_' + id_sub + '_expiry" /></td>' +
					'<td class="ui-widget-right ui-widget-content " ><input id="form_' + nrow + '_' + id_sub + '_amount" style="text-align:right;" type="text" name="paymov[' + nrow + '][' + id_sub + '][amount]" value="' + am + '" id="post_' + nrow + '_' + id_sub + '_amount" /></td>' +
					'<td class="ui-widget-right ui-widget-content " ><button id="btn_' + id_sub + '"><img src="../../library/images/x.gif" /></button></td>' +
					"</tr>" );
				$('#form_' + nrow + '_' + id_sub + '_expiry' ).change(function(){
					if (checkDate($(this),"La data di chiusura è sbagliata (gg-mm-aaaa)")) {
						$('#post_' + nrow + '_' + id_sub + '_expiry').val($(this).val());
					}
				});
				$('#form_' + nrow + '_' + id_sub + '_amount' ).change(function(){
					if (checkAmount($(this),"L'importo è sbagliato")) {
						$('#post_' + nrow + '_' + id_sub + '_amount').val($(this).val());
					}
				});					
				$( "#btn_"+id_sub ).click(function() { 
					$("#pm_form_"+id_sub ).remove();
					$("#pm_post_"+id_sub ).remove();
				});
				$( "#unlink_"+id_sub ).click(function() { 
					$('#post_' + nrow + '_' + id_sub + '_id_tesdoc_ref').val('');
					updateCloseForm();
					$('#image_unlink_' + id_sub).remove();
				});

			});
	}

    function updateOpenForm() {
		var paymov_op_cl = $("#paymov_op_cl"+nrow).val();
		$( "#pm_form_container_"+ nrow + " tbody tr" ).remove();
		$( "#pm_form_container_"+ nrow + " tbody" ).replaceWith('<tbody> <tr id="pm_header_'+ nrow + '" def="nopost" >' +
			'<td class="ui-widget ui-widget-content " >ID partita </td>' +
			'<td class="ui-widget ui-widget-content " >Scadenza</td>' +
			'<td class="ui-widget-right ui-widget-content ">Importo</td>' + 
			'<td class="ui-widget-right ui-widget-content "><button id="addOpenExpiry'+ nrow + '" value="' + nrow +'">'+
			'<img src="../../library/images/add.png" /></button></td></tr></tbody>');
			$( "#pm_post_container_"+ nrow + " div" ).each(function(i,v) {
				var idv = $(v).attr('id').split('_');
				var id_sub = idv[2];
				var id = $('input[id=post_' + nrow + '_' + id_sub + '_id_tesdoc_ref]:first',v).focus().attr('value');
				var ex = $('input[id=post_' + nrow + '_' + id_sub + '_expiry]:first',v).focus().attr('value');
				var am = $('input[id=post_' + nrow + '_' + id_sub + '_amount]:first',v).focus().attr('value');
				$( "#pm_form_container_"+ nrow + " tbody" ).append( '<tr id="pm_form_'+id_sub+'"><td> ' + id +
					'</td><td class="ui-widget-right ui-widget-content " ><input id="form_' + nrow + '_' + id_sub + '_expiry" type="text" name="paymov[' + nrow + '][' + id_sub + '][expiry]" value="' + ex + '" id="post_' + nrow + '_' + id_sub + '_expiry" /></td>' +
					'<td class="ui-widget-right ui-widget-content " ><input id="form_' + nrow + '_' + id_sub + '_amount" style="text-align:right;" type="text" name="paymov[' + nrow + '][' + id_sub + '][amount]" value="' + am + '" id="post_' + nrow + '_' + id_sub + '_amount" /></td>' +
					'<td class="ui-widget-right ui-widget-content " ><button id="btn_' + id_sub + '"><img src="../../library/images/x.gif" /></button></td>' +
					"</tr>" );
				$('#form_' + nrow + '_' + id_sub + '_expiry' ).change(function(){
					if (checkDate($(this),"La data di Scadenza è sbagliata (gg-mm-aaaa)")) {
						$('#post_' + nrow + '_' + id_sub + '_expiry').val($(this).val());
					}
				});
				$('#form_' + nrow + '_' + id_sub + '_amount' ).change(function(){
					if (checkAmount($(this),"L'importo è sbagliato")) {
						$('#post_' + nrow + '_' + id_sub + '_amount').val($(this).val());
					}
				});					
				$( "#btn_"+id_sub ).click(function() { 
					$("#pm_form_"+id_sub ).remove();
					$("#pm_post_"+id_sub ).remove();
				});

			});
	}

    function getSamePaymov(tes_ref,excl_val,link=false) {
       $.get("same_paymov.php",
             {id_tesdoc_ref:tes_ref, id_exc:excl_val},
             function(data) {
			    var j=0;
                $.each(data, function(i,value){
					if(j==0){
						if (link){
							link_ref = '<button id="linking_same_'+ value.datdoc +'"><img src="../../library/images/link.png" width="12"/></button>';
						};
						$( "#db-contain" + nrow + " tbody").append( "<tr>" +
						"<td class='ui-widget-content ui-state-active' colspan=7" + ' class="ui-widget ui-widget-content " > Altri movimenti della stessa partita ' + tes_ref + ' ' + link_ref + "</td></tr>");
					}					

                    $( "#db-contain" + nrow + " tbody").append( "<tr>" +
                    "<td" + ' class="ui-widget ui-widget-content " ></td>' +
                    "<td" + ' class="ui-widget ui-widget-content " > '+ value.descri + " n."
                    + value.numdoc + "/" + value.seziva + " del " + value.datdoc + "</td>" +
                    "<td" + ' class="ui-widget ui-widget-content " >' + value.expiry + "</td>" +
                    "<td" + ' class="ui-widget-right ui-widget-content " >' + value.amount + "</td>" +
                     '<td class="ui-widget-right ui-widget-content " >'+value.darave+'</td>' +
                     '<td class="ui-widget-right ui-widget-content "><A target="NEW" href="admin_movcon.php?id_tes=' + value.id_tes + '&Update"><img src="../../library/images/new.png" width="12"/></A></td>' +
                     "</tr>" );
					$( "#linking_same_" + value.datdoc).click(function() { 
							var paymov_op_cl = $("#paymov_op_cl"+nrow).val();
							var docref = value.datdoc.substring(0,4);
							docref += value.regiva;
							docref += value.seziva*1000000000+parseInt(value.protoc);
							updateSchedule(docref);
							updateCloseForm();
					});
					j++;
               });
             },"json"
             );
	}

    function getOtherMov(term_val,excl_val,link) {
       $.get("expiry.php",
             {clfoco:term_val, id_tesdoc_ref:excl_val},
             function(data) {
			    var j=0;
				var link_ref="";
                $.each(data, function(i,value){
					if(j==0){
						$( "#db-contain" + nrow + " tbody").append( "<tr>" +
						"<td class='ui-widget-content ui-state-active' colspan=7" + ' class="ui-widget ui-widget-content " > Altri movimenti di: '+ value.ragso1 +' ' + value.ragso2 +'</td></tr>');
					};
					if (link){
						link_ref = '<button id="linking_'+ value.datdoc +'"><img src="../../library/images/link.png" width="12"/></button>';
					};
                    $( "#db-contain" + nrow + " tbody").append( "<tr>" +
                       '<td class="ui-widget-right ui-widget-content ">' + link_ref + '</td>' +
                       "<td" + ' class="ui-widget ui-widget-content " > '+ value.descri + " n." +
                       value.numdoc + "/" + value.seziva + " del " + value.datdoc + "</td>" +
                       "<td" + ' class="ui-widget ui-widget-content " >' + value.expiry + "</td>" +
                       "<td" + ' class="ui-widget-right ui-widget-content " >' + value.amount + "</td>" +
                        '<td class="ui-widget-right ui-widget-content " >'+value.darave+'</td>' +
                        '<td class="ui-widget-right ui-widget-content "><A target="NEW" href="admin_movcon.php?id_tes=' + value.id_tes + '&Update"><img src="../../library/images/new.png" width="12"/></A></td>' +
                        "</tr>" );
						$( "#linking_" + value.datdoc).click(function() { 
							var paymov_op_cl = $("#paymov_op_cl"+nrow).val();
							var docref = value.datdoc.substring(0,4);
							docref += value.regiva;
							docref += value.seziva*1000000000+parseInt(value.protoc);
							updateSchedule(docref);
							updateCloseForm();
						});
						j++;
					   
               });
             },"json"
             );
    }

    function updateSchedule(docref) {
			$( "#pm_post_container_"+ nrow + " div" ).each(function(i,v) {
				var idv = $(v).attr('id').split('_');
				var id_sub = idv[2];
				var tesref = $('input[id=post_' + nrow + '_' + id_sub + '_id_tesdoc_ref]:first',v).focus().attr('value');
				if (tesref == ''){ // replace value only if row is empty
					$('input[id=post_' + nrow + '_' + id_sub + '_id_tesdoc_ref]:first',v).val(docref)
				}
			});
			updateTips("");
			return true;
	}
	
    function updateTips( t ) {
       tips.text( t ).addClass( "ui-state-highlight" );
       setTimeout(function() {
            tips.removeClass( "ui-state-highlight", 1500 );
       }, 500 );
    }
	
	function checkTot (n) {
		var accu = 0;
		$( "#pm_post_container_"+ nrow + " div" ).each(function(i,v) {
			var idv = $(v).attr('id').split('_');
			var id_sub = idv[2];
			accu += parseFloat($('input[id=post_' + nrow + '_' + id_sub + '_amount]:first',v).focus().attr('value'));
		});
		accu = accu.toFixed(2);
		if (accu == tot_amount) {
			return true; 
		} else  { 
			updateTips( "Errore !!! " + n + " ("+accu+" - "+tot_amount+")");
			return false;
		}
	}

	function checkField (open=true) {
		var bval=true;
		$( "#pm_post_container_"+ nrow + " div" ).each(function(i,v) {
			var idv = $(v).attr('id').split('_');
			var id_sub = idv[2];
			var tesref = $('input[id=post_' + nrow + '_' + id_sub + '_id_tesdoc_ref]:first',v).focus().attr('value');
			var am = parseFloat($('input[id=post_' + nrow + '_' + id_sub + '_amount]:first',v).focus().attr('value')).toFixed(2);
			var d = $('input[id=post_' + nrow + '_' + id_sub + '_expiry]:first',v).focus().attr('value').split("-");
			var day,month,year;
			day = d[0] - 0;
			month = d[1]-0;
			year = d[2] - 0;			
			if ( am < 0.00 ) {
				updateTips( "Errore !!! Un rigo importo non è stato valorizzato");
				bval=false;
			} else if (!(month > 0 && month < 13 && year > 2000 && year < 3000 && day > 0 && day <= (new Date(year, month, 0)).getDate()) && open ) { // la data è indispensabile solo in caso di apertura
				updateTips( "Errore !!! Un rigo scadenza non è stato valorizzato (gg-mm-aaaa)");
				bval=false;
			} else if (tesref=="") { // la partita di riferimento non è stata valorizzata
				updateTips( "Errore !!! Un rigo è senza partita di riferimento");
				bval=false;
			}
		});
		return bval;
    }
	
	function checkDate (o_date, n) {
		var d,day,month,year;
		var chk_date = o_date.val().toString().length;
	    d = o_date.val().toString().replace(/\//g,"-").split("-");
		day = d[0] - 0;
		month = d[1]-0;
		year = d[2] - 0;			
		if ( ( month > 0 && month < 13 && year > 2000 && year < 3000 && day > 0 && day <= (new Date(year, month, 0)).getDate() ) || chk_date== 0 ){
            o_date.removeClass();
			updateTips('');
			return true;
		} else {
            o_date.addClass( "ui-state-error" );
            updateTips( "Errore !!! "+n);
            return false;
		}
	}
	
    function checkAmount( o, n ) {
		var amou;
        amou = parseFloat(o.val().toString().replace(/\,/g,".")).toFixed(2);
        if ( amou < 0.00 ) {
            o.addClass( "ui-state-error" );
            updateTips( "Errore !!! " + n );
            return false;
       } else {
            o.removeClass();
			o.val(amou);
			updateTips('');
            return true;
       }
    }
    
    $( "#dialog_open"+nrow ).dialog({
      autoOpen: false,
      show: "scale",
      width: 820,
      modal: true,
	  position: "top",	  
	  open: function(){
			updateOpenForm(); 
			getSamePaymov(tesdoc_ref,id_rig);
			getOtherMov(clfoco,tesdoc_ref,false);
		},
      buttons: {
			"Conferma":function(){ $(this).dialog( "close" );}
		},
	  beforeClose:function(event,ui) { 
	      if (!checkField() || !checkTot('Il totale delle scadenze non coincide con il totale rigo')  ){
		    return false;
		  } 
		},
      close: function() {
			allFields.val( "" ).removeClass( "ui-state-error" );
			$( "#db-contain"+ nrow + " tbody").remove();
			$( "#db-contain"+ nrow ).append("<tbody></tbody>");
		}
    });

    $("#dialog_open"+nrow ).dialog( "open" );

    $( "#dialog_close"+nrow ).dialog({
      autoOpen: false,
      show: "scale",
      width: 820,
      modal: true,
	  position: "top",	  
	  open: function(){
			updateCloseForm(); 
			getSamePaymov(tesdoc_ref,id_rig,true);
			getOtherMov(clfoco,tesdoc_ref,true);
		},
      buttons: {
			"Conferma":function(){ $(this).dialog( "close" );}
		},
	  beforeClose:function(event,ui) { 
	      if (!checkField(false) || !checkTot('Il totale delle scadenze non coincide con il totale rigo')  ){
		    return false;
		  } 
		},
      close: function() {
			allFields.val( "" ).removeClass( "ui-state-error" );
			$( "#db-contain"+ nrow + " tbody").remove();
			$( "#db-contain"+ nrow ).append("<tbody></tbody>");
		}
    });

    $("#dialog_close"+nrow ).dialog( "open" );

    $("#addOpenExpiry"+nrow).click(function() {
			var id_btn = new Date().valueOf().toString();
			$( "#pm_post_container_"+ nrow ).append( '<div id="pm_post_' + id_btn + '">'+
				'<input type="hidden" id="post_' + nrow + '_' + id_btn + '_id_tesdoc_ref" name="paymov[' + nrow + '][' + id_btn + '][id_tesdoc_ref]" value="' + tesdoc_ref + '" />'+
				'<input type="hidden" id="post_' + nrow + '_' + id_btn + '_expiry" name="paymov[' + nrow + '][' + id_btn + '][expiry]" value="" />'+
				'<input type="hidden" id="post_' + nrow + '_' + id_btn + '_amount" name="paymov[' + nrow + '][' + id_btn + '][amount]" value="" />'+
				'</div>');
            $( "#pm_form_container_"+ nrow + " tbody" ).append( '<tr id="pm_form_'+id_btn+'"><td>' + tesdoc_ref +
					 '</td><td class="ui-widget-right ui-widget-content " ><input  id="form_' + nrow + '_' + id_btn + '_expiry" type="text" name="paymov[' + nrow + '][' + id_btn + '][expiry]" value="" /></td>' +
               '<td class="ui-widget-right ui-widget-content " ><input id="form_' + nrow + '_' + id_btn + '_amount" style="text-align:right;" type="text" name="paymov[' + nrow + '][' + id_btn + '][amount]" value="" /></td>' +
               '<td class="ui-widget-right ui-widget-content " ><button id="btn_' + id_btn + '"><img src="../../library/images/x.gif" /></button></td>' +
               "</tr>" );
			$('#form_' + nrow + '_' + id_btn + '_expiry' ).change(function(){
				if (checkDate($(this),"La data di Scadenza è sbagliata (gg-mm-aaaa)")) {
					$('#post_' + nrow + '_' + id_btn + '_expiry').val($(this).val());
				}
			});
			$('#form_' + nrow + '_' + id_btn + '_amount' ).change(function(){
				if (checkAmount($(this),"L'importo è sbagliato")) {
					$('#post_' + nrow + '_' + id_btn + '_amount').val($(this).val());
				}
			});
     		$( "#btn_"+id_btn ).click(function(){
				$("#pm_form_"+id_btn).remove();
				$("#pm_post_"+id_btn).remove();
				updateOpenForm();
			});
	}   
	);
	$("#addCloseExpiry"+nrow).click(function() {
			var id_btn = new Date().valueOf().toString();
			$( "#pm_post_container_"+ nrow ).append( '<div id="pm_post_' + id_btn + '">'+
				'<input type="hidden" id="post_' + nrow + '_' + id_btn + '_id" name="paymov[' + nrow + '][' + id_btn + '][id]" value="new" />'+
				'<input type="hidden" id="post_' + nrow + '_' + id_btn + '_id_tesdoc_ref" name="paymov[' + nrow + '][' + id_btn + '][id_tesdoc_ref]" value="' + tesdoc_ref + '" />'+
				'<input type="hidden" id="post_' + nrow + '_' + id_btn + '_expiry" name="paymov[' + nrow + '][' + id_btn + '][expiry]" value="" />'+
				'<input type="hidden" id="post_' + nrow + '_' + id_btn + '_amount" name="paymov[' + nrow + '][' + id_btn + '][amount]" value="" />'+
				'</div>');
            $( "#pm_form_container_"+ nrow + " tbody" ).append( '<tr id="pm_form_'+id_btn+'"><td><button id="unlink_' + id_btn + '"><img src="../../library/images/link_break.png" width="12" /></button> ' + tesdoc_ref +
					 '</td><td class="ui-widget-right ui-widget-content " ><input  id="form_' + nrow + '_' + id_btn + '_expiry" type="text" name="paymov[' + nrow + '][' + id_btn + '][expiry]" value="" /></td>' +
               '<td class="ui-widget-right ui-widget-content " ><input id="form_' + nrow + '_' + id_btn + '_amount" style="text-align:right;" type="text" name="paymov[' + nrow + '][' + id_btn + '][amount]" value="" /></td>' +
               '<td class="ui-widget-right ui-widget-content " ><button id="btn_' + id_btn + '"><img src="../../library/images/x.gif" /></button></td>' +
               "</tr>" );
			$('#form_' + nrow + '_' + id_btn + '_expiry' ).change(function(){
				if (checkDate($(this),"La data di Scadenza è sbagliata (gg-mm-aaaa)")) {
					$('#post_' + nrow + '_' + id_btn + '_expiry').val($(this).val());
				}
			});
			$('#form_' + nrow + '_' + id_btn + '_amount' ).change(function(){
				if (checkAmount($(this),"L'importo è sbagliato")) {
					$('#post_' + nrow + '_' + id_btn + '_amount').val($(this).val());
				}
			});
     		$( "#btn_"+id_btn ).click(function(){
				$("#pm_form_"+id_btn).remove();
				$("#pm_post_"+id_btn).remove();
				updateCloseForm();
			});
	}
	);

}