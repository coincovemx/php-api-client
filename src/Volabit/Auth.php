<?php namespace Volabit\Auth;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Token as Token;

class Manager extends AbstractProvider {
  // URL for the Volabit production site.
  const PRODUCTION_SITE = 'https://stageex.volabit.com';
  // URL for the Volabit test site.
  const SANDBOX_SITE    = 'https://sandbox.volabit.com';
  // Holds the used production environment.
  protected $env;

  public function urlAuthorize() {
    return $this->siteFor($this->env).'/oauth/authorize';
  }

  public function urlAccessToken() {
    return $this->siteFor($this->env).'/oauth/token';
  }

  public function __toString() {
    return print_r($this, true);
  }

  private function siteFor($env) {
    return ($env == 'production') ? $this::PRODUCTION_SITE : $this::SANDBOX_SITE;
  }

  // These functions are here because the abstract class requires them. May be im
  public function urlUserDetails(Token\AccessToken $token) {}
  public function userDetails($response, Token\AccessToken $token) {}
}
