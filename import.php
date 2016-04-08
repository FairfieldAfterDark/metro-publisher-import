<?php

print '<PRE>';
$ct = 0;
if (($handle = fopen(IMPORT_FILE, "r")) !== FALSE) {
  while (($row = fgetcsv($handle, 10000, ",")) !== FALSE) {
    print_r($row);
    $ct++;
  }
  fclose($handle);
}