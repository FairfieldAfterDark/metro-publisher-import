<?php
namespace WidgetsBurritos;

use WidgetsBurritos\CSVListingsException;

/**
 * Class CSVListings
 * @package WidgetsBurritos
 */
class CSVListings {
  /**
   * Simple function that imports the contents of a CSV into an array of associative arrays.
   *
   * @param $file
   * @return array
   * @throws \WidgetsBurritos\CSVListingsException
   */
  public static function importFromFile($file) {
    ini_set("auto_detect_line_endings", "1");
    $ct = 0;
    $keys = array();
    $rows = array();

    // Ensure we can read the file
    if (($fh = @fopen($file, "r")) === FALSE) {
      throw new CSVListingsException("Could not read file");
    }

    // Foreach row add to hash array.
    while (($row = fgetcsv($fh, 10000, ",")) !== FALSE) {
      if ($ct == 0) {
        $keys = $row;
      }
      else {
        $row_hash = array();
        $row_size = sizeof($row);

        // Skip empty rows.
        if ($row_size < 1 || $row_size == 1 && empty($row[0]) || strlen(implode('', $row)) === 0) {
          continue;
        }

        // Foreach column, evaluate key/value.
        for ($i = 0; $i < sizeof($row); $i++) {
          $key = $keys[$i];
          if ($key == 'content') {
            $value = self::__sanitize($row[$i], TRUE);
            $row_hash[$key] = trim(WPTextFormatting::wpautop($value));
          }
          else {
            $row_hash[$key] = self::__sanitize($row[$i]);
          }
        }

        $rows[] = $row_hash;
      }
      $ct++;
    }
    fclose($fh);

    return $rows;
  }

  /**
   * Simple function that exports a listing array to a CSV file
   * @param $listing_array
   */
  public static function exportToFile($listing_array, $file) {
    $header = array_keys($listing_array[0]);

    // Attempt to open file for write.
    if (($fh = @fopen($file, "w")) === FALSE) {
      throw new CSVListingsException("Could not save file");
    }
    // Output header.
    fputcsv($fh, $header);

    // Output rows.
    foreach ($listing_array as $listing) {
      fputcsv($fh, array_values($listing));
    }

    // Close file.
    fclose($fh);
  }


  /**
   * Strips any special/out-of-range characters off of the string.
   *
   * @param $string
   * @return mixed
   */
  private static function __sanitize($string, $switch_ampersands = FALSE) {
    $string = str_replace('&#13;', '', $string);
    $string = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $string);
    if ($switch_ampersands) {
      $string = str_replace('&', '&amp;', $string);
    }
    return $string;
  }
}