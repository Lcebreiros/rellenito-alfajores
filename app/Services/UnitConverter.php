<?php

// app/Services/UnitConverter.php
namespace App\Services;

use InvalidArgumentException;

class UnitConverter {
  public static function factorToBase(string $from, string $base): float {
    $from = strtolower($from); $base = strtolower($base);
    $mass = ['g'=>1, 'kg'=>1000];
    $vol  = ['ml'=>1, 'l'=>1000, 'cm3'=>1];
    $u    = ['u'=>1, 'unidad'=>1];

    if ($base === 'g'  && isset($mass[$from])) return $mass[$from];
    if ($base === 'ml' && isset($vol[$from]))  return $vol[$from];
    if ($base === 'u'  && isset($u[$from]))    return $u[$from];

    throw new InvalidArgumentException("No conversion from $from to base $base");
  }
}
