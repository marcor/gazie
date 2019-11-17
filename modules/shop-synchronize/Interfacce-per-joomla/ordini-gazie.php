<?php
/* ------------------------------------------------------------------------
  Download ordini da Hikashop a GAzie
  ------------------------------------------------------------------------
  @Author    Antonio Germani
  @Website   http://www.lacasettabio.it
  @Copyright Copyright (C) 2017 - 2019 Antonio Germani All Rights Reserved.
  versione 1.0
  ****
  Per modifiche o personalizzazioni contattarmi al 340-5011912 oppure lacasettabio@lacasettabio.it
  E' possibile gestire altri tipi di negozi online così come applicazioni per hotel o case vacanze
  ****
   ------------------------------------------------------------------------ */
  
/* impostazioni da fare prima di avviare il download da GAzie
inserire i dati dentro alle virgolette non toccare il resto */

$host = "localhost"; /* host database, di solito localhost */
$user = "++"; /* nome utente database */
$pass = "++"; /* password database */
$database = "++"; /* nome database */
$hostname="++"; /*nome host per ftp, lo stesso inserito su GAzie */
$passftp="++"; /* password per ftp, la stessa inserita su GAzie */
$orderstatus="Pronto in attesa del corriere"; /* nome o tipo di stato deve avere l'ordine di Hikashop per essere scaricato su GAzie, di solito confirmed */
$orderstatus2="Pagato"; /* eventuale secondo stato ordine  */
$orderstatus3="confirmed"; /* eventuale terzo stato ordine  */
$includevat="true"; /*  true= il prezzo è iva compresa - false= il prezzo è iva esclusa */
$prefixdb="++"; /* il prefisso delle tabelle del database */
$pricelist="++"; /* il numero, impostato su Gazie, del listino prezzi */
+

  ------------------------------------------------------------------------ */

