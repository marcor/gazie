<?php

/*
  --------------------------------------------------------------------------
  GAzie - Gestione Azienda
  Copyright (C) 2004-2021 - Antonio De Vincentiis Montesilvano (PE)
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
require('../../library/include/datlib.inc.php');
$admin_aziend = checkAdmin();
if (isset($_GET['zn'])) {
    $user = gaz_dbi_get_row($gTables['admin'], 'user_name', $_SESSION['user_name']);
	$zn = substr($_GET['zn'], 0, 37);
	$send_fae_zip_package = gaz_dbi_get_row($gTables['company_config'], 'var', 'send_fae_zip_package')['val'];
	if (!empty($send_fae_zip_package)) {
		require('../../library/' . $send_fae_zip_package . '/SendFaE.php');
		$file_url = DATA_DIR.'files/' . $admin_aziend['codice'] . '/' . $zn;
		$IdentificativiSdI = SendFattureElettroniche($file_url);
		if (!empty($IdentificativiSdI)) {
			if (is_array($IdentificativiSdI)) {
				gaz_dbi_put_query($gTables['fae_flux'], "filename_zip_package = '" . $zn."'", "flux_status", "@@");
				foreach ($IdentificativiSdI as $filename_ori=>$IdentificativoSdI) {
					gaz_dbi_put_query($gTables['fae_flux'], "filename_ori = '" . $filename_ori."'", "id_SDI", $IdentificativoSdI);
				}
			} else {
				echo '<p>' . print_r($IdentificativiSdI, true) . '</p>';
			}
		}
		header('Location: report_fae_sdi.php?post_xml_result=OK');
	}
}
?>