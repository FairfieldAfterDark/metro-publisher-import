<?php
/**
 * Class GeonameLookup
 */

namespace WidgetsBurritos;

class GeonameLookup {
  private $myUserName;
  const baseURL = 'http://api.geonames.org/searchJSON';

  public function __construct($username) {
    $this->myUserName = $username;
  }

  /**
   * Retrieves Geoname ID for a search string
   *
   * Returns empty string on failure.
   *
   * @param $latitude
   * @param $longitude
   */
  public function lookupGeonameId($search_string) {
    static $_cache = array();
    if (isset($_cache[$search_string])) {
      return $_cache[$search_string];
    }
    $url = sprintf('%s?q=%s&maxRows=1&username=%s', self::baseURL, urlencode($search_string), $this->myUserName);
    $resp = $this->__curl($url, 'GET');
    sleep(1); // sleeping 1 second between each geoname lookup to reduce overflow limits.
    $_cache[$search_string] = isset($resp->geonames[0]->geonameId) ? $resp->geonames[0]->geonameId : '';
    return $_cache[$search_string];
  }


  /**
   * Runs a CURL request.
   *
   * @param $url
   * @param string $custom_request
   * @param string $postman_token
   */
  private function __curl($url, $custom_request = 'GET') {
    $curl = curl_init();

    // Setup HTTP Header Arrays
    $http_header_array = array();
    $http_header_array[] = "cache-control: no-cache";

    // Initialize cURL options
    curl_setopt_array($curl, array(
      CURLOPT_URL => $url,
      CURLOPT_RETURNTRANSFER => TRUE,
      CURLOPT_ENCODING => "",
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 30,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => $custom_request,
      CURLOPT_HTTPHEADER => $http_header_array,
    ));

    // Execute remote command
    $response = curl_exec($curl);
    $err = curl_error($curl);

    curl_close($curl);

    if ($err) {
      return '';
    }
    else {
      return json_decode($response);
    }
  }

}