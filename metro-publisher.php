<?php


class MetroPublisher {
  private $myInstance, $myApiKey, $myApiSecret;

  /**
   * MetroPublisher constructor.
   *
   * @param $instance
   * @param $api_key
   * @param $api_secret
   */
  function __construct($instance, $api_key, $api_secret) {
    $this->myInstance = $instance;
    $this->myApiKey = $api_key;
    $this->myApiSecret = $api_secret;
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
  private function _getLocationEndpoint($uuid) {
    return '/'.$this->myInstance.'/locations/'.$uuid;
  }
}