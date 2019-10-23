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
 // IL REGISTRO DI CAMPAGNA E' UN MODULO DI ANTONIO GERMANI - MASSIGNANO AP
// >> Creazione del file .txt di upload per il SIAN <<
require("../../library/include/datlib.inc.php");
require ("../../modules/magazz/lib.function.php");
if (!ini_get('safe_mode')){ //se me lo posso permettere...
    ini_set('memory_limit','128M');
    gaz_set_time_limit (0);
}

if ($handle = opendir('../../data/files/1/sian/')){
   while (false !== ($file = readdir($handle))){
       $prevfiles[]=$file;
   }
   closedir($handle);
}

$admin_aziend=checkAdmin();
$id_sian = gaz_dbi_get_row($gTables['company_config'], 'var', 'id_sian');

if (!isset ($id_sian) or intval($id_sian['val']==0)){ 
echo "errore manca id sian";
die;}
function getMovements($date_ini,$date_fin)
    {
        global $gTables,$admin_aziend;
        $m=array();
        $where="datdoc BETWEEN $date_ini AND $date_fin";
        $what=$gTables['movmag'].".*, ".
              $gTables['camp_mov_sian'].".*, ".
			  $gTables['artico'].".SIAN, ".
			  $gTables['anagra'].".ragso1, ".$gTables['anagra'].".id_SIAN, ".
			  $gTables['clfoco'].".id_anagra, ".
			  $gTables['rigdoc'].".id_tes, ".
			  $gTables['tesdoc'].".numdoc, ".
			  $gTables['lotmag'].".identifier, ".
			  $gTables['camp_artico'].".* ";
        $table=$gTables['movmag']." LEFT JOIN ".$gTables['camp_mov_sian']." ON (".$gTables['movmag'].".id_mov = ".$gTables['camp_mov_sian'].".id_movmag)
               LEFT JOIN ".$gTables['clfoco']." ON (".$gTables['movmag'].".clfoco = ".$gTables['clfoco'].".codice)
			   LEFT JOIN ".$gTables['camp_artico']." ON (".$gTables['movmag'].".artico = ".$gTables['camp_artico'].".codice)
               LEFT JOIN ".$gTables['artico']." ON (".$gTables['movmag'].".artico = ".$gTables['artico'].".codice)
			   LEFT JOIN ".$gTables['rigdoc']." ON (".$gTables['movmag'].".id_rif = ".$gTables['rigdoc'].".id_rig)
			   LEFT JOIN ".$gTables['tesdoc']." ON (".$gTables['rigdoc'].".id_tes = ".$gTables['tesdoc'].".id_tes)
			   LEFT JOIN ".$gTables['lotmag']." ON (".$gTables['lotmag'].".id = ".$gTables['movmag'].".id_lotmag)
			   LEFT JOIN ".$gTables['anagra']." ON (".$gTables['anagra'].".id = ".$gTables['clfoco'].".id_anagra)";
        $rs=gaz_dbi_dyn_query ($what,$table,$where, 'datreg ASC, tipdoc ASC, clfoco ASC, operat DESC, id_mov ASC');
        while ($r = gaz_dbi_fetch_array($rs)) {
            $m[] = $r;
        }
        return $m;
    }

$type_array=array(); 
// $type_zero è la stringa formattata SIAN vuota *** NON TOCCARE MAI!!! ***
$type_zero="                ;0000000000;0000000000;        ;          ;        ;          ;0000000000;0000000000;0000000000000;0000000000000;          ;          ;0000000000;00;00;00;                                                                                ;00;                                                                                ;0000000000000;0000000000000;0000000000000;0000000000000;0000000000000;0000000000000;0000000000000;                    ;                                                                                                                                                                                                                                                                                                            ; ; ; ; ; ; ; ; ; ; ; ;                 ;                 ;0000;          ;          ;             ;        ;          ; ;";

$giori = substr($_GET['ri'],0,2);
$mesri = substr($_GET['ri'],2,2);
$annri = substr($_GET['ri'],4,4);
$utsri= mktime(0,0,0,$mesri,$giori,$annri);
$giorf = substr($_GET['rf'],0,2);
$mesrf = substr($_GET['rf'],2,2);
$annrf = substr($_GET['rf'],4,4);
$utsrf= mktime(0,0,0,$mesrf,$giorf,$annrf);

