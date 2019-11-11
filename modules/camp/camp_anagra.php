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
  scriva   alla   Free  Software Foundation,  Inc.,   59
  Temple Place, Suite 330, Boston, MA 02111-1307 USA Stati Uniti.
  --------------------------------------------------------------------------
 */
  // IL REGISTRO DI CAMPAGNA E' UN MODULO DI ANTONIO GERMANI - MASSIGNANO AP
 // >> Gestione File upload anagrafica clienti/fornitori SIAN <<
 
require("../../library/include/datlib.inc.php");

$admin_aziend = checkAdmin();
$msg='';

if (!isset($_POST['ritorno'])) {
    $form['ritorno'] = $_SERVER['HTTP_REFERER'];
} else {
    $form['ritorno'] = $_POST['ritorno'];
}

if (isset($_POST['return'])) {
    header("Location: ".$form['ritorno']);
    exit;
}

// inizio controlli
	
// fine controlli

if (isset($_POST['create']) && $msg=='') {
    // creazione file anagrafica SIAN                                 
    
    
}	
	
require("../../library/include/header.php");
$script_transl = HeadMain();

// Antonio Germani - prendo tutti i clienti e fornitori che hanno un codice SIAN nella loro anagrafica
$cf=array();
$where="id_SIAN > 0 ";
        $what=$gTables['camp_anagra'].".trasmesso, ".
              $gTables['anagra'].".ragso1, ".$gTables['anagra'].".id_SIAN, ".
			  $gTables['anagra'].".indspe, ".$gTables['anagra'].".citspe, ".
			  $gTables['anagra'].".prospe ";
        $table=$gTables['anagra']." LEFT JOIN ".$gTables['camp_anagra']." ON (".$gTables['anagra'].".id = ".$gTables['camp_anagra'].".id_anagra)";
        $rs=gaz_dbi_dyn_query ($what,$table,$where, 'id ASC');
        while ($r = gaz_dbi_fetch_array($rs)) {
            $cf[] = $r;
        }       
    
?>
<form method="POST" name="select">
<input type="hidden" value="<?php echo $form['hidden_req']; ?>" name="hidden_req"/>
<input type="hidden" value="<?php echo $form['ritorno']; ?>" name="ritorno"/>
<div class="panel panel-default gaz-table-form">
    <div class="container-fluid">
		<div class="row">
			<div class="col-sm-12" align="center"><b>File upload dell'anagrafica fornitori e clienti SIAN</b>
				<p align="justify">
				Il sistema del SIAN non permette di effettuare l'aggiornamento dell'anagrafica fornitori tramite il file di upload.
				Pertanto, per la modifica di quelli già inseriti nel portale dell'olio SIAN, è necessario avvalersi delle funzioni online del portale stesso.
				Il sistema SIAN, se rileva che il soggetto è già presente, lo scarta mentre acquisisce gli eventuali restanti record.
			</p></div>
		</div>
		<?php 
		$n=0;
		foreach ($cf as $row){			
			?>
			<div class="row">
				<div class="col-sm-1 bg-warning">
				<?php echo $row['id_SIAN']; ?>
				</div>
				<div class="col-sm-3 bg-success">
				<?php echo $row['ragso1']; ?>
				</div>
				<div class="col-sm-3 bg-warning">
				<?php echo $row['indspe']; ?>
				</div>
				<div class="col-sm-3 bg-success">
				<?php echo $row['citspe']; ?>
				</div>
				<div class="col-sm-1 bg-warning">
				<?php echo $row['prospe']; ?>
				</div>
				<div class="col-sm-1 bg-success">
				<?php if ($row['trasmesso']>0){ ?>
					<span class="glyphicon glyphicon-ban-circle text-danger" title="Già trasmesso"></span>
				<?php } else { ?>
					<input type="checkbox" name="download <?php echo $n; ?>" value="download"/>
				<?php } ?>
				</div>	
			</div>
			<?php
			$n++;
		}?>
		
		
		<div class="row">
			<div class="col-sm-6 bg-success">
			<input type="submit" name="return" value="<?php echo $script_transl['return']; ?>"/>
			</div>
			<div class="col-sm-6 bg-success">
			<input type="submit" name="create" value="CREA file"/>
			</div>
		</div>
		
		
		
		
	</div>
</div>


    <?php
require("../../library/include/footer.php");
?>
