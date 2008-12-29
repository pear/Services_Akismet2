<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * PHPUnit3.2 test framework script for the Services_Akismet2 package.
 *
 * These tests require the PHPUnit 3.2 package to be installed. PHPUnit is
 * installable using PEAR. See the
 * {@link http://www.phpunit.de/pocket_guide/3.2/en/installation.html manual}
 * for detailed installation instructions.
 *
 * LICENSE:
 *
 * This library is free software; you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as
 * published by the Free Software Foundation; either version 2.1 of the
 * License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
 *
 * @category  Services
 * @package   Services_Akismet2
 * @author    Michael Gauthier <mike@silverorange.com>
 * @copyright 2008 silverorange
 * @license   http://www.gnu.org/copyleft/lesser.html LGPL License 2.1
 * @version   CVS: $Id$
 * @link      http://pear.php.net/package/Services_Akismet2
 */

/**
 * PHPUnit3 framework
 */
require_once 'PHPUnit/Framework.php';

/**
 * Akismet class to test
 */
require_once 'Services/Akismet2.php';

/**
 * Akismet comment class
 */
require_once 'Services/Akismet2/Comment.php';

/**
 * For mock HTTP responses
 *
 * @see http://clockwerx.blogspot.com/2008/11/pear-and-unit-tests-httprequest2.html
 */
require_once 'HTTP/Request2.php';

/**
 * For mock HTTP responses
 *
 * @see http://clockwerx.blogspot.com/2008/11/pear-and-unit-tests-httprequest2.html
 */
require_once 'HTTP/Request2/Adapter/Mock.php';

/**
 * Base class for testing Services_Akismet2
 *
 * @category  Services
 * @package   Services_Akismet2
 * @author    Michael Gauthier <mike@silverorange.com>
 * @copyright 2008 silverorange
 * @license   http://www.gnu.org/copyleft/lesser.html LGPL License 2.1
 * @link      http://pear.php.net/package/Services_Akismet2
 */
class Services_Akismet2_TestCase extends PHPUnit_Framework_TestCase
{
    // {{{ protected properties

    /**
     * @var HTTP_Request2_Adapter_Mock
     *
     * @see Services_Akismet2_TestCase::addHttpResponse()
     */
    protected $mock = null;

    // }}}
    // {{{ private properties

    /**
     * @var integer
     */
    private $_oldErrorLevel;

    // }}}
    // {{{ setUp()

    public function setUp()
    {
        $this->_oldErrorLevel = error_reporting(E_ALL | E_STRICT);

        $this->mock = new HTTP_Request2_Adapter_Mock();

        $request = new HTTP_Request2();
        $request->setAdapter($this->mock);

        $this->akismet = new Services_Akismet2('', '', array(), $request);
    }

    // }}}
    // {{{ tearDown()

    public function tearDown()
    {
        unset($this->akismet);
        unset($this->mock);
        error_reporting($this->_oldErrorLevel);
    }

    // }}}
    // {{{ addHttpResponse()

    protected function addHttpResponse($body, $status = 'HTTP/1.1 200 OK')
    {
        $response = new HTTP_Request2_Response($status);
        $response->appendBody($body);
        $this->mock->addResponse($response);
    }

    // }}}

    // tests
    // {{{ testIsSpam()

    public function testIsSpam()
    {
        $this->addHttpResponse('valid');
        $this->addHttpResponse('true');
        $this->addHttpResponse('false');

        $spamComment = new Services_Akismet2_Comment(array(
            'author'      => 'viagra-test-123',
            'authorEmail' => 'test@example.com',
            'authorUri'   => 'http://example.com/',
            'content'     => 'Spam, I am.',
            'userIp'      => '127.0.0.1',
            'userAgent'   => 'Services_Akismet2 unit tests',
            'referrer'    => 'http://example.com/'
        ));

        $isSpam = $this->akismet->isSpam($spamComment);
        $this->assertTrue($isSpam);

        $comment = new Services_Akismet2_Comment(array(
            'author'      => 'Services_Akismet2 unit tests',
            'authorEmail' => 'test@example.com',
            'authorUri'   => 'http://example.com/',
            'content'     => 'Hello, World!',
            'userIp'      => '127.0.0.1',
            'userAgent'   => 'Services_Akismet2 unit tests',
            'referrer'    => 'http://example.com/'
        ));

        $isSpam = $this->akismet->isSpam($comment);
        $this->assertFalse($isSpam);
    }

    // }}}
    // {{{ testSubmitSpam()

    public function testSubmitSpam()
    {
        $this->addHttpResponse('valid');
        $this->addHttpResponse('Thanks for making the web a better place.');

        $spamComment = new Services_Akismet2_Comment(array(
            'author'      => 'viagra-test-123',
            'authorEmail' => 'test@example.com',
            'authorUri'   => 'http://example.com/',
            'content'     => 'Spam, I am.',
            'userIp'      => '127.0.0.1',
            'userAgent'   => 'Services_Akismet2 unit tests',
            'referrer'    => 'http://example.com/'
        ));

        $newAkismet = $this->akismet->submitSpam($spamComment);

        // test fluent interface
        $this->assertSame($this->akismet, $newAkismet);
    }

    // }}}
    // {{{ testSubmitFalsePositive()

    public function testSubmitFalsePositive()
    {
        $this->addHttpResponse('valid');
        $this->addHttpResponse('Thanks for making the web a better place.');

        $comment = new Services_Akismet2_Comment(array(
            'author'      => 'Services_Akismet2 unit tests',
            'authorEmail' => 'test@example.com',
            'authorUri'   => 'http://example.com/',
            'content'     => 'Hello, World!',
            'userIp'      => '127.0.0.1',
            'userAgent'   => 'Services_Akismet2 unit tests',
            'referrer'    => 'http://example.com/'
        ));

        $newAkismet = $this->akismet->submitFalsePositive($comment);

        // test fluent interface
        $this->assertSame($this->akismet, $newAkismet);
    }

    // }}}
    // {{{ testInvalidApiKeyException()

    /**
     * @expectedException Services_Akismet2_InvalidApiKeyException
     */
    public function testInvalidApiKeyException()
    {
        $this->addHttpResponse('invalid');

        $spamComment = new Services_Akismet2_Comment(array(
            'author'      => 'viagra-test-123',
            'authorEmail' => 'test@example.com',
            'authorUri'   => 'http://example.com/',
            'content'     => 'Spam, I am.',
            'userIp'      => '127.0.0.1',
            'userAgent'   => 'Services_Akismet2 unit tests',
            'referrer'    => 'http://example.com/'
        ));

        // try to make a request
        $this->akismet->submitSpam($spamComment);
    }

    // }}}
}

?>
