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

  // API Exchange Rates ///////////////////////////////////////////////

  /**
   * Gets the exchange price list for the supported currencies.
   */
  public function tickers() {
    return $this->manager->getTickers();
  }

  /**
   * Gets the exchange price from certain currency amount to other currency.
   */
  public function spotPrices($amount, $from, $to) {
    return $this->manager->getSpotPrices($amount, $from, $to);
  }

  // API Users ////////////////////////////////////////////////////////

  /**
   * Request the creation of a new user with the given params.
   *
   * @note This method requires partner privileges.
   */
  public function createUser($acceptance, $email, $pass = '') {
    return $this->manager->userCreate($acceptance, $email, $pass);
  }

  /**
   * Gets the information details of the app user.
   */
  public function userData() {
    return $this->manager->getUserData();
  }

  // API Slips ////////////////////////////////////////////////////////

  /**
   * # Creates a slip that can be used to load the user wallet.
   */
  public function createSlip($currency, $amount, $type) {
    return $this->manager->slipCreate($currency, $amount, $type);
  }

  /**
   * Gets the information of a specific slip.
   */
  public function slipData($id) {
    return $this->manager->getSlipData($id);
  }

  /**
   * Deletes a specific slip.
   */
  public function deleteSlip($id) {
    return $this->manager->slipDelete($id);
  }

  /**
   * Informs of a receipt used to load a wallet's slip.
   */
  public function reportReceipt($id, $amount, $affiliation, $authorization) {
    return $this->manager->reportLoad($id, $amount, $affiliation, $authorization);
  }

  /**
   * Lists the available options to load a slip.
   */
  public function loadMethods() {
    return $this->manager->getLoadMethods();
  }

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
