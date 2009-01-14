<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * PHPUnit3.2 test framework script for the Services_Akismet2_Comment class
 *
 * These tests require the PHPUnit 3.2 package to be installed. PHPUnit is
 * installable using PEAR. See the
 * {@link http://www.phpunit.de/pocket_guide/3.2/en/installation.html manual}
 * for detailed installation instructions.
 *
 * LICENSE:
 *
 * Copyright (c) 2007-2008 Bret Kuhns, silverorange
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
 * @copyright 2008 silverorange
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
 * Class for testing Services_Akismet2_Comment
 *
 * @category  Services
 * @package   Services_Akismet2
 * @author    Michael Gauthier <mike@silverorange.com>
 * @copyright 2008 silverorange
 * @license   http://www.opensource.org/licenses/mit-license.html MIT License
 * @link      http://pear.php.net/package/Services_Akismet2
 */
class Services_Akismet2_CommentTestCase extends PHPUnit_Framework_TestCase
{
    // {{{ private properties

    /**
     * @var integer
     */
    private $_oldErrorLevel;

    /**
     * $_SERVER vars to backup
     *
     * @var array
     */
    private $_serverVarNames = array(
        'REMOTE_ADDR',
        'SCRIPT_URI',
        'HTTP_HOST',
        'HTTP_USER_AGENT',
        'HTTP_ACCEPT',
        'HTTP_ACCEPT_LANGUAGE',
        'HTTP_ACCEPT_ENCODING',
        'HTTP_ACCEPT_CHARSET',
        'HTTP_KEEP_ALIVE',
        'HTTP_CONNECTION',
        'HTTP_CACHE_CONTROL',
        'HTTP_PRAGMA',
        'HTTP_REFERER',
        'HTTP_DATE',
        'HTTP_EXPECT',
        'HTTP_MAX_FORWARDS',
        'HTTP_RANGE',
        'CONTENT_TYPE',
        'CONTENT_LENGTH',
        'SERVER_SIGNATURE',
        'SERVER_SOFTWARE',
        'SERVER_NAME',
        'SERVER_ADDR',
        'SERVER_PORT',
        'REMOTE_PORT',
        'GATEWAY_INTERFACE',
        'SERVER_PROTOCOL',
        'REQUEST_METHOD',
        'QUERY_STRING',
        'REQUEST_URI',
        'SCRIPT_NAME',
        'REQUEST_TIME'
    );

    /**
     * @var array
     */
    private $_serverVars = array();

    // }}}
    // {{{ setUp()

    public function setUp()
    {
        $this->_oldErrorLevel = error_reporting(E_ALL | E_STRICT);
        $this->backupServerVars();
    }

    // }}}
    // {{{ tearDown()

    public function tearDown()
    {
        $this->restoreServerVars();
        error_reporting($this->_oldErrorLevel);
    }

    // }}}
    // {{{ assertSetterWorks()

    protected function assertSetterWorks($methodName, $fieldName)
    {
        $value   = 'test';
        $comment = new Services_Akismet2_Comment();

        $comment->$methodName($value);

        $fields = $this->readAttribute($comment, 'fields');

        $this->assertArrayHasKey($fieldName, $fields);
        $this->assertEquals($value, $fields[$fieldName]);
    }

    // }}}
    // {{{ backupServerVars()

    protected function backupServerVars()
    {
        $this->_serverVars = array();

        foreach ($this->_serverVarNames as $name) {
            if (array_key_exists($name, $_SERVER)) {
                $this->_serverVars[$name] = $_SERVER[$name];
                unset($_SERVER[$name]);
            }
        }
    }

    // }}}
    // {{{ restoreServerVars()

    protected function restoreServerVars()
    {
        foreach ($this->_serverVarNames as $name) {
            if (array_key_exists($name, $this->_serverVars)) {
                $_SERVER[$name] = $this->_serverVars[$name];
            } else {
                unset($_SERVER[$name]);
            }
        }
    }

    // }}}

    // tests
    // {{{ testConstruct()

    public function testConstruct()
    {
        $comment = new Services_Akismet2_Comment();

        // the following fields should not be set by the default constructor
        $fieldNames = array(
            'comment_author',
            'comment_author_email',
            'comment_author_url',
            'comment_content',
            'comment_type',
            'permalink',
            'referrer',
            'user_ip',
            'user_agent'
        );

        foreach ($fieldNames as $fieldName) {
            $constraint = $this->attribute(
                $this->logicalNot(
                    $this->arrayHasKey($fieldName)
                ), 'fields'
            );
            $this->assertThat($comment, $constraint);
        }
    }

    // }}}
    // {{{ testConstructWithArray()

    public function testConstructWithArray()
    {
        $fields = array(
            'comment_author'       => 'Test Author',
            'comment_author_email' => 'test@example.com',
            'comment_author_url'   => 'http://myblog.example.com/',
            'comment_content'      => 'Hello, World!',
            'comment_type'         => 'comment',
            'permalink'            => 'http://example.com/post1',
            'user_ip'              => '127.0.0.1',
            'user_agent'           => 'Services_Akismet2 unit tests',
            'referrer'             => 'http://example.com/'
        );

        $comment = new Services_Akismet2_Comment($fields);

        $this->assertAttributeEquals($fields, 'fields', $comment);
    }

    // }}}
    // {{{ testConstructWithDefaults()

    public function testConstructWithDefaults()
    {
        $fields = array(
            'referrer'   => 'http://example.com/',
            'user_ip'    => '127.0.0.1',
            'user_agent' => 'Services_Akismet2 Unit Tests'
        );

        $_SERVER['HTTP_REFERRER']   = $fields['referrer'];
        $_SERVER['REMOTE_ADDR']     = $fields['user_ip'];
        $_SERVER['HTTP_USER_AGENT'] = $fields['user_agent'];

        $comment = new Services_Akismet2_Comment($fields);

        $this->assertAttributeEquals($fields, 'fields', $comment);
    }

    // }}}
    // {{{ testToString()

    public function testToString()
    {
        $fields = array(
            'comment_author'       => 'Test Author',
            'comment_author_email' => 'test@example.com',
            'comment_author_url'   => 'http://myblog.example.com/',
            'comment_content'      => 'Hello, World!',
            'comment_type'         => 'comment',
            'permalink'            => 'http://example.com/post1',
            'user_ip'              => '127.0.0.1',
            'user_agent'           => 'Services_Akismet2 unit tests',
            'referrer'             => 'http://example.com/'
        );

        $string = "Fields:\n\n" .
            "\tcomment_author => Test Author\n" .
            "\tcomment_author_email => test@example.com\n" .
            "\tcomment_author_url => http://myblog.example.com/\n" .
            "\tcomment_content => Hello, World!\n" .
            "\tcomment_type => comment\n" .
            "\tpermalink => http://example.com/post1\n" .
            "\tuser_ip => 127.0.0.1\n" .
            "\tuser_agent => Services_Akismet2 unit tests\n" .
            "\treferrer => http://example.com/\n";

        $comment = new Services_Akismet2_Comment($fields);
        $comment = strval($comment);

        $this->assertEquals($string, $comment);
    }

    // }}}
    // {{{ testToStringWithMissingRequiredFields()

    public function testToStringWithMissingRequiredFields()
    {
        $fields = array(
            'comment_author'       => 'Test Author',
            'comment_author_email' => 'test@example.com',
            'comment_author_url'   => 'http://myblog.example.com/',
            'comment_content'      => 'Hello, World!',
            'comment_type'         => 'comment',
            'permalink'            => 'http://example.com/post1',
        );

        $string = "Fields:\n\n" .
            "\tcomment_author => Test Author\n" .
            "\tcomment_author_email => test@example.com\n" .
            "\tcomment_author_url => http://myblog.example.com/\n" .
            "\tcomment_content => Hello, World!\n" .
            "\tcomment_type => comment\n" .
            "\tpermalink => http://example.com/post1\n" .
            "\n\tMissing Required Fields:\n\n" .
            "\tuser_ip\n" .
            "\tuser_agent\n";

        $comment = new Services_Akismet2_Comment($fields);
        $comment = strval($comment);

        $this->assertEquals($string, $comment);
    }

    // }}}
    // {{{ testGetPostParameters()

    public function testGetPostParameters()
    {
        $fields = array(
            'comment_author'       => 'Test Author',
            'comment_author_email' => 'test@example.com',
            'comment_author_url'   => 'http://myblog.example.com/',
            'comment_content'      => 'Hello, World!',
            'comment_type'         => 'comment',
            'permalink'            => 'http://example.com/post1',
            'user_ip'              => '127.0.0.1',
            'user_agent'           => 'Services_Akismet2 unit tests',
            'referrer'             => 'http://example.com/'
        );

        $comment = new Services_Akismet2_Comment($fields);

        $parameters = $comment->getPostParameters();
        $this->assertEquals($fields, $parameters);
    }

    // }}}
    // {{{ testGetPostParametersMissingUserAgent()

    /**
     * @expectedException Services_Akismet2_InvalidCommentException
     */
    public function testGetPostParametersMissingUserAgent()
    {
        $comment = new Services_Akismet2_Comment(array(
            'user_ip'  => '127.0.0.1'
        ));

        $comment->getPostParameters();
    }

    // }}}
    // {{{ testGetPostParametersMissingUserIp()

    /**
     * @expectedException Services_Akismet2_InvalidCommentException
     */
    public function testGetPostParametersMissingUserIp()
    {
        $comment = new Services_Akismet2_Comment(array(
            'user_agent' => 'Services_Akismet2 Unit Tests'
        ));

        $comment->getPostParameters();
    }

    // }}}
    // {{{ testGetPostParametersWithServerFields()

    public function testGetPostParametersWithServerFields()
    {
        // fake $_SERVER fields for testing
        $serverFields = array(
            'SCRIPT_URI'           => 'http://example.com/akismet-tests',
            'HTTP_HOST'            => 'example.com',
            'HTTP_USER_AGENT'      => 'Mozilla/5.0 (Linux) Firefox 3.0.5',
            'HTTP_ACCEPT'          => 'text/html,application/xml',
            'HTTP_ACCEPT_LANGUAGE' => 'en-us,en',
            'HTTP_ACCEPT_ENCODING' => 'gzip,deflate',
            'HTTP_ACCEPT_CHARSET'  => 'utf-8',
            'HTTP_KEEP_ALIVE'      => '300',
            'HTTP_CONNECTION'      => 'keep-alive',
            'HTTP_CACHE_CONTROL'   => 'max-age=0',
            'HTTP_PRAGMA'          => 'Apache/2.2.9 Server at example.com',
            'HTTP_DATE'            => 'Wed, 22 Dec 2004 11:34:47 GMT',
            'HTTP_EXPECT'          => '100-continue',
            'HTTP_MAX_FORWARDS'    => '10',
            'HTTP_RANGE'           => '0-134 bytes',
            'CONTENT_TYPE'         => 'application/x-www-form-urlencoded',
            'CONTENT_LENGTH'       => '134',
            'SERVER_SIGNATURE'     => 'Apache/2.2.9 Server at example.com',
            'SERVER_SOFTWARE'      => 'Apache/2.2.9 (Fedora)',
            'SERVER_NAME'          => 'example',
            'SERVER_ADDR'          => '127.0.0.255',
            'SERVER_PORT'          => '80',
            'REMOTE_PORT'          => '34623',
            'GATEWAY_INTERFACE'    => 'CGI/1.1',
            'SERVER_PROTOCOL'      => 'HTTP/1.1',
            'REQUEST_METHOD'       => 'POST',
            'QUERY_STRING'         => '',
            'REQUEST_URI'          => '/web/scripts/akismet-tests.php',
            'SCRIPT_NAME'          => '/web/scripts/akismet-tests.php',
            'REQUEST_TIME'         => 1231946955
        );

        foreach ($serverFields as $name => $value) {
            $_SERVER[$name] = $value;
        }

        $fields = array(
            'comment_author'       => 'Test Author',
            'comment_author_email' => 'test@example.com',
            'comment_author_url'   => 'http://myblog.example.com/',
            'comment_content'      => 'Hello, World!',
            'comment_type'         => 'comment',
            'permalink'            => 'http://example.com/post1',
            'user_ip'              => '127.0.0.1',
            'user_agent'           => 'Mozilla/5.0 (Linux) Firefox 3.0.5',
            'referrer'             => 'http://example.com/',
        );

        $comment = new Services_Akismet2_Comment($fields);

        $parameters = $comment->getPostParameters(true);

        foreach ($fields as $name => $value) {
            $this->assertArrayHasKey($name, $parameters);
            $this->assertEquals($value, $parameters[$name]);
        }

        foreach ($serverFields as $name => $value) {
            $this->assertArrayHasKey($name, $parameters);
            $this->assertEquals($value, $parameters[$name]);
        }
    }

    // }}}
    // {{{ testSetField()

    public function testSetField()
    {
        $name    = 'test-name';
        $value   = 'test-value';
        $comment = new Services_Akismet2_Comment();

        $comment->setField($name, $value);

        $fields = $this->readAttribute($comment, 'fields');

        $this->assertArrayHasKey($name, $fields);
        $this->assertEquals($value, $fields[$name]);
    }

    // }}}
    // {{{ testSetFields()

    public function testSetFields()
    {
    }

    // }}}
    // {{{ testSetType()

    public function testSetType()
    {
        $this->assertSetterWorks('setType', 'comment_type');
    }

    // }}}
    // {{{ testSetAuthor()

    public function testSetAuthor()
    {
        $this->assertSetterWorks('setAuthor', 'comment_author');
    }

    // }}}
    // {{{ testSetAuthorEmail()

    public function testSetAuthorEmail()
    {
        $this->assertSetterWorks('setAuthorEmail', 'comment_author_email');
    }

    // }}}
    // {{{ testSetAuthorUrl()

    public function testSetAuthorUrl()
    {
        $this->assertSetterWorks('setAuthorUrl', 'comment_author_url');
    }

    // }}}
    // {{{ testSetContent()

    public function testSetContent()
    {
        $this->assertSetterWorks('setContent', 'comment_content');
    }

    // }}}
    // {{{ testSetPostPermalink()

    public function testSetPostPermalink()
    {
        $this->assertSetterWorks('setPostPermalink', 'permalink');
    }

    // }}}
    // {{{ testSetUserIp()

    public function testSetUserIp()
    {
        $this->assertSetterWorks('setUserIp', 'user_ip');
    }

    // }}}
    // {{{ testSetUserAgent()

    public function testSetUserAgent()
    {
        $this->assertSetterWorks('setUserAgent', 'user_agent');
    }

    // }}}
    // {{{ testSetHttpReferrer()

    public function testSetHttpReferrer()
    {
        $this->assertSetterWorks('setHttpReferrer', 'referrer');
    }

    // }}}
    // {{{ testFluentInterface()

    public function testFluentInterface()
    {
        $comment = new Services_Akismet2_Comment();

        // setField()
        $newComment = $comment->setField('test', 'test');
        $this->assertSame($comment, $newComment);

        // setFields()
        $newComment = $comment->setFields(array('test', 'test'));
        $this->assertSame($comment, $newComment);

        // setType()
        $newComment = $comment->setType('test');
        $this->assertSame($comment, $newComment);

        // setAuthor()
        $newComment = $comment->setAuthor('test');
        $this->assertSame($comment, $newComment);

        // setAuthorEmail()
        $newComment = $comment->setAuthorEmail('test');
        $this->assertSame($comment, $newComment);

        // setAuthorUrl()
        $newComment = $comment->setAuthorUrl('test');
        $this->assertSame($comment, $newComment);

        // setContent()
        $newComment = $comment->setContent('test');
        $this->assertSame($comment, $newComment);

        // setPostPermalink()
        $newComment = $comment->setPostPermalink('test');
        $this->assertSame($comment, $newComment);

        // setUserIp()
        $newComment = $comment->setUserIp('test');
        $this->assertSame($comment, $newComment);

        // setUserAgent()
        $newComment = $comment->setUserAgent('test');
        $this->assertSame($comment, $newComment);

        // setHttpReferrer()
        $newComment = $comment->setHttpReferrer('test');
        $this->assertSame($comment, $newComment);
    }

    // }}}
}

?>
