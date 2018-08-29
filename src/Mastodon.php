<?php

namespace Lrf141\OAuth2\Client\Provider;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;
use Psr\Http\Message\ResponseInterface;

class Mastodon extends AbstractProvider
{

    use BearerAuthorizationTrait;


    /**
     * @return string
     */
    public function getBaseAuthorizationUrl()
    {
        return '';
    }

    /**
     * @param array $params
     * @return string
     */
    public function getBaseAccessTokenUrl(array $params)
    {
        return '';
    }


    /**
     * @param AccessToken $token
     * @return string
     */
    public function getResourceOwnerDetailsUrl(AccessToken $token)
    {
        return '';
    }

    /**
     * @return array
     */
    protected function getDefaultScopes()
    {
        return [];
    }

    /**
     * @throws IdentityProviderException
     * @param ResponseInterface $response
     * @param array|string $data
     * @return void
     */
    protected function checkResponse(ResponseInterface $response, $data)
    {
        if (isset($data['error'])) {
            throw  new IdentityProviderException(
                $data['error'] ?: $response->getReasonPhrase(),
                $response->getStatusCode(),
                $response
            );
        }
    }


    /**
     * @param array $response
     * @param AccessToken $token
     * @return \League\OAuth2\Client\Provider\ResourceOwnerInterface|MastodonResourceOwner
     */
    protected function createResourceOwner(array $response, AccessToken $token)
    {
        return new MastodonResourceOwner($response);
    }


    /**
     * @param AccessToken $token
     * @return mixed
     */
    protected function fetchResourceOwnerDetails(AccessToken $token)
    {
        return parent::fetchResourceOwnerDetails($token);
    }


    /**
     * @param array $options
     * @return string
     */
    public function getAuthorizationUrl(array $options = [])
    {
        return parent::getAuthorizationUrl($options);
    }
}