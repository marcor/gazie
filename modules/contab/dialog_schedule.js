 function dialogSchedule(paymov){
        clfoco = paymov.id.substring(6,15);
        nrow = paymov.id.substring(23);
        impo= document.getElementById("impoRC"+nrow).value.toString();
        id_exclud= document.getElementById("id_rig_rc"+nrow).value.toString();
        getResults(clfoco,id_exclud);
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
                           $( "#db-contain"+ nrow + " tbody").append( "<tr>" +
                              "<td" + ' class="ui-widget ui-widget-content " >' + value.descri + " n."
                              + value.numdoc + "/" + value.seziva + " del " + value.datdoc + "</td>" +
                              "<td" + ' class="ui-widget ui-widget-content " >' + value.expiry + "</td>" +
                              "<td" + ' class="ui-widget-right ui-widget-content " >' + value.amount + "</td>" +
                               '<td class="ui-widget-right ui-widget-content " >'+value.darave+'</td>' +
                               '<td class="ui-widget-right ui-widget-content "><A target="_new" href="admin_movcon.php?id_tes=' + value.id_tes + '&Update"><img src="../../library/images/new.png" width="12"/></A></td>' +
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

        function checkDate( o, n, min, max ) {
            var d = new Date();
            o.val(o.val().toString().replace(/\//g,"-"));
            if ( o.val().length > max || o.val().length < min ) {
                o.addClass( "ui-state-error" );
                updateTips( "La lunghezza della " + n + " dev'essere tra " + min + " e " + max + " caratteri!" );
                return false;
           } else {
                return true;
           }
        }

        function checkAmount( o, n, min, max ) {
            o.val(o.val().toString().replace(/\,/g,"."));
            if ( o.val().length > max || o.val().length < min ) {
                o.addClass( "ui-state-error" );
                updateTips( "La lunghezza della " + n + " dev'essere tra " + min + " e " + max + " caratteri!" );
                return false;
           } else {
                return true;
           }
        }

        function checkRegexp( o, regexp, n ) {
           if ( !( regexp.test( o.val() ) ) ) {
                o.addClass( "ui-state-error" );
                updateTips( n );
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
        $("#rerun").click(function() {
                var bValid = true;
                allFields.removeClass( "ui-state-error" );
                bValid = bValid && checkDate( expiry, "Scadenza", 10, 10 );
                bValid = bValid && checkAmount( amount, "Importo", 1, 12 );
                bValid = bValid && checkRegexp( expiry, /^\d{4}[-]\d{2}[-]\d{2}$/i, "La data di scadenza è errata inserire la data con questo formato: AAAA-MM-GG" );
                bValid = bValid && checkRegexp( amount, /^\d+(\.\d{1,2})?$/i, "Il valore dell'importo non è valido" );
                if ( bValid ) {     
                    $( "#openitem"+ nrow + " tbody" ).append( "<tr>" +
                       "<td></td>" +
                       "<td" + ' class="ui-widget ui-widget-content " >' + expiry.val() + "</td>" +
                       "<td" + ' class="ui-widget-right ui-widget-content " >' + amount.val() + "</td>" +
                       '<td class="ui-widget-right ui-widget-content "><button id="'+nrow+'"><img src="../../library/images/x.gif" /></button></td>' +
                       "</tr>" );
                    $("#paymov_dial"+nrow+" input" ).append("<input type='hidden' name='paymov[" + nrow + "][3][amount]' value='" + amount.val() + "' />");
                    $("#paymov_dial"+nrow+" input" ).append("<input type='hidden' name='paymov[" + nrow + "][3][expiry]' value='" + expiry.val() + "' />");
                    allFields.val( "" ).removeClass( "ui-state-error" );
                    updateTips( "" );
                }
        });
    }