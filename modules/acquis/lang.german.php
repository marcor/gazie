<?php

/*
  --------------------------------------------------------------------------
  GAzie - Gestione Azienda
  Copyright (C) 2004-2020 - Antonio De Vincentiis Montesilvano (PE)
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

  Traduzione Tedesca, Da Sangregorio Antonino.
  --------------------------------------------------------------------------
 */

$strScript = array("admin_fornit.php" =>
    array('title' => 'Lieferanten-Management',
        'ins_this' => 'Legen Sie neue Lieferanten',
        'upd_this' => 'Update Lieferanten',
        'mesg' => array('Die Suche ergab keine Treffer!',
            'Legen Sie mindestens 2 Zeichen!',
            'Wechsel des Stromversorgers'
        ),
        'errors' => array('Unter Angabe des Firmennamens',
            'Muss die Adresse angeben',
            'Ungültige Postleitzahl',
            'Muss die Stadt zeigen',
            'Muss der Provinz zeigen',
            'Muss das Geschlecht angeben',
            'IBAN ist falsch',
            'Die IBAN und die Nation sind anders',
            'Tax Code nicht korrekt für eine individuelle',
            'USt-Id-Nummer ist falsch',
            'Es gibt bereits einen Anbieter mit der gleichen Umsatzsteuer-Identifikationsnummer',
            'Tax-Code ist falsch',
            'Es gibt bereits einen Anbieter mit dem gleichen Code MwSt.',
            'Tax Code fehlt! Wird automatisch mit <br /> gesetzt den gleichen Wert der Umsatzsteuer-Identifikationsnummer',
            'Ist ein Individuum, geben Sie den Code MwSt.',
            'Gibt es ein Register mit der gleichen Umsatzsteuer-Identifikationsnummer',
            'Gibt es eine Registrierung mit dem gleichen Steuerkennzeichen',
            'Sie müssen wählen Sie Ihre Zahlungsweise',
            'Der Lieferanten-Code ist bereits vorhanden, versuchen Sie die Eingabe mit der einen vorgeschlagen (plus 1)',
            'The date of birth is wrong',
            'Email address formally wrong'
        ),
        'link_anagra' => 'Klicken Sie unten, um die vorhandene Registrierung auf der Sie Ihre Kontenplan eingeben ',
        'codice' => "Code ",
        'ragso1' => "Firmenname 1",
        'ragso2' => "Firmenname 2",
        'sedleg' => "Registered Office",
        'luonas' => 'Birthplace',
        'datnas' => 'Date of birth',
        'pronas' => 'Province of birth',
        'counas' => 'Country of birth',
        'legrap_pf_nome' => "Gesetzlicher Vertreter ",
        'sexper' => "Sex / juristischen Person",
        'sexper_value' => array('' => '-', 'M' => 'Male', 'F' => 'Female', 'G' => 'Legal'),
        'indspe' => 'Addresse',
        'capspe' => 'Postleitzahl',
        'citspe' => 'City - Provinz',
        'country' => 'Nation',
        'id_language' => 'Sprache',
        'id_currency' => 'Währung',
        'telefo' => 'Telephone',
        'fax' => 'Fax',
        'cell' => 'Cellphone',
        'codfis' => 'Tax code',
        'pariva' => 'Umsatzsteuer-Identifikationsnummer',
        'e_mail' => 'e mail',
        'cosric' => 'Kostenrechnung',
        'codpag' => 'Zahlungsweise*',
        'sconto' => '% Rabatt angewendet',
        'banapp' => 'Bank-Unterstützung',
        'portos' => 'Rendered Hafen',
        'spediz' => 'Lieferung',
        'imball' => 'Paket',
        'listin' => 'Preisliste angewendet',
        'id_des' => 'Destination aus der Registry',
        'destin' => 'Destination ⇒ freie Beschreibung',
        'iban' => 'IBAN',
        'maxrat' => 'Maximale Höhe der Rechnungen',
        'ragdoc' => 'Gruppieren von Dokumenten',
        'addbol' => 'Laden Sie den Stempel Aufwendungen',
        'speban' => 'Laden Sie die Bankspesen',
        'spefat' => 'Charge the cost of billing',
        'stapre' => 'Print Preise auf Frachtpapieren',
        'allegato' => 'Attached MwSt. - Kunden berichten',
        'yn_value' => array('S' => 'ja', 'N' => 'Nein'),
        'aliiva' => 'Ermäßigung der Mehrwertsteuer',
        'ritenuta' => '% Quellensteuer',
        'status' => 'Sichtbarkeit in der Forschung',
        'status_value' => array('' => 'Yes', 'HIDDEN' => 'Hidden'),
        'annota' => 'Note'
    ),
    "report_broacq.php" =>
    array('New Preveter', 'New Order',
        'title' => 'Preventivi e ordini',
        'mail_alert0' => 'Invio documento con email',
        'mail_alert1' => 'Hai scelto di inviare una e-mail all\'indirizzo: ',
        'mail_alert2' => 'con allegato il seguente documento:',
    ),
    "report_debiti.php" =>
    array('title' => 'List of debts to suppliers',
        'start_date' => 'Year-start',
        'end_date' => 'Year-end',
        'codice' => 'Code',
        'partner' => 'Lieferant',
        'telefo' => 'Telephone',
        'mov' => 'N. Einträge',
        'dare' => 'Soll',
        'avere' => 'Kredit',
        'saldo' => 'Balance',
        'pay' => 'Zahlen',
        'statement' => 'Erklärung',
        'pay_title' => 'Zahlt die Schulden mit ',
        'statement_title' => 'Drucken der Mitteilung der '
    ),
    "admin_docacq.php" =>
    array('title' => 'Inserimento/modifica documenti a fornitori',
        array("DDR" => "D.d.T. di Reso a Fornitore", "DDL" => "D.d.T. c/lavorazione", "AFA" => "Fattura d'Acquisto", "ADT" => "D.d.T. d'Acquisto", "AFC" => "Nota Credito da Fornitore", "AOR" => "Ordine a Fornitore", "APR" => "Richiesta di Preventivo a Fornitore"),
        'mesg' => array('The search yielded no results!',
            'Insert at least 2 characters!',
            'Changing customer / supplier'
        ),
        " body ",
        " Fuß ",
        " Pulls",
        " Abschnitt ",
        " Adresse ",
        " Datum ",
        " Liste ",
        " Zahlung ",
        " Bank ",
        "Reiseziel ",
        " Kausal ",
        " Lagerhaus ",
        "Kauf ",
        "Träger",
        "Artikel",
        "Menge",
        "Typ",
        "Kosten",
        "Mehrwertsteuer",
        "Code",
        "Beschreibung",
        "U.M.",
        "Preis",
        "Rabatt",
        "Betrag",
        "Verpackung",
        "Versand",
        "Transport",
        "Port",
        "Top Transport",
        "Stunden  ",
        "Steuerpflichtig",
        "Steuer",
        "Waren",
        "Gewicht",
        "Gesamt",
        "Der Beginn der Beförderung sind nicht korrekt!",
        "Der Zeitpunkt des Beginns der Beförderung nicht früher als das Datum der Ausstellung werden!",
        "Es sind Linien, die Ausstellung des Dokuments!",
        "Sie versuchen, die DDT mit einem Zeitpunkt als die vorherige Frage mit DDT zu ändern!",
        "Stai tentando di modificare il DdT con una data successiva a quella del DdT con numero successivo!",
        "Wollen Sie das Dokument mit einem früheren Zeitpunkt als das gleiche Dokument mit dem vorherigen Thema zu bearbeiten!",
        "Sind Sie versuchen, um das Dokument zu einem späteren Zeitpunkt als das gleiche Dokument mit der nächsten Ausgabe bearbeiten!",
        "Die Liberierung kann nicht früher als die letzte DDT ausgestellt werden!",
        "Die Liberierung kann nicht früher als das letzte Dokument des gleichen Typs ausgegeben werden!",
        "Das Ausstellungsdatum ist nicht korrekt!",
        "Sie haben nicht die Lieferanten ausgewählt!",
        "Du hast nicht eine Zahlungsmethode auswählen!",
        "Eine Linie hat keine Beschreibung!",
        "Eine Linie ist, ohne das Gerät!",
        "Kausale",
        "Zahl ",
        "Der Stichtag kann nicht früher als um das Dokument zu erfassen!",
        "Das Datum des Dokuments zu erfassen ist falsch!",
        "War das nicht in den Aktenzeichen enthalten!",
        /** ENRICO FEDELE */
        "add_article" => "Aggiungi un nuovo articolo",
        'art_code' => 'Codice articolo',
        'art_barcode' => 'Codice a barre',
        'art_descr' => 'Descrizione',
        'search_for' => 'Cerca per',
        'weight' => 'Peso specifico:',
        'zero_rows' => 'Il documento non contiene righi o prodotti, compila la ricerca articoli nella sezione corpo per aggiungerne, inserisci il valore % per avere una lista completa o per effettuare una ricerca parziale',
        'discount_alert' => 'sconto da anagrafe articoli',
        'last_row' => 'Ultimo rigo inserito'
    /** ENRICO FEDELE */
    ),
    "delete_docacq.php" =>
    array('title' => 'Eliminazione Documento d\'Acquisto' ),
    "accounting_documents.php" =>
    array('title' => 'Neues vom steuerbaren Bewegungen Rechnungslegung Dokumente',
        'errors' => array('Falscher Zeitpunkt',
            'Es gibt keine Dokumente in schriftlicher werden die ausgewählten'
        ),
        'vat_section' => 'MwSt. n. § ',
        'date' => 'Bis zum:',
        'type' => ' MwSt.-Register',
        'type_value' => array('A' => 'von Eingangsrechnungen'),
        'proini' => 'Initial-Protokoll',
        'profin' => 'Final-Protokoll',
        'preview' => 'Accounting-Vorschau',
        'date_reg' => 'Datum',
        'protoc' => 'Protokoll',
        'doc_type' => 'Typ',
        'doc_type_value' => array('FAD' => 'AUFGESCHOBENE Rechnung an den Kunden',
            'FAI' => 'IMMEDIATE Rechnung an den Kunden',
            'FNC' => 'Gutschrift Der Kunde',
            'FND' => 'HINWEIS FÜR DEN SCHULDEN Der Kunde',
            'VCO' => 'GEBÜHREN',
            'VRI' => 'ERHALTEN',
            'AFA' => 'EINKAUFSRECHNUNG',
            'AFC' => 'Gutschrift vom Kauf',
            'AFD' => 'SCHULD Hinweis ab Kaufdatum'
        ),
        'customer' => 'Lieferant',
        'taxable' => 'Steuerpflichtig',
        'vat' => 'Mehrwertsteuer',
        'stamp' => 'Briefmarken auf Rechnungen',
        'tot' => 'Gesamt'
    )
);
?>