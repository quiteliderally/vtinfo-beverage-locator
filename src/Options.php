<?php

namespace BeverageLocator;

class Options
{
  protected static function getOptionName($n)
  {
    return "beverage_locator_{$n}";
  }

  public static function getAllOptionNames()
  {
    return [
      'api_key',
      'customer_id',
      'limited_category1',
      'limited_category2',
      'limited_category3',
      'limited_category4',
      'limited_category5',
      'limited_category6',
      'limited_category7',
      'limited_category8',
      'limited_category9',
      'limited_category10',
      'limited_category11',
      'limited_category12',
      'limited_location',
      'limited_brand',
    ];
  }

  public static function getAllOptions()
  {
    $options = [];
    foreach (static::getAllOptionNames() as $optionName) {
      $options[$optionName] = static::getOption($optionName);
    }
    return $options;
  }

  public static function updateAllFromRequest($request)
  {
    foreach (static::getAllOptionNames() as $optionName) {
      if (preg_match('/limited/i', $optionName)) {
        if (!array_key_exists($optionName, $request)) {
          $request[$optionName] = [];
        }
      }
    }
    foreach ($request as $k => $v) {
      static::setOption($k, $v);
    }
  }

  public static function getDefaultAPIBrandArgs()
  {
    $default_args = [];

    foreach (static::getAllOptionNames() as $optionName) {
      if (preg_match('/limited_(\w+)/i', $optionName, $matches)) {
        $values = static::getOption($optionName, []);
        if (is_array($values) && count($values) > 0) {
          $default_args[$matches[1]] = $values;
        }
      }
    }
    return $default_args;
  }

  public static function isLimitedValue($select, $value)
  {
    static $cache = [];
    if (!array_key_exists($select, $cache)) {
      $cache[$select] = static::getOption("limited_{$select}", []);
      if (!is_array($cache[$select])) {
        $cache[$select] = [];
      }
    }
    return in_array($value, $cache[$select]);
  }

  public static function getOption($optionName, $default = null)
  {
    return get_option(static::getOptionName($optionName), $default);
  }

  public static function setOption($optionName, $value)
  {
    return update_option(static::getOptionName($optionName), $value);
  }
}
