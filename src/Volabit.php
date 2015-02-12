<?php namespace Volabit;

require_once(dirname(__FILE__).'/Volabit/Auth.php');

use Auth\Manager;

class Client {

  public function __construct($id, $secret, $url, $env = 'production') {
    $this->url = $url;
    $config = $this->auth_params($id, $secret, $url, $env);
    $this->authManager = new Auth\Manager($config);
  }

  // OAuth2 ///////////////////////////////////////////////////////////

  public function authorize() {
    return $this->authManager->getAuthorizationUrl();
  }

  public function getTokens($code) {
    return $this->authManager->getAccessToken('authorization_code', [
      'code' => $code, 'grant_type' => 'authorization_code'
    ]);
  }

  public function refreshTokens($token) {
    return $this->authManager->getAccessToken('refresh_token', [
      'refresh_token' => $token, 'grant_type' => 'refresh_token'
    ]);
  }

  // API Rates ////////////////////////////////////////////////////////
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
