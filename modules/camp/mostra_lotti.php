
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
>>>>>> Antonio Germani -- MOSTRA Lotti  <<<<<<
 */

require("../../library/include/datlib.inc.php");
$lm = new lotmag;
$admin_aziend=checkAdmin();
$codice = filter_input(INPUT_GET, 'codice');
$lm -> getAvailableLots($codice,0);
require("../../library/include/header.php"); 

if (isset($_POST['close'])){
	foreach (glob("../../modules/camp/tmp/*") as $fn) {// prima cancello eventuali precedenti file temporanei
             unlink($fn);
    } // poi chiudo la finestra e esco
	echo "<script>window.close();</script>";exit;
}
?>

<body>
<div align="center" class="FacetFormHeaderFont">Elenco lotti disponibili per <?php echo $codice; ?></div>
<table class="Tlarge table table-striped table-bordered table-condensed table-responsive">
    	<thead>
            <tr class="FacetDataTD">
				<th align="center" >Id lotto   
                </th>
                <th align="center" >Numero lotto   
                </th>
				<th align="center" >Scadenza				
                </th>
				<th align="center" >Disponibilità   
                </th>
                <th align="center" >Certificato   
                </th>              
            </tr>
			</thead>
<?php
	foreach (glob("../../modules/camp/tmp/*") as $fn) {// prima cancello eventuali precedenti file temporanei
             unlink($fn);
    } 
	
	if (count($lm->available) > 0) { 
        foreach ($lm->available as $v_lm) {
               $img="";
               echo '<tr class="FacetDataTD"><td class="FacetFieldCaptionTD">'
               . $v_lm['id']
               . '</td><td>' . $v_lm['identifier']
               . '</td><td>' . gaz_format_date($v_lm['expiry'])
				. '</td><td>' . gaz_format_quantity($v_lm['rest'], 0, $admin_aziend['decimal_quantity'])
                .'</td><td>';
							
				If (file_exists('../../data/files/' . $admin_aziend['company_id'])>0) {		
					// recupero il filename 
					$dh = opendir('../../data/files/' . $admin_aziend['company_id']);
					while (false !== ($filename = readdir($dh))) {
						$fd = pathinfo($filename); 
						$r = explode('_', $fd['filename']); 
						if ($r[0] == 'lotmag' && $r[1] == $v_lm['id']) {
							// assegno il nome file a img
							$img = $fd['basename'];
							} 
						}
						if (strlen($img)>0) {
							$tmp_file = "../../data/files/".$admin_aziend['company_id']."/".$img;
							// sposto nella cartella di lettura il relativo file temporaneo            
							copy($tmp_file, "../../modules/camp/tmp/".$img);
							echo '<img src="../../modules/camp/tmp/'.$img.'" alt="certificato lotto" width="50" border="1" style="cursor: -moz-zoom-in;" onclick="this.width=500;" ondblclick="this.width=50;" />';
							echo '<a class="btn btn-xs btn-default btn-elimina" href="../../modules/camp/tmp/'.$img.'" download><i class="glyphicon glyphicon-download"></i></a></td>';
							} else {
									echo '<i class="glyphicon glyphicon-eye-close"></i>';
								} 
				}
            } 
        } else {
				echo '<div><button class="btn btn-xs btn-danger" type="image" >Non sono disponibili altri lotti, <br> oppure non è possibile cambiare lotto negli inserimenti multipli</button></div>';
            }
?>
	</table>
	</body>	
	<form method="post" name="closewindow">  
	<input type="submit" title="elimina file temporanei e chiudi finestra" name="close" value="X"  style="float:right">
	</form>	