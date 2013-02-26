<?php
/*
 --------------------------------------------------------------------------
                            GAzie - Gestione Azienda
    Copyright (C) 2004-2013 - Antonio De Vincentiis Montesilvano (PE)
                                (www.devincentiis.it)
                        <http://gazie.it>
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

$strScript = array ("browse_document.php" =>
                   array('title'=>"List of Documents / Certificates",
                         'errors'=>array('The file format was not accepted!',
                                         'The file is too big!',
                                         'The file is empty!',
                                         'No file selected'),
                         'ins_this'=>"Insert a document and / or Certificate",
                         'upd_this'=>"Edit Document and / or Certificate",
                         'item'=>"Item reference",
                         'table_name_ref'=>"Reference Table",
                         'note'=>"Caption / Notes ",
                         'ext'=>"Extension",
                         'select'=>"Sel.",
                         'code'=>"Code"),
                    "report_statis.php" =>
                   array(  "statistic ",
                           "sales",
                           "purchases",
                           "year",
                           " order by ",
                           " from: ",
                           " to: ",
                           "Last purchase ",
                           "Last sales ",
                           " of the year ",
                           " Item ",
                           " Quantity ",
                           " Amount - ".$admin_aziend['curr_name'],
                           "Out of warehouse"),
                    "report_movmag.php" =>
                   array(  "warehouse movements ",
                           "code",
                           "Insert ",
                           "Report of ",
                           "Reg. date",
                           "Item",
                           "Quantity",
                           "Amount",
                           "Document",
                           " of ",
                           "Create movements from documents"),
                    "admin_movmag.php" =>
                   array(  "warehouse movement ",
                           "Entry date ",
                           "Causal ",
                           "customer",
                           "Supplier",
                           "C",
                           "S",
                           "Item",
                           "Document date ",
                           "Doc. description ",
                           "Reduction in closing",
                           "Measure unit ",
                           "Quantity ",
                           "Price ",
                           "Reduction in line",
                           "The document date is not corrected!",
                           "The record date is not corrected!",
                           "The date of the document is successive to that one of record date",
                           "It has not been selected the item!",
                           "The quantity cannot be equal to zero!",
                           'operat'=>'Operator',
                           'operat_value'=>array(-1=>"Unloading",0=>"Nop",1=>"Loading"),
                           'partner'=>'Partner',
                           'del_this'=>'Delete stock movement',
                           'amount'=>" Amount - ".$admin_aziend['curr_name'],
                           ),
                    "report_caumag.php" =>
                   array(  "warehouse causals ",
                           "Report of "
                           ),
                    "admin_catmer.php" =>
                   array(  "categoria merceologica ",
                           "Numero ",
                           "Descrizione ",
                           "Image (jpg,png,gif) max 10kb: ",
                           "% di ricarico ",
                           "Annotazioni ",
                           "codice gi&agrave; esistente!",
                           "la descrizione &egrave; vuota!",
                           "Il file immagine dev'essere nel formato PNG",
                           "L'immagine non dev'essere pi&ugrave; grande di 10 kb",
                           'web_url'=>'Web url<br />(ex: http://site.com/group.html)'
                           ),
                    "admin_caumag.php" =>
                   array(  "warehouse causal ",
                           "Code ",
                           "Description ",
                           "Document data ",
                           "Operation ",
                           "Update consistency ",
                           "No",
                           "Yes",
                           "Download",
                           "Nop",
                           "Upload",
                           "customer/Supplier",
                           "customer",
                           "Both",
                           "Supplier",
                           "codice gi&agrave; esistente!",
                           "la descrizione &egrave; vuota!",
                           "il codice dev'essere un numero minore di 99"
                           ),
                    "genera_movmag.php" =>
                   array(  "Genera movimenti di magazzino da documenti",
                           "Data inizio ",
                           "Data fine",
                           "Azienda senza obbligo di magazzino fiscale!",
                           " successiva alla ",
                           "  righi sono da traferire in magazzino:",
                           " Non ci sono righi da trasferire in magazzino!"),
                    "select_giomag.php" =>
                    array( 0=>'Stampa giornale di magazzino',
                           'title'=>'Selezione per la visualizzazzione e/o la stampa del giornale di magazzino',
                           'errors'=>array('La data  non &egrave; corretta!',
                                           'La data di inizio dei movimenti contabili da stampare non pu&ograve; essere successiva alla data dell\'ultimo !',
                                           'La data di stampa non pu&ograve; essere precedente a quella dell\'ultimo movimento!'
                                          ),
                           'date'=>'Data di stampa ',
                           'date_ini'=>'Data registrazione inizio  ',
                           'date_fin'=>'Data registrazione fine ',
                           'header'=>array('Date'=>'','Causal'=>'','Document description'=>'',
                                            'Price'=>'','Amount'=>'','MU' =>'','Quantity'=>''
                                           )
                           ),
                    "recalc_exist_value.php" =>
                   array(  "Rivalutazione esistenza articoli da movimenti di magazzino",
                           "Anno di riferimento",
                           "Metodo di rivalutazione, scelto in configurazione azienda",
                           "Sono stati movimentati i seguenti",
                           "articoli durante il ",
                           "Movimenti",
                           "Codice",
                           "Descrizione",
                           "Esistenza",
                           "UM acq.",
                           "Valore precedente",
                           "Valore rivalutato",
                           "NON RIVALUTATO vedi nota ",
                           "(1) perch&egrave; ci sono degli acquisti negli anni successivi al ",
                           "(2) perch&egrave; non ci sono movimenti di acquisto nel ",
                           "Non ci sono articoli movimentati!"),
                    "inventory_stock.php" =>
                   array(  'title'=>"Inventory exist stock",
                           'del'=>"of",
                           'catmer'=>"Item Group ",
                           'select'=>"Sel.",
                           'code'=>"Code",
                           'descri'=>"Item description",
                           'mu'=>"M.U.",
                           'load'=>"Load",
                           'unload'=>"Unload",
                           'value'=>"New stock value",
                           'v_a'=>"Current Value",
                           'v_r'=>"Real value",
                           'g_a'=>"Current stock",
                           'g_r'=>"Real stock",
                           'g_v'=>"Stock value",
                           'noitem'=>"No items found in this category",
                           'errors'=>array(" The real stock can not be negative",
                                           " The real value can not be negative or zero",
                                           " You are trying to make an inventory with zero real stock and zero current stock "),
                           'preview_title'=>'Confirming the choices made will be recorded the following movements of stock:'
                           ),
                    "select_schart.php" =>
                    array( 0=>'Stampa schedari di magazzino',
                           'title'=>'Selection for view and/or print the warehouse item reports',
                           'mesg'=>array('La ricerca non ha dato risultati!',
                                         'Inserire almeno 1 carattere!',
                                         'Cambia articolo'
                                          ),
                           'errors'=>array('La data  non &egrave; corretta!',
                                           'La data di inizio dei movimenti contabili da stampare non pu&ograve; essere successiva alla data dell\'ultimo !',
                                           'La data di stampa non pu&ograve; essere precedente a quella dell\'ultimo movimento!',
                                           'L\'articolo iniziale non pu&ograve; avere un codice successivo a quello finale!',
                                           'La categoria merceologica iniziale non pu&ograve; avere un codice successivo a quello finale!'
                                          ),
                           'date'=>'Data di stampa ',
                           'cm_ini'=>'Categoria merceologica inizio ',
                           'art_ini'=>'Articolo inizio ',
                           'date_ini'=>'Data registrazione inizio  ',
                           'cm_fin'=>'Categoria merceologica fine ',
                           'art_fin'=>'Articolo fine ',
                           'date_fin'=>'Data registrazione fine ',
                           'header'=>array('Date'=>'','Causal'=>'','Document<br \>description'=>'',
                                            'Price'=>'','UM' =>'','Quantity'=>'',
                                            $admin_aziend['symbol'].'<br \>loading'=>'',$admin_aziend['symbol'].'<br \>unloadig'=>'',
                                            'Storage<br \>quantity'=>'','Storage<br \>value'=>''
                                           ),
                           'tot'=>'Consistenza'
                           ),
                    "stampa_schart.php" =>
                    array( 0=>'VALUATION STOCK REPORT from ', 1=>' to ',
                           'bot'=>'to carry : ',
                           'top'=>'from carry :  ',
                           'item_head'=>array('Code','Group','Description','MU','Min.Stock'),
                           'header'=>array('Date','Causal','Document description',
                                            'Price','MU','Quantity',
                                            $admin_aziend['symbol'].' load',$admin_aziend['symbol'].' unload',
                                            'Q.ty stock','Val. stock'
                                           ),
                           'tot'=>'Consistency at '
                           ),
                    "select_deplia.php" =>
                    array( 'title'=>'Selezione per la stampa del catalogo',
                           'mesg'=>array('La ricerca non ha dato risultati!',
                                         'Inserire almeno 1 carattere!',
                                         'Change item'
                                          ),
                           'errors'=>array('La data  non &egrave; corretta!',
                                           'L\'articolo iniziale non pu&ograve; avere un codice successivo a quello finale!',
                                           'La categoria merceologica iniziale non pu&ograve; avere un codice successivo a quello finale!'
                                          ),
                           'date'=>'Print date ',
                           'cm_ini'=>'Categoria merceologica inizio ',
                           'art_ini'=>'Articolo inizio ',
                           'cm_fin'=>'Categoria merceologica fine ',
                           'art_fin'=>'Articolo fine ',
                           'barcode'=>'Stampa',
                           'barcode_value'=>array(0=>'Immagini',1=>'Codici a Barre'),
                           'listino'=>'Price list',
                           'listino_value'=>array(1=>' of sales 1',2=>' of sales 2',3=>' of sales 3','web'=>'of web sales')
                           ),
                    "select_listin.php" =>
                    array( 'title'=>'Selezione per la stampa dei listini',
                           'mesg'=>array('La ricerca non ha dato risultati!',
                                         'Inserire almeno 1 carattere!',
                                         'Change item'
                                          ),
                           'errors'=>array('La data  non &egrave; corretta!',
                                           'L\'articolo iniziale non pu&ograve; avere un codice successivo a quello finale!',
                                           'La categoria merceologica iniziale non pu&ograve; avere un codice successivo a quello finale!'
                                          ),
                           'date'=>'Data di stampa ',
                           'cm_ini'=>'Categoria merceologica inizio ',
                           'art_ini'=>'Articolo inizio ',
                           'cm_fin'=>'Categoria merceologica fine ',
                           'art_fin'=>'Articolo fine ',
                           'listino'=>'Price list ',
                           'listino_value'=>array(0=>'of purchase',1=>' of sales 1',2=>' of sales 2',3=>' of sales 3','web'=>'of web sales')
                           ),
                    "update_prezzi.php" =>
                   array( 'title'=>'Update item prices',
                           'mesg'=>array('La ricerca non ha dato risultati!',
                                         'Inserire almeno 1 carattere!',
                                         'Change item'
                                          ),
                           'errors'=>array('Valore "0" inaccettabile in questa modalit&agrave; di modifica !',
                                           'L\'articolo iniziale non pu&ograve; avere un codice successivo a quello finale!',
                                           'La categoria merceologica iniziale non pu&ograve; avere un codice successivo a quello finale!'
                                          ),
                           'cm_ini'=>'Categoria merceologica inizio ',
                           'art_ini'=>'Articolo inizio ',
                           'cm_fin'=>'Categoria merceologica fine ',
                           'art_fin'=>'Articolo fine ',
                           'lis_obj'=>'Object change price list',
                           'lis_bas'=>'Base price list',
                           'listino_value'=>array(0=>'of purchase',1=>' of sales 1',2=>' of sales 2',3=>' of sales 3','web'=>'of web sales'),
                           'mode'=>'Modalit&agrave; di modifica',
                           'mode_value'=>array('A'=>'Sostituzione','B'=>'Somma in percentuale','C'=>'Somma valore',
                                               'D'=>'Moltiplicazione per valore','E'=>'Divisione per valore','F'=>'Azzeramento e somma percentuale'),
                           'valore'=>'Percentuale/valore',
                           'round_mode'=>'Arrotondamento matematico a',
                           'round_mode_value'=>array('1 '.$admin_aziend['curr_name'],'10 cents','1 cent','1 mils','0,1 mils','0,01 mils'),
                           'header'=>array('Cat.Merceologica'=>'','Codice'=>'','Descrizione'=>'','U.M.'=>'',
                                            'Old price'=>'','New price'=>''
                                          )
                           ),
                   "update_vatrate.php" =>
                   array( 'title'=>'Changing the VAT rate of Items',
                           'mesg'=>array('The search gave no results!',
                                         'Enter at least 1 character!',
                                         'Change item'
                                          ),
                           'errors'=>array('Errore nullo',
                                           'The initial item may not have a code following the final code!',
                                           'The initial item group can not have a code following the final!'
                                          ),
                           'cm_ini'=>'Initial item group ',
                           'art_ini'=>'Initial item ',
                           'cm_fin'=>'Final item group ',
                           'art_fin'=>'Final item ',
                           'rate_obj'=>'VAT rate to change',
                           'rate_new'=>'New VAT rate',
                           'header'=>array('Item group'=>'','Code'=>'','Description'=>'','M.U.'=>'',
                                            'Old VAT rate'=>'','New VAT rate'=>''
                                          )
                           ),
                    "admin_artico.php" =>
                   array(  'title'=>'Management of products',
                           'ins_this'=>'Add product',
                           'upd_this'=>'Update product',
                           'errors'=>array('The product code already exists',
                                           'You are trying to change the code to a product associated with the movement of stock',
                                           'The product code already exists',
                                           'The file must be in PNG',
                                           'The image of the product should not be larger than 10 kb',
                                           'Enter a valid code',
                                           'Enter a description',
                                           'Insert the unit sales',
                                           'Enter the VAT rate'
                                          ),
                           'codice'=>"Code",
                           'descri'=>"Description",
                           'barcode'=>"Barcode EAN13",
                           'image'=>"Image (jpg,png,gif) max 10kb",
                           'unimis'=>"Measurement Unit for sales",
                           'catmer'=>"Product Category",
                           'preacq'=>'Purchase price',
                           'preve1'=>'Selling price of a list 1',
                           'preve2'=>'Selling price of a list 2',
                           'preve3'=>'Selling price of a list 3',
                           'aliiva'=>'VAT rate',
                           'esiste'=>'Actual existence',
                           'valore'=>'Value of the existing',
                           'last_cost'=>'Cost of the last purchase',
                           'scorta'=>'Minimum stock',
                           'riordino'=>'Purchase lot',
                           'uniacq'=>'Measurement Unit of purchases',
                           'peso_specifico'=>'Specific Gravity / Multiplier',
                           'volume_specifico'=>'Specific volume',
                           'pack_units'=>'Pieces in packaging',
                           'codcon'=>'Account of income from sales',
                           'id_cost'=>'Account of cost on purchases',
                           'annota'=>'Note (also published on the website)',
                           'document'=>'Documents and / or certification',
                           'web_mu'=>'Measurement Units on the website',
                           'web_price'=>'Selling price on the website',
                           'web_multiplier'=>'Web price multiplier',
                           'web_public'=>'Public website',
                           'web_public_value'=>array(0=>'No',1=>'Yes'),
                           'web_url'=>'Web url<br />(ex: http://site.com/item.html)'
                           )
             );
?>