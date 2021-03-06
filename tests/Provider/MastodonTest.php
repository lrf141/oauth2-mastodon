<?php
/**
 * Created by PhpStorm.
 * User: lrf141
 * Date: 18/08/30
 * Time: 11:18
 */

namespace Lrf141\OAuth2\Client\Test\Provider;

use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\QueryBuilderTrait;
use Mockery as m;

class MastodonTest extends \PHPUnit\Framework\TestCase
{

    use QueryBuilderTrait;


    /**
     * @var \Lrf141\OAuth2\Client\Provider\Mastodon
     */
    protected $provider;


    protected function setUp()
    {
        $this->provider = new \Lrf141\OAuth2\Client\Provider\Mastodon([
            'clientId' => 'mock_client_id',
            'clientSecret' => 'mock_secret',
            'redirectUri' => 'none',
            'instance' => 'https://mstdn.jp',
        ]);
    }


    public function tearDown()
    {
        m::close();
        parent::tearDown();
    }


    public function testAuthorizationUrl()
    {
        $url = $this->provider->getAuthorizationUrl([
            'scope' => 'read write follow',
            'response_type' => 'code',
            'redirect_uri' => 'urn:ietf:wg:oauth:2.0:oob',
            'client_id' => 'mock_client_id',
            'test_params' => [],
        ]);

        $uri = parse_url($url);
        parse_str($uri['query'], $query);

        $this->assertArrayHasKey('client_id', $query);
        $this->assertArrayHasKey('scope', $query);
        $this->assertArrayHasKey('response_type', $query);
        $this->assertArrayHasKey('redirect_uri', $query);
        $this->assertArrayHasKey('state', $query);
        $this->assertArrayNotHasKey('test_params', $query);
        $this->assertNotNull($this->provider->getState());
    }

    public function testInstanceUrl()
    {
        $url = $this->provider->getInstanceUrl();
        $this->assertSame('https://mstdn.jp', $url);
    }


    public function testScopes()
    {
        $options = [
            'scope' => 'read write follow',
        ];
        $query = ['scope' => 'read write follow'];
        $url = $this->provider->getAuthorizationUrl($options);
        $encodedScope = $this->buildQueryString($query);
        $this->assertContains($encodedScope, $url);
    }


    public function testGetAuthorizationUrl()
    {
        $url = $this->provider->getAuthorizationUrl();
        $uri = parse_url($url);

        $this->assertEquals('/oauth/authorize', $uri['path']);
    }

    public function testGetBaseAccessTokenUrl()
    {
        $url = $this->provider->getBaseAccessTokenUrl([]);
        $uri = parse_url($url);
        $this->assertEquals('/oauth/token', $uri['path']);
    }

    public function testGetAccessToken()
    {
        $response = m::mock('Psr\Http\Message\ResponseInterface');
        $response->shouldReceive('getBody')->andReturn('{"access_token": "mock_access_token", "token_type": "bearer", "account_id": "12345", "uid": "deprecated_id"}');
        $response->shouldReceive('getHeader')->andReturn(['content-type' => 'json']);

        $client = m::mock('GuzzleHttp\ClientInterface');
        $client->shouldReceive('send')->times(1)->andReturn($response);
        $this->provider->setHttpClient($client);

        $token = $this->provider->getAccessToken('authorization_code', ['code' => 'mock_authorization_code']);
        $this->assertEquals('mock_access_token', $token->getToken());
    }

    public function testGetInstanceUrl()
    {
        $url = $this->provider->getInstanceUrl();

        $this->assertEquals('https://mstdn.jp', $url);
        $this->assertNotEquals('https://mastodon.jp', $url);
    }

    public function testUserData()
    {

        $name = uniqid();
        $account_id = 'dbid:' . rand(1000, 9999);
        $postResponse = m::mock('Psr\Http\Message\ResponseInterface');
        $postResponse->shouldReceive('getBody')->andReturn('{"access_token": "mock_access_token", "token_type": "bearer", "account_id": "' . $account_id . '", "uid": "12345"}');
        $postResponse->shouldReceive('getHeader')->andReturn(['content-type' => 'json']);
        $userResponse = m::mock('Psr\Http\Message\ResponseInterface');
        $userResponse->shouldReceive('getBody')->andReturn('{"id": "'.$account_id.'", "username": "'.$name.'", "display_name": "'.$name.'"}');
        $userResponse->shouldReceive('getHeader')->andReturn(['content-type' => 'json']);
        $client = m::mock('GuzzleHttp\ClientInterface');
        $client->shouldReceive('send')
            ->times(2)
            ->andReturn($postResponse, $userResponse);
        $this->provider->setHttpClient($client);
        $token = $this->provider->getAccessToken('authorization_code', ['code' => 'mock_authorization_code']);
        $user = $this->provider->getResourceOwner($token);

        $this->assertEquals($account_id, $user->getId());
        $this->assertEquals($account_id, $user->toArray()['id']);
        $this->assertEquals($name, $user->getName());
        $this->assertEquals($name, $user->toArray()['username']);
    }

    /**
     * @expectedException League\OAuth2\Client\Provider\Exception\IdentityProviderException
     */
    public function testThrowExceptionWhenErrorObjectReceived()
    {
        $message = uniqid();
        $status = rand(400,600);
        $postResponse = m::mock('Psr\Http\Message\ResponseInterface');
        $postResponse->shouldReceive('getBody')->andReturn('{"error": "'.$message.'"}');
        $postResponse->shouldReceive('getHeader')->andReturn(['content-type' => 'json']);
        $postResponse->shouldReceive('getStatusCode')->andReturn($status);
        $client = m::mock('GuzzleHttp\ClientInterface');
        $client->shouldReceive('send')
            ->times(1)
            ->andReturn($postResponse);
        $this->provider->setHttpClient($client);
        $token = $this->provider->getAccessToken('authorization_code', ['code' => 'mock_authorization_code']);
    }
}
