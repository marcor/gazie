<?php
/*
  --------------------------------------------------------------------------
  GAzie - Gestione Azienda
  Copyright (C) 2004-2019 - Antonio De Vincentiis Montesilvano (PE)
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
require("../../library/include/datlib.inc.php");
require("../../modules/magazz/lib.function.php");
$admin_aziend = checkAdmin();
$upd_mm = new magazzForm;
$docOperat = $upd_mm->getOperators();
$message = "Sei sicuro di voler rimuovere ?";
if (!isset($_POST['ritorno'])) {
    $_POST['ritorno'] = $_SERVER['HTTP_REFERER'];
}

if (isset($_GET['id_tes'])) { //sto eliminando un singolo documento
    $result = gaz_dbi_dyn_query("*", $gTables['tesdoc'], "id_tes = " . intval($_GET['id_tes']));
    $row = gaz_dbi_fetch_array($result);
    if (substr($row['tipdoc'], 0, 2) == 'DD') {
        $rs_ultimo_documento = gaz_dbi_dyn_query("*", $gTables['tesdoc'], "YEAR(datemi) = '" . substr($row['datemi'], 0, 4) . "' AND tipdoc LIKE '" . substr($row['tipdoc'], 0, 2) . "_' AND seziva = " . $row['seziva'] . " ", "numdoc DESC", 0, 1);
    } elseif ($row['tipdoc'] == 'RDV') { 
        $rs_ultimo_documento = gaz_dbi_dyn_query("*", $gTables['tesdoc'], "id_tes = " . intval($_GET['id_tes']));
    } elseif ($row['tipdoc'] == 'VCO') {
        $rs_ultimo_documento = gaz_dbi_dyn_query("*", $gTables['tesdoc'], "datemi = '" . $row['datemi'] . "' AND tipdoc = 'VCO' AND seziva = " . $row['seziva'], "datemi DESC, numdoc DESC", 0, 1);
    } else {
        $rs_ultimo_documento = gaz_dbi_dyn_query("*", $gTables['tesdoc'], "YEAR(datemi) = '" . substr($row['datemi'], 0, 4) . "' AND tipdoc LIKE '" . substr($row['tipdoc'], 0, 1) . "%' AND seziva = " . $row['seziva'] . " ", "protoc DESC, numdoc DESC", 0, 1);
    }
} elseif (isset($_GET['anno']) and isset($_GET['seziva']) and isset($_GET['protoc'])) { //sto eliminando una fattura differita
    $result = gaz_dbi_dyn_query("*", $gTables['tesdoc'], "YEAR(datfat) = '" . intval($_GET['anno']) . "' AND seziva = '" . intval($_GET['seziva']) . "' AND protoc = '" . intval($_GET['protoc']) . "' AND tipdoc LIKE 'F__'");
    $row = gaz_dbi_fetch_array($result);
    $rs_ultimo_documento = gaz_dbi_dyn_query("*", $gTables['tesdoc'], "YEAR(datfat) = '" . substr($row['datfat'], 0, 4) . "' AND tipdoc LIKE '" . substr($row['tipdoc'], 0, 1) . "%' AND seziva = " . $row['seziva'] . " ", "protoc DESC, numdoc DESC", 0, 1);
} else { //non ci sono dati sufficenti per stabilire cosa eliminare
    header("Location: " . $_POST['ritorno']);
    exit;
}

if (!$row) {
    header("Location: " . $_POST['ritorno']);
    exit;
}

if (isset($_POST['Delete'])) {
    //controllo se sono stati emessi documenti nel frattempo...
    $ultimo_documento = gaz_dbi_fetch_array($rs_ultimo_documento);
    if ($ultimo_documento) {
        if (($ultimo_documento['tipdoc'] == 'VRI' || $ultimo_documento['tipdoc'] == 'VCO' 
            || substr($ultimo_documento['tipdoc'], 0, 2) == 'DD' || $ultimo_documento['tipdoc'] == 'RDV' || $ultimo_documento['tipdoc'] == 'CMR' ) 
            && $ultimo_documento['numdoc'] == $row['numdoc']) {
            gaz_dbi_del_row($gTables['tesdoc'], 'id_tes', $row['id_tes']);
            gaz_dbi_del_row($gTables['tesmov'], 'id_tes', $row['id_con']);
            gaz_dbi_del_row($gTables['rigmoc'], 'id_tes', $row['id_con']);
            gaz_dbi_del_row($gTables['rigmoi'], 'id_tes', $row['id_con']);
            gaz_dbi_put_query($gTables['rigbro'], 'id_doc = ' . $row["id_tes"], "id_doc", "");
            //cancello i righi
            $rs_righidel = gaz_dbi_dyn_query("*", $gTables['rigdoc'], "id_tes = '" . $row['id_tes'] . "'");
            while ($val_old_row = gaz_dbi_fetch_array($rs_righidel)) {
                if (intval($val_old_row['id_mag']) > 0) {  //se c'� stato un movimento di magazzino lo azzero
                    $upd_mm->uploadMag('DEL', $row['tipdoc'], '', '', '', '', '', '', '', '', '', '', $val_old_row['id_mag']);
					// se c'è stato, cancello pure il movimento sian 
					gaz_dbi_del_row($gTables['camp_mov_sian'], "id_movmag", $val_old_row['id_mag']);
                }
                gaz_dbi_del_row($gTables['rigdoc'], "id_rig", $val_old_row['id_rig']);
                gaz_dbi_del_row($gTables['body_text'], "table_name_ref = 'rigdoc' AND id_ref", $val_old_row['id_rig']);
            }
            // in caso di eliminazione di un reso da c/visione che quindi ha un link su un DDV
            if ($ultimo_documento['id_doc_ritorno'] > 0 ) {
                    gaz_dbi_put_row($gTables['tesdoc'], 'id_tes', $ultimo_documento['id_doc_ritorno'], 'id_doc_ritorno',0);
            }
            header("Location: " . $_POST['ritorno']);
            exit;
        } elseif ($ultimo_documento['protoc'] == $_GET['protoc'] and $ultimo_documento['tipdoc'] != 'FAD') {
            //allora procedo all'eliminazione della testata e dei righi...
            //cancello la testata
            gaz_dbi_del_row($gTables['tesdoc'], "id_tes", $row['id_tes']);
            gaz_dbi_del_row($gTables['tesmov'], 'id_tes', $row['id_con']);
            gaz_dbi_del_row($gTables['rigmoc'], 'id_tes', $row['id_con']);
            gaz_dbi_del_row($gTables['rigmoi'], 'id_tes', $row['id_con']);
            gaz_dbi_put_query($gTables['rigbro'], 'id_doc = ' . $row["id_tes"], "id_doc", "");
            // cancello pure l'eventuale movimento di split payment
            $r_split = gaz_dbi_get_row($gTables['tesmov'], 'id_doc', $row['id_tes']);
            gaz_dbi_del_row($gTables['tesmov'], 'id_tes', $r_split['id_tes']);
            gaz_dbi_del_row($gTables['rigmoc'], 'id_tes', $r_split['id_tes']);
            //cancello i righi
            $rs_righidel = gaz_dbi_dyn_query("*", $gTables['rigdoc'], "id_tes = '" . $row['id_tes'] . "'");
            while ($val_old_row = gaz_dbi_fetch_array($rs_righidel)) {
                if (intval($val_old_row['id_mag']) > 0) {  //se c'� stato un movimento di magazzino lo azzero
                    $upd_mm->uploadMag('DEL', $row['tipdoc'], '', '', '', '', '', '', '', '', '', '', $val_old_row['id_mag']);
					// se c'è stato, cancello pure il movimento sian 
					gaz_dbi_del_row($gTables['camp_mov_sian'], "id_movmag", $val_old_row['id_mag']);
                }
                gaz_dbi_del_row($gTables['rigdoc'], "id_rig", $val_old_row['id_rig']);
                gaz_dbi_del_row($gTables['body_text'], "table_name_ref = 'rigdoc' AND id_ref", $val_old_row['id_rig']);
            }
            header("Location: " . $_POST['ritorno']);
            exit;
        } elseif ($ultimo_documento['protoc'] == intval($_GET['protoc']) and $ultimo_documento['tipdoc'] == 'FAD') {
            //allora procedo alla modifica delle testate per ripristinare i DdT...
            if ( $row["ddt_type"]!="R") {
                gaz_dbi_put_row($gTables['tesdoc'], "id_tes", $row["id_tes"], "tipdoc", "DD" . $row["ddt_type"]);
            } else {
                gaz_dbi_put_row($gTables['tesdoc'], "id_tes", $row["id_tes"], "tipdoc", "CM" . $row["ddt_type"]);
            }
            gaz_dbi_put_row($gTables['tesdoc'], "id_tes", $row["id_tes"], "protoc", "");
            gaz_dbi_put_row($gTables['tesdoc'], "id_tes", $row["id_tes"], "numfat", "");
            gaz_dbi_put_row($gTables['tesdoc'], "id_tes", $row["id_tes"], "datfat", "");
            gaz_dbi_del_row($gTables['tesmov'], 'id_tes', $row['id_con']);
            gaz_dbi_del_row($gTables['rigmoc'], 'id_tes', $row['id_con']);
            gaz_dbi_del_row($gTables['rigmoi'], 'id_tes', $row['id_con']);
            while ($a_row = gaz_dbi_fetch_array($result)) {
                if ( $row["ddt_type"]!="R") {
                    gaz_dbi_put_row($gTables['tesdoc'], "id_tes", $a_row["id_tes"], "tipdoc", "DD" . $a_row["ddt_type"]);
                } else {
                    gaz_dbi_put_row($gTables['tesdoc'], "id_tes", $a_row["id_tes"], "tipdoc", "CM" . $a_row["ddt_type"]);
                }
                gaz_dbi_put_row($gTables['tesdoc'], "id_tes", $a_row["id_tes"], "protoc", "");
                gaz_dbi_put_row($gTables['tesdoc'], "id_tes", $a_row["id_tes"], "numfat", "");
                gaz_dbi_put_row($gTables['tesdoc'], "id_tes", $a_row["id_tes"], "datfat", "");
                gaz_dbi_del_row($gTables['tesmov'], 'id_tes', $row['id_con']);
                gaz_dbi_del_row($gTables['rigmoc'], 'id_tes', $row['id_con']);
                gaz_dbi_del_row($gTables['rigmoi'], 'id_tes', $row['id_con']);
                // cancello pure l'eventuale movimento di split payment
                $r_split = gaz_dbi_get_row($gTables['tesmov'], 'id_doc', $a_row['id_tes']);
                gaz_dbi_del_row($gTables['tesmov'], 'id_tes', $r_split['id_tes']);
                gaz_dbi_del_row($gTables['rigmoc'], 'id_tes', $r_split['id_tes']);
            }
            header("Location: " . $_POST['ritorno']);
            exit;
        } elseif ($ultimo_documento['protoc'] != $row["protoc"]) {
            $message = "Si sta tentando di eliminare un documento <br /> diverso dall'ultimo emesso!";
        }
    } else {
        $message = "Si sta tentando di eliminare un documento <br /> inesistente o contabilizzato!";
    }
}

if (isset($_POST['Return'])) {
    header("Location: " . $_POST['ritorno']);
    exit;
}
$numddt = gaz_dbi_num_rows($result);
$anagrafica = new Anagrafica();
$cliente = $anagrafica->getPartner($row['clfoco']);
$titolo = "Elimina l'Ultimo Documento di Vendita";
require("../../library/include/header.php");
$script_transl = HeadMain();
?>
<form method="POST">
    <input type="hidden" name="ritorno" value="<?php print $_POST['ritorno']; ?>">
    <div align="center"><font class="FacetFormHeaderFont">Attenzione!!! Stai eliminando il Documento n.<?php print $row['numdoc'] . "/" . $row['seziva'] . " dell'anno " . substr($row['datemi'], 0, 4); ?> </font></div>
    <table border="0" cellpadding="3" cellspacing="1" class="FacetFormTABLE" align="center">
        <!-- BEGIN Error -->
        <tr>
            <td colspan="2" class="FacetDataTDred">
                <?php
                if (!$message == "") {
                    print "$message";
                }
                ?>
            </td>
        </tr>
        <!-- END Error -->
        <tr>
            <td class="FacetFieldCaptionTD">Protocollo &nbsp;</td><td class="FacetDataTD"><?php print $row["protoc"] ?>&nbsp;</td>
        </tr>
        <tr>
            <td class="FacetFieldCaptionTD">Tipo documento &nbsp;</td><td class="FacetDataTD"><?php print $row["tipdoc"] ?>&nbsp;</td>
        </tr>
        <tr>
            <td class="FacetFieldCaptionTD">Numero Documento &nbsp;</td><td class="FacetDataTD"><?php print $row["numdoc"] ?>&nbsp;</td>
        </tr>
        <tr>
            <td class="FacetFieldCaptionTD">Cliente &nbsp;</td><td class="FacetDataTD"><?php print $cliente["ragso1"] ?>&nbsp;</td>
        </tr>
        <tr>
            <td class="FacetFieldCaptionTD">Num. di testate &nbsp;</td><td class="FacetDataTD"><?php print $numddt ?>&nbsp;</td>
        </tr>
        <tr>
            <td colspan="2" align="right">Se sei sicuro conferma l'eliminazione &nbsp;
                <!-- BEGIN Button Return --><input type="submit" name="Return" value="Indietro"><!-- END Button Return -->&nbsp;
                <!-- BEGIN Button Insert --><input type="submit" name="Delete" value="ELIMINA !"><!-- END Button Insert -->&nbsp;
            </td>
        </tr>
    </table>
</form>
<?php
require("../../library/include/footer.php");
?>