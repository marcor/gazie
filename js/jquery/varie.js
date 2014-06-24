$(function() {
$('.paper').click(function(e){
    $("#nome_file").val($(e.target).text()); // using jQuery
})
});