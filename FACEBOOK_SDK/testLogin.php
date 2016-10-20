<?php
session_start();
require_once('./src/Facebook/autoload.php');
$fb = new Facebook\Facebook([
  'app_id' => '300870153585521', // Replace {app-id} with your app id
  'app_secret' => '1566b1ddb69018cad286db1fbd2b26d0',
  'default_graph_version' => 'v2.2',
  ]);

$helper = $fb->getRedirectLoginHelper();

$permissions = ['email']; // Optional permissions
$loginUrl = $helper->getLoginUrl('http://www.meineke.local/receiveData.php', $permissions);

echo '<a href="' . htmlspecialchars($loginUrl) . '">Log in with Facebook!</a>';