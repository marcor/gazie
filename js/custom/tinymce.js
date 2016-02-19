tinymce.init({
  selector: '.mceClass',
  height: '40',
  theme: 'modern',
  language: "it",
  plugins: [
    'advlist autolink lists link image charmap print preview hr anchor pagebreak',
    'searchreplace wordcount visualblocks visualchars code fullscreen',
    'insertdatetime media nonbreaking save table contextmenu directionality',
    'emoticons template textcolor colorpicker textpattern paste '/*imagetools*/
  ],
  toolbar1: 'insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image | print preview media | forecolor backcolor emoticons',
  image_advtab: true,
  /*templates: [
    { title: 'Test template 1', content: 'Test 1' },
    { title: 'Test template 2', content: 'Test 2' }
  ],*//*
  imagetools_cors_hosts: ['www.tinymce.com', 'codepen.io'],
  content_css: '//www.tinymce.com/css/codepen.min.css'*/
 });