<?php

namespace ReCaptcha\Lib;

use Cake\Core\Configure;
use Cake\Network\Http\Client;

/**
 * Class ReCaptcha
 *
 * @package Recaptcha\Lib
 */
class ReCaptcha
{
    protected static $response;

    const RECAPTCHA_API_SERVER = 'http://www.google.com/recaptcha/api';
    const RECAPTCHA_API_SECURE_SERVER = 'https://www.google.com/recaptcha/api';
    const RECAPTCHA_VERIFY_SERVER = 'http://www.google.com/recaptcha/api/verify';

    const SUCCESS_RESPONSE = 'success';

    const ERR_RESPONSE_INVALID_SITE_PRIVATE_KEY = 'invalid-site-private-key';
    const ERR_RESPONSE_INVALID_REQUEST_COOKIE = 'invalid-request-cookie';
    const ERR_RESPONSE_INCORRECT_CAPTCHA_SOL = 'incorrect-captcha-sol';
    const ERR_RESPONSE_CAPTCHA_TIMEOUT = 'captcha-timeout';
    const ERR_RESPONSE_RECAPTCHA_NOT_REACHABLE = 'recaptcha-not-reachable';

    /**
     * Calls an HTTP POST function to verify if the user's guess was correct
     *
     * @param string $challengeField Challenge field data
     * @param string $responseField  Response field data
     * @param array  $extraParams    an array of extra variables to post to the verify server
     *
     * @return bool
     */
    public static function isValid($challengeField, $responseField, $extraParams = [])
    {
        $privateKey = Configure::read('reCaptcha.privateKey');
        $remoteIp = env('REMOTE_ADDR');

        if ($privateKey == null || $privateKey == '') {
            $url = '<a href="https://www.google.com/recaptcha/admin/create">
            https://www.google.com/recaptcha/admin/create</a>';

            die(__d('re_captcha', 'To use reCAPTCHA you must get an API key from {0}', $url));
        }

        if ($remoteIp == null || $remoteIp == '') {
            die(__d('re_captcha', 'For security reasons, you must pass the remote ip to reCAPTCHA'));
        }

        $http = new Client();
        $response = $http->post(
            self::RECAPTCHA_VERIFY_SERVER,
            [
                'privatekey' => $privateKey,
                'remoteip' => $remoteIp,
                'challenge' => $challengeField,
                'response' => $responseField
            ] + $extraParams,
            [
                'headers' => [
                    'User-Agent' => 'reCAPTCHA/PHP',
                    'Content-Type' => 'application/x-www-form-urlencoded'
                ]
            ]
        );


        return self::responseParser($response->body());
    }

    /**
     * Response Parser
     *
     * @param string $body reCaptcha response
     *
     * @return bool
     */
    protected static function responseParser($body)
    {
        $responseBody = explode("\n", $body);
        $isValid = filter_var($responseBody[0], FILTER_VALIDATE_BOOLEAN);
        $response = $responseBody[1];

        self::setResponse($response);

        return $isValid;
    }

    /**
     * Set response
     *
     * @param string $response Response verify server
     */
    protected static function setResponse($response)
    {
        self::$response = $response;
    }

    /**
     * Get response
     *
     * @return null|string
     */
    public static function getResponse()
    {
        return self::$response;
    }

    /**
     * Get responses
     *
     * @param string $response
     *
     * @return array|bool
     */
    public static function getResponses($response = null)
    {
        $responses = [
            self::SUCCESS_RESPONSE => __d('re_captcha', 'Success'),
            self::ERR_RESPONSE_INCORRECT_CAPTCHA_SOL => __d('re_captcha', 'The CAPTCHA solution was incorrect.'),
            self::ERR_RESPONSE_CAPTCHA_TIMEOUT => __d(
                're_captcha',
                'The solution was received after the CAPTCHA timed out.'

            ),
            self::ERR_RESPONSE_INVALID_REQUEST_COOKIE => __d(
                're_captcha',
                'The challenge parameter of the verify script was incorrect.'
            ),
            self::ERR_RESPONSE_INVALID_SITE_PRIVATE_KEY => __d(
                're_captcha',
                'We weren\'t able to verify the private key.'
            ),
            self::ERR_RESPONSE_RECAPTCHA_NOT_REACHABLE => __d(
                're_captcha',
                'Unable to contact the reCAPTCHA verify server.'
            ),
        ];

        if (!is_null($response)) {
            if (array_key_exists($response, $responses)) {
                return $responses[$response];
            } else {
                return false;
            }
        } else {
            return $responses;
        }
    }

    /**
     * Get Challenge Uri
     *
     * @return string
     */
    public static function getChallengeUri()
    {
        return self::prepareUri('challenge');
    }

    /**
     * Get NoScript Uri
     *
     * @return string
     */
    public static function getNoScriptUri()
    {
        return self::prepareUri('noscript');
    }

    /**
     * Prepare challenge or no script uri
     *
     * @param string $type challenge or noscript
     *
     * @return string
     */
    protected static function prepareUri($type)
    {
        if (Configure::read('ReCaptcha.publicKey') == null || Configure::read('ReCaptcha.publicKey') == '') {
            $url = '<a href="https://www.google.com/recaptcha/admin/create">
            https://www.google.com/recaptcha/admin/create</a>';

            $message = __d('re_captcha', 'To use reCAPTCHA you must get an API key from {0}', $url);

            return $message;
        }

        if (Configure::read('ReCaptcha.secure')) {
            $server = self::RECAPTCHA_API_SECURE_SERVER;
        } else {
            $server = self::RECAPTCHA_API_SERVER;
        }

        $query = [
            'k' => Configure::read('ReCaptcha.publicKey'),
            'error' => Configure::read('ReCaptcha.error')
        ];

        return $challengeAddress = $server . "/{$type}?" . http_build_query($query);
    }
}