/* da qui in poi non toccare più nulla!!! */
$access=$_GET['access'];
$password=base64_decode($access);
If ($password==$passftp){
header("Content-Type: text/xml; charset=ISO-8859-1");
$linkID = new mysqli($host, $user, $pass, $database) or die("Could not connect to host.");
$query = "SELECT * FROM ".$prefixdb."_hikashop_order WHERE order_status = '$orderstatus' OR order_status = '$orderstatus2' OR order_status = '$orderstatus3' ORDER BY order_id";     
$resultID = $linkID->query($query) or die("Data not found1.");
$xml_output .= <<<XYZ
<?xml version="1.0" encoding="ISO-8859-1"?>
<GAzieDocuments AppVersion="2" Creator="Antonio Germani 2018-2019" CreatorUrl="https://www.lacasettabio.it"> 
  
  <Documents>
XYZ;
for($x = 0 ; $x < mysqli_num_rows($resultID) ; $x++){
    $row = mysqli_fetch_assoc($resultID);
    $xml_output .= "\t \n<Document>\n";
    $xml_output .= "\t<CustomerCode>".$row['order_user_id']."</CustomerCode>\n";
	$queryuser = "SELECT * FROM ".$prefixdb."_hikashop_user WHERE user_id = ".$row['order_user_id'];
	$resultuser = $linkID->query($queryuser) or die("Data not found user.");
	$rowuser = mysqli_fetch_assoc($resultuser);
	$xml_output .= "\t<CustomerEmail>".$rowuser['user_email']."</CustomerEmail>\n";
$queryuser = "SELECT * FROM ".$prefixdb."_hikashop_address WHERE address_user_id = ".$row['order_user_id'];
$resultuser = $linkID->query($queryuser) or die("Data not found user.");
    $rowuser = mysqli_fetch_assoc($resultuser);
	$xml_output .= "\t<CustomerName>".$rowuser['address_firstname']."</CustomerName>\n";
	$xml_output .= "\t<CustomerSurname>".$rowuser['address_lastname']."</CustomerSurname>\n";
    $xml_output .= "\t<CustomerAddress>".$rowuser['address_street']."</CustomerAddress>\n";
    $xml_output .= "\t<CustomerPostCode>".$rowuser['address_post_code']."</CustomerPostCode>\n"; 
	$xml_output .= "\t<CustomerCity>".$rowuser['address_city']."</CustomerCity>\n";
	$province=$rowuser['address_state']; $result = explode("_", $province);
    $xml_output .= "\t<CustomerProvince>".$result[1]."</CustomerProvince>\n";
	$country=$rowuser['address_country']; $result = explode("_", $country);
	if ($result[1]=="Ita"){
		$country="IT";
	} else {
		$country="EN";
	}
    $xml_output .= "\t<CustomerCountry>".$country."</CustomerCountry>\n";
	$xml_output .= "\t<CustomerFiscalCode>".$rowuser['cod_fisc']."</CustomerFiscalCode>\n";
    $xml_output .= "\t<CustomerVatCode>".$rowuser['address_vat']."</CustomerVatCode>\n";
    $xml_output .= "\t<CustomerTel>".$rowuser['address_telephone']."</CustomerTel>\n";
	$xml_output .= "\t<CustomerCellPhone>".$rowuser['address_telephone2']."</CustomerCellPhone>\n";
	$xml_output .= "\t<CustomerPecEmail>" . $rowuser['pec'] . "</CustomerPecEmail>\n";
	$xml_output .= "\t<CustomerCodeFattEl>" . $rowuser['fatt_el'] . "</CustomerCodeFattEl>\n";
	
$query2 = "SELECT * FROM ".$prefixdb."_hikashop_order WHERE order_id = ".$row['order_id']." ORDER BY order_id";
$resultID2 = $linkID->query($query2) or die("Data not found2.");         
  for($y = 0 ; $y < mysqli_num_rows($resultID2) ; $y++){
      $inner = mysqli_fetch_assoc($resultID2);
      $xml_output .= "\t<Warehouse>".$warehouse."</Warehouse>\n";
      $xml_output .= "\t<DateOrder>". date("Y-m-d", $inner['order_created'])."</DateOrder>\n";
      $xml_output .= "\t<Number>".$inner['order_created']."</Number>\n";
      $xml_output .= "\t<Numbering></Numbering>\n";  
	  $xml_output .= "\t<TotalWithoutTax></TotalWithoutTax>\n";
      $xml_output .= "\t<VatAmount>".$row['order_tax_price']."</VatAmount>\n";
      $xml_output .= "\t<WithholdingTaxAmount>0</WithholdingTaxAmount>\n";
      $xml_output .= "\t<Total>".$row['order_full_price']."</Total>\n";
      $xml_output .= "\t<PriceList>".$pricelist."</PriceList>\n";
      $xml_output .= "\t<PricesIncludeVat>".$includevat."</PricesIncludeVat>\n";
      $xml_output .= "\t<WithholdingTaxPerc>0</WithholdingTaxPerc>\n";
  $order_payment_method=$row['order_payment_method'];
if ($order_payment_method == "banktransfer") {$order_payment_method="19";} 
if ($order_payment_method == "stripe" || $order_payment_method == "paypal") {$order_payment_method="18";} 
if ($order_payment_method == "collectondelivery" || $order_payment_method == "none") {$order_payment_method="1";}
	$xml_output .= "\t<PaymentName>".$order_payment_method."</PaymentName>\n";             
        $xml_output .= "\t<PaymentBank></PaymentBank>\n";
        $xml_output .= "\t<PaymentBank></PaymentBank>\n"; 
        $xml_output .= "\t<Payments>\n \t<Payment>\n";                    
        $xml_output .= "\t<Advance>false</Advance>\n";
        
        $xml_output .= "\t<Amount>".$row['order_full_price']."</Amount>\n";
        $xml_output .= "\t<Paid>".$inner['order_status']."</Paid>\n";
        $xml_output .= "\t</Payment>\n";                      
        $xml_output .= "\t</Payments>\n";
      $xml_output .= "\t<Carrier>".$row['order_shiping_id']."</Carrier>\n";
     
  
		$xml_output .= "\t<CostShippingDescription>Spese di trasporto</CostShippingDescription>\n";  
		$shipping = $row['order_shipping_price'];     
		$xml_output .= "\t<CostShippingAmount>".$shipping."</CostShippingAmount>\n";
	 
		$xml_output .= "\t<CostPaymentDescription>Spese d'incasso</CostPaymentDescription>\n";
		$shipping = $row['order_payment_price'];     
		$xml_output .= "\t<CostPaymentAmount>".$shipping."</CostPaymentAmount>\n";
	  
		$xml_output .= "\t<CostVatCode>22</CostVatCode>\n"; 
      $xml_output .= "\t<TransportReason>C/Vendita</TransportReason>\n";    
      $xml_output .= "\t<InternalComment>".$row['customer_note']."</InternalComment>\n";
      $xml_output .= "\t<CustomField1></CustomField1>\n
      <CustomField2></CustomField2>\n
      <CustomField3></CustomField3>\n
      <CustomField4></CustomField4>\n
      <CustomField4></CustomField4>\n
      <FootNotes></FootNotes>\n
      <SalesAgent></SalesAgent>\n<Rows>\n";  	  
  $query5 = "SELECT * FROM ".$prefixdb."_hikashop_order_product WHERE order_id = ".$inner['order_id']." ORDER BY order_id";
  $resultID5 = $linkID->query($query5) or die("Data not found3.");
    for($u = 0 ; $u < mysqli_num_rows($resultID5) ; $u++){
      $uinner = mysqli_fetch_assoc($resultID5);  
      $xml_output .= "\t<Row>\n";
      $xml_output .= "\t<Code>".$uinner['order_product_code']."</Code>\n";
	  $description=$uinner['order_product_name']; $result = explode("<", $description);
	  $xml_output .= "\t<Description>".$description."</Description>\n";
	  $xml_output .= "\t<Qty>".$qty."</Qty>\n";
	  $xml_output .= "\t<MeasureUnit>"."n."."</MeasureUnit>\n";
      $xml_output .= "\t<Price>".$uinner['order_product_price']."</Price>\n";
      $xml_output .= "\t<Discounts></Discounts>\n"; 
      $xml_output .= "\t<VatCode></VatCode>\n";
      $totalp = $uinner['order_product_price']*$qty;
      $xml_output .= "\t<Total>".$totalp."</Total>\n";
      $xml_output .= "\t<Stock>false</Stock>\n";
      $xml_output .= "\t</Row>\n";     
    } 
      $xml_output .= "\t</Rows>\n";                
  }
    $xml_output .= "\n</Document>";
}
$xmlFile = "ordini.xml";
$xmlHandle = fopen($xmlFile, "w");
fwrite($xmlHandle, $xml_output);
fclose($xmlHandle);
 echo $xml_output."
 \n</Documents>\n
</GAzieDocuments>";
} else {
	header ("HTTP/1.1 400 Bad request");
	echo "Password non valida";
	exit;
}
?>
                            