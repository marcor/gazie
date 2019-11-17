<?php
/* ------------------------------------------------------------------------
  Aggiornamento quantità degli articoli da GAzie a Hikashop
  
   >>>>> i codici degli articoli di Hikashop devono essere identici ai codici degli articoli di GAzie o ai codici a barre <<<<<
  ------------------------------------------------------------------------
  @Author    Antonio Germani
  @Website   http://www.lacasettabio.it
  @Copyright Copyright (C) 2017 - 2018 Antonio Germani All Rights Reserved.
  versione 1.0
  ****
  Per modifiche o personalizzazioni contattarmi al 340-5011912 oppure lacasettabio@lacasettabio.it
  E' possibile gestire altri tipi di negozi online così come applicazioni per hotel o case vacanze
  ****
  ------------------------------------------------------------------------ */
  
/* impostazioni da fare prima di avviare il download da GAzie
inserire i dati dentro alle virgolette non toccare il resto */

/* se non ricordate i dati di configurazione del database aprite il file configuration.php presente nella root principale del sito Joomla. LEGGETE SOLO NON TOCCATE NULLA ALTRIMENTI BLOCCHERETE IL SITO !!!!!!*/

$host = "localhost"; /* host database, di solito localhost */
$user = "++"; /* nome utente database */
$pass = "++"; /* password database */
$database = "++"; /* nome database */
$hostname="++"; /*nome host per ftp, lo stesso inserito su GAzie */
$passftp="++"; /* password per ftp, la stessa inserita su GAzie */
$prefixdb="++"; /* il prefisso delle tabelle del database */

// ---------------------------------------------------------------------
/* da qui in poi non toccare più nulla!!! */

$access=$_GET['access'];
$password=base64_decode($access);

If ($password==$passftp){
/*apro connessione database su $linkID */
$linkID = new mysqli($host, $user, $pass, $database) or die("Could not connect to host."); 
	if (file_exists("prodotti.xml")) {
		echo "OK","<br>";
		$xml = simplexml_load_file('prodotti.xml');
		foreach($xml->attributes() as $a => $b) {
			if ($a == "Mode") {
				$mode=$b;
				}
			}
			foreach($xml->Products->Product as $product) {
				echo $product->Code," ",$product->AvailableQty,"<br>";
				if(!empty($product->Code) && isset ($product->AvailableQty)  ) {
					/* imposto il comando su $query */
					$query = "UPDATE ". $prefixdb ."_hikashop_product SET product_quantity = '". $product->AvailableQty ."' WHERE product_code = '". $product->Code . "' OR product_code = '". $product->BarCode ."'";	
					/* aggiorno database per questo singolo articolo */
					echo $query,"<br>";
					if (mysqli_query($linkID,$query)) {
						echo "Updating OK!",$product->Code,"<br>";
					} else {
					echo "Error updating record: " . mysqli_error($linkID),"<br>";
					}
					/* fine aggiornamente per questo singolo articolo */
				}
			}  
	} else {
		echo "Errore file articoli.xml non presente";
	}
} else {
	header ("HTTP/1.1 400 Bad request");
	echo "Utente o password non validi";
	exit;
}
?>
                                                                                                    