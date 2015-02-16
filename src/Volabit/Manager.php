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

  // API Calls and URLs ///////////////////////////////////////////////

              /////////////////////////////////////// Exchange rates //

  public function getTickers() {
    return $this->resource('get', $this->urlTickers());
  }

  public function urlTickers() {
    return $this->baseUrl().'api/v1/tickers/';
  }

  public function getSpotPrices($amount, $from, $to) {
    return $this->resource('get', $this->urlSpotPrices(), [
      'amount' => $amount,
      'currency_from' => $from,
      'currency_to' => $to
    ]);
  }

  public function urlSpotPrices() {
    return $this->baseUrl().'api/v1/spot-prices/';
  }

              //////////////////////////////////////////////// Users //

  public function userCreate($acceptance, $email, $pass) {
    return $this->resource('post', $this->urlUserCreate(), [
      'accepts_terms_of_service' => $acceptance,
      'user' => [
        'email' => $email,
        'password' => $pass
      ]
    ]);
  }

  public function urlUserCreate() {
    return $this->baseUrl().'api/v1/users/';
  }

  public function getUserData() {
    return $this->resource('get', $this->urlUserData());
  }

  public function urlUserData() {
    return $this->baseUrl().'api/v1/users/me/';
  }

  // Helpers //////////////////////////////////////////////////////////

  private function resource($verb, $url, $params = []) {
    if ($this->hasTokenExpired()) { $this->refreshTokens(); }
    $this->headers['Authorization'] = 'Bearer '.$this->tokens->accessToken;
    $url .= '?'.$this->httpBuildQuery($params);

    try {
        $client = $this->getHttpClient();
        $client->setBaseUrl($url);

        if ($this->headers) {
            $client->setDefaultOption('headers', $this->headers);
        }

        $request = call_user_func(array($client, $verb))->send();
        $response = $request->getBody();
    } catch (BadResponseException $e) {
        // @codeCoverageIgnoreStart
        $raw_response = explode("\n", $e->getResponse());
        throw new IDPException(end($raw_response));
        // @codeCoverageIgnoreEnd
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

  public function __toString() {
    return print_r($this, true);
  }

  // Unused methods, required by AbstractProvider.
  public function userDetails($response, AccessToken $token) {}
  public function urlUserDetails(AccessToken $token) {}
}
