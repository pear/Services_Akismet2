<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Exception class test cases for the Services_Akismet2 package
 *
 * These tests require the PHPUnit 3.6 or greater package to be installed.
 * PHPUnit is installable using PEAR. See the
 * {@link http://www.phpunit.de/manual/3.6/en/installation.html manual}
 * for detailed installation instructions.
 *
 * LICENSE:
 *
 * Copyright (c) 2007-2011 Bret Kuhns, silverorange
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
 * For testing HTTP exceptions.
 */
require_once 'HTTP/Request2.php';

/**
 * Exception thrown when an HTTP error occurs.
 */
require_once 'Services/Akismet2/HttpException.php';

/**
 * Exception thrown when an invalid API key is used.
 */
require_once 'Services/Akismet2/InvalidApiKeyException.php';

/**
 * Exception thrown when an invalid comment is used.
 */
require_once 'Services/Akismet2/InvalidCommentException.php';

/**
 * Exception classes tests for Services_Akismet2
 *
 * @category  Services
 * @package   Services_Akismet2
 * @author    Michael Gauthier <mike@silverorange.com>
 * @copyright 2008-2011 silverorange
 * @license   http://www.opensource.org/licenses/mit-license.html MIT License
 * @link      http://pear.php.net/package/Services_Akismet2
 */
class Services_Akismet2_ExceptionsTestCase extends PHPUnit_Framework_TestCase
{
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
    }

    // }}}
    // {{{ tearDown()

    public function tearDown()
    {
        error_reporting($this->_oldErrorLevel);
    }

    // }}}

    // HTTP exception
    // {{{ testHttpException()

    /**
     * @expectedException Services_Akismet2_HttpException
     */
    public function testHttpException()
    {
        throw new Services_Akismet2_HttpException(
            'test exception', 0, new HTTP_Request2());
    }

    // }}}
    // {{{ testHttpException_getRequest()

    public function testHttpException_getRequest()
    {
        $request = new HTTP_Request2();
        $e = new Services_Akismet2_HttpException('test exception', 0, $request);
        $this->assertSame($request, $e->getRequest());
    }

    // }}}

    // invalid API key exception
    // {{{ testInvalidApiKeyException()

    /**
     * @expectedException Services_Akismet2_InvalidApiKeyException
     */
    public function testInvalidApiKeyException()
    {
        throw new Services_Akismet2_InvalidApiKeyException(
            'test exception', 0, 'AABBCCDDEEFF');
    }

    // }}}
    // {{{ testInvalidApiKeyException_getApiKey()

    public function testInvalidApiKeyException_getApiKey()
    {
        $key = 'AABBCCDDEEFF';
        $e = new Services_Akismet2_InvalidApiKeyException(
            'test exception', 0, $key);

        $this->assertEquals($key, $e->getApiKey());
    }

    // }}}

    // invalid comment exception
    // {{{ testInvalidCommentException()

    /**
     * @expectedException Services_Akismet2_InvalidCommentException
     */
    public function testInvalidCommentException()
    {
        $comment = new Services_Akismet2_Comment();
        throw new Services_Akismet2_InvalidCommentException(
            'test exception', 0, $comment);
    }

    // }}}
    // {{{ testInvalidCommentException_getRequest()

    public function testInvalidCommentException_getComment()
    {
        $comment = new Services_Akismet2_Comment();
        $e = new Services_Akismet2_InvalidCommentException(
            'test exception', 0, $comment);

        $this->assertSame($comment, $e->getComment());
    }

    // }}}
}

?>
