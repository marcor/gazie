<!DOCTYPE html>
<html lang="en">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="author" content="Antonio De Vincentiis http://www.devincentiis.it">
        <link rel="stylesheet" href="../../library/bootstrap/css/bootstrap.min.css" >
        <link rel="shortcut icon" href="../../library/images/favicon.ico">
        <link rel="stylesheet" type="text/css" href="../../library/style/base.css">
        <link rel="stylesheet" type="text/css" href="../../library/style/skins/base.css">
        <title>a scuola con GAzie - Login</title>

</head>
<body>

<?php
// show potential errors / feedback (from login object)
if (isset($login)) {
    if ($login->errors) {
        foreach ($login->errors as $error) {
            echo $error;
        }
    }
    if ($login->messages) {
        foreach ($login->messages as $message) {
            echo $message;
        }
    }
}
?>

<?php
// show potential errors / feedback (from registration object)
if (isset($registration)) {
    if ($registration->errors) {
        foreach ($registration->errors as $error) {
            echo $error;
        }
    }
    if ($registration->messages) {
        foreach ($registration->messages as $message) {
            echo $message;
        }
    }
}
?>
