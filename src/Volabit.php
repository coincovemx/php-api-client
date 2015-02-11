<?php namespace Volabit;

use League\OAuth2\Client\Provider\AbstractProvider;

class Client extends AbstractProvider {
  const PRODUCTION_SITE = 'https://stageex.volabit.com';
  const SANDBOX_SITE = 'https://sandbox.volabit.com';

  public function urlAuthorize() {
    return $this->siteFor('production').'/oauth/authorize';
  }

  public function urlAccessToken() {
    return $this->siteFor('production').'/oauth/token';
  }

  public function urlUserDetails(\League\OAuth2\Client\Token\AccessToken $token) {}
  public function userDetails($response, \League\OAuth2\Client\Token\AccessToken $token) {}

  private function siteFor($env) {
    return ($env == 'production') ? $this::PRODUCTION_SITE : $this::SANDBOX_SITE;
  }

  public function __toString() {
    return print_r($this, true);
  }
}
