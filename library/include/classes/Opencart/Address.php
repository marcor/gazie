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
  --------------------------------------------------------------------------
 */

namespace Opencart;

class Address  {
	
  private $address_id;
  private $customer_id;
  private $firstname;
  private $lastname;
  private $company;
  private $address_1;
  private $address_2;
  private $city;
  private $postcode;
  private $country_id;
  private $zone_id;
  private $custom_field;

  public function __construct( \Opencart\Customer $customer = NULL ) {
	  if ( $customer )
	 	$this->customer_id = $customer->getCustomerId();
  }

  public function getAddressId() {
	return $this->address_id;
  }

  public function getCustomerId() {
	return $this->customer_id;
  }

  public function getFirstname() {
	return $this->getFirstname();
  }

  public function getLastname() {
	return $this->getLastname();
  }
 
  public function getCompany() {
	return $this->company;
  }

  public function getAddress1() {
	return $this->address_1;
  }

  public function getAddress2() {
	return $this->address_2;
  }

  public function getCity() {
	return $this->city;
  }

  public function getPostcode() {
	return $this->postcode;
  }

  public function getCountryId() {
	return $this->country_id;
  }

  public function getZoneId() {
	return $this->zone_id;
  }

  public function getCustomField() {
	return $this->custom_field;
  }

  public function setAddressId($address_id) {
	$this->address_id=$address_id;
  }

  public function setCustomerId($customer_id) {
	$this->customer_id=$customer_id;
  }

  public function setFirstname($firstname) {
	$this->getFirstname=$firstname;
  }

  public function setLastname($lastname) {
	$this->getLastname=$lastname;
  }
 
  public function setCompany($company) {
	$this->company=$company;
  }

  public function setAddress1($address_1) {
	$this->address_1=$address_1;
  }

  public function setAddress2($address_2) {
	$this->address_2=$address_2;
  }

  public function setCity($city) {
	$this->city = $city;
  }

  public function setPostcode($postcode) {
	$this->postcode = $postcode;
  }

  public function setCountryId($country_id) {
	$this->country_id = $country_id;
  }

  public function setZoneId( $zone_id ) {
	$this->zone_id = $zone_id;
  }

  public function setCustomField( array $fields ) {
	$this->custom_field = $fields;
  }


}


}
?>