$giosta = substr($_GET['ds'],0,2);
$messta = substr($_GET['ds'],2,2);
$annsta = substr($_GET['ds'],4,4);

$progr=0;
$datsta=$annsta.$messta.$giosta;
$datrf=$annrf.$mesrf.$giorf;

foreach ($prevfiles as $files){
	$f=explode("_",$files);
	if (isset($f[1])){ 
		if ($f[1]==$datsta){
			if($f[1]>$progr){
				$progr=$f[2];
			}
		}
	}
}
$progr++;
	
// Antonio Germani - creo il nome del file
$namefile=$admin_aziend['codfis']."_".$datsta."_".sprintf ("%05d",$progr)."_OPERREGI.txt";

$result=getMovements(strftime("%Y%m%d",$utsri),strftime("%Y%m%d",$utsrf));

if (sizeof($result) > 0) { // se ci sono movimenti creo il file
	$myfile = fopen("../../data/files/1/sian/".$namefile, "w") or die("Unable to open file!");
	$nprog=1;$lastdatdoc="";
	while (list($key, $row) = each($result)) {
		$type_array= explode (";", $type_zero); // azzero il type array per ogni movimento da creare
		if ($row['SIAN']>0) {
			if ( $_GET['ud']==str_replace("-", "", $row['datdoc'])) {
					// escludo i movimenti già inseriti null'ultimo file con stessa data
			} else { 
					if ($lastdatdoc==$row['datdoc']){ // se il movimento ha la stessa data del precedente aumento il progressivo
						$nprog++;
					} else {
						$nprog=1;
					}
					if (isset($row['datdoc'])) { //se c'è la data documento, imposto la data operazione come GGMMAAA
						$gio = substr($row['datdoc'],8,2);
						$mes = substr($row['datdoc'],5,2);
						$ann = substr($row['datdoc'],0,4);
						$dd=$gio.$mes.$ann;// data operazione
						$datdoc=$gio.$mes.$ann;// data documento nel formato GGMMAAA
					} else { // altrimenti la data operazione è quella di registrazione movimento
						$gio = substr($row['datreg'],8,2);
						$mes = substr($row['datreg'],5,2);
						$ann = substr($row['datreg'],0,4);
						$dd=$gio.$mes.$ann;
					}
					if (intval($row['numdoc'])==0){ // se il numero documento è zero tolgo lo zero e annullo l'eventuale data documento
						$row['numdoc']="";$row['datdoc']="";
					}  
					// >> Antonio Germani - caso produzione da orderman
					if (intval($row['id_orderman'])>0 AND $row['operat']==1){ // se è una produzione e il movimento è di entrata
						// cerco il movimento di scarico connesso
						$row2=gaz_dbi_get_row($gTables['camp_mov_sian'], 'id_movmag', $row['id_mov'], "AND id_mov_sian_rif = '0'");
						$row3=gaz_dbi_get_row($gTables['camp_mov_sian'], 'id_mov_sian_rif', $row2['id_mov_sian']);
						$row4=gaz_dbi_get_row($gTables['movmag'], 'id_mov', $row3['id_movmag']);
						$row5=gaz_dbi_get_row($gTables['camp_artico'], 'codice', $row4['artico']);
						$row4['quanti'] = sprintf ("%013d", str_replace(".", "", $row4['quanti'])); // tolgo il separatore decimali perché il SIAN non lo vuole. le ultime tre cifre sono sempre decimali. Aggiungo zeri iniziali.
						$quantilitri=number_format($row['quanti']*$row['confezione'],3);// trasformo le confezioni in litri
						$quantilitri = str_replace(".", "", $quantilitri); // tolgo il separatore decimali perché il SIAN non lo vuole. le ultime tre cifre sono sempre decimali. Aggiungo zeri iniziali.
						if ($row5['estrazione']=1){ 
							$type_array[31]="X"; // Flag prima spremitura a freddo a fine operazione
						}
						if ($row5['estrazione']=2){ 
							$type_array[33]="X"; // Flag estratto a freddo a fine operazione
						}
						if ($row5['biologico']=1){
							$type_array[35]="X"; // Flag biologico a fine operazione
						}
						if ($row5['biologico']=2){
							$type_array[37]="X"; // Flag in conversione a fine operazione
						}
						if ($row5['etichetta']=0){
							$type_array[39]="X"; // Flag NON etichettato a fine operazione
						}
						If ($row['cod_operazione']==1){// Confezionamento con etichettatura
							$type_array[6]=str_pad("L", 10); // codice operazione
							$type_array[23]=sprintf ("%013d",$row4['quanti']); // quantità scarico olio sfuso
							$type_array[24]=sprintf ("%013d",$quantilitri); // quantità carico olio confezionato in litri
							$type_array[18]=sprintf ("%02d",$row5['or_macro']); // Codice Origine olio per macro area a fine operazione
							$type_array[19]=str_pad($row5['or_spec'], 80); // Descrizione Origine olio specifica a fine operazione
													
						}
						If ($row['cod_operazione']==2){// Confezionamento senza etichettatura
							$type_array[6]=str_pad("L1", 10); // codice operazione
							$type_array[23]=sprintf ("%013d",$row4['quanti']); // quantità scarico olio sfuso
							$type_array[24]=sprintf ("%013d",$quantilitri); // quantità carico olio confezionato in litri
							$type_array[18]=sprintf ("%02d",$row5['or_macro']); // Codice Origine olio per macro area a fine operazione
							$type_array[19]=str_pad($row5['or_spec'], 80); // Descrizione Origine olio specifica a fine operazione
							$type_array[39]="X"; // Flag NON etichettato a fine operazione
						}
						If ($row['cod_operazione']==3){// Etichettatura
							$type_array[6]=str_pad("L2", 10); // codice operazione
						}
						If ($row['cod_operazione']==4){// Svuotamento di olio confezionato
							$type_array[6]=str_pad("X", 10); // codice operazione
							$type_array[18]=sprintf ("%02d",$row5['or_macro']); // Codice Origine olio per macro area a fine operazione
							$type_array[19]=str_pad($row5['or_spec'], 80); // Descrizione Origine olio specifica a fine operazione
							$type_array[15]=sprintf ("%02d",$row5['categoria']);// categoria olio fine operazione
						}
					}
					if (intval($row['id_orderman'])>0 AND $row['operat']==-1 AND $row['cod_operazione']<>"S7"){ // se è uno scarico di produzione
						continue; // escludo il movimento dal ciclo di creazione file perché le uscite di produzione di olio vengono lavorate insieme alle entrate
					} 
					if (intval($row['id_orderman'])>0 AND $row['operat']==-1 AND $row['cod_operazione']=="S7") {// è un'uscita di olio per produrre altro
						$type_array[6]=str_pad("S7", 10); // codice operazione > S7 scarico di olio destinato ad altri usi
						if ($row['SIAN']==1){ // se è olio
							if ($row['confezione']==0) { // se è sfuso
								$type_array[23]=sprintf ("%013d", str_replace(".", "", $row['quanti']));
							} else { // se è confezionato
								$quantilitri=number_format($row['quanti']*$row['confezione'],3);// trasformo le confezioni in litri
								$quantilitri = str_replace(".", "", $quantilitri);
								$type_array[25]=sprintf ("%013d",$quantilitri);
							}
						} else { //se sono olive
							$type_array[10]=sprintf ("%013d", str_replace(".", "", $row['quanti']));
						}
					}
					
					// >> Antonio Germani - Caso carico da acquisti e magazzino
					
					if ($row['operat']==1 AND intval($row['id_orderman'])==0){ //se è un carico NON connesso a produzione
						$type_array[6]=str_pad("C".$row['cod_operazione'], 10); // codice operazione
						if ($row['SIAN']==1){ // se è olio
							if ($row['confezione']==0) { // se è sfuso
								$type_array[22]=sprintf ("%013d", str_replace(".", "", $row['quanti']));
							} else { // se è confezionato
								$quantilitri=number_format($row['quanti']*$row['confezione'],3);// trasformo le confezioni in litri
								$quantilitri = str_replace(".", "", $quantilitri);
								$type_array[24]=sprintf ("%013d",$quantilitri);
							}
						} else { //se sono olive
							$type_array[9]=sprintf ("%013d", str_replace(".", "", $row['quanti']));
						}
						if ($row['cod_operazione']==3 OR $row['cod_operazione']==8 ){
							$type_array[7]=sprintf ("%010d",$row['id_SIAN']); // identificatore fornitore/cliente/terzista
						} 
						if ($row['cod_operazione']==0 OR $row['cod_operazione']==1 OR $row['cod_operazione']==2) {
							$type_array[8]=sprintf ("%010d",$row['id_SIAN']); // identificatore fornitore/cliente/terzista/committente
						}
						if ($row['cod_operazione']==5) {
							$type_array[13]=sprintf ("%010d",$row['id_SIAN']); // identificativo stabilimento di provenienza/destinazione olio
						}
					}
					
					// >> Antonio Germani - Caso scarico da vendite e magazzino
					if ($row['operat']==-1 AND intval($row['id_orderman'])==0){ // se è uno scarico NON connesso a produzione
						$type_array[6]=str_pad("S".$row['cod_operazione'], 10); // codice operazione
						if ($row['SIAN']==1){ // se è olio
							if ($row['confezione']==0) { // se è sfuso
								$type_array[23]=sprintf ("%013d", str_replace(".", "", $row['quanti']));
							} else { // se è confezionato
								$quantilitri=number_format($row['quanti']*$row['confezione'],3);// trasformo le confezioni in litri
								$quantilitri = str_replace(".", "", $quantilitri);
								$type_array[25]=sprintf ("%013d",$quantilitri);
							}
						} else { //se sono olive
							$type_array[10]=sprintf ("%013d", str_replace(".", "", $row['quanti']));
						}
						if ($row['cod_operazione']==1 OR $row['cod_operazione']==2 OR $row['cod_operazione']==3 OR $row['cod_operazione']==10){
							$type_array[8]=sprintf ("%010d",$row['id_SIAN']); // identificatore fornitore/cliente/terzista/committente
						}
						if ($row['cod_operazione']==5 OR $row['cod_operazione']==6) {
							$type_array[7]=sprintf ("%010d",$row['id_SIAN']); // identificatore fornitore/cliente/terzista
						}
						if ($row['cod_operazione']==4) {
							$type_array[13]=sprintf ("%010d",$row['id_SIAN']); // identificativo stabilimento di provenienza/destinazione olio
						}
					}
					
					// Antonio Germani - campi comuni a tutti i casi
					$type_array[0]=str_pad($admin_aziend['codfis'], 16); // aggiunge spazi finali
					$type_array[1]=sprintf ("%010d",$id_sian['val']); // identificativo stabilimento/deposito
					$type_array[2]=sprintf ("%010d",$nprog); // num. progressivo
					$type_array[3]=str_pad($dd, 8);//data dell'operazione
					$type_array[4]=str_pad($row['numdoc'], 10);// numero documento giustificativo
					$type_array[5]=str_pad($datdoc, 8);//data del documento giustificativo								
					$type_array[11]=str_pad($row['recip_stocc'], 10); // identificativo recipiente o silos di stoccaggio
					$type_array[12]=str_pad($row['recip_stocc_destin'], 10); // identificativo recipiente o silos di stoccaggio destinazione
					$type_array[14]=sprintf ("%02d",$row['categoria']); // Categoria olio
					$type_array[16]=sprintf ("%02d",$row['or_macro']); // Codice Origine olio per macro area
					$type_array[17]=str_pad($row['or_spec'], 80); // Descrizione Origine olio specifica
					$type_array[27]=str_pad($row['identifier'], 20); // Lotto di appartenenza
					if ($row['estrazione']==1){
						$type_array[30]="X"; // Flag prima spremitura a freddo
					}
					if ($row['estrazione']==2){
						$type_array[32]="X"; // Flag estratto a freddo
					}
					if ($row['biologico']==1){
						$type_array[34]="X"; // Flag biologico
					}
					if ($row['biologico']==2){
						$type_array[36]="X"; // Flag in conversione
					}
					if ($row['etichetta']==0){
						$type_array[38]="X"; // Flag NON etichettato
					}
					if ($row['confezione']>0){
						$type_array[45]=sprintf ("%013d", str_replace(".", "", $row['confezione'])); // capacità confezione
					}
					$type_array[48]="I";
					$type= implode(";",$type_array);
					fwrite($myfile, $type);
					$lastdatdoc=$row['datdoc'];
			}
		}
	}
	fclose($myfile);
}

require("../../library/include/header.php");
$script_transl=HeadMain(0,array('calendarpopup/CalendarPopup'));

echo "file generato e salvato: ",$namefile," ";
$namefile=substr($namefile,0,-4)
?>

<a href="../camp/getfilesian.php?filename=<?php echo $namefile;?>&ext=txt&company_id=1">
<i class="glyphicon glyphicon-file" title="Scarica il file appena generato"></i>
</a>
<?php
require("../../library/include/footer.php");
?>