<?php
/* $Id: lang.english.php,v 1.5 2010/02/25 15:21:54 devincen Exp $
 --------------------------------------------------------------------------
                            Gazie - Gestione Azienda
    Copyright (C) 2004-2010 - Antonio De Vincentiis Montesilvano (PE)
                                (www.devincentiis.it)
                        <http://gazie.sourceforge.net>
	Spanish Translation by Dante Becerra Lagos softenglish@gmail.com
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

/*
 * Translated by: Antonio De Vincentiis
 * Revised by:
 */

$strScript = array ("admin_staff.php" =>
                   array(  'title'=>'Gestione del personale',
                           'ins_this'=>'Inserisci un collaboratore',
                           'upd_this'=>'Modifica  dati del collaboratore ',
                           'mesg'=>array('La ricerca non ha dato risultati!',
                                         'Inserire almeno 2 caratteri!',
                                         'Cambia anagrafica'),
                           'errors'=>array('&Egrave; necessario indicare la Ragione Sociale',
                                           '&Egrave; necessario indicare l\'indirizzo',
                                           'Il codice di avviamento postale (CAP) &egrave; sbagliato',
                                           '&Egrave; necessario indicare la citt&agrave;',
                                           '&Egrave; necessario indicare la provincia',
                                           '&Egrave; necessario indicare il sesso',
                                           'L\'IBAN non &egrave; corretto',
                                           'L\'IBAN e la nazione sono diversi',
                                           'Codice fiscale sbagliato per una persona fisica',
                                           'La partita IVA &egrave; formalmente errata!',
                                           'Esiste gi&agrave un Cliente con la stessa Partita IVA',
                                           'Il codice fiscale &egrave; formalmente errato',
                                           'Esiste gi&agrave; un Cliente con lo stesso Codice Fiscale',
                                           'C.F. mancante! In automatico &egrave; stato<br />impostato con lo stesso valore della Partita IVA!',
                                           'E\' una persona fisica, inserire il codice fiscale',
                                           'Esiste una anagrafica con la stessa partita IVA',
                                           'Esiste una anagrafica con lo stesso Codice Fiscale',
                                           '&Egrave; necessario scegliere la modalit&agrave; di pagamento',
                                           'Il codice del cliente &egrave; gi&agrave; esistente, riprova l\'inserimento con quello proposto (aumentato di 1)',
                                           'La data di nascita &egrave; sbagliata',
                                           'Indirizzo email formalmente sbagliato'
                                          ),
                           'link_anagra'=>' Clicca sotto per inserire l\'anagrafica esistente sul piano dei conti',
                           'codice'=>"Codice ",
                           'ragso1'=>"Cognome",
                           'ragso2'=>"Nome",
                           'sedleg'=>"Sede legale",
                           'luonas'=>'Luogo di nascita',
                           'datnas'=>'Data di Nascita',
                           'pronas'=>'Provincia di nascita',
                           'counas'=>'Nazione di Nascita',
                           'legrap'=>"Legale rappresentante ",
                           'sexper'=>"Sesso/pers.giuridica ",
                           'sexper_value'=>array(''=>'-','M'=>'Maschio','F'=>'Femmina','G'=>'Giuridica'),
                           'indspe'=>'Indirizzo',
                           'capspe'=>'Codice Postale',
                           'citspe'=>'Citt&agrave; - Provincia',
                           'prospe'=>'Provincia',
                           'country'=>'Nazione',
                           'telefo'=>'Telefono',
                           'fax'=>'Fax',
                           'cell'=>'Cellulare',
                           'codfis'=>'Codice Fiscale',
                           'pariva'=>'Partita I.V.A.',
                           'e_mail'=>'e mail',
                           'id_agente'=>'Agente',
                           'codpag'=>'Modalit&agrave; di pagamento *',
                           'sconto'=>'% Sconto da apllicare',
                           'banapp'=>'Banca d\'appoggio',
                           'portos'=>'Porto - Resa',
                           'spediz'=>'Spedizione',
                           'imball'=>'Imballo',
                           'listin'=>'Listino da applicare',
                           'id_des'=>'Destinazione &rArr; da anagrafica',
                           'destin'=>'Destinazione &rArr; descrizione libera',
                           'iban'=>'IBAN',
                           'maxrat'=>'Massimo importo delle rate',
                           'ragdoc'=>'Raggruppamento documenti',
                           'addbol'=>'Addebito spese bolli',
                           'speban'=>'Addebito spese bancarie',
                           'spefat'=>'Addebito spese di fatturazione',
                           'stapre'=>'Stampa prezzi su D.d.T.',
                           'op_type'=>'Tipologia operazioni',
                           'op_type_value'=>array(1=>'Cessione di beni',2=>'Prestazione di servizi'),
                           'allegato'=>'Allegato IVA - Elenco Clienti',
                           'yn_value'=>array('S'=>'Si','N'=>'No'),
                           'aliiva'=>'Riduzione I.V.A.',
                           'ritenuta'=>'% Ritenuta',
                           'status'=>'Visibilit&agrave; alla ricerca',
                           'status_value'=>array(''=>'Attiva','HIDDEN'=>'Disabilitata'),
                           'annota'=>'Annotazioni'
                         ),
                   "report_letter.php" =>
                   array(  "Reporte de Cartas ",
                           "Fecha ",
                           "Numero ",
                           "Tipo ",
                           "Nombre de Empresa ",
                           "Objeto ",
                           "Escribir nueva carta"),
                    "admin_letter.php" =>
                   array(  'title' => " Carta ",
                           'mesg'=>array('La busqueda no arrojo resultados!',
                                         'Inserte al menos 2 caracteres!',
                                         'Cambiando cliente'
                                          ),
                           array("LET" => " Normal ","DIC" => "Declaracion","SOL" => " Solicitud "),
                           " de ",
                           " a las ",
                           " numero ",
                           "Objeto ",
                           " atencion para ",
                           "adjuntar la firma del usuario ",
                           "Tipo ",
                           "Cuerpo ",
                           "Nombre de usuario",
                           "La fecha no es correcta!",
                           "Debe seleccionar un cliente o un proveedor!"
                        )
                    );
?>