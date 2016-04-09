<?php
namespace WidgetsBurritos;

use WidgetsBurritos\WPTextFormatting;

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
   * Inserts/updates location on Metro Publisher.
   *
   * @param $listing_array
   */
  function putLocation($listing_array) {
    // Generate a unique URL Name for this listing.
    static $url_names = array();
    $base_key_idx = 1;
    do {
      $url_key_base = preg_replace('/\s+/', '-', strtolower($listing_array['title']));
      $url_key = $url_key_base;
      if ($base_key_idx > 1) {
        $url_key .= '_' . $base_key_idx;
      }
      $base_key_idx++;
    } while (isset($url_names[$url_key]));
    $url_names[$url_key] = TRUE;
    $listing_array['urlname'] = $url_key;

    // Wrap content with <p> tags to meet Metro Publisher's content requirements.
    $listing_array['content'] = WPTextFormatting::wpautop($listing_array['content']);

    // Send request to Metro Publisher
    $url = sprintf("%s/locations/%s", $this->myURLBase, $listing_array['uuid']);
    $request = 'PUT';
    $json = json_encode((object) $listing_array);
    $content_type = 'application/json';
    $postman_token = '8babdb0d-d402-e11f-6fa5-54d5d03d3f8c';

    return $this->__curl($url, $request, $json, $content_type, $postman_token);
  }

  /**
   * Retrieves a location by uuid.
   *
   * @param $uuid
   * @return mixed
   * @throws \Exception
   */
  public function getLocation($uuid) {
    $url = sprintf("%s/locations/%s", $this->myURLBase, $uuid);
    $request = 'GET';
    $data = NULL;
    $content_type = 'application/json';
    $postman_token = '52c156c7-ca46-25f3-f810-0150b49f16ae';

    return $this->__curl($url, $request, $data, $content_type, $postman_token);
  }


  /**
   * Sets an authorization token.
   *
   * @return mixed
   * @throws \WidgetsBurritos\Exception
   */
  private function __setAuthorizationToken() {
    $url = "https://go.metropublisher.com/oauth/token";
    $request = 'POST';
    $data = sprintf("grant_type=client_credentials&api_key=%s&api_secret=%s", $this->myApiKey, $this->myApiSecret);
    $content_type = 'application/x-www-form-urlencoded';
    $postman_token = '5e4b41af-2d23-091c-9e73-89ebb091aa3f';
    $response = $this->__curl($url, $request, $data, $content_type, $postman_token, FALSE);

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
  private function __curl($url, $custom_request = 'GET', $data = NULL, $content_type = '', $postman_token = '', $requires_token = TRUE) {
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
    if (!empty($postman_token)) {
      $http_header_array[] = sprintf("postman-token: %s", $postman_token);
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
      throw new \Exception("cURL Error:" . $err);
    }
    else {
      return json_decode($response);
    }
  }
}