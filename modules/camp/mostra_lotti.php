<!DOCTYPE html>
<?php
/*
  --------------------------------------------------------------------------
  GAzie - Gestione Azienda
  Copyright (C) 2004-2017 - Antonio De Vincentiis Montesilvano (PE)
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
$lm = new lotmag;
$admin_aziend=checkAdmin();
$codice = filter_input(INPUT_GET, 'codice');
$lm -> getAvailableLots($codice,0);
require("../../library/include/header.php");
?>

<body>
<div align="center" class="FacetFormHeaderFont">Elenco lotti disponibili per <?php echo $codice; ?></div>
<table class="Tlarge table table-striped table-bordered table-condensed table-responsive">
    	<thead>
            <tr class="FacetDataTD">
                
                <th align="center" >Lotti disponibili
                    
                </th>
                <th align="center" >Certificati
                   
                </th>
               
            </tr>
			
			</thead>
			
			<?php
if (count($lm->available) > 0) { 
                    foreach ($lm->available as $v_lm) {
                            $img="";
                            echo '<tr class="FacetDataTD"><td class="FacetFieldCaptionTD">id: '
                            . $v_lm['id']
                            . ' - lotto: ' . $v_lm['identifier']
                            . ' - exp: ' . gaz_format_date($v_lm['expiry'])
							. ' - disponibilità: ' . gaz_format_quantity($v_lm['rest'], 0, $admin_aziend['decimal_quantity'])
                            .'</td><td>';
							
							If (file_exists('../../data/files/' . $admin_aziend['company_id'])>0) {		
								// recupero il filename dal filesystem e lo sposto sul tmp 
								$dh = opendir('../../data/files/' . $admin_aziend['company_id']);
								while (false !== ($filename = readdir($dh))) {
									$fd = pathinfo($filename); 
									$r = explode('_', $fd['filename']); 
									if ($r[0] == 'lotmag' && $r[1] == $v_lm['id']) {
									// riassegno il nome file 
										$img = $fd['basename'];
									} 
								}
							 
								if (strlen($img)>0) {
									echo '
									<img src="../../data/files/'. $admin_aziend['company_id'] .'/'.$img.'" alt="certificato lotto" width="50" border="1" style="cursor: -moz-zoom-in;" onclick="this.width=500;" ondblclick="this.width=50;" />
									';
									echo '
									<a class="btn btn-xs btn-default btn-elimina" href="../../data/files/'. $admin_aziend['company_id'] .'/'.$img.'" download>
									<i class="glyphicon glyphicon-download"></i>
									</a></td>
								';
								} else {
									echo '<i class="glyphicon glyphicon-eye-close"></i>';
								}
							}
                    } 
                } else {
                    echo '<div><button class="btn btn-xs btn-danger" type="image" >Non sono disponibili altri lotti, <br> oppure non è possibile cambiare lotto negli inserimenti multipli</button></div>';
                }
?></table>
</body>			

