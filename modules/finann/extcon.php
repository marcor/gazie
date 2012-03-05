<?php
/*
--------------------------------------------------------------------------
                            GAzie - Gestione Azienda
    Copyright (C) 2004-2012 - Antonio De Vincentiis Montesilvano (PE)
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
require("../../library/include/datlib.inc.php");
$admin_aziend=checkAdmin();
require("../../library/include/header.php");
$script_transl = HeadMain();
//
// Anno predefinito.
//
$okdat    = 0;
$year     = date("Y")-1;
$eB7__ind = 0;
$eB7__amm = 0;
$eB7__com = 0;
//
// Array per la lettura dei dati extragestione
// dalla tabella extges.
//
$extra;
//
// Salva i dati POST recepiti.
//
if (isset ($_POST["okdat"])    && is_numeric $_POST["okdat"])    $okdat    = $_POST["okdat"];
if (isset ($_POST["year"])     && is_numeric $_POST["year"])     $year     = $_POST["year"];
if (isset ($_POST["eB7__ind"]) && is_numeric $_POST["eB7__ind"]) $eB7__ind = $_POST["eB7__ind"];
if (isset ($_POST["eB7__amm"]) && is_numeric $_POST["eB7__amm"]) $eB7__amm = $_POST["eB7__amm"];
if (isset ($_POST["eB7__com"]) && is_numeric $_POST["eB7__com"]) $eB7__com = $_POST["eB7__com"];
//
// Cerca di leggere i dati relativi all'anno selezionato.
//
$query  = "SELECT * FROM " . $gTables['extges'] . " WHERE year = \"$year\"";
$result = gaz_dbi_query ($query);
$nrows  = gaz_dbi_num_rows ($result);
//
// Se l'anno non c'è, aggiunge una riga vuota e la rilegge.
//
if ($nrows == 0)
  {
    $query  = "INSERT INTO " . $gTables['extges'] . " (`year`) VALUES ($year)";
    $result = gaz_dbi_query ($query);
    $query  = "SELECT * FROM " . $gTables['extges'] . " WHERE year = \"$year\"";
    $result = gaz_dbi_query ($query);
  }
//
// Se le variabili contengono dei dati, li memorizza nella tabella, altrimenti,
// li preleva dalla tabella.
//
if ($okdat)
  {
    //
    // Ci sono dati da memorizzare nella tabella.
    //
    $query  = "UPDATE " . $gTables['extges'] . " "
            . "SET "
              . "cos_serv_ind = $eB7__ind, "
              . "cos_serv_amm = $eB7__amm, "
              . "cos_serv_com = $eB7__com "
            . "WHERE "
              . "year = \"$year\"";
    $result = gaz_dbi_query ($query);
  }
else
  {
    $extra = gaz_dbi_fetch_array ($result);
    $eB7__ind = $extra['cos_serv_ind'];
    $eB7__amm = $extra['cos_serv_amm'];
    $eB7__com = $extra['cos_serv_com'];
  }
//
// Produce il form.
//
echo "<form method=\"POST\">";
echo "<p><strong>Informazioni extragestionali per la rivalutazione di bilancio</strong></p>";
echo "<table>";
echo "<tr>";
echo "<th>voce civilistica</th>";
echo "<th>quota costi industriali</th>";
echo "<th>quota costi amministrativi</th>";
echo "<th>quota costi commerciali</th>";
echo "</tr>";
echo "<tr>";
echo "<td>c.e. B7) costi per servizi</td>";
echo "<td><input type=\"text\" name=\"eB7__ind\" size=\"14\" value=\"".$eB7__ind."\"></td>";
echo "<td><input type=\"text\" name=\"eB7__amm\" size=\"14\" value=\"".$eB7__amm."\"></td>";
echo "<td><input type=\"text\" name=\"eB7__com\" size=\"14\" value=\"".$eB7__com."\"></td>";
echo "</tr>";
echo "</table>";
echo "<input type=\"hidden\" name=\"okdat\" value=\"1\">";
echo "<input type=\"submit\" name=\"aggiorna\" value=\"aggiorna\">";
echo "</form>";

echo "</body>";
echo "</html>";
?>
