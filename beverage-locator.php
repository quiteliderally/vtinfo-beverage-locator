<?php
/*
Plugin Name: Beverage Locator
Plugin URI: https://github.com/quiteliderally/
Description: A plugin to connect to the vtinfo api and locate products
Version: 0.1
Author: Tim Ariyeh
Author URI: https://github.com/quiteliderally/
License: GPL2
*/



if (!defined('ABSPATH')) {
  exit;
}

require_once(__DIR__ . "/src/Options.php");
require_once(__DIR__ . "/src/Transients.php");

use BeverageLocator\Options;
use BeverageLocator\Transients;


function beverage_locator_menu()
{
  add_options_page(
    'Beverage Locator Options',
    'Beverage Locator',
    'manage_options',
    'beverage-locator-options',
    'beverage_locator_options_page'
  );
}
add_action('admin_menu', 'beverage_locator_menu');


function beverage_locator_get_category($id, $limit = true)
{
  $key = "category_{$id}";
  $resp = [];

  $limits = Options::getOption("limited_category{$id}", []);
  if ($limit && !empty($limits)) {
    $options = $limits;
  } else {
    $options = Transients::memoize($key, function () use ($id, $limit) {
      $client = beverage_locator_api_client();
      if (!$client) {
        return [];
      }
      $resp = [];

      $output = $client->getCategories($id);
      foreach ($output as $category) {
        $resp[] = [
          'label' => is_array($category) ? $category['name'] : $category,
          'value' => is_array($category) ? $category['name'] : $category,
        ];
      }

      return $resp;
    });
  }

  foreach ($options as $category) {
    $resp[] = [
      'label' => is_array($category) ? $category['value'] : $category,
      'value' => is_array($category) ? $category['label'] : $category,
    ];
  }

  return $resp;
}

function beverage_locator_api_client()
{
  $customer_id = get_option('beverage_locator_customer_id', '');
  $api_key = get_option('beverage_locator_api_key', '');

  if (empty($customer_id) || empty($api_key)) {
    return false;
  }
  require_once(__DIR__ . '/src/VTInfoClient.php');
  $client = new BeverageLocator\VTInfoClient($customer_id, $api_key);
  return $client;
}



function beverage_locator_get_brands($args = [])
{

  $default_args = Options::getDefaultAPIBrandArgs();

  $args = array_merge($default_args, $args);
  $key = "brands_" . crc32(json_encode($args));

  return Transients::memoize($key, function () use ($args) {
    $client = beverage_locator_api_client();
    if (!$client) {
      return [];
    }

    $output = $client->getBrands($args);
    $resp = [];
    foreach ($output['brands'] as $brand) {
      $resp[] = [
        'label' => $brand['name'],
        'value' => $brand['name'],
      ];
    }
    return $resp;
  }, 60 * 60 * 24);
}

function beverage_locator_get_locations()
{
  return Transients::memoize('locations', function () {
    $client = beverage_locator_api_client();
    if (!$client) {
      return [];
    }
    $default_args = Options::getDefaultAPIBrandArgs();

    $output = $client->getBrands($default_args);
    $resp = [];
    foreach ($output['locations'] as $location) {
      $resp[] = [
        'label' => $location['name'],
        'value' => $location['code'],
      ];
    }
    return $resp;
  }, 60 * 60 * 24);
}



function beverage_locator_options_page()
{
  if (!current_user_can('manage_options')) {
    wp_die(__('You do not have sufficient permissions to access this page.'));
  }

  if (isset($_POST['submit'])) {
    Options::updateAllFromRequest($_POST['options']);
  }

  $options = Options::getAllOptions();
  extract($options);


  require __DIR__ . "/templates/options.php";
}


function beverage_locator_get_options($select)
{
  $options = [];

  if (preg_match('/category(\d+)/i', $select, $matches)) {
    $id = $matches[1];
    $options = beverage_locator_get_category($id);
  } else if ($select == 'locations') {
    $options = beverage_locator_get_locations();
  } else if ($select == 'brands') {
    $options = beverage_locator_get_brands();
  }

  return $options;
}

function beverage_locator_get_admin_options($select)
{
  $options = [];

  if (preg_match('/category(\d+)/i', $select, $matches)) {
    $id = $matches[1];
    $options = beverage_locator_get_category($id, false);
  } else if ($select == 'locations') {
    $options = beverage_locator_get_locations();
  } else if ($select == 'brands') {
    $options = beverage_locator_get_brands();
  }

  return $options;
}

function beverage_locator_is_limited_option($select, $value)
{
  static $cache = [];
  if (!array_key_exists($select, $cache)) {
    $cache[$select] = Options::getOption("limited_{$select}", []);
    if (!is_array($cache[$select])) {
      $cache[$select] = [];
    }
  }
  return in_array($value, $cache[$select]);
}

function beverage_locator_search($args)
{
  $client = beverage_locator_api_client();
  if (!$client) {
    return [];
  }
  $default_args = Options::getDefaultAPIBrandArgs();
  $args = array_merge($default_args, $args);
  $output = $client->findLocations($args);
  return $output;
}
