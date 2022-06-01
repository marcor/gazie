<?php
/*
   --------------------------------------------------------------------------
  GAzie - MODULO 'VACATION RENTAL'
  Copyright (C) 2022-20223 - Antonio Germani, Massignano (AP)
  (http://www.programmisitiweb.lacasettabio.it)
  Ogni diritto è riservato.
  E' possibile usare questo modulo solo dietro autorizzazione dell'autore
  --------------------------------------------------------------------------
 */
?>
<!DOCTYPE html>
<html lang='en'>
  <head>
    <meta charset='utf-8' />
    <link href='fullcalendar-5.10.2/lib/main.css' rel='stylesheet' />
    <script src='fullcalendar-5.10.2/lib/main.js'></script>
	<style>
		.overlay{			
			position: fixed;
			width: 100%;
			height: 100%;
			top: 0;
			left: 0;
			z-index: 999;
			background: rgba(255,255,255,0.8) url("spinner.gif") center no-repeat;
		}
	</style>
	<!-- questo style insieme a 'display' => 'background' inviato da load db from event e inviando il title '' crea il calendario per il frontend
	<style>
	.fc-bg-event {
		  background-color: red !important;
		  opacity: 1 !important;
	}
	</style>
	-->
<?php
//require("../../library/include/datlib.inc.php");
$id=substr($_GET['house_code'],0,32);
?>

<script>

  document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    var calendar = new FullCalendar.Calendar(calendarEl, {
		height: 600,
		initialView: 'dayGridMonth',
		selectable: true, //abilita il controllo del passaggio mouse cliccato sopra i giorni
		headerToolbar:{
		left:'prev,next today,dayGridMonth',
		center:'title',
		right:'prevYear,nextYear'
		},
		editable: true,
		eventColor: '#378006',
		timeZone: 'local',
		locale: 'it',
		eventDisplay  : 'block',// tutti gli eventi vengono mostrati con un rettangolo pieno in visualizzazione giornaliera
		events : 'load_from_db_events.php?id=<?php echo $id; ?>',

		loading: function( isLoading, view ) {
			if(isLoading) {// isLoading gives boolean value
				calendarEl.classList.add("overlay");
			} else {
				calendarEl.classList.remove("overlay");
			}
		},

/* ***** L'EVENTO, AD ESEMPIO DI UN GIORNO, COMINCIA ALLE ORE 00:00 DEL GIORNO DI INIZIO E FINISCE ALLE ORE 00:00 DEL GIORNO DOPO (SONO DUE DATE DIFFERENTI MA SONO 24 ORE E QUINDI VIENE MOSTRATO PIENO SOLO UN GIORNO) ***** */

		// se c'è select, dateClick non serve!!! LO TENGO COME PROMEMORIA DI CODICE
		/*
			dateClick: function(info) {
				alert('Clicked on: ' + info.dateStr);
				alert('Coordinates: ' + info.jsEvent.pageX + ',' + info.jsEvent.pageY);
				alert('Current view: ' + info.view.type);
				alert('Test get dayEl: ' + info.dayEl);
				// change the day's background color just for fun
				info.dayEl.style.backgroundColor = 'red';
				var title = prompt("Enter Event Title dateclick");
				var start = info.dateStr;
				var end = info.dateStr;
				var xhttp = new XMLHttpRequest();
				xhttp.open("GET", "save_to_db_events.php?title="+ title +"&start="+ start +"&end="+ end +"&house_code="+<?php echo $id; ?>, true);
				xhttp.send();
				calendar.refetchEvents();
				window.location.reload(true);
				//calendar.refetchEvents();
			},
		*/

		select: function(info) {// seleziona più giorni passando sopra con il mouse cliccato
			/*
			alert('Clicked on: ' + info.startStr);
			alert('Clicked to: ' + info.endStr);
			*/
			var title = prompt("Enter Event Title select");
			var start = info.startStr;
			var end = info.endStr;
			var xhttp = new XMLHttpRequest();
		
			xhttp.open("GET", "save_to_db_events.php?title="+ title +"&start="+ start +"&end="+ end +"&house_code=<?php echo $id; ?>", false);
				
			xhttp.onreadystatechange = function() {    
				console.log(this);		   
			};					
			xhttp.send();
			calendar.refetchEvents();
			window.location.reload(true);
		},

		eventDrop:function(info){
			 var start = info.event.start;
			 var end = info.event.end;
			 var title = info.event.title;
			 var id = info.event.id;
			// alert ("update title:"+ title);
			//alert ("update_db_events.php?title="+ title +"&start="+ start.toISOString() +"&end="+ end.toISOString() +"&id="+ id);
			 if (end == null){// nel caso di evento di un solo giorno
				 var end = start;
			 }
			 var xhttp = new XMLHttpRequest();
			 xhttp.open("GET", "update_db_events.php?title="+ title +"&start="+ start.toISOString() +"&end="+ end.toISOString() +"&id="+ id +"&house_code=<?php echo $id; ?>", true);
			xhttp.send();
			calendar.refetchEvents();
			window.location.reload(true);
		},

		eventResize:function(info){
			 var start = info.event.start;
			 var end = info.event.end;
			 var title = info.event.title;
			 var id = info.event.id;
			//alert ("update_isostring=start="+ start.toISOString() +"&end="+ end.toISOString() +"&id="+ id);
			//alert ("update_normal=start="+ start +"&end="+ end +"&id="+ id);
			 var xhttp = new XMLHttpRequest();
			 xhttp.open("GET", "update_db_events.php?title="+ title +"&start="+ start.toISOString() +"&end="+ end.toISOString() +"&id="+ id +"&house_code=<?php echo $id; ?>", true);
			xhttp.send();
			calendar.refetchEvents();
			window.location.reload(true);
		},

		eventMouseEnter: function (info) {
			//alert(info.event.title);
			 document.getElementById("tooltip").innerHTML = this.responseText;
		},

		eventClick: function(info) {
			//alert('Event: ' + info.event.title);
			//alert('Coordinates: ' + info.jsEvent.pageX + ',' + info.jsEvent.pageY);
			//alert('View: ' + info.view.type);
			if(confirm("Se sicuro di voler cancellare? Le prenotazioni dirette non verranno comunque cancellate.")){
				var id = info.event.id;
				//alert('Test get id: ' + id);
				var xhttp = new XMLHttpRequest();
				xhttp.open("GET", "delete_db_events.php?id="+ id, true);
				xhttp.send();
				calendar.refetchEvents();
				window.location.reload(true);
			}
			/* per fare una modifica bisogna usare un pop up ma c'è il problema che bisogna aggiornare il calendario dopo aver chiuso il popup
			let newWindow = open('/', 'example', 'width=300,height=300');
				if(newWindow.closed){
				alert(newWindow.closed); // true
				}
			*/
		}

    });
    calendar.render();
  });
</script>
  </head>
  <body>
    <div id='calendar'>
      <form method="GET">
        <div style="display:none" id="tooltip" title="Tooltip">
          <p><b>tooltip bla:</b></p>
          <p>Codice</p>
          <p class="ui-state-highlight" id="idcodice"></p>
          <p>Descrizione</p>
          <p class="ui-state-highlight" id="iddescri"></p>
        </div>
      </form>
    </div>
  </body>
 </html>