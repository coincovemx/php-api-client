<?php

namespace Volabit\Client;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Token\AccessToken as AccessToken;

class ArgumentError extends \Exception {}

class Core extends AbstractProvider {
  // URL for the Volabit production site.
  const PRODUCTION_SITE = 'https://stageex.volabit.com/';
  // URL for the Volabit test site.
  const SANDBOX_SITE    = 'https://sandbox.volabit.com/';

  // Holds the used production environment as a string.
  public $env;
  // Holds the current access token as an AccessToken object.
  public $tokens;

  public function __construct($options) {
    parent::__construct($options);
    $this->headers = ['Authorization' => 'Bearer'];
  }

  // OAuth Management /////////////////////////////////////////////////

  public function requestTokens($code) {
    $tokensObject = $this->getAccessToken('authorization_code', [
      'code' => $code, 'grant_type' => 'authorization_code'
    ]);
    $this->setTokens($tokensObject);
  }

  public function refreshTokens($refreshToken = 'none given') {
    if ($refreshToken == 'none given') {
      $refreshToken = $this->tokens->refreshToken;
    }

    $newTokens = $this->getAccessToken('refresh_token', [
      'refresh_token' => $refreshToken, 'grant_type' => 'refresh_token'
    ]);

    $this->setTokens($newTokens);
  }

  public function setTokensFromArray($tokens) {
    $this->tokens = new AccessToken($tokens);
  }

  public function hasTokenExpired($expires = 'unknown') {
    if ($expires == 'unknown') {
      $expires = $this->tokens->expires;
    }

    return ($expires < time()) ? true : false;
  }

  private function setTokens(AccessToken $token) {
    $this->tokens = $token;
  }

  // OAuth URLs ///////////////////////////////////////////////////////

  public function urlAuthorize() {
    return $this->baseUrl().'/oauth/authorize';
  }

  public function urlAccessToken() {
    return $this->baseUrl().'/oauth/token';
  }

  // API Calls and URLs ///////////////////////////////////////////////

              /////////////////////////////////////// Exchange rates //

  public function getTickers() {
    return $this->resource('get', 'api/v1/tickers');
  }

  public function getSpotPrices($amount, $from, $to) {
    return $this->resource('get', 'api/v1/spot-prices/', [
      'amount' => $amount,
      'currency_from' => $from,
      'currency_to' => $to
    ]);
  }

              //////////////////////////////////////////////// Users //

  public function userCreate($acceptance, $email, $pass) {
    return $this->resource('post', 'api/v1/users/', [
      'accepts_terms_of_service' => $acceptance,
      'user' => [
        'email' => $email,
        'password' => $pass
      ]
    ]);
  }

  public function getUserData() {
    return $this->resource('get', 'api/v1/users/me');
  }

              //////////////////////////////////////////////// Slips //

  public function slipCreate($currency, $amount, $type) {
    return $this->resource('post', 'api/v1/users/me/slips/', [
      'currency' => $currency,
      'amount' => $amount,
      'type' => $type
    ]);
  }

  public function getSlipData($id) {
    if ($id == '') { $this->emptyArgumentError('id'); }
    return $this->resource('get', 'api/v1/users/me/slips/'.$id);
  }

  public function slipDelete($id) {
    if ($id == '') { $this->emptyArgumentError('id'); }
    return $this->resource('delete', 'api/v1/users/me/slips/'.$id);
  }

  public function reportLoad($id, $amount, $affiliation, $authorization) {
    if ($id == '') { $this->emptyArgumentError('id'); }
    return $this->resource('post', 'api/v1/users/me/slips/'.$id.'/report/', [
      'amount' => $amount,
      'affiliation_number' => $affiliation,
      'authorization_number' => $authorization
    ]);
  }

  public function getLoadMethods(){
    return $this->resource('get', 'api/v1/users/me/slips/methods');
  }

      //////////////////////////////////////////////// Transactions //

  public function bitcoinBuy($amount) {
    return $this->resource('post', 'api/v1/users/me/buys/', [
      'amount' => $amount
    ]);
  }

  public function bitcoinSell($amount) {
    return $this->resource('post', 'api/v1/users/me/sells/', [
      'amount' => $amount
    ]);
  }

  public function send($currency, $amount, $address) {
    return $this->resource('post', 'api/v1/users/me/send/', [
      'amount' => $amount,
      'address' => $address,
      'currency' => $currency
    ]);
  }

  public function newGreenAddress($currency, $amount) {
    return $this->resource('post', 'api/v1/users/me/green-addresses/', [
      'amount' => $amount,
      'currency' => $currency
    ]);
  }

  // Helpers //////////////////////////////////////////////////////////

  private function resource($verb, $endpoint, $params = []) {
    if ($this->hasTokenExpired()) { $this->refreshTokens(); }
    $url = $this->baseUrl().$endpoint.'?'.$this->httpBuildQuery($params);

    try {
      $client = $this->getHttpClient();
      $client->setBaseUrl($url);
      $client->setDefaultOption('exceptions', false);

      if ($this->headers) {
        $client->setDefaultOption('headers', [
          'Authorization' => 'Bearer '.$this->tokens->accessToken
        ]);
      }

      $request = call_user_func(array($client, $verb))->send();
      $response = $request->getBody();
    } catch (Exception $ex) {
      $raw_response = explode("\n", $ex->getResponse());
      return $raw_response;
    }

    return json_decode($response, true);
  }

  private function baseUrl() {
    if ($this->env == 'production') {
      return $this::PRODUCTION_SITE;
    } else {
      return $this::SANDBOX_SITE;
    }
  }

  private function emptyArgumentError($arg) {
    throw new ArgumentError($arg.' must not be empty.');
  }

  public function __toString() {
    return print_r($this, true);
  }

  // Unused methods, required by AbstractProvider.
  public function userDetails($response, AccessToken $token) {}
  public function urlUserDetails(AccessToken $token) {}
}
