#### ATTIVAZIONE
Per attivare questa sincronizzazione, in configurazione utente si deve attivare il modulo "Sincronizza e-commerce". Poi si consiglia di attivare anche il relativo widget in home page.
####   DOWNLOAD ORDINI
Prima dell'uso, nelle righe iniziali della configurazione avanzata azienda di GAzie, si dovranno inserire i dati di accesso FTP all'host del sito internet, su Website root directory si dovrà inserire il percorso della cartella su cui si inserira l'interfaccia, ad esempio: https://www.tuosito.it/syncro/ (è importante che sia presente la barra finale).
 Il file di interfaccia che si inserirà nella suddetta cartella si dovrà chiamare: ordini-gazie.php 
  Per evitare intrusioni indesiderate, l'interfaccia di GAzie, al momento di connettersi con quella di Joomla, passa, tramite l'url, una password. Tale
password è, per comodità, la stessa che GAzie ha per l' FTP e che viene memorizzata su configurazione avanzata azienda. L'interfaccia del sito dovrà, quindi,
fare un controllo sulla password che ha ricevuto da GAzie e, se confermata, si avvierà creando un file xml.

  Il file creato dovrà essere formattato come segue:

    <?xml version="1.0" encoding="ISO-8859-1"?>
    <GAzieDocuments AppVersion="2" Creator="Antonio Germani 2018-2019" CreatorUrl="https://www.lacasettabio.it">   
    <Documents>    
        <Document>
        <CustomerCode></CustomerCode>
        <CustomerEmail></CustomerEmail>
        <CustomerName></CustomerName>
        <CustomerSurname></CustomerSurname>
        <CustomerAddress></CustomerAddress>
        <CustomerPostCode></CustomerPostCode>
        <CustomerCity></CustomerCity>
        <CustomerProvince></CustomerProvince>
        <CustomerCountry></CustomerCountry>
        <CustomerFiscalCode></CustomerFiscalCode>
        <CustomerVatCode></CustomerVatCode>
        <CustomerTel></CustomerTel>
        <CustomerCellPhone></CustomerCellPhone>
        <CustomerEmail></CustomerEmail>
        <CustomerPecEmail></CustomerPecEmail>
        <CustomerCodeFattEl></CustomerCodeFattEl>
        <Warehouse></Warehouse>
        <DateOrder></DateOrder>
        <Number></Number>
        <Numbering></Numbering>
        <TotalWithoutTax></TotalWithoutTax>
        <VatAmount></VatAmount>
        <WithholdingTaxAmount>0</WithholdingTaxAmount>
        <Total></Total>
        <PriceList></PriceList>
        <PricesIncludeVat></PricesIncludeVat>
        <WithholdingTaxPerc></WithholdingTaxPerc>
        <PaymentName></PaymentName>
        <PaymentBank></PaymentBank>
        <PaymentBank></PaymentBank>
        <Payments>
         <Payment>
        <Advance></Advance>
        <Amount></Amount>
        <Paid></Paid>
        </Payment>
        </Payments>
        <Carrier></Carrier>
        <CostShippingDescription></CostShippingDescription>
        <CostShippingAmount></CostShippingAmount>
        <CostPaymentDescription></CostPaymentDescription>
        <CostPaymentAmount></CostPaymentAmount>
        <CostVatCode></CostVatCode>
        <TransportReason></TransportReason>
        <InternalComment></InternalComment>
        <CustomField1></CustomField1>
        <CustomField2></CustomField2>
        <CustomField3></CustomField3>
        <CustomField4></CustomField4>
        <CustomField4></CustomField4>
        <FootNotes></FootNotes>
        <SalesAgent></SalesAgent>
        <Rows>
            <Row>
            <Code></Code>
            <Description></Description>
            <Qty></Qty>
            <MeasureUnit></MeasureUnit>
            <Price></Price>
            <Discounts></Discounts>
            <VatCode></VatCode>
            <Total></Total>
            <Stock></Stock>
            </Row>
        </Rows>
        </Document>

Ogni `<Document>`  è la testata dell'ordine mentre le `<Row>` in esso contenute sono gli articoli ordinati.

A causa di eventuali aggiornamenti si consiglia di controllare il codice per vedere se sono stati apportati dei cambiamenti.

#### UPLOAD QUANTITA' ARTICOLI
  Per evitare intrusioni indesiderate, l'interfaccia di GAzie, al momento di connettersi con quella di Joomla, passa, tramite l'url, una password. Tale password è, per comodità, la stessa che GAzie ha per l' FTP e che viene memorizzata su configurazione avanzata azienda.
  L'interfaccia di Joomla dovrà, quindi, fare un controllo sulla password che ha ricevuto da GAzie e, se confermata, aggiornerà il database del negozio online.
Prima dell'uso, se ancora non è stato fatto, nelle righe iniziali della configurazione avanzata azienda di GAzie, si dovranno inserire i dati di accesso FTP all'host del sito Joomla nonché il percorso per raggiungere i file di interfaccia presenti in Joomla. Il file per l'upload degli articoli si dovrà chiamare "articoli-gazie.php". Al contrario dell'download ordini, nel caso dell'upload sarà l'interfaccia presente su GAzie a creare il file xml. Questo file sarà poi trasferito via FTP sul sito joomla dove, attraverso
la seconda interfaccia ivi residente, i dati saranno processati e verrà aggiornato il database del negozio online.
La formattazione del file xml è la seguente:

    <?xml version="1.0" encoding="ISO-8859-1"?>
    <GAzieDocuments AppVersion="1" Creator="Antonio Germani 2018-2019" CreatorUrl="https://www.lacasettabio.it">
    <Products>
        <Product>
        <Code></Code>
        <BarCode></BarCode>
        <AvailableQty></AvailableQty>
        </Product>
    </Products>
    </GAzieDocuments>

L'aggiornamento delle quantità avverrà solo se verrà trovata corrispondenza con il codice e/o barcode dell'articolo. Articoli con codici non corrispondenti verranno ignorati.
Altresì, verranno ignorati gli articoli "servizio" di GAzie e quelli dove non sarà stato abilitato il campo "pubblica su sito web".
 A causa di eventuali aggiornamenti si consiglia di controllare il codice per vedere se sono stati apportati dei cambiamenti.

####  FILE DI INTERFACCIA DA INSERIRE NEL SITO 
  I file di interfaccia da inserire nel sito devono essere creati in base alle esigenze e alle impostazioni dell'applicazione installata nel sito.
 Nella cartella di GAzie shop_synchronize > interfacce per Joomla ci sono degli esempi da cui partire.
 Se non si ha dimestichezza nell'adattare i file si prega di richiedere assistenza privata specifica.