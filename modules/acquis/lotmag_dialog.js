function lotmagDialog(ref) {
    var nrow = ref.id.substring(6, 15),
            tips = $(".validateTips");
    ;
    function updateTips(t) {
        tips.text(t).addClass("ui-state-highlight");
        setTimeout(function () {
            tips.removeClass("ui-state-highlight", 1500);
        }, 500);
    }
    function checkField(open) {
        var bval = true;
        var files = $('#' + nrow + '_file').prop("files");
        var fi = $.map(files, function (val) {
            return val.name;
        });
        var id = $("#" + nrow + "_identifier").val();
        if (fi == "") { // non è stato scelto il file
            updateTips("Errore! Non è stato selezionato un file ");
            bval = false;
        } else if (id == "") {// non è stata scritto il seriale, avverto che ne assegnerò uno interno 
            updateTips("Attenzione! Verrà asseganto un valore automatico al numero di serie/matricola/identificativo/targa del prodotto. ");
            bval = false;
            $("#" + nrow + "_identifier").val('#');
        }
        return bval;
    }

    $("#lm_dialog" + nrow).dialog({
        autoOpen: false,
        show: "scale",
        width: "80%",
        modal: true,
        open: function () {
            $('#' + nrow + '_identifier').change(function () {
                $('#lotmag_' + nrow + '_identifier').val($(this).val());
            });
            $('#' + nrow + '_identifier').change(function () {
                $('#lotmag_' + nrow + '_file').val($(this).val());
            });
            $('#' + nrow + '_expiry').change(function () {
                $('#lotmag_' + nrow + '_expiry').val($(this).val());
            });
            $('#' + nrow + '_expiry').datepicker({
                dateFormat: "dd-mm-yy"
            });
        },
        buttons: {
            "Conferma": function () {
                $(this).dialog("close");
            }
        },
        beforeClose: function (event, ui) {
            if (!checkField(true)) {
                return false;
            } else {
                updateTips("");
            }
        },
        close: function () {
        }
    });
    $("#lm_dialog" + nrow).dialog("open");
}
