<?php
require_once 'PHPUnit/Framework.php';

require_once '/home/afranco/private_html/phpcas/source/CAS/CookieJar.php';


/**
 * Test harness for the cookie Jar to allow us to test protected methods.
 *
 */
class CAS_CookieJarExposed extends CAS_CookieJar {
    public function __call($method, array $args = array()) {
        if (!method_exists($this, $method))
            throw new BadMethodCallException("method '$method' does not exist");
        return call_user_method_array($method, $this, $args);
    }
}


/**
 * Test class for verifying the operation of cookie handling methods used in
 * serviceWeb() proxy calls.
 *
 *
 * Generated by PHPUnit on 2010-09-07 at 13:33:53.
 */
class CookieJarTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var CASClient
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
	$this->cookieArray = array();
        $this->object = new CAS_CookieJarExposed($this->cookieArray);

        $this->serviceUrl_1 = 'http://service.example.com/lookup/?action=search&query=username';
        $this->responseHeaders_1 = array(
		'HTTP/1.1 302 Found',
		'Date: Tue, 07 Sep 2010 17:51:54 GMT',
		'Server: Apache/2.2.3 (Red Hat)',
		'X-Powered-By: PHP/5.1.6',
		'Set-Cookie: SID=k1jut1r1bqrumpei837kk4jks0; path=/',
		'Expires: Thu, 19 Nov 1981 08:52:00 GMT',
		'Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0',
		'Pragma: no-cache',
		'Location: https://cas.example.edu:443/cas/login?service=http%3A%2F%2Fservice.example.edu%2Flookup%2F%3Faction%3Dsearch%26query%3Dusername',
		'Content-Length: 525',
		'Connection: close',
		'Content-Type: text/html; charset=UTF-8',
        );
        $this->serviceUrl_1b = 'http://service.example.com/lookup/?action=search&query=another_username';
        $this->serviceUrl_1c = 'http://service.example.com/make_changes.php';

        // Verify that there are no cookies to start.
	$this->assertEquals(0, count($this->object->getServiceCookies($this->serviceUrl_1)));
	$this->assertEquals(0, count($this->object->getServiceCookies($this->serviceUrl_1b)));
	$this->assertEquals(0, count($this->object->getServiceCookies($this->serviceUrl_1c)));

	// Add service cookies as if we just made are request to serviceUrl_1
	// and recieved responseHeaders_1 as the header to the response.
        $this->object->setServiceCookies($this->serviceUrl_1, $this->responseHeaders_1);
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {

    }

    /**
     * @todo Implement testServiceWeb().
     */
    public function testServiceWeb()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
          'This test has not been implemented yet.'
        );
    }

    /**
     * Verify that our first response will set a cookie that will be available to
     * the same URL.
     */
    public function testSameUrlCookies()
    {
        // Verify that our cookie is available.
        $cookies = $this->object->getServiceCookies($this->serviceUrl_1);
        $this->assertEquals(1, count($cookies));
        $this->assertEquals('k1jut1r1bqrumpei837kk4jks0', $cookies['SID']);
    }

    /**
     * Verify that our first response will set a cookie that is available to a second
     * request to a different url on the same host.
     */
    public function testSamePathDifferentQueryCookies()
    {
        // Verify that our cookie is available.
        $cookies = $this->object->getServiceCookies($this->serviceUrl_1b);
        $this->assertEquals(1, count($cookies));
        $this->assertEquals('k1jut1r1bqrumpei837kk4jks0', $cookies['SID']);
    }

    /**
     * Verify that our first response will set a cookie that is available to a second
     * request to a different url on the same host.
     */
    public function testDifferentPathCookies()
    {
        // Verify that our cookie is available.
        $cookies = $this->object->getServiceCookies($this->serviceUrl_1c);
        $this->assertEquals(1, count($cookies));
        $this->assertEquals('k1jut1r1bqrumpei837kk4jks0', $cookies['SID']);
    }

    /**
     * Verify that when no domain is set for the cookie, it will be unavailable
     * to other hosts
     */
    public function testDifferentHostCookies()
    {
        // Verify that our cookie isn't available when the hostname is changed.
        $cookies = $this->object->getServiceCookies('http://service2.example.com/make_changes.php');
        $this->assertEquals(0, count($cookies));

        // Verify that our cookie isn't available when the domain is changed.
        $cookies = $this->object->getServiceCookies('http://service.example2.com/make_changes.php');
        $this->assertEquals(0, count($cookies));

        // Verify that our cookie isn't available when the tdl is changed.
        $cookies = $this->object->getServiceCookies('http://service.example.org/make_changes.php');
        $this->assertEquals(0, count($cookies));
    }

    /**
     * Test the basic operation of parseCookieHeaders.
     */
    public function testParseCookieHeaders()
    {
        $cookies = $this->object->parseCookieHeaders($this->responseHeaders_1, 'service.example.com');

        $this->assertType('array', $cookies);
        $this->assertEquals(1, count($cookies));
        $this->assertEquals('SID', $cookies[0]['name']);
        $this->assertEquals('k1jut1r1bqrumpei837kk4jks0', $cookies[0]['value']);
        $this->assertEquals('/', $cookies[0]['path']);
        $this->assertEquals('service.example.com', $cookies[0]['domain']);
        $this->assertFalse($cookies[0]['secure']);
    }

    /**
     * Test the addition of a domain to the parsing of cookie headers
     */
    public function testParseCookieHeaders_Domain()
    {
	$headers = array('Set-Cookie: SID=k1jut1r1bqrumpei837kk4jks0; path=/; domain=.example.com');
        $cookies = $this->object->parseCookieHeaders($headers, 'service.example.com');

        $this->assertType('array', $cookies);
        $this->assertEquals(1, count($cookies));
        $this->assertEquals('SID', $cookies[0]['name']);
        $this->assertEquals('k1jut1r1bqrumpei837kk4jks0', $cookies[0]['value']);
        $this->assertEquals('/', $cookies[0]['path']);
        $this->assertEquals('.example.com', $cookies[0]['domain']);
        $this->assertFalse($cookies[0]['secure']);
    }

    /**
     * Test the addition of a domain to the parsing of cookie headers
     */
    public function testParseCookieHeaders_hostname()
    {
	$headers = array('Set-Cookie: SID=k1jut1r1bqrumpei837kk4jks0; path=/; domain=service.example.com');
        $cookies = $this->object->parseCookieHeaders($headers, 'service.example.com');

        $this->assertType('array', $cookies);
        $this->assertEquals(1, count($cookies));
        $this->assertEquals('SID', $cookies[0]['name']);
        $this->assertEquals('k1jut1r1bqrumpei837kk4jks0', $cookies[0]['value']);
        $this->assertEquals('/', $cookies[0]['path']);
        $this->assertEquals('service.example.com', $cookies[0]['domain']);
        $this->assertFalse($cookies[0]['secure']);
    }

    /**
     * Test the usage of a hostname that is different from the default URL.
     */
    public function testParseCookieHeaders_altHostname()
    {
	$headers = array('Set-Cookie: SID=k1jut1r1bqrumpei837kk4jks0; path=/; domain=service2.example.com');
        $cookies = $this->object->parseCookieHeaders($headers, 'service.example.com');

        $this->assertType('array', $cookies);
        $this->assertEquals(1, count($cookies));
        $this->assertEquals('SID', $cookies[0]['name']);
        $this->assertEquals('k1jut1r1bqrumpei837kk4jks0', $cookies[0]['value']);
        $this->assertEquals('/', $cookies[0]['path']);
        $this->assertEquals('service2.example.com', $cookies[0]['domain']);
        $this->assertFalse($cookies[0]['secure']);
    }

    /**
     * Test the the inclusion of a path in the cookie.
     */
    public function testParseCookieHeaders_path()
    {
	$headers = array('Set-Cookie: SID=k1jut1r1bqrumpei837kk4jks0; path=/something/; domain=service2.example.com');
        $cookies = $this->object->parseCookieHeaders($headers, 'service.example.com');

        $this->assertType('array', $cookies);
        $this->assertEquals(1, count($cookies));
        $this->assertEquals('SID', $cookies[0]['name']);
        $this->assertEquals('k1jut1r1bqrumpei837kk4jks0', $cookies[0]['value']);
        $this->assertEquals('/something/', $cookies[0]['path']);
        $this->assertEquals('service2.example.com', $cookies[0]['domain']);
        $this->assertFalse($cookies[0]['secure']);
    }

    /**
     * Test the addition of a 'Secure' parameter
     */
    public function testParseCookieHeaders_secure()
    {
	$headers = array('Set-Cookie: SID=k1jut1r1bqrumpei837kk4jks0; Secure; path=/something/; domain=service2.example.com');
        $cookies = $this->object->parseCookieHeaders($headers, 'service.example.com');

        $this->assertType('array', $cookies);
        $this->assertEquals(1, count($cookies));
        $this->assertEquals('SID', $cookies[0]['name']);
        $this->assertEquals('k1jut1r1bqrumpei837kk4jks0', $cookies[0]['value']);
        $this->assertEquals('/something/', $cookies[0]['path']);
        $this->assertEquals('service2.example.com', $cookies[0]['domain']);
        $this->assertTrue($cookies[0]['secure']);
    }

    /**
     * Test the addition of a 'Secure' parameter that is lower-case
     */
    public function testParseCookieHeaders_secureLC()
    {
	$headers = array('Set-Cookie: SID=k1jut1r1bqrumpei837kk4jks0; secure; path=/something/; domain=service2.example.com');
        $cookies = $this->object->parseCookieHeaders($headers, 'service.example.com');

        $this->assertType('array', $cookies);
        $this->assertEquals(1, count($cookies));
        $this->assertEquals('SID', $cookies[0]['name']);
        $this->assertEquals('k1jut1r1bqrumpei837kk4jks0', $cookies[0]['value']);
        $this->assertEquals('/something/', $cookies[0]['path']);
        $this->assertEquals('service2.example.com', $cookies[0]['domain']);
        $this->assertTrue($cookies[0]['secure']);
    }

    /**
     * Test the inclusion of a semicolon in a quoted cookie value.
     *
     * Note: As of September 12th, the current implementation is known to
     * fail this test since it explodes values on the semicolon symbol. This
     * behavior is not ideal but should be ok for most cases.
     */
    public function testParseCookieHeaders_quotedSemicolon()
    {
	$headers = array('Set-Cookie: SID="hello;world"; path=/; domain=.example.com');
        $cookies = $this->object->parseCookieHeaders($headers, 'service.example.com');

        $this->assertType('array', $cookies);
        $this->assertEquals(1, count($cookies));
        $this->assertEquals('SID', $cookies[0]['name']);
        $this->assertEquals('hello;world', $cookies[0]['value']);
        $this->assertEquals('/', $cookies[0]['path']);
        $this->assertEquals('.example.com', $cookies[0]['domain']);
        $this->assertFalse($cookies[0]['secure']);
    }

    /**
     * Test the inclusion of an escaped quote in a quoted cookie value.
     */
    public function testParseCookieHeaders_quotedQuote()
    {
	$headers = array('Set-Cookie: SID="hello\"world"; path=/; domain=.example.com');
        $cookies = $this->object->parseCookieHeaders($headers, 'service.example.com');

        $this->assertType('array', $cookies);
        $this->assertEquals(1, count($cookies));
        $this->assertEquals('SID', $cookies[0]['name']);
        $this->assertEquals('hello\"world', $cookies[0]['value']);
        $this->assertEquals('/', $cookies[0]['path']);
        $this->assertEquals('.example.com', $cookies[0]['domain']);
        $this->assertFalse($cookies[0]['secure']);
    }

    /**
     * Test the inclusion of a trailing semicolon
     */
    public function testParseCookieHeaders_trailingSemicolon()
    {
	$headers = array('Set-Cookie: SID="hello world"; path=/;');
        $cookies = $this->object->parseCookieHeaders($headers, 'service.example.com');

        $this->assertType('array', $cookies);
        $this->assertEquals(1, count($cookies));
        $this->assertEquals('SID', $cookies[0]['name']);
        $this->assertEquals('hello world', $cookies[0]['value']);
        $this->assertEquals('/', $cookies[0]['path']);
        $this->assertEquals('service.example.com', $cookies[0]['domain']);
        $this->assertFalse($cookies[0]['secure']);
    }

    /**
     * Test the inclusion of an equals in a quoted cookie value.
     *
     * Note: As of September 12th, the current implementation is known to
     * fail this test since it explodes values on the equals symbol. This
     * behavior is not ideal but should be ok for most cases.
     */
    public function testParseCookieHeaders_quotedEquals()
    {
	$headers = array('Set-Cookie: SID="hello=world"; path=/; domain=.example.com');
        $cookies = $this->object->parseCookieHeaders($headers, 'service.example.com');

        $this->assertType('array', $cookies);
        $this->assertEquals(1, count($cookies));
        $this->assertEquals('SID', $cookies[0]['name']);
        $this->assertEquals('hello;world', $cookies[0]['value']);
        $this->assertEquals('/', $cookies[0]['path']);
        $this->assertEquals('.example.com', $cookies[0]['domain']);
        $this->assertFalse($cookies[0]['secure']);
    }

    /**
     * Test setting a single service cookie
     */
    public function testSetServiceCookie()
    {
        $cookies = $this->object->getServiceCookies($this->serviceUrl_1c);
        $this->assertType('array', $cookies);
        $this->assertEquals(1, count($cookies));
        $this->assertEquals('k1jut1r1bqrumpei837kk4jks0', $cookies['SID']);
    }

    /**
     * Test setting a single service cookie
     */
    public function testSetServiceCookie_duplicates()
    {
	$headers = array('Set-Cookie: SID="hello world"; path=/');
        $cookiesToSet = $this->object->parseCookieHeaders($headers, 'service.example.com');
        $this->object->setServiceCookie($cookiesToSet[0]);

        $headers = array('Set-Cookie: SID="goodbye world"; path=/');
        $cookiesToSet = $this->object->parseCookieHeaders($headers, 'service.example.com');
        $this->object->setServiceCookie($cookiesToSet[0]);

        $cookies = $this->object->getServiceCookies($this->serviceUrl_1c);
        $this->assertType('array', $cookies);
        $this->assertEquals(1, count($cookies));
        $this->assertEquals('goodbye world', $cookies['SID']);
    }

    /**
     * Test setting two service cookies
     */
    public function testSetServiceCookie_twoCookies()
    {
        // Second cookie
        $headers = array('Set-Cookie: message="hello world"; path=/');
        $cookiesToSet = $this->object->parseCookieHeaders($headers, 'service.example.com');
        $this->object->setServiceCookie($cookiesToSet[0]);


        $cookies = $this->object->getServiceCookies($this->serviceUrl_1c);
        $this->assertType('array', $cookies);
        $this->assertEquals(2, count($cookies));
        $this->assertEquals('k1jut1r1bqrumpei837kk4jks0', $cookies['SID']);
        $this->assertEquals('hello world', $cookies['message']);
    }

    /**
     * Test setting two service cookies
     */
    public function testSetServiceCookie_twoCookiesOneAtDomain()
    {

        // Second cookie
        $headers = array('Set-Cookie: message="hello world"; path=/; domain=.example.com');
        $cookiesToSet = $this->object->parseCookieHeaders($headers, 'service.example.com');
        $this->object->setServiceCookie($cookiesToSet[0]);


        $cookies = $this->object->getServiceCookies($this->serviceUrl_1c);
        $this->assertType('array', $cookies);
        $this->assertEquals(2, count($cookies));
        $this->assertEquals('k1jut1r1bqrumpei837kk4jks0', $cookies['SID']);
        $this->assertEquals('hello world', $cookies['message']);
    }

    /**
     * @todo Implement testDiscardServiceCookie().
     */
    public function testDiscardServiceCookie()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
          'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testExpireServiceCookies().
     */
    public function testExpireServiceCookies()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
          'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testCookieMatchesTarget().
     */
    public function testCookieMatchesTarget()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
          'This test has not been implemented yet.'
        );
    }

    /**
     * Test setting two service cookies
     */
    public function testDomainCookieMatchesTarget()
    {
        $headers = array('Set-Cookie: message="hello world"; path=/; domain=.example.com');
        $cookies = $this->object->parseCookieHeaders($headers, 'otherhost.example.com');

        $this->assertTrue($this->object->cookieMatchesTarget($cookies[0], parse_url('http://service.example.com/make_changes.php')));
    }

}
?>
