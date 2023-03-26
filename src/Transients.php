<?php

namespace BeverageLocator;

class Transients
{
  protected static function getTransientName($n)
  {
    return "beverage_locator_{$n}";
  }

  public static function getTransient($transientName)
  {
    return get_transient(static::getTransientName($transientName));
  }

  public static function setTransient($transientName, $value, $expiration = 60 * 60 * 24)
  {
    return set_transient(static::getTransientName($transientName), $value, $expiration);
  }

  public static function memoize($transientName, $callback, $expiration = 60 * 60 * 24)
  {
    $transient = static::getTransient($transientName);
    if ($transient === false || isset($_GET['_flush'])) {
      $transient = $callback();
      static::setTransient($transientName, $transient, $expiration);
    }
    return $transient;
  }
}
