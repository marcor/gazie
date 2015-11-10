/** ENRICO FEDELE */
/*
  Se scegliamo bootstrap come framewors css (e credo che sia la cosa più opportuna dal momento che,
  pur con tutti i suoi difetti tra i quali una certa pesantezza/lentezza, ci consente di procedere spediti 
  potendo contare sul lavoro della community) allora tanto vale cercare di sfruttarlo quanto più possibile,
  per evitare di sovraccaricare il sistema, tenerlo snello e soprattutto facilitarci il compito.
  Allora per i tooltip possiamo pensare di utilizzare il plugin nativo di Bootstrap, per cui la versione di 
  Jquery-ui che utilizziamo attualmente, l'ultima disponibile al 09/11/2015, è compilata senza il plugin tooltip,
  che altrimenti genererebbe conflitti con quello di Bootstrap, dal momento che hanno lo stesso nome.
  
  Questa funzione è un tentativo di portare in un unico posto i tooltip di gazie, differenziandoli per contesto:
  product-thumb: tooltip per l'immagine di un prodotto
  weight: tooltip per il peso
  
  la magia si fa con:
  class="gazie-tooltip" (classe da assegnare all'elemento da dotare di tooltip)
  data-type="product-thumb/weight" (tipologia di tooltip, al momento solo immagine prodotto e peso)
  data-id="ID_PRODOTTO/PESO_PRODOTTO"  (id prodotto per l'immagine, peso altrimenti)
  data-title="TITOLO DA DARE" (è possibile passare una stringa di testo, al momento presa in considerazione solo per il peso)
*/
/** ENRICO FEDELE */
this.gazieTooltip = function(){

	$('.gazie-tooltip').tooltip(
		{html:true,
		 placement:'bottom',
		 title:function(){
			   var codeDtls = this.getAttribute('data-type');
				if(codeDtls=="product-thumb") {
				   codeDtls='<img src="../root/view.php?table=artico&value='+this.getAttribute('data-id')+'" onerror="this.src=\'../../library/images/link_break.png\'" alt="'+this.getAttribute('data-title')+'" />';
				   return codeDtls;
				} else if(codeDtls=="weight") {
				   codeDtls = this.getAttribute('data-title')+'&nbsp;'+this.getAttribute('data-id')+'kg';
				   return codeDtls;
				}
		}
	});
};
// starting the script on page load
$(document).ready(function(){
	gazieTooltip();
});