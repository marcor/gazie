<?php
class Compute
{
  function stampTax($value,$percent,$cents_ceil_round=5)
  {
  if ($cents_ceil_round==0) {
      $cents_ceil_round=5;
  }
  $cents=100*$value*($percent/100+$percent*$percent/10000);
  if ($cents_ceil_round<0) { // quando passo un arrotondamento negativo ritorno il valore di $percent
     return round($percent,2);
  } else {
     return round(ceil($cents/$cents_ceil_round)*$cents_ceil_round/100,2);
  }
  }
}
?>