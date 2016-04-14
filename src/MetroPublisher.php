<?php
namespace WidgetsBurritos;

use WidgetsBurritos\MetroPublisherException;

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
   * Retrieves all tags from MetroPublisher
   */
  public function getAllTags() {
    static $_cache = NULL;
    if (isset($_cache)) {
      return $_cache;
    }
    $tags = array();
    for ($page = 1; TRUE; $page++) {
      $page_tags = $this->getTagsByPage($page);
      $ct = sizeof($page_tags);
      if ($ct == 0) {
        break;
      }
      $tags = array_merge($tags, $page_tags);
    }

    $_cache = $tags;
    return $tags;
  }

  /**
   * Retrieves a location by uuid.
   *
   * @param $uuid
   * @return mixed
   * @throws \WidgetsBurritos\MetroPublisherException
   */
  public function getLocation($uuid) {
    $url = sprintf("%s/locations/%s", $this->myURLBase, $uuid);
    $request = 'GET';
    $data = NULL;
    $content_type = 'application/json';

    return $this->__curl($url, $request, $data, $content_type);
  }

  /**
   * Retrieves 1 page of tags (up to 100 tags) from MetroPublisher
   *
   * @return mixed
   * @throws \WidgetsBurritos\MetroPublisherException
   */
  public function getTagsByPage($page = 1, $page_size = 100) {
    static $_cache = array();
    if (isset($_cache[$page])) {
      return $_cache[$page];
    }
    $url = sprintf("%s/tags?fields=uuid-urlname&rpp=%d&page=%d&type=default&state=approved&order=title.asc", $this->myURLBase, $page_size, $page);
    $request = 'GET';
    $data = NULL;
    $content_type = 'application/json';

    $results = $this->__curl($url, $request, $data, $content_type);

    $tags = array();
    foreach ($results->items as $tag) {
      $tags[$tag[1]] = $tag[0];
    }
    $_cache[$page] = $tags;
    return $tags;
  }

  /**
   * Inserts/updates location on Metro Publisher.
   *
   * @param $listing_array
   */
  function putLocation($listing_array) {
    // Retrieve tag uuids for listing
    $tag_uuids = array();
    if (isset($listing_array['tags'])) {
      $tag_hash = $this->getAllTags();
      $tags = explode(',', preg_replace('/\s+/', '', $listing_array['tags']));

      foreach ($tags as $tag) {
        if (isset($tag_hash[$tag])) {
          $tag_uuids[] = $tag_hash[$tag];
        }
        else {
          printf("The tag [%s] is undefined on MetroPublisher\n", $tag);
        }
      }

      unset($listing_array['tags']);
    }

    // Generate a unique URL Name for this listing.
    static $url_names = array();
    $base_key_idx = 1;
    do {
      $url_key_base = preg_replace('/\s+/', '-', strtolower($listing_array['title']));
      $url_key_base = preg_replace('/[^a-z0-9\-]/', '-', $url_key_base);
      $url_key = $url_key_base;
      if ($base_key_idx > 1) {
        $url_key .= '_' . $base_key_idx;
      }
      $base_key_idx++;
    } while (isset($url_names[$url_key]));
    $url_names[$url_key] = TRUE;
    $listing_array['urlname'] = $url_key;

    // Wrap content with <p> tags to meet Metro Publisher's content requirements.
    if (isset($listing_array['content'])) {
      $listing_array['content'] = WPTextFormatting::wpautop($listing_array['content']);
    }

    // Send request to Metro Publisher
    $url = sprintf("%s/locations/%s", $this->myURLBase, $listing_array['uuid']);
    $request = 'PUT';
    $json = json_encode((object) $listing_array);
    $content_type = 'application/json';

    $ret = $this->__curl($url, $request, $json, $content_type);

    // Add tags for location
    foreach ($tag_uuids as $tag_uuid) {
      $tagging_response = $this->setLocationTag($listing_array['uuid'], $tag_uuid);
    }

    return $ret;
  }

  /**
   * Adds a tag to the location.
   *
   * @param $location_uuid
   * @param $tag_uuid
   */
  public function setLocationTag($location_uuid, $tag_uuid) {
    $url = sprintf("%s/tags/%s/describes/%s", $this->myURLBase, $tag_uuid, $location_uuid);
    $request = 'PUT';
    $data = '{}';
    $content_type = 'application/json';

    return $this->__curl($url, $request, $data, $content_type);
  }

  /**
   * Sets an authorization token.
   *
   * @return mixed
   * @throws \WidgetsBurritos\MetroPublisherException
   */
  private function __setAuthorizationToken() {
    $url = "https://go.metropublisher.com/oauth/token";
    $request = 'POST';
    $data = sprintf("grant_type=client_credentials&api_key=%s&api_secret=%s", $this->myApiKey, $this->myApiSecret);
    $content_type = 'application/x-www-form-urlencoded';
    $response = $this->__curl($url, $request, $data, $content_type, FALSE);

    $this->myAuthToken = $response;
    $this->myURLBase = $this->myAuthToken->items[0]->url;
  }

  /**
   * Runs a CURL request.
   *
   * @param $url
   * @param string $custom_request
   * @param string $postman_token
   */
  private function __curl($url, $custom_request = 'GET', $data = NULL, $content_type = '', $requires_token = TRUE) {
    $curl = curl_init();

    // Setup HTTP Header Arrays
    $http_header_array = array();
    $http_header_array[] = "cache-control: no-cache";
    if ($requires_token) {
      $http_header_array[] = sprintf("authorization: Bearer %s", $this->myAuthToken->access_token);
    }
    if (!empty($content_type)) {
      $http_header_array[] = sprintf("content-type: %s", $content_type);
    }

    // Initialize cURL options
    curl_setopt_array($curl, array(
      CURLOPT_URL => $url,
      CURLOPT_RETURNTRANSFER => TRUE,
      CURLOPT_ENCODING => "",
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 30,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => $custom_request,
      CURLOPT_POSTFIELDS => $data,
      CURLOPT_HTTPHEADER => $http_header_array,
    ));

    // Execute remote command
    $response = curl_exec($curl);
    $err = curl_error($curl);

    curl_close($curl);

    if ($err) {
      throw new MetroPublisherException("cURL Error:" . $err);
    }
    else {
      return json_decode($response);
    }
  }
}
