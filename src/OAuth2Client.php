<?php

namespace Volabit\Client;

use Volabit\Client\Core;

class OAuth2Client {

  protected $manager;

  public function __construct($id, $secret, $url, $env = 'production') {
    $this->url = $url;
    $config = $this->auth_params($id, $secret, $url, $env);
    $this->manager = new Core($config);
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
   * Uses given tokens to access the API.
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
   * Gets token info from OAuth2
   */
  public function tokenInfo() {
    return $this->manager->getTokenInfo();
  }

  /**
   * Gets the exchange price from certain currency amount to other currency.
   * @note BTC units are expected in satoshis. Other currencies units are
   *       expected in cents.
   */
  public function spotPrices($amount, $from, $to) {
    return $this->manager->getSpotPrices($amount, $from, $to);
  }

  // API Users ////////////////////////////////////////////////////////

  /**
   * Request the creation of a new user with the given params.
   * @note This action requires partner privileges.
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

  /**
   * Gets the information details of a user by ID
   * ID is either: email, or, phone number
   */
  public function userDataById($id) {
    return $this->manager->getUserDataById($id);
  }



  /**
   * Alias for `userData`.
   */
  public function me() {
    return $this->userData();
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

  /**
   * Lists the available request methods
   */
  public function requestMethods() {
    return $this->manager->getRequestMethods();
  }


  /**
   * Delete a slip by ID, Email, Phone
   */
  public function deleteSlipById($id,$email,$phone) {
    return $this->manager->deleteSlipById($id,$email,$phone);
  }

  /**
   * Create a slip, keeps order of currency,amount,type to comply with createSlip() 
   */
  public function createSlipById($email,$phone,$currency,$amount,$type) {
    return $this->manager->createSlipById($email,$phone,$currency,$amount,$type);
  }


  // API Transactions /////////////////////////////////////////////////

  /**
   * Instantly buy bitcoins using fiat balance from the wallet.
   * @note The amount is expected in fiat cents.
   */
  public function buyBitcoins($amount) {
    return $this->manager->bitcoinBuy($amount);
  }

  /**
   * Instantly sell bitcoins to get fiat balance to the wallet.
   * @note The amount is expected in satoshis.
   */
  public function sellBitcoins($amount) {
    return $this->manager->bitcoinSell($amount);
  }

  /**
   * Instantly send fiat or bitcoins to an address.
   * @note The amount is expected in satoshis for bitcoins and cents for
   *       fiat currencies.
   */
  public function sendMoney($currency, $amount, $address) {
    return $this->manager->send($currency, $amount, $address);
  }

  /**
   * Requests a special address to receive a bitcoin payment that will be
   * instantly converted to the designated currency.
   * @note This action requires merchant privileges.
   */
  public function newPayment($currency, $amount) {
    return $this->manager->newGreenAddress($currency, $amount);
  }

  // Helpers //////////////////////////////////////////////////////////

  private function auth_params($id, $secret, $url, $env) {
    return [
      'clientId' => $id,
      'clientSecret' => $secret,
      'redirectUri' => $url,
      'env' => $env
    ];
  }

  /**
   * Toggles the test environment with a boolean value.
   * @note Set it before requiring the user authorization or your app
   * will need to be reauthorized.)
   *
   * @deprecating: use setEnvironment('sandbox') in the future.
   */
  public function sandbox($flag) {
    switch($flag) {
      case true:
        $this->manager->env = 'sandbox';
        break;
      case false:
        $this->manager->env = 'production';
        break;
    }
  }

  /**
   * Set the environment.
   * 
   * Possible options:
   * sandbox -> https://sandbox.volabit.com/
   * production -> https://www.volabit.com/
   * 
   * Or another other string will become:
   * https://{environment}.volabit.com/
   */
  public function setEnvironment($env) {
    $this->manager->env = $env;
  }

  public function __toString() {
    return print_r($this, true);
  }
}
