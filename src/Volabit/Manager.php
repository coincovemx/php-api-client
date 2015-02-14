<?php namespace Volabit\Manager;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Token\AccessToken as AccessToken;

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

  // API URLs /////////////////////////////////////////////////////////

              /////////////////////////////////////// Exchange rates //

  public function getTickers() {
    $params = ['token' => $this->tokens->accessToken];
    $url = $this->urlTickers().'?'.$this->httpBuildQuery($params);
    $response = $this->fetchProviderData($url);

    return json_decode($response, true);
  }

  public function getSpotPrices($amount, $from, $to) {
    $params = [
      'token' => $this->tokens->accessToken,
      'amount' => $amount,
      'currency_from' => $from,
      'currency_to' => $to
    ];

    $url = $this->urlSpotPrices().'?'.$this->httpBuildQuery($params);
    $response = $this->fetchProviderData($url);

    return json_decode($response, true);
  }

  public function urlTickers() {
    return $this->baseUrl().'api/v1/tickers/';
  }

  public function urlSpotPrices() {
    return $this->baseUrl().'api/v1/spot-prices/';
  }

              //////////////////////////////////////////////// Users //

  public function urlUserDetails(AccessToken $token) {}

  public function userDetails($response, AccessToken $token) {}

  // Helpers //////////////////////////////////////////////////////////

  public function __toString() {
    return print_r($this, true);
  }

  private function parseStream($stream) {
    $content = '';

    while (!$stream->feof()) {
      $content .= $stream->readLine();
      echo "hello!\n";
      print_r($content);
    }

    return json_decode($content, true);
  }

  private function baseUrl() {
    if ($this->env == 'production') {
      return $this::PRODUCTION_SITE;
    } else {
      return $this::SANDBOX_SITE;
    }
  }
}
