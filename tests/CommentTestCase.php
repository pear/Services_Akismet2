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
     * @var array
     */
    private $_serverVarNames = array(
        'REMOTE_ADDR',
        'HTTP_USER_AGENT',
        'HTTP_REFERER'
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
        $fields = $comment->getFields();

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

        $fields = $comment->getFields();
        foreach ($fieldNames as $fieldName) {
            $this->assertArrayNotHasKey($fieldName, $fields);
        }
    }

    // }}}
    // {{{ testConstructWithArray()

    public function testConstructWithArray()
    {
        $fields = array(
            'comment_author'       => 'Test Author',
            'comment_author_email' => 'test@exmaple.com',
            'comment_author_url'   => 'http://myblog.example.com/',
            'comment_content'      => 'Hello, World!',
            'comment_type'         => 'comment',
            'permalink'            => 'http://example.com/post1',
            'user_ip'              => '127.0.0.1',
            'user_agent'           => 'Services_Akismet2 unit tests',
            'referrer'             => 'http://example.com/'
        );

        $comment = new Services_Akismet2_Comment($fields);

        $commentFields = $comment->getFields();
        $this->assertEquals($fields, $commentFields);
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

        $commentFields = $comment->getFields();
        $this->assertEquals($fields, $commentFields);
    }

    // }}}
    // {{{ testToString()

    public function testToString()
    {
        $fields = array(
            'comment_author'       => 'Test Author',
            'comment_author_email' => 'test@exmaple.com',
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
            "\tcomment_author_email => test@exmaple.com\n" .
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
            'comment_author_email' => 'test@exmaple.com',
            'comment_author_url'   => 'http://myblog.example.com/',
            'comment_content'      => 'Hello, World!',
            'comment_type'         => 'comment',
            'permalink'            => 'http://example.com/post1',
        );

        $string = "Fields:\n\n" .
            "\tcomment_author => Test Author\n" .
            "\tcomment_author_email => test@exmaple.com\n" .
            "\tcomment_author_url => http://myblog.example.com/\n" .
            "\tcomment_content => Hello, World!\n" .
            "\tcomment_type => comment\n" .
            "\tpermalink => http://example.com/post1\n" .
            "\n\tMissing Required Fields:\n\n" .
            "\tuser_ip\n" .
            "\tuser_agent\n" .
            "\treferrer\n";

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
            'comment_author_email' => 'test@exmaple.com',
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
        foreach ($fields as $name => $value) {
            $this->assertArrayHasKey($name, $parameters);
            $this->assertEquals($value, $parameters[$name]);
        }
    }

    // }}}
    // {{{ testGetPostParametersMissingUserAgent()

    /**
     * @expectedException Services_Akismet2_InvalidCommentException
     */
    public function testGetPostParametersMissingUserAgent()
    {
        $comment = new Services_Akismet2_Comment(array(
            'user_ip'  => '127.0.0.1',
            'referrer' => 'http://example.com'
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
            'user_agent' => 'Services_Akismet2 Unit Tests',
            'referrer'   => 'http://example.com'
        ));

        $comment->getPostParameters();
    }

    // }}}
    // {{{ testGetPostParametersMissingReferrer()

    /**
     * @expectedException Services_Akismet2_InvalidCommentException
     */
    public function testGetPostParametersMissingReferrer()
    {
        $comment = new Services_Akismet2_Comment(array(
            'user_ip'    => '127.0.0.1',
            'user_agent' => 'Services_Akismet2 Unit Tests'
        ));

        $comment->getPostParameters();
    }

    // }}}
    // {{{ testSetField()

    public function testSetField()
    {
        $name    = 'test-name';
        $value   = 'test-value';
        $comment = new Services_Akismet2_Comment();

        $comment->setField($name, $value);
        $fields = $comment->getFields();

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
