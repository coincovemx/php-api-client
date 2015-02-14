<?php namespace Volabit;

require_once(dirname(__FILE__).'/Volabit/Manager.php');

use Manager\Core;

class Client {

  protected $manager;

  public function __construct($id, $secret, $url, $env = 'production') {
    $this->url = $url;
    $config = $this->auth_params($id, $secret, $url, $env);
    $this->manager = new Manager\Core($config);
  }

  // OAuth2 ///////////////////////////////////////////////////////////

  /**
   * Builds the URL to get the authorization code.
   *
   * The generated URL is to be opened on the user browser to authorize the app.
   */
  public function authorize() {
    return $this->manager->getAuthorizationUrl();
  }

  /**
   * Exchanges the authorization code for the access and request tokens.
   *
   * The obtained tokens are set into the client to be able to make calls to the
   * API.
   */
  public function getTokens($code) {
    $this->manager->requestTokens($code);
    return $this->tokens();
  }

  /**
   * Uses provided tokens to access the API.
   *
   * This method is intended to use tokens stored and loaded by the app to get
   * fresh tokens for the API calls.
   */
  public function useTokens($tokens) {
    if ($this->manager->hasTokenExpired($tokens['expires_in'])) {
      $this->manager->refreshTokens($tokens['refresh_token']);
    } else {
      $this->manager->setTokensFromArray($tokens);
    }

    return $this->tokens();
  }

  /**
   * Provides the tokens currently set on the client.
   */
  public function tokens() {
    return [
      'access_token'  => $this->manager->tokens->accessToken,
      'refresh_token' => $this->manager->tokens->refreshToken,
      'expires_in'      => $this->manager->tokens->expires
    ];
  }

  // API Rates ////////////////////////////////////////////////////////

  /**
   * Gets the exchange price list for the supported currencies.
   */
  public function tickers() {
    return $this->manager->getTickers();
  }

  /**
   * Gets the exchange price from certain currency amount to another currency.
   */
  public function spotPrices($amount, $from, $to) {
    return $this->manager->getSpotPrices($amount, $from, $to);
  }

  // API Users ////////////////////////////////////////////////////////
  // API Slips ////////////////////////////////////////////////////////
  // API Transactions /////////////////////////////////////////////////

  private function auth_params($id, $secret, $url, $env) {
    return [
      'clientId' => $id,
      'clientSecret' => $secret,
      'redirectUri' => $url,
      'env' => $env
    ];
  }

  public function __toString() {
    return print_r($this, true);
  }
}
