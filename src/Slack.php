<?php

use GuzzleHttp\Client as Guzzle;
//use GuzzleHttp\Exception\ClientException;
//use GuzzleHttp\Exception\ServerException;

class Slack {

  protected $endpoint;
  protected $channel;
  protected $username;
  protected $icon;
  protected $guzzle;

  public function __construct($endpoint, array $attributes = [], Guzzle $guzzle = null) {
    $this->endpoint = $endpoint;

    if (isset($attributes['channel'])) $this->setChannel($attributes['channel']);
    if (isset($attributes['username'])) $this->setUsername($attributes['username']);
    if (isset($attributes['icon'])) $this->setIcon($attributes['icon']);

    $this->guzzle = $guzzle ?: new Guzzle;
  }

  public function setChannel($channel) {
    $this->channel = $channel;
  }

  public function getChannel() {
    return $this->channel;
  }

  public function setUsername($username) {
    $this->username = $username;
  }

  public function getUsername() {
    return $this->username;
  }

  /**
   * Set the icon for the messages
   *
   * @param string $icon
   * @return void
   */
  public function setIcon($icon) {
    $this->icon = $icon;
  }

  /**
   * Get the icon for the messages
   *
   * @return string
   */
  public function getIcon() {
    return $this->icon;
  }

  public function sendMessage($message) {

    $payload = [
      'text'       => $message,
      'channel'    => $this->getChannel(),
      'username'   => $this->getUsername(),
      'icon_emoji' => $this->getIcon(),
    ];

    $body = json_encode($payload);

    $this->guzzle->post($this->endpoint, ['body' => $body]);
  }
}
