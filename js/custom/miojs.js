function toast(msg) {
    $().toastmessage('showToast', {
        text: msg,
        sticky: false,
        position: 'center',
        type: 'notice',
        closeText: '',
        close: function () {
            console.log("toast is closed ...");
        }
    });
}
function cambiaDestinazione(valoreSelezionato) {
//    if(valoreSelezionato.value!=""){
        document.getElementsByName("destin")[0].value=valoreSelezionato.value;
//    }
}
