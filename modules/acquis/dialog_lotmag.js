function dialogLotmag(lotmag) {
    var nrow = lotmag.id.substring(6,15),
		id_rig = $( "#id_purchase"+nrow ).val(),
		lot_or_serial = $("#post_"+nrow+"_lot_or_serial").val();
    var description = $( "#description" ),
        expiry = $( "#expiry" ),
        id_doc = $( "#id_doc" ),
        allFields = $( [] ).add( description ).add( expiry ).add( id_doc ),
        tips   = $( ".validateTips" );

		//disegno la prima riga della table (con i titoli delle colonne e il tasto +)
    function updateOpenFormOnStart() {
		$( "#lm_form_container_"+ nrow + " tbody tr" ).remove();
		$( "#lm_form_container_"+ nrow + " tbody" ).replaceWith('<tbody> <tr id="lm_header_'+ nrow + '" def="nopost" >' +
			'<td class="ui-widget ui-widget-content " >ID lotto </td>' +
			'<td class="ui-widget ui-widget-content " >Scadenza</td>' +
			'<td class="ui-widget-right ui-widget-content ">Importo</td>' + 
			'<td class="ui-widget-right ui-widget-content "><button id="addOpenExpiry'+ nrow + '" value="' + nrow +'" class="btn btn-xs btn-default">'+
			'<i class="glyphicon glyphicon-plus"></i></button></td></tr></tbody>');
    }
    
    function updateOpenForm() {
    	var lotmag_op_cl = $("#lotmag_op_cl"+nrow).val();	    	

    	$( "#lm_form_container_"+ nrow + " tbody tr:not(:first)").remove();

		$( "#lm_post_container_"+ nrow + " div" ).each(function(i,v) {
				var idv = $(v).attr('id').split('_');
				var id_sub = idv[2];
				var id = $('input[id=post_' + nrow + '_' + id_sub + '_id_tesdoc_ref]:first',v).focus().attr('value');
				var ex = $('input[id=post_' + nrow + '_' + id_sub + '_expiry]:first',v).focus().attr('value');
				var am = $('input[id=post_' + nrow + '_' + id_sub + '_id_doc]:first',v).focus().attr('value');
			    updateOpenFormRiga(id_sub, id, ex, am);
		});
	}
    
    function updateOpenFormRiga(id_sub, id, ex, am) {
		$( "#lm_form_container_"+ nrow + " tbody" ).append( '<tr id="lm_form_'+id_sub+'">'+
			'<td>' + id +'</td>'+
			'<td class="ui-widget-right ui-widget-content " ><input id="form_' + nrow + '_' + id_sub + '_expiry" type="text" name="lotmag[' + nrow + '][' + id_sub + '][expiry]" value="' + ex + '" id="post_' + nrow + '_' + id_sub + '_expiry" /></td>' +
			'<td class="ui-widget-right ui-widget-content " ><input id="form_' + nrow + '_' + id_sub + '_id_doc" style="text-align:right;" type="text" name="lotmag[' + nrow + '][' + id_sub + '][id_doc]" value="' + am + '" id="post_' + nrow + '_' + id_sub + '_id_doc" /></td>' +
			'<td class="ui-widget-right ui-widget-content " ><button id="btn_' + id_sub + '"><img src="../../library/images/x.gif" /></button></td>' +
			"</tr>" );
		
		$('#form_' + nrow + '_' + id_sub + '_expiry' ).change(function(){
			if (checkDate($(this),"La data di Scadenza è sbagliata (gg-mm-aaaa)")) {
				$('#post_' + nrow + '_' + id_sub + '_expiry').val($(this).val());
			}
		});

		$('#form_' + nrow + '_' + id_sub + '_expiry').datepicker( {
			  dateFormat: "dd-mm-yy"
		});
		
		$('#form_' + nrow + '_' + id_sub + '_id_doc' ).change(function(){
			if (checkid_doc($(this),"L'importo è sbagliato")) {
				$('#post_' + nrow + '_' + id_sub + '_id_doc').val($(this).val());
			}
		});
		
		$( "#btn_"+id_sub ).click(function() { 
			$("#lm_form_"+id_sub ).remove();
			$("#lm_post_"+id_sub ).remove();
			updateOpenForm();
		});
	}

    function getSameLotmag(tes_ref,excl_val,link) {
       $.get("lotmag_data.php",
             {id_tesdoc_ref:tes_ref, id_exc:excl_val},
             function(data) {
			    var j=0;
			    
                $.each(data, function(i,value){
					if(j==0){
						if (link){
							link_ref = '<button id="linking_same_'+ j +'" class="btn btn-xs btn-default"><i class="glyphicon glyphicon-link"></i></button>';
						};
						$( "#db-contain" + nrow + " tbody").append( "<tr>" +
						"<td class='ui-widget-content ui-state-active' colspan=7" + ' class="ui-widget ui-widget-content " > Altri movimenti della stessa lotto ' + tes_ref + ' ' + link_ref + "</td></tr>");
					}					

                    $( "#db-contain" + nrow + " tbody").append( "<tr>" +
                    "<td" + ' class="ui-widget ui-widget-content " ></td>' +
                    "<td" + ' class="ui-widget ui-widget-content " > '+ value.description + " n." + value.numdoc + "/" + value.seziva + " del " + value.datdoc + "</td>" +
                    "<td" + ' class="ui-widget ui-widget-content " >' + value.expiry + "</td>" +
                    "<td" + ' class="ui-widget-right ui-widget-content " >' + value.id_doc + "</td>" +
                     '<td class="ui-widget-right ui-widget-content " >'+value.darave+'</td>' +
                     '<td class="ui-widget-right ui-widget-content "><a target="NEW" href="admin_movcon.php?id_tes=' + value.id_tes + '&Update" class="btn btn-xs btn-default"><i class="glyphicon glyphicon-new-window"></i></a></td>' +
                     "</tr>" );
                    
                    $( "#linking_same_" + j).click(function() {
							var lotmag_op_cl = $("#lotmag_op_cl"+nrow).val();
							var docref = value.datdoc.substring(0,4);
							docref += value.regiva;
							docref += value.seziva*1000000000+parseInt(value.protoc);
							updateLotmag(docref);
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
						link_ref = '<button id="linking_'+j+'" class="btn btn-xs btn-default"><i class="glyphicon glyphicon-link"></i></button>';
					};
					
                    $( "#db-contain" + nrow + " tbody").append( "<tr>" +
                       '<td class="ui-widget-right ui-widget-content ">' + link_ref + '</td>' +
                       "<td" + ' class="ui-widget ui-widget-content " > '+ value.description + " n." +
                       value.numdoc + "/" + value.seziva + " del " + value.datdoc + "</td>" +
                       "<td" + ' class="ui-widget ui-widget-content " >' + value.expiry + "</td>" +
                       "<td" + ' class="ui-widget-right ui-widget-content " >' + value.id_doc + "</td>" +
                        '<td class="ui-widget-right ui-widget-content " >'+value.darave+'</td>' +
                        '<td class="ui-widget-right ui-widget-content "><a target="NEW" href="admin_movcon.php?id_tes=' + value.id_tes + '&Update" class="btn btn-xs btn-default"><i class="glyphicon glyphicon-new-window"></i></a></td>' +
                        "</tr>" );
                    
						$( "#linking_" + j).click(function() { 
							var lotmag_op_cl = $("#lotmag_op_cl"+nrow).val();
							var docref = value.datdoc.substring(0,4);
							docref += value.regiva;
							docref += value.seziva*1000000000+parseInt(value.protoc);
							updateLotmag(docref);
						});
						
						j++;
					   
               });
             },"json"
             );
    }

    function updateLotmag(docref) {
    	
			$( "#lm_post_container_"+ nrow + " div" ).each(function(i,v) {
				var idv = $(v).attr('id').split('_');
				var id_sub = idv[2];
				var tesref = $('input[id=post_' + nrow + '_' + id_sub + '_id_tesdoc_ref]:first',v).focus().attr('value');
				if (tesref == '') // replace value only if row is empty
					$('input[id=post_' + nrow + '_' + id_sub + '_id_tesdoc_ref]:first',v).val(docref);
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
		$( "#lm_post_container_"+ nrow + " div" ).each(function(i,v) {
			var idv = $(v).attr('id').split('_');
			var id_sub = idv[2];
			accu += parseFloat($('input[id=post_' + nrow + '_' + id_sub + '_id_doc]:first',v).focus().attr('value'));
		});
		accu = accu.toFixed(2);
		if (accu == tot_id_doc) {
			return true; 
		} else  { 
			$('#impoRC' + nrow ).val(accu);
			$('#impoRC' + nrow).trigger('change');						
			return true;
		}
	}

	function checkField (open) {
		var bval=true;
		$( "#lm_post_container_"+ nrow + " div" ).each(function(i,v) {
			var idv = $(v).attr('id').split('_');
			var id_sub = idv[2];
			var tesref = $('input[id=post_' + nrow + '_' + id_sub + '_id_tesdoc_ref]:first',v).focus().attr('value');
			var am = parseFloat($('input[id=post_' + nrow + '_' + id_sub + '_id_doc]:first',v).focus().attr('value')).toFixed(2);
			var d = $('input[id=post_' + nrow + '_' + id_sub + '_expiry]:first',v).focus().attr('value').split("-");
			var day,month,year;
			day   = d[0]-0;
			month = d[1]-0;
			year  = d[2]-0;			
			if ( am < 0.00 ) {
				updateTips( "Errore ! Un rigo importo non è stato valorizzato");
				bval=false;
			} else if (!(month > 0 && month < 13 && year > 2000 && year < 3000 && day > 0 && day <= (new Date(year, month, 0)).getDate()) && open ) { // la data è indispensabile solo in caso di apertura
				updateTips( "Errore ! Un rigo scadenza non è stato valorizzato (gg-mm-aaaa)");
				bval=false;
			} else if (tesref=="") { // la lotto di riferimento non è stata valorizzata
				updateTips( "Errore ! Un rigo è senza lotto di riferimento");
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
            updateTips( "Errore ! "+n);
            return false;
		}
	}
	
    function checkid_doc( o, n ) {
		var amou;
        amou = parseFloat(o.val().toString().replace(/\,/g,".")).toFixed(2);
        if ( amou < 0.00 ) {
            o.addClass( "ui-state-error" );
            updateTips( "Errore ! " + n );
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
		  	updateOpenFormOnStart(); 
		  	updateOpenForm();
		  	//getSameLotmag(tesdoc_ref,id_rig,false);
		  	//getOtherMov(clfoco,tesdoc_ref,false);
		},
      buttons: {
			"Conferma":function(){	$(this).dialog( "close" );}
		},
	  beforeClose:function(event,ui) {
	      if (!checkField(true) || !checkTot('Il totale delle scadenze non coincide con il totale rigo')  ){
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

}
