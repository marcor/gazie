<?php
/*
 --------------------------------------------------------------------------
                            GAzie - Gestione Azienda
    Copyright (C) 2004-2011 - Antonio De Vincentiis Montesilvano (PE)
                                (www.devincentiis.it)
                        <http://gazie.it>
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
    scriva   alla   Free  Software Foundation,  Inc.,   59
    Temple Place, Suite 330, Boston, MA 02111-1307 USA Stati Uniti.
 --------------------------------------------------------------------------
*/
$errors                  = Array();
$errors['is_align']      = "La base de datos est&aacute; alineada con la &uacute;ltima versi&oacute;n";
$errors['no_conn']       = "La conexi&oacute;n de base de datos no es correcta.<br />Establecer correctamente usuario, contrase&ntilde;a y el nombre de la base de datos <br />en el archivo config/config/gconfig.php";
$errors['no_data_files_writable']  = "El servidor no tiene el permiso (min. 666) para almacenar los documentos en el directorio /data/files";
$errors['no_tcpdf_cache_writable'] = "TCPDF no puede generar archivos PDF sin el permiso (min. 666) requiere el directorio /library/tcpdf/cache";

$msg                     = Array();
$msg['title']            = "Instalaci&oacute;n y actualizaci&oacute;n de la Base de Datos de GAzie";
$msg['install']          = "Instalaci&oacute;n";
$msg['upgrade']          = "Actualizaci&oacute;n";
$msg['error']            = "Error";
$msg['gi_install']       = "Instalaci&oacute;n de la Base de Datos de ";
$msg['gi_upgrade']       = "Actualizaci&oacute;n de la Base de Datos de ";
$msg['gi_upg_to']        = "a la";
$msg['gi_upg_from']      = "de la versi&oacute;n";
$msg['gi_lang']          = "Seleccionar el idioma";
$msg['gi_error']         = "";
$msg['gi_is_align']      = "Haga clic aqu&iacute; para Ingresar";
$msg['gi_usr_psw']       = "User = amministratore <br />Password = password";
?>