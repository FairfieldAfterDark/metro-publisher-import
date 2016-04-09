<?php
namespace WidgetsBurritos;

/**
 * Class MetroPublisher
 * @package WidgetsBurritos
 */
class MetroPublisher {
  private $myApiKey, $myApiSecret, $myAuthToken, $myURLBase;

  /**
   * MetroPublisher constructor.
   *
   * @param $instance
   * @param $api_key
   * @param $api_secret
   */
  function __construct($api_key, $api_secret) {
    $this->myApiKey = $api_key;
    $this->myApiSecret = $api_secret;
    $this->__setAuthorizationToken();
  }

  /**
   * @param $listing_array
   */
  function addListing($listing_array) {
    print '<h3>ADD LISTING ARRAY</h3>';
    print_r($listing_array);
  }


  /**
   * Determines if a listing with the specified UUID exists.
   *
   * @param $uuid
   */
  function listingExists($uuid) {
    static $_cache = array();

    // If empty assume new listing.
    if (empty($uuid)) {
      return FALSE;
    }

    // If we've already evaluated
    if (isset($_cache[$uuid])) {
      return $_cache[$uuid];
    }

    // TODO: Check if listing exists.
    $_cache[$uuid] = 'x';
    return $_cache[$uuid];
  }

  /**
   * Retrives the location endpoint for the specified UUID.
   *
   * @param $uuid
   */
  private function __getLocationEndpoint($uuid) {
    return $this->myURLBase . '/locations/' . $uuid;
  }


  /**
   * Sets an authorization token.
   *
   * @return mixed
   * @throws \WidgetsBurritos\Exception
   */
  private function __setAuthorizationToken() {
    $curl = curl_init();

    curl_setopt_array($curl, array(
      CURLOPT_URL => "https://go.metropublisher.com/oauth/token",
      CURLOPT_RETURNTRANSFER => TRUE,
      CURLOPT_ENCODING => "",
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 30,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => "POST",
      CURLOPT_POSTFIELDS => sprintf("grant_type=client_credentials&api_key=%s&api_secret=%s", $this->myApiKey, $this->myApiSecret),
      CURLOPT_HTTPHEADER => array(
        "cache-control: no-cache",
        "content-type: application/x-www-form-urlencoded",
        "postman-token: 5e4b41af-2d23-091c-9e73-89ebb091aa3f"
      ),
    ));

    $response = curl_exec($curl);
    $err = curl_error($curl);

    curl_close($curl);

    if ($err) {
      $this->myAuthToken = NULL;
      throw new Exception('CURL Error: '. $err);
    }
    else {
      $this->myAuthToken = json_decode($response);
      $this->myURLBase = $this->myAuthToken->items[0]->url;
    }
  }
}