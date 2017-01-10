<?php

/*
  --------------------------------------------------------------------------
  GAzie - Gestione Azienda
  Copyright (C) 2004-2016 - Antonio De Vincentiis Montesilvano (PE)
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

/*
  -- TRANSLATED BY : Dante Becerra Lagos (softenglish@gmail.com)
 */

$strScript = array("admin_fornit.php" =>
    array('title' => 'Administracion de Proveedores',
        'ins_this' => 'Insertar nuevo proveedor',
        'upd_this' => 'Actualizar proveedor',
        'mesg' => array('La b&uacute;squeda no dio resultados!',
            'Inserte al menos 2 caracteres!',
            'Cambiando clientes'
        ),
        'errors' => array('Usted debe indicar el nombre',
            'Debes indicar la direcci&oacute;n',
            'Usted debe indicar el CAP',
            'Usted debe indicar la ciudad',
            'Usted debe indicar la region',
            'Debes indicar el sexo',
            'El IBAN es incorrecto',
            'El IBAN y la nacion son diferentes',
            'NIF mal para una persona',
            'El IVA es formalmente incorrecto',
            'Existe ya un cliente con el mismo IVA',
            'El codigo de impuestos es formalmente incorrecto',
            'Existe ya un cliente con el mismo Codigo de Impuestos',
            'C.F. desaparecido! Fue autom&aacute;ticamente<br />Establecer el mismo valor del IVA!',
            'Es una persona, introduzca el c&oacute;digo de impuestos',
            'Existe un registro con el mismo IVA',
            'Existe un registro con el mismo c&oacute;digo impuestos',
            'Usted debe elegir la forma pago',
            'El c&oacute;digo de proveedor ya est&aacute; ah&iacute;, intentar la entrada con la que se propone (m&aacute;s 1)',
            'The date of birth is wrong',
            'Email formalmente mal la dirección'
        ),
        'link_anagra' => ' Haga clic a continuaci&oacute;n para introducir sus datos personales en la tabla existente de cuentas',
        'codice' => "Codigo ",
        'ragso1' => "Nombre de la empresa 1",
        'ragso2' => "Nombre de la empresa 1",
        'sedleg' => "Domicilio legal",
        'luonas' => 'Birthplace',
        'datnas' => 'Date of birth',
        'pronas' => 'Province of birth',
        'counas' => 'Country of birth',
        'legrap' => "Representante legal ",
        'sexper' => "Sexo/persona juridica ",
        'sexper_value' => array('' => '-', 'M' => 'Masculino', 'F' => 'Femenino', 'G' => 'Juridica'),
        'indspe' => 'Direccion',
        'capspe' => 'Codigo Postal',
        'citspe' => 'Ciudad; - Region',
        'country' => 'Pais',
        'id_language' => 'Lengua',
        'id_currency' => 'Moneda',
        'telefo' => 'Telefono',
        'fax' => 'Fax',
        'cell' => 'Celular',
        'codfis' => 'Codigo Tributario',
        'pariva' => 'Coincidir I.V.A.',
        'pec_email' => 'Posta Elettronica Certificata',
        'e_mail' => 'email',
        'cosric' => 'Cost Account',
        'id_agente' => 'Agente',
        'codpag' => 'Modo de pago *',
        'sconto' => '% Descuento a aplicar',
        'banapp' => 'El apoyo del Banco',
        'portos' => 'Puerto - Rendimiento',
        'spediz' => 'Transporte',
        'imball' => 'Embalaje',
        'listin' => 'Lista de precios aplicados',
        'id_des' => 'Destino del registro',
        'destin' => 'Destino descripci&oacute;n libre',
        'iban' => 'IBAN (numero cuenta bancaria internacional)',
        'maxrat' => 'El importe m&aacute;ximo de las cuotas',
        'ragdoc' => 'Agrupaci&oacute;n de los documentos',
        'addbol' => 'sellos de carga de debito',
        'speban' => 'Las comisiones bancarias de debito',
        'spefat' => 'Los costes de facturaci&oacute;n de d&eacute;bito',
        'stapre' => 'Los precios de impresi&oacute;n en D.d.T.',
        'allegato' => 'Anexo IVA - Clientes',
        'yn_value' => array('S' => 'Si', 'N' => 'No'),
        'aliiva' => 'Reduccion de I.V.A.',
        'op_type' => 'Tipos de operaciones',
        'op_type_value' => array(3 => 'Compra de bienes', 4 => 'Compra de servicios'),
        'ritenuta' => '% Retencion',
        'status' => 'Visibilidad de Investigacion',
        'status_value' => array('' => 'Activo', 'HIDDEN' => 'Deshabilitado'),
        'annota' => 'Anotaciones'
    ),
    "report_broacq.php" =>
    array('New Preveter', 'New Order',
        'title' => 'Preventivi e ordini',
        'mail_alert0' => 'Invio documento con email',
        'mail_alert1' => 'Hai scelto di inviare una e-mail all\'indirizzo: ',
        'mail_alert2' => 'con allegato il seguente documento:',
    ),
    "report_debiti.php" =>
    array('title' => 'Lista de las deudas a los proveedores de',
        'start_date' => 'A&ntilde;o de inicio',
        'end_date' => 'A&ntilde;o de fin',
        'codice' => 'Codigo',
        'partner' => 'Proveedor',
        'telefo' => 'Tel&eacute;fono',
        'mov' => 'N.Entradas',
        'dare' => 'Debe',
        'avere' => 'Credit',
        'saldo' => 'Balance',
        'pay' => 'Pagar',
        'statement' => 'Estado',
        'pay_title' => 'pagar la deuda con ',
        'statement_title' => 'Pulse la declaraci&oacute;n de '
    ),
    "admin_docacq.php" =>
    array('title' => 'Inserimento/modifica documenti a fornitori',
        array("DDR" => "D.d.T. volver al Proveedor", "DDL" => "D.d.T. c / procesamiento", "AFA" => "Las facturas de compra", "ADT" => "D.d.T. de Compra", "AFC" => "Nota Credito Proveedor", "AOR" => "Pedidos a proveedores", "APR" => "Solicite un Presupuesto de Proveedores"),
        'mesg' => array('La busqueda no dio resultados!',
            'Inserte al menos 2 caracteres!',
            'Cambiando cliente / proveedor'
        ),
        " cuerpo ",
        " pie ",
        " Tira ",
        " Seccion ",
        " Direccion ",
        " Fecha ",
        " Lista de precios ",
        " Pago ",
        " Banco ",
        " Destino ",
        " Causal ",
        " Existencias ",
        " Compra ",
        "Transporte",
        "Articulo",
        "Cantidad",
        "Tipo",
        "Costo",
        "I.V.A.",
        "Codigo",
        "Descripcion",
        "U.M. (no traducido)",
        "Precio",
        "Descuento",
        "Cantidad",
        "Embalaje",
        "Expedicion",
        "Transporte",
        "Puerto",
        "Inicio Transporte",
        " horas ",
        "Impuestos",
        "Establecer",
        "Mercaderia",
        "Peso",
        "Total",
        "La fecha de inicio del transporte no es correcta!",
        "La fecha de inicio del transporte no puede ser anterior a la fecha de edicion!",
        "No hay l&iacute;neas para poder expedir el documento!",
        "Estas intentando modificar el DdT con una fecha antecedente a aquella del DdT con numero antecedente!",
        "Estas intentando modificar el DdT con una fecha sucesiva a aquella del DdT con n&uacute;mero sucesivo!",
        "Estas intentando modificar el documento con una fecha antecedente a aquello del mismo tipo de documento con numero antecedente!",
        "Estas intentando editar el documento con fecha posterior a la del mismo documento con la pr&oacute;xima edici&oacute;n!",
        "La fecha de emisi&oacute;n no puede ser anterior a la &uacute;ltima emitida DDT!",
        "La fecha de emisi&oacute;n no puede ser anterior al &uacute;ltimo documento de la misma naturaleza, emitidos!",
        "La fecha de emisi&oacute;n no es correcta!",
        "No ha seleccionado proveedor!",
        "Usted no ha seleccionado un m&eacute;todo de pago!",
        "Una fila no tiene ninguna descripci&oacute;n!",
        "Un rengl&oacute;n sin la unidad de medida!",
        "Causal mag.",
        "Numero ",
        "La fecha de registro no puede ser antecedente a aquella del documento a registrar!",
        "La fecha del documento a registrar no es correcta!",
        "No se incluy&oacute; el n&uacute;mero del documento!",
        "It was not possible to load the document for product traceability!",
        "elemento mercancías tiene un número de serie, por lo tanto, la cantidad se ha visto obligado a 1",
        /** ENRICO FEDELE */
        "add_article" => "Añadir un nuevo artículo",
        'art_code' => 'Número de artículo',
        'art_barcode' => 'Código de barras',
        'art_descr' => 'Descripción',
        'search_for' => 'Cerca per',
        'weight' => 'Peso specifico:',
        'zero_rows' => 'Il documento non contiene righi o prodotti, compila la ricerca articoli nella sezione corpo per aggiungerne, inserisci il valore % per avere una lista completa o per effettuare una ricerca parziale',
        'discount_alert' => 'sconto da anagrafe articoli',
        'last_row' => 'Ultimo rigo inserito',
        'lotmag' => 'certificado',
        'expiry' => 'expiración',
        'identifier' => 'Número de serie - si no se ha introducido se le asignará automáticamente'
    ),
    "admin_assets.php" =>
    array('title' => 'Acquisto bene ammmortizzabile da fornitore',
        'mesg' => array('La ricerca non ha dato risultati!', 'Inserire almeno 2 caratteri', 'Cambia cliente/fornitore'),
        'info' => array('gg_to_year_end_1' => ' giorni mancano alla fine dell\'anno ',
            'gg_to_year_end_2' => ' quando potranno essere ammortizzati € '),
        'err' => array('regdat' => 'La data di registrazione precede quella della fattura',
            'numfat' => 'Non è stato introdotto il numero della fattura',
            'clfoco' => 'Non è stato scelto il fornitore',
            'pagame' => 'Non è stata scelta la modalità di pagamento',
            'datfat' => 'La data fattura è sbagliata',
            'datreg' => 'La data di registrazione è sbagliata',
            'mas_fixed_assets' => 'Non è stato scelto il mastro immobilizzazioni',
            'mas_found_assets' => 'Non è stato scelto il mastro fondo ammortamenti',
            'mas_cost_assets' => 'Non è stato scelto il mastro costo ammortamenti',
            'deduct_vat' => 'Non è stato scelta l\'aliquota IVA indetraibile',
            'deduct_cost' => 'Non è stato scelto il costo per la quota ammortamenti indeducibile',
            'descri' => 'Non è stata inserita una descrizione del bene acquistato',
            'ss_amm_min' => 'Non è stata inserita la sottospecie della tabella Ammortamenti Ministeriali'),
        'war' => array('update' => 'Sono consentite le modifiche solo ad alcuni campi perché'
            . ' l\'inserimento di questo cespite ha creato sul piano dei conti un sottoconto su ognuno dei'
            . ' tre mastri scelti per l\'immobilizzazione, il fondo ammortamento e per le quote di costi'
            . ' annuali per ammortamenti. Inoltre è stata fatta una registrazione contabile ed IVA '
            . ' pertanto se volete modificare gli altri dati dovrete agire in prima nota sul seguente'
            . ' movimento contabile'),
        'seziva' => "Sez. IVA",
        'indspe' => "Indirizzo",
        'datfat' => "Data fattura",
        'datreg' => "Data registr.",
        'pagame' => "Pagamento",
        'valamm' => "% ammortamento",
        'numfat' => "Numero fatt.",
        'mas_fixed_assets' => 'Immobilizzaz.',
        'mas_found_assets' => 'Fondo ammort.',
        'mas_cost_assets' => 'Costo ammort.',
        'des_fixed_assets' => 'Immobilizzazione ',
        'des_found_assets' => 'Fondo ammortamento ',
        'des_cost_assets' => 'Quota ammortamento ',
        'id_no_deduct_vat' => 'IVA indetraibile',
        'no_deduct_vat_rate' => '% IVA indetraibile',
        'acc_no_deduct_cost' => 'Costo indeducibile',
        'no_deduct_cost_rate' => '% quota indeducibile',
        'descri' => 'Descrizione bene',
        'quantity' => 'Quantità',
        'unimis' => 'Unità Misura',
        'a_value' => 'Prezzo',
        'codvat' => 'I.V.A.',
        'amount' => 'Costo totale del bene € ',
        'ss_amm_min' => 'Ammort.Minister.',
        'super_ammort'=>'% Super ammortamento'
    ),
    "accounting_documents.php" =>
    array('title' => 'Crear movimientos contables de los documentos tributables',
        'errors' => array('fecha incorrect',
            'No hay documentos para ser escritos en el seleccionado'
        ),
        'vat_section' => ' de seccion IVA  n.',
        'date' => 'hasts :',
        'type' => 'registro IVA ',
        'type_value' => array('A' => 'de Facturas de Compra'),
        'proini' => 'Protocolo Inicial',
        'profin' => 'Protocolo Final',
        'preview' => 'VistaPrevia Contabilidad',
        'date_reg' => 'Fecha',
        'protoc' => 'Protocolo',
        'doc_type' => 'Tipo',
        'doc_type_value' => array('FAD' => 'FACTURA DIFERIDA AL CLIENTE',
            'FAI' => 'FACTURA INMEDIATA AL CLIENTE',
            'FNC' => 'NOTA CREDITO  AL CLIENTE',
            'FND' => 'NOTA DEUDA AL CLIENTE',
            'VCO' => 'HONORARIOS',
            'VRI' => 'RECIBIDOS',
            'AFA' => 'FACTURA DE COMPRA',
            'AFC' => 'NOTA CREDITO DE COMPRA',
            'AFD' => 'NOTA DEUDA FDE COMPRA'
        ),
        'customer' => 'Proveedor',
        'taxable' => 'Tributable',
        'vat' => 'IVA',
        'stamp' => 'Sellos en las facturas',
        'tot' => 'Total'
    ),
    "report_schedule_acq.php" =>
    array('title' => 'Lista dei movimenti delle partite',
        'header' => array('ID' => 'id',
            'Identificativo partita' => "id_tesdoc_ref",
            'Movimento contabile apertura (documento)' => "id_rigmoc_doc",
            'Movimento contabile chiusura (pagamento)' => "id_rigmoc_pay",
            'Importo' => "amount",
            'Scadenza' => "expiry"
        ),
    ),
    "select_schedule_debt.php" =>
    array('title' => 'Selezione per la visualizzazione e/o la stampa delle partite aperte',
        'mesg' => array('La ricerca non ha dato risultati!',
            'Inserire almeno 2 caratteri!',
            'Cambia fornitore'
        ),
        'errors' => array('La data  non &egrave; corretta!',
            'Non sono stati trovati movimenti!'
        ),
        'account' => 'Fornitore ',
        'orderby' => 'Ordina per: ',
        'orderby_value' => array(0 => 'Scadenza crescente', 1 => 'Scadenza decrescente',
            2 => 'Fornitore crescente', 3 => 'Fornitore decrescente'
        ),
        'header' => array('Fornitore' => '', 'ID Partita' => '', 'Status' => '', 'Mov.Cont.' => '', 'Descrizione' => '',
            'N.Doc.' => '', 'Data Doc.' => '', 'Data Reg.' => '', 'Dare' => '', 'Avere' => '',
            'Scadenza' => '',
            'Opciones' => ''
        ),
        'status_value' => array(0 => 'APERTA', 1 => 'CHIUSA', 2 => 'ESPOSTA', 3 => 'SCADUTA', 9 => 'ANTICIPO'),
        'total_open' => 'Total de partidas abiertas'
    ),
    "delete_schedule.php" =>
    array('title' => 'Cancellazione movimenti chiusi dello scadenzario',
        'ragsoc' => 'Fornitore',
        'id_tesdoc_ref' => 'Identificativo partita',
        'descri' => 'Descrizione',
        'amount' => 'Importo'
    ),
    "select_suppliers_status.php" =>
    array('title' => 'Selezione per la visualizzazione e/o la stampa dello scadenzario dei fornitori',
        'print_title' => 'SCADENZARIO FORNITORI ',
        'errors' => array('La data  non &egrave; corretta!',
            'err' => 'Ci sono degli errori che impediscono la stampa'
        ),
        'date_ini' => 'Data di riferimento ',
        'header' => array('ID' => '', 'Importo apertura' => '', 'Data Scadenza' => '', 'Importo chiusura' => ''
            , 'Data chiusura' => '', 'Giorni esposizione' => '', 'Stato' => ''
        ),
        'status_value' => array(0 => 'APERTA', 1 => 'CHIUSA', 2 => 'ESPOSTA', 3 => 'SCADUTA', 9 => 'ANTICIPO'),
        'remove' => 'Elimina tutte le partite chiuse di '
    ),
    "delete_docacq.php" =>
    array('title' => 'Eliminazione Documento d\'Acquisto'),
    "supplier_payment.php" =>
    array('title' => 'Pagamento debito verso fornitore (chiusura partita/e)',
        'errors' => array('La data  non &egrave; corretta!'),
        'partner' => 'Fornitore ',
        'date_ini' => 'Data di registrazione',
        'target_account' => "Conto per il pagamento: ",
        'accbal' => 'Saldo risultante dai movimenti contabili: ',
        'paymovbal' => 'Saldo risultante dalle partite aperte: ',
        'header' => array('ID' => '', 'Importo apertura' => '', 'Data Scadenza' => '', 'Importo chiusura' => ''
            , 'Data chiusura' => '', 'Giorni esposizione' => '', 'Stato' => ''
        ),
        'status_value' => array(0 => 'APERTA', 1 => 'CHIUSA', 2 => 'ESPOSTA', 3 => 'SCADUTA', 9 => 'ANTICIPO'),
        'header' => array('ID' => '', 'Importo apertura' => '', 'Data Scadenza' => '', 'Importo chiusura' => ''
            , 'Data chiusura' => '', 'Giorni esposizione' => '', 'Stato' => '', 'Paga' => ''
        ),
        'mesg' => array('La ricerca non ha dato risultati!',
            'Inserire almeno 2 caratteri!',
            'Cambia fornitore',
            'Il saldo contabile è diverso da quello dello scadenzario,<br> se vuoi registrare la riscossione di un documento presente solo in contabilità fallo da qui:',
            'Nessun importo è stato pagato!',
            "Non è stato selezionato il conto per l'incasso",
            'Stai tentando di inserire il pagamento ad un fornitore senza movimenti'
        ),
        'descr_mov' => 'Descrizione del movimento<br>(vuoto per descrizione standard)',
		'transfer_fees_acc'=>'Conto spese bonifico',
		'transfer_fees'=>'Addebito per bonifico'
    )
);
?>
