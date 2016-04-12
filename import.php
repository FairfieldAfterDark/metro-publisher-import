<?php
/**
 * @file Imports from specified file into Metro Publisher.
 */

require __DIR__ . '/vendor/autoload.php';

use WidgetsBurritos\CSVListings;
use WidgetsBurritos\MetroPublisher;

// Ensure settings exist and then include them.
if (!file_exists('settings.php')) {
  die('Copy default.settings.php to settings.php and update variables to get started');
}
require_once('settings.php');

// Ensure user is on command line.
if (php_sapi_name() !== 'cli') {
  die("This must be run from the command line\n");
}

// Make sure user is using script correctly.
if ($argc < 2) {
  die("Usage `php " . $argv[0] . " import-file.csv\n");
}

try {
  date_default_timezone_set('UTC');

  $MP = new MetroPublisher(API_KEY, API_SECRET);

  $tag_cats = $MP->getAllTags();
  // Establish a new metropublisher connection.

  $csv_rows = CSVListings::importFromFile($argv[1]);

  // Import each row
  foreach ($csv_rows as $csv_row) {
    if (empty($csv_row['uuid'])) {
      throw new \Exception('All rows must have a UUID. Please update the csv using `php add-uuid-to-csv.php` and then run this script again.');
    }

    // Remove any empty column data.
    foreach ($csv_row as $field => $value) {
      if (empty($value)) {
        unset($csv_row[$field]);
      }
    }

    // address_* aren't valid MetroPublisher columns, and were only used for our import purposes, so remove them.
    unset($csv_row['address_city']);
    unset($csv_row['address_state']);
    unset($csv_row['address_street_combined']);

    // Populate the coords array based on existing latitude/longitude.
    if (!empty($csv_row['lat']) && !empty($csv_row['long'])) {
      $csv_row['coords'] = array($csv_row['lat'], $csv_row['long']);
    }

    if ($csv_row['is_listing'] && !isset($csv_row['listing_start'])) {
      $csv_row['listing_start']=substr(date('c'), 0, -6);
    }

    // Upload the listing and exit/warn on failure.
    $put_response = $MP->putLocation($csv_row);
    if (isset($put_response->error)) {
      throw new Exception($csv_row['title'] . ': ' . json_encode($put_response->error_info));
    }
  }
} catch (\WidgetsBurritos\MetroPublisherException $e) {
  die($e->getMessage()."\n");
}
