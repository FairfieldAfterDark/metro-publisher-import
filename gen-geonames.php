<?php
/**
 * @file Generates Geonames based on existing latitude and longitude.
 */

require __DIR__ . '/vendor/autoload.php';

use WidgetsBurritos\CSVListings;
use WidgetsBurritos\GeonameLookup;

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
if ($argc < 3) {
  die("Usage `php " . $argv[0] . " before.csv after.csv [--force]\n");
}

// Check input file for existance.
$before_file = $argv[1];
if (!file_exists($before_file)) {
  die("$before_file does not exist\n");
}

// Check output file for existance, and --force flag.
$after_file = $argv[2];
$force = isset($argv[3]) && $argv[3] === '--force';
if (file_exists($after_file) && !$force) {
  die("$after_file already exists.  Add --force if you wish to replace it.\n");
}


try {
  // Attempt to import listings from CSV file
  $csv_rows = CSVListings::importFromFile($before_file);

  // Initialize our geoname lookup.
  $lookup = new GeonameLookup(GEONAMES_USER);

  // If row is missing a UUID, add one.
  foreach ($csv_rows as &$csv_row) {
    // Ignore rows that already have a geonameId.
    if (!empty($csv_row['geoname_id'])) {
      continue;
    }
    // Ignore rows missing 
    if (empty($csv_row['lat']) || empty($csv_row['long'])) {
      printf("Skipping *%s* due to missing latitude/longitude", $csv_row['title']);
      continue;
    }
    $csv_row['geoname_id'] = $lookup->lookupGeonameId($csv_row['address_city'] . ', ' . $csv_row['address_state']);
    // Exports listings file.
    CSVListings::exportToFile($csv_rows, $after_file);
  }

} catch (\WidgetsBurritos\CSVListingsException $e) {
  die($e->getMessage() . "\n");
}
