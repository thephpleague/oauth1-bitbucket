<?php

namespace League\OAuth1\Client\Server;

use League\OAuth1\Client\Credentials\TokenCredentials;
use Psr\Http\Message\ResponseInterface;

class Bitbucket extends AbstractServer
{
    /**
     * Checks a provider response for errors.
     *
     * @param  ResponseInterface $response
     * @param  array|string $data Parsed response data
     *
     * @return void
     * @throws IdentityProviderException
     */
    protected function checkResponse(ResponseInterface $response, $data)
    {
        //
    }

    /**
     * Generates a resource owner object from a successful resource owner
     * details request.
     *
     * @param  array $response
     * @param  TokenCredentials $tokenCredentials
     *
     * @return ResourceOwnerInterface
     */
    protected function createResourceOwner(array $response, TokenCredentials $tokenCredentials)
    {
        return new BitbucketResourceOwner($response);
    }

    /**
     * Gets the URL for redirecting the resource owner to authorize the client.
     *
     * @return string
     */
    protected function getBaseAuthorizationUrl(array $options = array())
    {
        return 'https://bitbucket.org/api/1.0/oauth/authenticate';
    }

    /**
     * Gets the URL for retrieving temporary credentials.
     *
     * @return string
     */
    protected function getBaseTemporaryCredentialsUrl()
    {
        return 'https://bitbucket.org/api/1.0/oauth/request_token';
    }

    /**
     * Gets the URL retrieving token credentials.
     *
     * @return string
     */
    protected function getBaseTokenCredentialsUrl()
    {
        return 'https://bitbucket.org/api/1.0/oauth/access_token';
    }

    /**
     * Gets the URL for retrieving user details.
     *
     * @return string
     */
    protected function getResourceOwnerDetailsUrl(TokenCredentials $tokenCredentials)
    {
        return 'https://bitbucket.org/api/1.0/user';
    }
}
