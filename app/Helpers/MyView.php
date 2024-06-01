<?php

function writeIDFormat($val)
{
  return rtrim(rtrim((string)number_format($val, 2, ",", "."),"0"),",");
}

function myDateFormat($millis,$format="Y-m-d H:i:s")
{
    return date($format,round($millis/1000));
}
