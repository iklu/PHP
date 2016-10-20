<?php
namespace AppBundle\Services;

use Facebook\Facebook;
use Facebook\Exceptions\FacebookResponseException;
use Facebook\Exceptions\FacebookSDKException;

/**
 * Created by PhpStorm.
 * User: ovidiu
 * Date: 29.08.2016
 * Time: 17:16
 */
class FacebookAuthentication
{
    /**
     * @var \Symfony\Component\DependencyInjection\Container
     */
    private $container;

    /**
     * @var Facebook
     */
    private $fb;

    /**
     * @var facebookId string
     */
    private $facebookId;

    /**
     * @var facebookEmail string
     */
    private $facebookEmail;

    /**
     * @var facebookName string
     */
    private $facebookName;

    /**
     * Facebook error message
     *
     * @var message string
     */
    private $message;

    /**
     * Facebook link for login
     *
     * @var loginUrl string
     */
    private $loginUrl;

    /**
     * FacebookAuthentication constructor.
     * @param \Symfony\Component\DependencyInjection\Container $container
     */
    public function __construct(\Symfony\Component\DependencyInjection\Container $container)
    {
        $this->container = $container;

        $this->fb = new Facebook([
            'app_id' =>  $this->container->getParameter('appid'), // Replace {app-id} with your app id
            'app_secret' => $this->container->getParameter('appsecret'),
            'default_graph_version' => 'v2.6',
            'default_access_token' => 'x|y'
        ]);
    }

    /**
     * @return bool
     */
    public function initiate() {

        $helper = $this->fb->getRedirectLoginHelper();
        $accessToken = $helper->getAccessToken();

        try {
            $response = $this->fb->get('/me?fields=id,email,name', $accessToken);
            $user = $response->getGraphUser();

            $this->facebookId = $user['id'];
            $this->facebookEmail = $user['email'];
            $this->facebookName = $user['name'];

            return true;

        } catch(FacebookResponseException $e) {
            // When Graph returns an error
            $this->message =  'Graph returned an error: ' . $e->getMessage();
            return false;
        } catch(FacebookSDKException $e) {
            // When validation fails or other local issues
            $this->message =  'Facebook SDK returned an error: ' . $e->getMessage();
            return false;
        }

    }

    /**
     * @return mixed
     */
    public function getFacebookUserId() {
        return $this->facebookId;
    }

    /**
     * @return mixed
     */
    public function getFacebookUserEmail() {
        return $this->facebookEmail;
    }

    /**
     * @return mixed
     */
    public function getFacebookUserName() {
        return $this->facebookName;
    }

    /**
     * @return mixed
     */
    public function getFacebookErrorMessage() {
        return $this->message;
    }

    public function getFacebookLoginUrl() {
        $helper = $this->fb->getRedirectLoginHelper();
        $permissions = ['email'];
        return $this->loginUrl = $helper->getLoginUrl($this->container->get('router')->generate( 'login_by_facebook', array(), true ), $permissions);
    }
}