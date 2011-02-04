<?php
/*
 --------------------------------------------------------------------------
                            GAzie - Gestione Azienda
    Copyright (C) 2004-2011 - Antonio De Vincentiis Montesilvano (PE)
                                (www.devincentiis.it)
                        <http://gazie.altervista.org>
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
 -- TRANSLATED BY : Dante Becerra Lagos (softenglish@gmail.com)
*/

$strScript = array ("select_comiva.php" =>
                   array(  "Comunicaci&oacute;n anual de los datos IVA  (archivo IVC)",
                           "DATOS GENERALES",
                           "Codigo de Impuesto",
                           "Nombre de la Empresa",
                           "Apellido",
                           "Nombre",
                           "A&ntilde;o Fiscal",
                           "Contribuyente",
                           "N° de Registro IVA",
                           "Contabilidad separada",
                           "Comunicaci&oacute;n por una empresa perteneciente a un grupo de IVA",
                           "Acontecimientos especiales",
                           "Declarante [COMPLETAR si es distinto del Contribuyente]",
                           "Codigo Fiscal de la empresa declarante",
                           "Codigo Fiscal",
                           "Codigo de Nombramiento",
                           "Agente de facto, legal, contractual, o miembro administrador ",
                           "Agente de un menor",
                           "Sindico (quiebra)",
                           "Administrador (custodia judicial)",
                           "Agente Fiscal de  una persona non-residente",
                           "El heredero",
                           "Liquidador (liquidaci&oacute;n voluntaria)",
                           "Una persona asignada a las operaciones extraordinarias",
                           "INFORMACION RELACIONADA CON OPERACIONES REALIZADAS",
                           "Codigo de actividad",
                           "TRANSACCION DE ACTIVOS",
                           "Total de transaccion de activos [neto de IVA]",
                           "de los cuales: transacciones no tributables",
                           "transacciones exentas",
                           "venta de bienes realizada dentro de la comunidad",
                           'attben'=>"Las ventas de equipo",
                           "TRANSACCION DE PASIVOS",
                           "Total detransaccion de pasivos [neto de IVA]",
                           "de los cuales: compras ni tributables",
                           "comprars exentas ",
                           "compra de bienes realizada dentro de la comunidad",
                           'pasben'=>"Las ventas de equipo",
                           "Importaci&oacute;n sin pagar el IVA sobre la entrada en ADUANAS - ORO INDUSTRIAL Y PLATA PURA",
                           "Importaci&oacute;n sin pagar el IVA sobre la entrada en ADUANAS - DESECHOS Y OTROS MATERIALES RECICLADOS",
                           "Tributable",
                           "Impuesto",
                           "C&aacute;LCULO DEL IMPUESTO ENTRADA O SALIDA",
                           "IVA ",
                           "Impuesto de Entrada",
                           "deducido",
                           "Impuesto de Salida",
                           "o impuesto de entrada"),
                   "select_chiape.php" =>
                   array(  'title'=>'Cuentas de Apertura y Cierre',
                           'errors'=>array('La fecha no es correcta!',
                                           'A la fecha de cierre no puede ser posterior a la fecha de apertura!',
                                           "Ya se ha realizado un cierre durante el periodo seleccionado!!",
                                           "Falta de balance en debito/credito de la comprobacion del libro general"
                                          ),
                           'date_closing'=>'Fecha de entrada de Cierre',
                           'date_opening'=>'Fecha de entrada de Apertura',
                           'closing_balance'=>"Balance de Cierre",
                           'economic_result'=>"Ingreso",
                           'operating_profit'=>"Ganacia del Periodio",
                           'operating_losses'=>"perdida del Periodio",
                           'opening_balance'=>"Balance de Apertura",
                           'closing'=>" CIERRE ",
                           'opening'=>" APERTURA ",
                           'economic'=>"ECONOMICO-A",
                           'code'=>"CODIGO",
                           'descr'=>"DESCRIPCION",
                           'exit'=>"DEUDA",
                           'entry'=>"CREDITO",
                           'of'=>" DE ",
                           'sheet'=>"HOJA",
                           'assets'=>"ACTIVOS",
                           'liabilities'=>"PASIVOS",
                           'costs'=>"COSTOS",
                           'revenues'=>"INGRESOS",
                           'acc_o'=>'CUENTAS APERTURA',
                           'acc_c'=>'CUENTAS CIERRE'
                           ),
                   "select_bilanc.php" =>
                   array(  "Balance - Libro de inventario",
                           "Fecha Periodo de Inicio",
                           "Fecha Periodo de Termino",
                           "Impresion Definitiva",
                           "Numero de la primera pagina",
                           "Descripcion",
                           " BALANCE ",
                           " DESDE EL ",
                           " HASTA EL ",
                           " SITUACION PATRIMONIAL  ",
                           " Ganancia ",
                           " Perdida ",
                           "ACTIVO",
                           "PASIVO",
                           "COSTOS",
                           "INGRESOS",
                           "TOTAL ",
                           " GANACIA & PERDIDA ",
                           " sucesiva a ",
                           "El balance actual esta en conformidad con los escritos de entrada de diario.",
                           "La elecci&oacute;n de la impresi&oacute;n en la actualizaci&oacute;n de la &uacute;ltima p&aacute;gina del campo 'de los libros  inventarios contables' de la empresa",
                           "Numero de la primera pagina a imprimir (por defecto: aquella escrita en los archivos de la empresa + 1)",
                           "Cuenta",
                           "Pagina ",
                           "Balance",
                           "Firma",
                           " a presentar : ",
                           " importe presentado :"
                           ),
                    "select_elencf.php" =>
                   array(  "Lista de Clientes y Proveedores",
                           "Lista de temas",
                           "Clientes",
                           "Proveedores",
                           "Lista",
                           "A&ntilde;o",
                           "No Imponible. art.8 c.2",
                           "Codigo Fiscal igual a 0",
                           "Codigo Fiscal errado para una persona",
                           "No tener el c&oacute;digo fiscal",
                           "Codigo Fiscal o una referencia a una persona jur&iacute;dica (G) errada",
                           "Codigo Fiscal o sexo (M) errado",
                           "Codigo Fiscal o sexo (F) errado",
                           "El Codigo Fiscal es formalmente incorrecto",
                           "El IVA es formalmente incorrecto",
                           "No tiene IVA",
                           "Domicilio Legal no es correcto, el formato adecuado debe ser como este ejemplo: Piazza del Quirinale, 41 00187 ROMA (RM)",
                           "Tipo impositivo del IVA con un impuesto igual a 0",
                           "Tipo de IVA que no establece un impuesto y que en lugar que no es distinto de 0",
                           "No se puede generar la Internet, porque e han encontrado errores para ser corregidos (ver m&aacute;s abajo)",
                           "CORRECTO !",
                           "Los movimientos de IVA no se han encontrado  volver reportar en la lista!",
                           "Totales",
                           "Hay algunos errores en la configuracion de los datos de la empresa !",
                           "Codigo Fiscal",
                           "IVA",
                           "Apellido",
                           "Nombre",
                           "Sexo",
                           "Fecha de Nacimiento",
                           "Comuna de Nacimiento",
                           "Region de Nacimiento",
                           "Titulo",
                           "Comuna",
                           "Region"
                           ),
                    "error_protoc.php" =>
                   array(  'title'=>'Numeraci&oacute;n de verificaci&oacute;n de registro de IVA',
                           'year'=>' del a&ntilde;o ',
                           'header'=>array('ID'=>'','Fecha '=>'','Seccion'=>'','Registro'=>'','Protocolo'=>'','Causal'=>'','Descripcion'=>''
                                          ),
                           'pre_dd'=>' de ',
                           'expect'=>' esperado '
                           )
                          );
?>