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
 * Copyright (c) 2008-2011 silverorange
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @category  Services
 * @package   Services_Akismet2
 * @author    Michael Gauthier <mike@silverorange.com>
 * @copyright 2008-2011 silverorange
 * @license   http://www.opensource.org/licenses/mit-license.html MIT License
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
 * @license   http://www.opensource.org/licenses/mit-license.html MIT License
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
    // {{{ setUp()

    public function setUp()
    {
        $this->mock = new HTTP_Request2_Adapter_Mock();

        $request = new HTTP_Request2();
        $request->setAdapter($this->mock);

        $this->akismet = new Services_Akismet2('http://blog.example.com/',
            'AABBCCDDEEFF', array(), $request);
    }

    // }}}
    // {{{ tearDown()

    public function tearDown()
    {
        unset($this->akismet);
        unset($this->mock);
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

    // general tests
    // {{{ testConstructWithoutHttpRequest()

    public function testConstructWithoutHttpRequest()
    {
        $akismet = new Services_Akismet2('http://test.example.com', 'test');

        $constraint = $this->attribute(
            $this->isInstanceOf('HTTP_Request2'), 'request'
        );

        $this->assertThat($akismet, $constraint,
            'A default HTTP Request object was not assigned in constructor.');
    }

    // }}}
    // {{{ testSetConfigWithSingleValue()

    public function testSetConfigWithSingleValue()
    {
        $this->akismet->setConfig('apiServer', 'http://akismet.example.com/');
        $this->assertAttributeEquals('http://akismet.example.com/',
            'apiServer', $this->akismet,
            'Setting single config value \'apiServer\' failed.');
    }

    // }}}
    // {{{ testSetConfigWithArray()

    public function testSetConfigWithArray()
    {
        $config = array(
            'apiServer'  => 'http://akismet.example.com/',
            'apiPort'    => 8080,
            'apiVersion' => '1.0',
            'userAgent'  => 'Services_Akismet2 Unit Tests/2.0 | Akismet/1.1'
        );

        $this->akismet->setConfig($config);

        foreach ($config as $name => $value) {
            $this->assertAttributeEquals($value, $name, $this->akismet,
                sprintf('Setting single config value \'%s\' failed.', $name));
        }
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
            'comment_author'       => 'viagra-test-123',
            'comment_author_email' => 'test@example.com',
            'comment_author_url'   => 'http://example.com/',
            'comment_content'      => 'Spam, I am.',
            'user_ip'              => '127.0.0.1',
            'user_agent'           => 'Services_Akismet2 unit tests',
            'referrer'             => 'http://example.com/'
        ));

        // try to make a request
        $this->akismet->submitSpam($spamComment);
    }

    // }}}
    // {{{ testFluentInterface()

    public function testFluentInterface()
    {
        $this->addHttpResponse('valid');
        $this->addHttpResponse('Thanks for making the web a better place.');
        $this->addHttpResponse('Thanks for making the web a better place.');

        // submitSpam()
        $newAkismet = $this->akismet->submitSpam(array(
            'comment_author'       => 'viagra-test-123',
            'comment_author_email' => 'test@example.com',
            'comment_author_url'   => 'http://example.com/',
            'comment_content'      => 'Spam, I am.',
            'user_ip'              => '127.0.0.1',
            'user_agent'           => 'Services_Akismet2 unit tests',
            'referrer'             => 'http://example.com/'
        ));

        $this->assertSame($this->akismet, $newAkismet);

        // submitFalsePositive()
        $newAkismet = $this->akismet->submitFalsePositive(array(
            'comment_author'       => 'Services_Akismet2 unit tests',
            'comment_author_email' => 'test@example.com',
            'comment_author_url'   => 'http://example.com/',
            'comment_content'      => 'Hello, World!',
            'user_ip'              => '127.0.0.1',
            'user_agent'           => 'Services_Akismet2 unit tests',
            'referrer'             => 'http://example.com/'
        ));

        $this->assertSame($this->akismet, $newAkismet);

        // setConfig()
        $newAkismet = $this->akismet->setConfig('apiPort', 8080);
        $this->assertSame($this->akismet, $newAkismet);

        // setRequest()
        $newAkismet = $this->akismet->setRequest(new HTTP_Request2());
        $this->assertSame($this->akismet, $newAkismet);
    }

    // }}}

    // Akismet API tests
    // {{{ testIsSpamYes()

    public function testIsSpamYes()
    {
        $this->addHttpResponse('valid');
        $this->addHttpResponse('true');

        $spamComment = new Services_Akismet2_Comment(array(
            'comment_author'       => 'viagra-test-123',
            'comment_author_email' => 'test@example.com',
            'comment_author_url'   => 'http://example.com/',
            'comment_content'      => 'Spam, I am.',
            'user_ip'              => '127.0.0.1',
            'user_agent'           => 'Services_Akismet2 unit tests',
            'referrer'             => 'http://example.com/'
        ));

        $isSpam = $this->akismet->isSpam($spamComment);
        $this->assertTrue($isSpam);
    }

    // }}}
    // {{{ testIsSpamNo()

    public function testIsSpamNo()
    {
        $this->addHttpResponse('valid');
        $this->addHttpResponse('false');

        $comment = new Services_Akismet2_Comment(array(
            'comment_author'       => 'Services_Akismet2 unit tests',
            'comment_author_email' => 'test@example.com',
            'comment_author_url'   => 'http://example.com/',
            'comment_content'      => 'Hello, World!',
            'user_ip'              => '127.0.0.1',
            'user_agent'           => 'Services_Akismet2 unit tests',
            'referrer'             => 'http://example.com/'
        ));

        $isSpam = $this->akismet->isSpam($comment);
        $this->assertFalse($isSpam);
    }

    // }}}
    // {{{ testIsSpamWithArray()

    public function testIsSpamWithArray()
    {
        $this->addHttpResponse('valid');
        $this->addHttpResponse('true');
        $this->addHttpResponse('false');

        $isSpam = $this->akismet->isSpam(array(
            'comment_author'       => 'viagra-test-123',
            'comment_author_email' => 'test@example.com',
            'comment_author_url'   => 'http://example.com/',
            'comment_content'      => 'Spam, I am.',
            'user_ip'              => '127.0.0.1',
            'user_agent'           => 'Services_Akismet2 unit tests',
            'referrer'             => 'http://example.com/'
        ));

        $this->assertTrue($isSpam);

        $isSpam = $this->akismet->isSpam(array(
            'comment_author'       => 'Services_Akismet2 unit tests',
            'comment_author_email' => 'test@example.com',
            'comment_author_url'   => 'http://example.com/',
            'comment_content'      => 'Hello, World!',
            'user_ip'              => '127.0.0.1',
            'user_agent'           => 'Services_Akismet2 unit tests',
            'referrer'             => 'http://example.com/'
        ));

        $this->assertFalse($isSpam);
    }

    // }}}
    // {{{ testIsSpamInvalidCommentException()

    /**
     * @expectedException Services_Akismet2_InvalidCommentException
     */
    public function testIsSpamInvalidCommentException()
    {
        $this->addHttpResponse('valid');
        $this->addHttpResponse('true');

        $spamComment = new Services_Akismet2_Comment(array(
            'comment_author'       => 'viagra-test-123',
            'comment_author_email' => 'test@example.com',
            'comment_author_url'   => 'http://example.com/',
            'comment_content'      => 'Spam, I am.'
        ));

        $isSpam = $this->akismet->isSpam($spamComment);
    }

    // }}}
    // {{{ testIsSpamInvalidArgumentException()

    /**
     * @expectedException InvalidArgumentException
     */
    public function testIsSpamInvalidArgumentException()
    {
        $this->addHttpResponse('valid');
        $isSpam = $this->akismet->isSpam('test');
    }

    // }}}

    // {{{ testSubmitSpam()

    public function testSubmitSpam()
    {
        $this->addHttpResponse('valid');
        $this->addHttpResponse('Thanks for making the web a better place.');

        $spamComment = new Services_Akismet2_Comment(array(
            'comment_author'       => 'viagra-test-123',
            'comment_author_email' => 'test@example.com',
            'comment_author_url'   => 'http://example.com/',
            'comment_content'      => 'Spam, I am.',
            'user_ip'              => '127.0.0.1',
            'user_agent'           => 'Services_Akismet2 unit tests',
            'referrer'             => 'http://example.com/'
        ));

        $this->akismet->submitSpam($spamComment);
    }

    // }}}
    // {{{ testSubmitSpamWithArray()

    public function testSubmitSpamWithArray()
    {
        $this->addHttpResponse('valid');
        $this->addHttpResponse('Thanks for making the web a better place.');

        $this->akismet->submitSpam(array(
            'comment_author'       => 'viagra-test-123',
            'comment_author_email' => 'test@example.com',
            'comment_author_url'   => 'http://example.com/',
            'comment_content'      => 'Spam, I am.',
            'user_ip'              => '127.0.0.1',
            'user_agent'           => 'Services_Akismet2 unit tests',
            'referrer'             => 'http://example.com/'
        ));
    }

    // }}}
    // {{{ testSubmitSpamInvalidCommentException()

    /**
     * @expectedException Services_Akismet2_InvalidCommentException
     */
    public function testSubmitSpamInvalidCommentException()
    {
        $this->addHttpResponse('valid');

        $spamComment = new Services_Akismet2_Comment(array(
            'comment_author'       => 'viagra-test-123',
            'comment_author_email' => 'test@example.com',
            'comment_author_url'   => 'http://example.com/',
            'comment_content'      => 'Spam, I am.'
        ));

        $this->akismet->submitSpam($spamComment);
    }

    // }}}
    // {{{ testSubmitSpamInvalidArgumentException()

    /**
     * @expectedException InvalidArgumentException
     */
    public function testSubmitSpamInvalidArgumentException()
    {
        $this->addHttpResponse('valid');
        $this->akismet->submitSpam('test');
    }

    // }}}

    // {{{ testSubmitFalsePositive()

    public function testSubmitFalsePositive()
    {
        $this->addHttpResponse('valid');
        $this->addHttpResponse('Thanks for making the web a better place.');

        $comment = new Services_Akismet2_Comment(array(
            'comment_author'       => 'Services_Akismet2 unit tests',
            'comment_author_email' => 'test@example.com',
            'comment_author_url'   => 'http://example.com/',
            'comment_content'      => 'Hello, World!',
            'user_ip'              => '127.0.0.1',
            'user_agent'           => 'Services_Akismet2 unit tests',
            'referrer'             => 'http://example.com/'
        ));

        $this->akismet->submitFalsePositive($comment);
    }

    // }}}
    // {{{ testSubmitFalsePositiveWithArray()

    public function testSubmitFalsePositiveWithArray()
    {
        $this->addHttpResponse('valid');
        $this->addHttpResponse('Thanks for making the web a better place.');

        $this->akismet->submitFalsePositive(array(
            'comment_author'       => 'Services_Akismet2 unit tests',
            'comment_author_email' => 'test@example.com',
            'comment_author_url'   => 'http://example.com/',
            'comment_content'      => 'Hello, World!',
            'user_ip'              => '127.0.0.1',
            'user_agent'           => 'Services_Akismet2 unit tests',
            'referrer'             => 'http://example.com/'
        ));
    }

    // }}}
    // {{{ testSubmitFalsePositiveInvalidCommentException()

    /**
     * @expectedException Services_Akismet2_InvalidCommentException
     */
    public function testSubmitFalsePositiveInvalidCommentException()
    {
        $this->addHttpResponse('valid');

        $spamComment = new Services_Akismet2_Comment(array(
            'comment_author'       => 'test',
            'comment_author_email' => 'test@example.com',
            'comment_author_url'   => 'http://example.com/',
            'comment_content'      => 'Hello, World!'
        ));

        $this->akismet->submitFalsePositive($spamComment);
    }

    // }}}
    // {{{ testSubmitFalsePositiveInvalidArgumentException()

    /**
     * @expectedException InvalidArgumentException
     */
    public function testSubmitFalsePositiveInvalidArgumentException()
    {
        $this->addHttpResponse('valid');
        $this->akismet->submitFalsePositive('test');
    }

    // }}}
}

?>
