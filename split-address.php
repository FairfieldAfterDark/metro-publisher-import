<?php
/**
 * @file Generates Geonames based on existing latitude and longitude.
 */

require __DIR__ . '/vendor/autoload.php';

use WidgetsBurritos\CSVListings;

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

  // If row is missing a UUID, add one.
  foreach ($csv_rows as &$csv_row) {
    // If has combined address, but no individual tokenized addresses, split them.
    if ((empty($csv_row['street']) || empty($csv['streetnumber'])) && !empty($csv_row['address_street_combined'])) {
      $tokens = explode(' ', $csv_row['address_street_combined']);
      $csv_row['streetnumber'] = array_shift($tokens);
      $csv_row['street'] = implode(' ', $tokens);
      
      // Exports listings file.
      CSVListings::exportToFile($csv_rows, $after_file);
    }
  }

} catch (\WidgetsBurritos\CSVListingsException $e) {
  die($e->getMessage() . "\n");
}
