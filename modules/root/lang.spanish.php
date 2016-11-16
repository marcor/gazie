<?php

/*
  --------------------------------------------------------------------------
  GAzie - Gestione Azienda
  Copyright (C) 2004-2016 - Antonio De Vincentiis Montesilvano (PE)
  (http://www.devincentiis.it)
  <http://gazie.sourceforge.net>
  --------------------------------------------------------------------------
  Questo programma e` free software;   e` lecito redistribuirlo  e/o
  modificarlo secondo i  termini della Licenza Pubblica Generica GNU
  come e` pubblicata dalla Free Software Foundation; o la versione 2
  della licenza o (a propria scelta) una versione successiva.

  Questo programma  e` distribuito nella speranza  che sia utile, ma
  SENZA   ALCUNA GARANZIA; senza  neppure  la  garanzia implicita di
  NEGOZIABILITA` o di  APPLICABILITA` PER UN  PARTICOLARE SCOPO.  Si
  veda la Licenza Pubblica Generica GNU per avere maggiori dettagli.

  Ognuno dovrebbe avere   ricevuto una copia  della Licenza Pubblica
  Generica GNU insieme a   questo programma; in caso  contrario,  si
  scriva   alla   Free  Software Foundation, 51 Franklin Street,
  Fifth Floor Boston, MA 02110-1335 USA Stati Uniti.
  --------------------------------------------------------------------------
 */

/*
  -- TRANSLATED BY : Dante Becerra Lagos (softenglish@gmail.com)
 */

$strScript = array("admin.php" =>
    array('morning' => "Buenos dias",
        'afternoon' => "Buenas tardes",
        'evening' => "Buenas tardes",
        'night' => "Buenas noches",
        'errors' => array(' Es necesario ajustar su versi&oacute;n de base de datos ',
            ' a version ',
            '  hacer clic AQUI ',
            ' Recordar que para el buen funcionamiento de la aplicacion la directiva magic_quotes_gpc debe estar en Off en el archivo php.ini!',
            ' Attenzione il backup esterno risale a più di 10 giorni, fallo adesso '
        ),
        'access' => ", este es su acceso numero",
        'pass' => "<br />Ultima actualizacion de clave : ",
        'logout' => "Si desea salir haga clic en el boton",
        'company' => " Ud esta administrando la empresa:<br /> ",
        'mesg_co' => array('La busqueda no dio resultados!', 'Inserte al menos 2 caracteres!', 'Cambiar empresa'),
        'upd_company' => "Cambiar datos de la empresa",
        'business' => "para la edministracion de negocios.",
        'proj' => "Administrador de Proyecto: ",
        'devel' => "Desarrollo, documentacion, reporte de errores: ",
        'change_usr' => "Cambiar sus datos",
        'auth' => "Sitio Web del Autor",
        'strBottom' => array(
            array('href' => "http://www.kernel.org/",
                'img' => "linux.gif",
                'title' => "Linux (kernel)"),
            array('href' => "http://www.apache.org",
                'img' => "apache.gif",
                'title' => "Apache the Web Server more used in the world!"),
            array('href' => "http://dev.mysql.com/downloads/",
                'img' => "mysqldbms.gif",
                'title' => "This is MySQL official web site. The database inside which GAzie memorizes hits data!"),
            array('href' => "http://www.php.net",
                'img' => "phppower.gif",
                'title' => "Go to PHP official web site, the language for Dynamic Web!"),
            array('href' => "http://sourceforge.net/projects/tcpdf/",
                'img' => "tcpdf.jpg",
                'title' => "You find TCPDF here, the PHP class FPDF based used to produce the GAzie's documents!"),
            array('href' => "https://jquery.com/",
                'img' => "jquery.png",
                'title' => "La libreria javascript per il web"),
            array('href' => "http://getbootstrap.com/",
                'img' => "bootstrap.png",
                'title' => "Bootstrap, front end web library"),
            array('href' => "http://www.mozilla.org/products/firefox/all.html",
                'img' => "firefox.gif",
                'title' => "Download FIREFOX, the browser GAzie has been tested with!")
        )
    ),
    "login_admin.php" =>
    array(/* 0 */ " La nueva contrase&ntilde;a debe tener un largo de al menos ",
        /* 1 */ " caracteres,<BR> diferente de la antigua e igual a la de la confirmaci&oacute;n!<br>",
        /* 2 */ " ha tenido acceso a Gazie <br> pero su contrase&ntilde;a ha caducado, debe insertar una nueva!<br>",
        /* 3 */ " Usuario y/o contrase&ntilde;a incorrecta!<br>",
        /* 4 */ " Acceso denegado a este m&oacute;dulo!",
        /* 5 */ " Nueva contrase&ntilde;a",
        /* 6 */ " Confirmacion de nueva contrase&ntilde;a",
        'log' => "El acceso al sistema localiza en:",
        'welcome' => "Bienvenida a GAzie",
        'intro' => "la planificación de recursos empresariales que le permite realizar un seguimiento de las cuentas, la documentación, ventas, compras, almacenes y más, para muchas empresas al mismo tiempo.",
        'usr_psw' => "Introduzca su nombre de usuario y la contraseña que se le ha asignado para comenzar:",
        'ins_psw' => "Introduzca contraseña",
        'label_conf_psw' => "Confirmacion de nueva contrase&ntilde;a",
        'conf_psw' => "Escriba la contraseña otra vez",
        'label_new_psw' => "Nueva contrase&ntilde;a",
        'new_psw' => "Introduzca nueva contraseña",
        'student'=>'Si usted es un estudiante puede iniciar sesión desde aquí'
        ));
$errors = array(
    'access' => 'Usted no tiene derecho de acceso al módulo'
);
?>