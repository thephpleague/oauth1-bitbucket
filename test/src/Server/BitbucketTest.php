<?php namespace League\OAuth1\Client\Test\Server;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use League\OAuth1\Client\Server\Bitbucket;
use League\OAuth1\Client\Server\BitbucketResourceOwner;
use League\OAuth1\Client\Credentials\TemporaryCredentials;
use League\OAuth1\Client\Credentials\TokenCredentials;
use Mockery as m;
use Psr\Http\Message\ResponseInterface;

class BitbucketTest extends \PHPUnit_Framework_TestCase
{
    protected $server;

    protected function setUp()
    {
        $this->server = new \League\OAuth1\Client\Server\Bitbucket([
            'identifier' => 'mock_identifier',
            'secret' => 'mock_secret',
            'callbackUri' => 'http://example.com/',
        ]);
    }

    public function tearDown()
    {
        m::close();
        parent::tearDown();
    }

    public function testGetAuthorizationUrl()
    {
        $credentials = m::mock(TemporaryCredentials::class);
        $credentials->shouldReceive('getIdentifier')->andReturn('foo');

        $url = $this->server->getAuthorizationUrl($credentials);

        $uri = parse_url($url);
        parse_str($uri['query'], $query);

        $this->assertArrayHasKey('oauth_token', $query);
        $this->assertEquals('/api/1.0/oauth/authenticate', $uri['path']);
    }

    public function testGetTemporaryCredentials()
    {
        $response = m::mock(ResponseInterface::class);
        $response->shouldReceive('getBody')->andReturn('oauth_token=temporarycredentialsidentifier&oauth_token_secret=temporarycredentialssecret&oauth_callback_confirmed=true');

        $client = m::mock(ClientInterface::class);
        $client->shouldReceive('send')->with(m::on(function($request) {
            $this->assertEquals('https', $request->getUri()->getScheme());
            $this->assertEquals('bitbucket.org', $request->getUri()->getHost());
            $this->assertEquals('/api/1.0/oauth/request_token', $request->getUri()->getPath());

            return true;
        }))->once()->andReturn($response);

        $temporaryCredentials = $this->server->setHttpClient($client)
            ->getTemporaryCredentials();

        $this->assertInstanceOf(TemporaryCredentials::class, $temporaryCredentials);
        $this->assertEquals('temporarycredentialsidentifier', (string) $temporaryCredentials);
        $this->assertEquals('temporarycredentialssecret', $temporaryCredentials->getSecret());
    }

    public function testGetAccessToken()
    {
        $temporaryIdentifier = 'foo';
        $verifier = 'bar';

        $response = m::mock(ResponseInterface::class);
        $response->shouldReceive('getBody')->andReturn('oauth_token=tokencredentialsidentifier&oauth_token_secret=tokencredentialssecret');

        $temporaryCredentials = m::mock(TemporaryCredentials::class);
        $temporaryCredentials->shouldReceive('getIdentifier')->andReturn('foo');
        $temporaryCredentials->shouldReceive('getSecret')->andReturn('bar');
        $temporaryCredentials->shouldReceive('checkIdentifier')->with($temporaryIdentifier);

        $client = m::mock(ClientInterface::class);
        $client->shouldReceive('send')->with(m::on(function($request) {
            $this->assertEquals('https', $request->getUri()->getScheme());
            $this->assertEquals('bitbucket.org', $request->getUri()->getHost());
            $this->assertEquals('/api/1.0/oauth/access_token', $request->getUri()->getPath());

            return true;
        }))->once()->andReturn($response);

        $tokenCredentials = $this->server->setHttpClient($client)
            ->getTokenCredentials($temporaryCredentials, $temporaryIdentifier, $verifier);

        $this->assertInstanceOf(TokenCredentials::class, $tokenCredentials);
        $this->assertEquals('tokencredentialsidentifier', (string) $tokenCredentials);
        $this->assertEquals('tokencredentialssecret', $tokenCredentials->getSecret());
    }

    public function testResourceOwnerData()
    {
        $userJson = file_get_contents(dirname(__DIR__).'/user.json');
        $response = m::mock(ResponseInterface::class);
        $response->shouldReceive('getBody')->andReturn($userJson);
        $expectedUser = json_decode($userJson, true);

        $tokenCredentials = m::mock(TokenCredentials::class);
        $tokenCredentials->shouldReceive('getIdentifier')->andReturn('foo');
        $tokenCredentials->shouldReceive('getSecret')->andReturn('bar');

        $client = m::mock(ClientInterface::class);
        $client->shouldReceive('send')->with(m::on(function($request) {
            $this->assertEquals('https', $request->getUri()->getScheme());
            $this->assertEquals('bitbucket.org', $request->getUri()->getHost());
            $this->assertEquals('/api/1.0/user', $request->getUri()->getPath());

            return true;
        }))->once()->andReturn($response);

        $resourceOwner = $this->server->setHttpClient($client)
            ->getResourceOwner($tokenCredentials);

        $this->assertInstanceOf(BitbucketResourceOwner::class, $resourceOwner);
        $this->assertEquals($expectedUser['id'], $resourceOwner->getId());
        $this->assertEquals($expectedUser, $resourceOwner->toArray());
    }

    /**
     * @expectedExceptions League\OAuth1\Client\Exception\IdentityProviderException
     **/
    // public function testExceptionThrownWhenErrorObjectReceived()
    // {
    //     //
    // }

    public function testGetAuthenticatedRequest()
    {
        //
    }
}
