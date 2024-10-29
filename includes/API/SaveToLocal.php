<?php

namespace MEC__CreateProducts\API;

use MEC__CreateProducts\Utils\Utils;

class SaveToLocal
{
  private $target_url;
  private $basefilePath;
  private $log = null;

  public function __construct($url = '', $basefilePath = '')
  {
    $this->log = Utils::getLogger();
    if ($url != '') {
      $this->setTarget($url);
    }

    if ($basefilePath != '') {
      $this->basefilePath = $basefilePath;
    }
  }
  // Set Target External Json URL
  public function setTarget($url)
  {
    if ($this->target_url === null) {
      $this->target_url = $url;
    }
    return $this->target_url;
  }

  // Function: Save JSON from the given URL as a text file in the same directory where this class exists
  public function saveJsonToFile()
  {
    if ($this->target_url === null) {
      return 'Error: Target URL is not set.';
    }

    // Fetch JSON data from the target URL
    $response = wp_remote_get($this->target_url, array(
      'timeout' => 45,
    ));

    // Handle errors
    if (is_wp_error($response)) {
      Utils::putLog('Error: ' . $response->get_error_message());
      return 'Error: ' . $response->get_error_message();
    }

    $body = wp_remote_retrieve_body($response);

    // Check if it's valid JSON
    if (!$this->isinValidJson($body)) {
      Utils::putLog('Error: Invalid JSON data retrieved.');
      return 'Error: Invalid JSON data retrieved.';
    }
    // Save JSON data to a file
    file_put_contents($this->basefilePath . '_raw.json', $body);
    $this->create_products_all($body);

    Utils::putLog('Success: JSON saved to ' . $this->basefilePath . '_raw.json');
    return $this->basefilePath;
  }

  public function create_products_all($encoded_json)
  {
    $rawdata = json_decode($encoded_json, true);
    $data = $rawdata['products_data'];
    $products = [];
    foreach ($data as $sku => $product) {
      $products[$sku] = [
        'name'        => $product['name'],
        'relation'    => explode(';', $product['freifeld6']), // [0] master or parent, [2] attribute 
        'compatible'  => explode(';', preg_replace("/\r\n|;$/", "",  $product['taxonomyField'])), // mdell, marke, hub. year
        'info'        => [
          'description' => $product['name'],
          'image'       => $product['image']
        ]
      ];
    }
    file_put_contents($this->basefilePath . '_all.json', json_encode($products, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
  }

  public function getFilePath()
  {
    // Check if the file exists
    if (file_exists($this->basefilePath . '_raw.json')) {
      // Get the file modification time as a Unix timestamp
      date_default_timezone_set('Europe/Berlin');
      $fileModificationTime = filemtime($this->basefilePath . '_raw.json');

      // Format the timestamp into a readable date and time format
      $formattedTime = date("Y-m-d H:i:s", $fileModificationTime);

      return $formattedTime;
    } else {
      return "File does not exist.";
    }

    return $filename;
  }

  // Helper function to check if a string is valid JSON
  private function isinValidJson($string)
  {
    json_decode($string);
    return (json_last_error() === JSON_ERROR_NONE);
  }
}
