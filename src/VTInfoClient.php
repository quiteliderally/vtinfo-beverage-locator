<?php

namespace BeverageLocator;

class VTInfoClient
{
  protected $key = '';
  protected $cust_id = '';
  public function __construct($cust_id, $key)
  {
    $this->key = $key;
    $this->cust_id = $cust_id;
  }

  protected function getCurl($query = [])
  {
    $url = "https://www.vtinfo.com/PF/product_finder-service.asp";
    $qs = http_build_query($query);
    $ch = curl_init($url . "?" . $qs);

    $ts = $this->getTimeStamp();
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
      'vipCustID: ' . $this->cust_id,
      'vipTimestamp: ' . $ts,
      'vipSignature: ' .  hash('sha256', $ts . $this->key . $qs . $this->cust_id),
    ]);

    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    return $ch;
  }

  protected function getTimeStamp()
  {
    return gmdate('D, j M Y H:i:00 T');
  }

  public function doAction($action, $args = [])
  {
    $args = array_merge($args, [
      'action' => $action,
    ]);
    $args = $this->cleanArgs($args);

    error_log("BeverageLocator: performing API request: " . print_r($args, true), 1);
    $ch = $this->getCurl($args);
    return curl_exec($ch);
  }

  public function getBrands($args = [])
  {

    $output = $this->doAction('brands', $args);
    $xml = simplexml_load_string($output);

    $brands = [];

    foreach ($xml->brands->brand as $brand) {
      $name = (string) $brand;
      $brands[] = [
        'name' => (string) $brand,
      ];
    }

    $locations = [];

    foreach ($xml->locations->location as $location) {
      $locations[] = [
        'name' => (string) $location,
        'code' => (string) $location['code'],
      ];
    }

    return [
      'locations' => $locations,
      'brands' => $brands,
    ];
  }

  public function getCategories($id)
  {
    $output = $this->doAction("category{$id}");
    $xml = simplexml_load_string($output);

    $categories = [];

    foreach ($xml->categories->category as $category) {
      $categories[] = [
        'name' => (string) $category,
      ];
    }

    return $categories;
  }

  public function findLocations($args = [])
  {
    $args = array_merge([
      'pagesize' => 10
    ], $args);

    $output = $this->doAction('results', $args);

    $xml = simplexml_load_string($output);

    $locations = [];
    if ($xml->locations) {
      foreach ($xml->locations->location as $location) {
        $locations[] = [
          'name' => (string) $location->dba,
          'street' => (string) $location->street,
          'city' => (string) $location->city,
          'state' => (string) $location->state,
          'zip' => (string) $location->zip,
          'lat' => (string) $location->lat,
          'lng' => (string) $location->long,
          'distance' => (string) $location->distance,
          'phone' => (string) $location->phone['formatted'],
          'storeType' => (string) $location->storeType,
        ];
      }
    }

    return [
      'miles' => (int)$xml->input->miles,
      'total' => (int)$xml->total,
      'page' => (int)$xml->page,
      'start' => (int)$xml->start,
      'end' => (int)$xml->end,
      'locations' => $locations,
    ];
  }

  protected function cleanArgs($args = [])
  {
    $output = [];
    foreach (array_keys($args) as $k) {
      if ($k == 'location') {
        $_k = 'storeType';
      } else {
        $_k = $k;
      }
      if (isset($args[$k]) && is_array($args[$k])) {
        $output[$_k] = implode(',', $args[$k]);
      } else if (isset($args[$k])) {
        $output[$_k] = $args[$k];
      }
    }

    return array_filter($output);
  }
}
