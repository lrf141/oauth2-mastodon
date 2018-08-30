<?php
/**
 * Created by PhpStorm.
 * User: lrf141
 * Date: 18/08/30
 * Time: 11:18
 */

namespace Lrf141\OAuth2\Client\Test\Provider;

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
}
