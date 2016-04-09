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
  // Establish a new metropublisher connection.
  $MP = new MetroPublisher(API_KEY, API_SECRET);
  $csv_rows = CSVListings::importFromFile($argv[1]);

  // Import each row
  foreach ($csv_rows as $csv_row) {
    if (empty($csv_row['uuid'])) {
      throw new \Exception('All rows must have a UUID. Please update the csv using `php add-uuid-to-csv.php` and then run this script again.');
    }
    print_r($csv_row);
  }
} catch (Exception $e) {
  die($e->getMessage()."\n");
}