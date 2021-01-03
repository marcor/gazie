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

    Traduzione Tedesca, Da Sangregorio Antonino.
 --------------------------------------------------------------------------
*/
$strScript = array ("select_liqiva.php" =>
                    array( 'title'=>'Wählen Sie zur Ansicht und / oder ausdrucken periodische MwSt-Abfertigung',
                           'errors'=>array('Das Datum ist falsch!',
                                           'Das Startdatum kann nicht später als Enddatum sein!'
                                          ),
                           'page_ini'=>'N. Seite beginnen',
                           'sta_def'=>'Final drucken',
                           'sta_def_title'=>'Wenn ausgewählt ändert sich der Wert der letzten Seite dieser Firma Rekord-Konfiguration gedruckt',
                           'descri'=>'Beschreibung',
                           'descri_value'=>array('M'=>' von

 ','T'=>'im Quartal '
                                           ),
                           'date_ini'=>'Start date  ',
                           'sem_ord'=>' Regime ',
                           'sem_ord_value'=>array(0=>' Ordinary accounting ',1=>' Semplified accounting '
                                           ),
                           'cover'=>'Drucken Sie die Abdeckung',
                           'date_fin'=>'Enddatum ',
                           'header'=>array('Abschnitt'=>'','Register'=>'',''=>'Bewerten Beschreibung',''=>'Steuerpflichtig',
                                           'Rate'=>'','Steuer'=>'','Indetraibile'=>'','Amount'=>''
                                           ),
                           'regiva_value'=>array(0=>'None',2 =>'Sale Invoices',4=>'Sale Tickets',6=>'Eingangsrechnungen',9=>'Versamenti IVA'),
                           'of'=>' von ',
                           'tot'=>'BETRAG  ',
                           't_pos'=>' V.A.T. DEBIT',
                           't_neg'=>' V.A.T. CREDIT',
                           'carry'=>'Credit gegenüber Vorperiode',
                           'pro_rata' => 'PRO RATA'
                           ),
                    "stampa_liqiva.php" =>
                    array( 'title'=>'Periodische MwSt-Clearance',
                           'cover_descri'=>'MwSt. Zusammenfassung Buch des Jahres ',
                           'page'=>'Seite',
                           'sez'=>'Abschnitt',
                           'regiva_value'=>array(0=>'None',2 =>'Register von Sale Rechnungen',4=>'Regitro  of Sale Tickets',6=>'Registrieren von Eingangsrechnungen',9=>'Versamenti IVA',
                           'code'=>'Code',
                           'descri'=>'Descriptiov of V.A.T. rate',
                           'imp'=>'Steuerpflichtig',
                           'iva'=>'Steuer',
                           'rate'=>'%',
                           'ind'=>'Indetraibile',
                           'tot'=>'Betrag',
                           't_reg'=>'MWST. insgesamt das Register ',
                           't_pos'=>' DEBIT MWST.',
                           't_neg'=>' CREDIT MWST',
                           'inter'=>'Steigern als Zinsen ',
                           'pay'=>'zu zahlen ',
                           'carry'=>'Credit gegenüber Vorperiode',
                           'pro_rata' => 'PRO RATA',
                           'pay_date'=>' Bezahlt',
                           'co'=>' am',
                           'abi'=>' A.B.I. ',
                           'cab'=>' C.A.B. '
                           ),
                    "select_partit.php" =>
                    array( 'title'=>'Auswahl für Ansicht und / oder Ausdrucken Konten Ledger Einträge',
                           'mesg'=>array('Die Suche ergab keine Treffer!',
                                         'Legen Sie mindestens 2 Zeichen!',
                                         'Ändern Kunde / Lieferant'
                                          ),
                           'errors'=>array('Das Datum ist falsch!',
                                           'Der Starttermin des Hauptbuches Einträge können nicht nach dem letzten gedruckt werden!',
                                           'Der Tag der Pressefreiheit kann nicht früher als die letzten Ledger-Einträge werden!',
                                           'Die erste Konto kann nicht später als die letzte sein!',
                                           'Es aren \'Bewegungen in ausgewählten'
                                          ),
                           'date'=>'Druckdatum ',
                           'master_ini'=>'Start Master Account',
                           'account_ini'=>'Start Sub Account',
                           'date_ini'=>'Start Date  ',
                           'master_fin'=>'End Master Account ',
                           'account_fin'=>'End Sub Account ',
                           'date_fin'=>'End Date ',
                           'selfin' => 'Copy initial account',
                           'header1'=>array('Account'=>'','Num.Mov.'=>'',''=>'Beschreibung',
                                            'Debt'=>'','Kredit'=>'','Progressive <br /> Gleichgewicht'=>''
                                           ),
                           'header2'=>array('Date'=>'','ID'=>'','Beschreibung'=>'','N.Doc.'=>'',
                                            'Date Doc.' =>'','Credit'=>'','Debt'=>'',
                                            'Progressive <br /> Gleichgewicht'=>''
                                           )
                           ),
                    "admin_caucon.php" =>
                    array( 'title'=>'Management Accounting kausalen',
                           'ins_this'=>'Geben Sie eine neue Bilanzierungs-kausalen ',
                           'upd_this'=>'Upadate der Rechnungslegung kausalen',
                           'mesg'=>array('Die Suche ergab keine Treffer!',
                                         'Legen Sie mindestens 2 Zeichen!',
                                         'Ändern Kunde / Lieferant'
                                          ),
                           'errors'=>array('Geben Sie einen gültigen Code ein!',
                                           'Sie müssen eine Beschreibung eingeben!',
                                           'Bestehende Code mit den entsprechenden Verfahren, wenn Sie ändern möchten!',
                                           'Sie müssen definieren, mindestens ein Konto',
                                           'Code für das automatische Schließen von Konten vorbehalten!',
                                           'Code für die automatische Öffnung der Konten vorbehalten!'
                                          ),
                           'head'=>'Konten verschoben werden ',
                           'codice'=>'Code kausalen *',
                           'descri'=>'Beschreibung *',
                           'insdoc'=>'Data Entry Reference Document',
                           'insdoc_value'=>array(0=>'Nein',1=>'Ja'),
                           'regiva'=>'MwSt-Register',
                           'regiva_value'=>array(0=>'None',2 =>'Invoice of sale',4=>'Tickets',6=>'Rechünung Kauf',9=>'Versamenti IVA'),
                           'operat'=>'Betreiber',
                           'operat_value'=>array(0=>'No',1=>'Sum',2=>'Subtrahieren'),
                           'pay_schedule'=>'Open items (scheduler)',
                           'pay_schedule_value'=>array(0=>'Does not work',1=>'Document sale / purchase (open)',2=>'Payment (close)'),
                           'contr'=>'Konto (min. 1) *',
                           'tipim'=>'Art der Höhe',
                           'tipim_value'=>array(''=>'','A'=>'Total','B'=>'Steuerpflichtig','C'=>'Steuer'),
                           'daav'=>'Lastschriften / Gutschriften',
                           'daav_value'=>array('D'=>'DEBITS','A'=>'CREDITS'),
                           'report'=>'Liste der Rechnungslegung Kausalen',
                           'del_this'=>'Konto kausalen '
                           ),
                    "admin_piacon.php" =>
                   array(  'title'=>'Verwaltung des Kontenplans',
                           'ins_this'=>'Legen Konto',
                           'upd_this'=>'update-Konto',
                           'errors'=>array('Geben Sie einen gültigen Code ein!',
                                           'Bestehende Code mit den entsprechenden Verfahren, wenn Sie ändern möchten!',
                                           'Sie müssen eine Beschreibung eingeben!'
                                          ),
                           'codice'=>"Code ",
                           'mas'=>"Master",
                           'sub'=>"Subakonto",
                           'descri'=>"Beschreibung",
                           'ceedar'=>"Umgliederung von EWG Bilanz / Lastschriften",
                           'ceeave'=>"Umgliederung von EWG Bilanz / Credits",
                           'annota'=>"Note"
                         ),
                    "admin_movcon.php" =>
                    array( 'title'=>'Management-Haupbuch-Einträge',
                           'ins_this'=>'Legen Sie neue Hauptbuch Einträge',
                           'upd_this'=>'Update neue Hauptbuch Einträge',
                           'mesg'=>array('Die Suche ergab keine Treffer!',
                                         'Legen Sie mindestens 2 Zeichen!',
                                         'Ändern Kunde / Lieferant'
                                          ),
                           'errors'=>array('Mindestens eine Zeile hat keine Konten!',
                                           'Mindestens eine Zeile hat den Wert Null!',
                                           'Buchhaltungseingaben ist unausgewogen!',
                                           'Total of Debt Zeilen darf nicht Null sein!',
                                           'Total der CREDIT Zeilen darf nicht Null sein!',
                                           'MwSt Eintrag ist Null!',
                                           'MwSt Eintrag einen anderen Betrag aus, dass der Buchhaltungseingaben!',
                                           'Muss Legen Sie eine Beschreibung!',
                                           'Der Stichtag ist falsch!',
                                           'Das Belegdatum ist falsch!',
                                           'Sie haben vergessen, die Nummer der Eintragung gestellt!',
                                           'Sie haben vergessen, den Aktenzeichen setzen!',
                                           'Das Datum des Dokuments muss nicht später als die der Registrierung werden!',
                                           'WARNUNG Sie \'re Bearbeitung einer Bewegung, die eine MwSt-Registrierung!',
                                           'Sie versuchen, ein Dokument, das bereits registriert ist aufzuzeichnen'
                                          ),
                           'id_testata'=>'Eintrag Nummer',
                           'date_reg'=>'Datum der Registrierung',
                           'descri'=>'Beschreibung',
                           'caucon'=>'Accounting Kausalen',
                           'v_caucon'=>'Bestätigen Kausalen!',
                           'insdoc'=>'Informationen über das Referenzdokument',
                           'insdoc_value'=>array(0=>'Ja',1=>'Nein'),
                           'regiva'=>'MwSt.-Register',
                           'regiva_value'=>array(0=>'None',2 =>'Rechnungen Umsatz',4=>'Receipts Steuer',6=>'Rechnungen Käufe',9=>'Versamenti IVA'),
                           'operat'=>'Betreiber',
                           'operat_value'=>array(0=>'Nein',1=>'Sum',4=>'Subtract'),
                           'date_doc'=>'Belegdatum',
                           'seziva'=>'MwSt. Abschnitt',
                           'protoc'=>'Registernummer',
                           'numdoc'=>'Zahl',
                           'partner'=>'Kunde / Lieferant',
                           'insiva'=>'MwSt Eintrag',
                           'vat'=>'MwSt-Satz',
                           'taxable'=>'Steuerpflichtig',
                           'tax'=>'Steuer',
                           'mas'=>"Master",
                           'sub'=>"Konto",
                           'amount'=>'Betrag',
                           'daav'=>'SCHULD/CREDIT',
                           'daav_value'=>array('D'=>'DEBT','A'=>'CREDIT'),
                           'bal_title'=>"Balance, um diesen Wert im Vergleich!",
                           'bal'=>"Ausgewogen",
                           'addval'=>" Aufwerten",
                           'subval'=>"Verringern Sie den Wert des ",
                           'zero'=>"Buchungsvorgang ist Null!",
                           'diff'=>"Odds",
                           'tot_d'=>'SCHULD insgesamt',
                           'tot_a'=>'CREDIT ingesamt',
                           'visacc'=>'Profil Hauptbuch',
                           'report'=>'Liste der Einträge Hauptbuch',
                           'del_this'=>'Hauptbuch Einträge',
                           'sourcedoc'=>'Quelldokument',
                           'source'=>'Quelle'
                           ),
                    "report_piacon.php" =>
                   array(  'title'=>'Kontenplan',
                           'ins_this'=>'Legen Sie neue Konto',
                           'view_this'=>'Profil und / oder Print-Konto Bericht',
                           'print_this'=>'Drucken des Kontenplan',
                           'header'=>array('Master'=>'','Konto'=>'','Beschreibung'=>'','Schuld'=>'',
                                            'Credits'=>'','Balance'=>'','Profil <br /> und / oder ausdrucken'=>'',
                                            'Löschen'=>''),
                           'msg1'=>'Profil <br /> und / oder printRemember dass Sie beherrschen müssen, um die Aktivitäten zwischen 100 und 199, zwischen 200 und 299 Verbindlichkeiten einzuführen, kostet zwischen 300 und 399, Einkommen zwischen 400 und 499 und das Memorandum Konten oder transiente zwischen 500 und 599. Clicca sulle righe dei mastri (in rosso) per espandere e visualizzare i sotto conti.',
                           'msg2'=>'Guthaben für das Jahr ', 
						   /** ENRICO FEDELE */
						   'edit_master' => 'Modifica il mastro', 
						   'edit_account' => 'Modifica il conto', 
						   'print_ledger' => 'Visualizza e stampa il paritario'
						   /** ENRICO FEDELE */
                         ),
                    "select_regiva.php" =>
                    array( 'title'=>'Wählen Sie für prewiev und / oder ausdrucken MwSt.-Register',
                           'errors'=>array('Falsches Datum!',
                                           'Das Startdatum kann nicht später als Enddatum sein!',
                                           'P'=>'Die Reihenfolge der Protokoll-Nummern ist nicht korrekt',
                                           'N'=>'Die Reihenfolge der Belegnummern ist nicht korrekt',
                                           'T'=>'Es ist eine Bewegung ohne Mehrwertsteuer',
                                           'err'=>'There are some errors that do not justify the printing of the register'
                                          ),
                           'vat_reg'=>'MwSt. anmelden print:',
                           'vat_reg_value'=>array(2=>'Verkauf Rechnungen',4=>'Gebühren',6=>'Ankaufsrechnung'),
                           'vat_section'=>'MwSt. Abschnitt ',
                           'page_ini'=>'N. der Startseite',
                           'jump'=>'Summary for each hop period',
                           'jump_title'=>'If selected print on the PDF all periodic summaries',
                           'sta_def'=>'Final drucken',
                           'sta_def_title'=>'Wenn Sie die Option entspricht dieser Wert dem Unternehmen Konfigurations-Archiv mit dem festen Wert der gedruckten Seite aktualisiert wird',
                           'descri'=>'Beschreibung',
                           'descri_value'=>array('M'=>' des Monats ','T'=>'des Quartals '
                                           ),
                           'date_ini'=>'Start date Eintrag',
                           'sem_ord'=>' system konto ',
                           'sem_ord_value'=>array(0=>' Ordinary ',1=>' Simplified '
                                           ),
                           'cover'=>'Drucken Sie die Abdeckung',
                           'date_fin'=>'Enddatum Eintrag',
                           'header'=>array('Protocol'=>'','Datum - ID Bewegung'=>'','Beschreibung des Dokuments'=>'','Kunden oder Lieferanten'=>'',
                                            'Steuerpflichtig' =>'','MwSt-Satz'=>'','Steuer'=>''
                                           ),
                           'of'=>' der ',
                           'tot'=>' TOTAL',
                           't_gen'=>' GENERAL'
                           ),
                    "stampa_regiva.php" =>
                    array( 'title'=>array(2=>'V.A.T. register of sales invoices ',
                                          4=>'V.A.T. register of receipts ',
                                          6=>'V.A.T. register of purchase invoices  '),
                           'cover_descri'=>array(2=>'Sales invoices register of the year',
                                                 4=>'Receipts register of the year ',
                                                 6=>'Purchase invoices register of the year '),
                           'partner_descri'=>array(2=>'Company customer',
                                                   4=>'Description',
                                                   6=>'CompanySupplier'),
                           'vat_section'=>' V.A.T. section n.',
                           'page'=>'page',
                           'top_carry'=>'from carry : ',
                           'bot_carry'=>'to carry : ',
                           'top'=>array('prot'=>'N.Prot.',
                                        'dreg'=>'Entry date', 
                                        'desc'=>'N.Document/Descr.',
                                        'ddoc'=>'Date Doc.', 
                                        'txbl'=>'Taxable',
                                        'perc'=>'Perc.',
                                        'tax'=>'Tax',
                                        'tot'=>'Total'), 
                           'of'=>' of ',
                           'vat_castle_title'=>' TOTAL SUMMARY FOR RATES ',
                           'descri'=>'description',
                           'taxable'=>'taxable',
                           'tax'=>'tax',
                           'tot'=>'total',
                           'tot_descri'=>'GENERAL TOTAL',
                           'acc_castle_title'=>' ACCOUNT TOTAL SUMMARY ',
                           'amount'=>'amount'
                           ),
                    "select_libgio.php" =>
                    array( 'title'=>'Wählen Sie für prewiev und / oder ausdrucken General Hauptbuch',
                           'errors'=>array('Falsche Startdatum!',
                                           'Falsche Enddatum!',
                                           'Das Startdatum kann nicht später als das Enddatum werden !'
                                          ),
                           'pagini'=>'N. der Startseite',
                           'stadef'=>'Final drucken',
                           'stadef_title'=>'Wenn ausgewählt ändert sich der Wert der letzten Seite dieser Firma Rekord-Konfiguration gedruckt',
                           'date_ini'=>'Entry Startdatum ',
                           'cover'=>' Decken Druken  -> ',
                           'date_fin'=>'Entry Enddatum',
                           'valdar'=>'SHULD (initial)',
                           'valave'=>'CREDIT (initial)',
                           'nrow'=>'Anzahl der Zeilen:',
                           'tot_a'=>' Total SCHULD ',
                           'tot_d'=>' Total CREDIT '
                           )
                    );

?>