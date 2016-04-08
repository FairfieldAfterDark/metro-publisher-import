<?php

// Ensure settings exist and include necessary files.
if (!file_exists('settings.php')) {
  die('Copy default.settings.php to settings.php and update variables to get started');
}

include_once('settings.php');
include_once('functions.php');
include_once('metro-publisher.php');

print '<PRE>';

$MP = new MetroPublisher(APP_INSTANCE, API_KEY, API_SECRET);
$csv_rows = importListingsFromCSV(IMPORT_FILE);

// Import each row
foreach ($csv_rows as $csv_row) {
  
}