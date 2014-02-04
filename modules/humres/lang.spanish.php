<?php
/* $Id: lang.english.php,v 1.5 2010/02/25 15:21:54 devincen Exp $
 --------------------------------------------------------------------------
                            Gazie - Gestione Azienda
    Copyright (C) 2004-2010 - Antonio De Vincentiis Montesilvano (PE)
         (http://www.devincentiis.it)
                        <http://gazie.sourceforge.net>
	Spanish Translation by Dante Becerra Lagos softenglish@gmail.com
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

/*
 * Translated by: Antonio De Vincentiis
 * Revised by:
 */

$strScript = array ("report_letter.php" =>
                   array(  "Reporte de Cartas ",
                           "Fecha ",
                           "Numero ",
                           "Tipo ",
                           "Nombre de Empresa ",
                           "Objeto ",
                           "Escribir nueva carta"),
                    "admin_letter.php" =>
                   array(  'title' => " Carta ",
                           'mesg'=>array('La busqueda no arrojo resultados!',
                                         'Inserte al menos 2 caracteres!',
                                         'Cambiando cliente'
                                          ),
                           array("LET" => " Normal ","DIC" => "Declaracion","SOL" => " Solicitud "),
                           " de ",
                           " a las ",
                           " numero ",
                           "Objeto ",
                           " atencion para ",
                           "adjuntar la firma del usuario ",
                           "Tipo ",
                           "Cuerpo ",
                           "Nombre de usuario",
                           "La fecha no es correcta!",
                           "Debe seleccionar un cliente o un proveedor!"
                        )
                    );
?>