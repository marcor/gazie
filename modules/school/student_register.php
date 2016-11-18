<?php
require("../../config/config/gconfig.php");
// include the config
require_once('./config.php');

// include the to-be-used language, english by default. feel free to translate your project and include something else
require("./lang.italian.php");

// include the PHPMailer library
//require_once('../../library/phpmailer/class.phpmailer.php');
require_once('libraries/PHPMailer.php');

// load the registration class
require_once('classes/Registration.php');

// create the registration object. when this object is created, it will do all registration stuff automatically
// so this single line handles the entire registration process.
$registration = new Registration();

// showing the register view (with the registration form, and messages/errors)
include("views/register.php");
