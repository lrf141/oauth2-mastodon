<?php
/**
 * Created by PhpStorm.
 * User: lrf141
 * Date: 18/08/30
 * Time: 11:18
 */

namespace Lrf141\OAuth2\Client\Test\Provider;

use League\OAuth2\Client\Tool\QueryBuilderTrait;

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
}
