<?php

namespace datamind\onedrivesdk;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Microsoft\Graph\Graph;
use yii\base\Component;
use yii\helpers\Json;

/**
 * Yii2 component wrapping of the MS Graph SDK for easy configuration
 */
class OneDriveSdk extends Component
{
    /**
     * @var string specifies the URL to login
     */
    public $url = 'https://login.microsoftonline.com';

    /**
     * @var string specifies the ENDPOINT to login
     */
    public $endpoint = 'oauth2/v2.0/token';

    /**
     * "/me" endpoint refers to the user in whose context running request,
     * thus is only available for delegate permission flows.
     * If using the application permissions model (client credentials), running this code without any user context,
     * so there is no user to "resolve" for the "/me" endpoint.
     * Use /users/{userId} instead.
     * @var string
     */
    public $user = 'me';

    /**
     * @var array specifies the MS Graph credentials
     */
    public $credentials = [];

    /**
     * @var Graph
     */
    protected $_msgraphsdk;

    /**
     * Initializes (if needed) and fetches the MS Graph SDK instance
     * @return Graph
     * @throws GuzzleException
     */
    public function getMsGraphSdk()
    {
        if (empty($this->_msgraphsdk) || !$this->_msgraphsdk instanceof Graph) {
            $this->setMsGraphSdk();
        }

        return $this->_msgraphsdk;
    }

    /**
     * Sets the MS Graph SDK instance
     * @throws GuzzleException
     */
    public function setMsGraphSdk()
    {
        $this->_msgraphsdk = (new Graph())->setAccessToken($this->_getAccessToken());
    }

    /**
     * Authenticate with the Microsoft Graph service
     * The MS Graph SDK for PHP does not include any default authentication implementations.
     * The "thephpleague/oauth2-client" library will handle the OAuth2 flow for you and provide a usable token
     * for querying the Graph.
     *
     * To authenticate as an application you can use the Guzzle HTTP client,
     * which comes preinstalled with this library
     *
     * @return mixed
     * @throws GuzzleException
     */
    private function _getAccessToken()
    {
        $guzzle = new Client();
        $requestUrl = $this->url . '/' . $this->credentials['tenant_id'] . '/' . $this->endpoint;

        $token = Json::decode($guzzle->post($requestUrl, [
            'form_params' => array_merge([
                'scope' => 'https://graph.microsoft.com/.default',
                'grant_type' => 'client_credentials',
            ], $this->credentials),
        ])->getBody()->getContents(), false);

        return $token->access_token;
    }
}
