<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Contains a class representing a comment on a weblog post
 *
 * PHP version 5
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
 * @author    Bret Kuhns
 * @copyright 2007-2008 Bret Kuhns, 2008 silverorange
 * @license   http://www.opensource.org/licenses/mit-license.html MIT License
 * @version   CVS: $Id$
 * @link      http://pear.php.net/package/Services_Akismet2
 */

/**
 * Exception thrown if an invalid comment is used.
 */
require_once 'Services/Akismet2/InvalidCommentException.php';

// {{{ class Services_Akismet2_Comment

/**
 * Akismet comment
 *
 * Example usage using initial array of values:
 *
 * <code>
 * $comment = new Services_Akismet2_Comment(array(
 *     'author'      => 'Test Author',
 *     'authorEmail' => 'test@example.com',
 *     'authorUri'   => 'http://example.com/',
 *     'content'     => 'Hello, World!'
 * ));
 *
 * echo $comment;
 * </code>
 *
 * @category  Services
 * @package   Services_Akismet2
 * @author    Michael Gauthier <mike@silverorange.com>
 * @author    Bret Kuhns
 * @copyright 2007-2008 Bret Kuhns, 2008 silverorange
 * @license   http://www.opensource.org/licenses/mit-license.html MIT License
 * @link      http://pear.php.net/package/Services_Akismet2
 */
class Services_Akismet2_Comment
{
    // {{{ private properties

    /**
     * Whitelist of allowed $_SERVER variables to send to Akismet
     *
     * A whitelist is used to ensure the privacy of people submitting comments.
     * Akismet recommends as many $_SERVER variables as possible be sent;
     * however, many $_SERVER variables contain sensitive data, and are not
     * relevant for spam checks. This subset of fields does not contain
     * sensitive information but does contain enough information to identify
     * a unique client/server sending spam.
     *
     * The $_SERVER variables are taken from the current request.
     *
     * @var array
     */
    private static $_allowedServerVars = array(
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
     * Fields that are required for a comment
     *
     * @var array
     *
     * @see http://akismet.com/development/api/#comment-check
     */
    private static $_requiredFields = array(
        'user_ip',
        'user_agent',
        'referrer'
    );

    /**
     * Fields of this comment
     *
     * @var array
     *
     * @see http://akismet.com/development/api/#comment-check
     */
    private $_fields = array();

    // }}}
    // {{{ __construct()

    /**
     * Creates a new comment
     *
     * Comments can be initialized from an array of named values. Available
     * names are:
     *
     * - <kbd>string author</kbd>      - the name of the author.
     * - <kbd>string authorEmail</kbd> - the email addedd of the author.
     * - <kbd>string authorUri</kbd>   - a link provided by the comment
     *                                   author.
     * - <kbd>string content</kbd>     - the content of the comment.
     * - <kbd>string permalink</kbd>   - permalink of the post to which the
     *                                   comment is being added.
     * - <kbd>string referrer</kbd>    - HTTP referrer. If not specified, the
     *                                   HTTP referrer of the current request
     *                                   is used.
     * - <kbd>string type</kbd>        - the comment type.
     * - <kbd>string userIp</kbd>      - IP address from which the comment was
     *                                   submitted. If not specified the remote
     *                                   IP address of the current request is
     *                                   used.
     * - <kbd>string userAgent</kbd>   - the HTTP user agent used to post the
     *                                   comment. If not specified, the user
     *                                   agent of the current request is used.
     *
     * If not specified, the 'userIp', 'userAgent' and 'referrer' fields are
     * defaulted to the current request values if possible. They may be changed
     * by calling the appropriate setter method.
     *
     * @param array $fields optional. An array of initial values.
     */
    public function __construct(array $fields = array())
    {
        // set default values from request
        if (array_key_exists('REMOTE_ADDR', $_SERVER)) {
            $this->_fields['user_ip'] = $_SERVER['REMOTE_ADDR'];
        }

        if (array_key_exists('HTTP_USER_AGENT', $_SERVER)) {
            $this->_fields['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
        }

        if (array_key_exists('HTTP_REFERER', $_SERVER)) {
            $this->_fields['referrer'] = $_SERVER['HTTP_REFERER'];
        }

        // set from fields
        if (array_key_exists('author', $fields)) {
            $this->setAuthor($fields['author']);
        }

        if (array_key_exists('authorEmail', $fields)) {
            $this->setAuthorEmail($fields['authorEmail']);
        }

        if (array_key_exists('authorUri', $fields)) {
            $this->setAuthorUri($fields['authorUri']);
        }

        if (array_key_exists('content', $fields)) {
            $this->setContent($fields['content']);
        }

        if (array_key_exists('permalink', $fields)) {
            $this->setPostPermalink($fields['permalink']);
        }

        if (array_key_exists('referrer', $fields)) {
            $this->setHttpReferer($fields['referrer']);
        }

        if (array_key_exists('type', $fields)) {
            $this->setType($fields['type']);
        }

        if (array_key_exists('userAgent', $fields)) {
            $this->setUserAgent($fields['userAgent']);
        }

        if (array_key_exists('userIp', $fields)) {
            $this->setUserIp($fields['userIp']);
        }
    }

    // }}}
    // {{{ __toString()

    /**
     * Gets a string representation of this comment
     *
     * This is useful for debugging. All the set fields of this comment are
     * returned as well as the results of
     * {@link Services_Akismet2_Comment::getPostData()}.
     *
     * @return string a string representation of this comment.
     */
    public function __toString()
    {
        $string = "Fields:\n\n";
        foreach ($this->_fields as $key => $value) {
            $string .= "\t" . $key . " => " . $value ."\n";
        }
        $string .= "\nPost Data:\n\n";
        try {
            $string .= "\t" . $this->getPostData() . "\n";
        } catch (Exception $e) {
            $string .= "\tmissing required fields\n";
        }

        return $string;
    }

    // }}}
    // {{{ getPostParameters()

    /**
     * Gets the fields of this comment as an array of name-value pairs for use
     * in an Akismet API method
     *
     * @return array the fields of this comment as an array of name-value pairs
     *                suitable for usage in an Akismet API method.
     *
     * @throws Services_Akismet2_InvalidCommentException if this comment is
     *         missing required fields.
     *
     * @see http://akismet.com/development/api/#comment-check
     */
    public function getPostParameters()
    {
        foreach (self::$_requiredFields as $field) {
            if (!array_key_exists($field, $this->_fields)) {
                throw new Services_Akismet2_InvalidCommentException('Comment ' .
                    'is missing required field: "' . $field . '".', 0, $this);
            }
        }

        $values = array();

        foreach ($this->_fields as $key => $value) {
            $values[$key] = $value;
        }

        foreach (self::$_allowedServerVars as $key) {
            if (array_key_exists($key, $_SERVER)) {
                $value = $_SERVER[$key];
                $values[$key] = $value;
            }
        }

        return $values;
    }

    // }}}
    // {{{ setType()

    /**
     * Sets the type of this comment
     *
     * @param string $type the type of this comment.
     *
     * @return Services_Akismet2_Comment the comment object.
     */
    public function setType($type)
    {
        if ($type === null) {
            unset($this->_fields['comment_type']);
        } else {
            $this->_fields['comment_type'] = strval($type);
        }

        return $this;
    }

    // }}}
    // {{{ setAuthor()

    /**
     * Sets the author of this comment
     *
     * @param string $author the author of this comment.
     *
     * @return Services_Akismet2_Comment the comment object.
     */
    public function setAuthor($author)
    {
        if ($author === null) {
            unset($this->_fields['comment_author']);
        } else {
            $this->_fields['comment_author'] = strval($author);
        }

        return $this;
    }

    // }}}
    // {{{ setAuthorEmail()

    /**
     * Sets the email address of the author of this comment
     *
     * @param string $email the email address of the author of this comment.
     *
     * @return Services_Akismet2_Comment the comment object.
     */
    public function setAuthorEmail($email)
    {
        if ($email === null) {
            unset($this->_fields['comment_author_email']);
        } else {
            $this->_fields['comment_author_email'] = strval($email);
        }

        return $this;
    }

    // }}}
    // {{{ setAuthorUri()

    /**
     * Sets the URI of the author of this comment
     *
     * @param string $uri the URI of the author of this comment.
     *
     * @return Services_Akismet2_Comment the comment object.
     */
    public function setAuthorUri($uri)
    {
        if ($uri === null) {
            unset($this->_fields['comment_author_url']);
        } else {
            $this->_fields['comment_author_url'] = strval($uri);
        }

        return $this;
    }

    // }}}
    // {{{ setContent()

    /**
     * Sets the content of this comment
     *
     * @param string $content the content of this comment.
     *
     * @return Services_Akismet2_Comment the comment object.
     */
    public function setContent($content)
    {
        if ($content === null) {
            unset($this->_fields['comment_content']);
        } else {
            $this->_fields['comment_content'] = strval($content);
        }

        return $this;
    }

    // }}}
    // {{{ setPostPermalink()

    /**
     * Sets the permalink of the post to which this comment is being added
     *
     * A {@link http://en.wikipedia.org/wiki/Permalink permalink} is a URI that
     * points to a specific weblog post and does not change over time.
     * Permalinks are intended to prevent link rot. Akismet does not require
     * the permalink field but can use it to improve spam detection accuracy.
     *
     * @param string $uri the permalink of the post to which this comment is
     *                    being added.
     *
     * @return Services_Akismet2_Comment the comment object.
     */
    public function setPostPermalink($uri)
    {
        if ($uri === null) {
            unset($this->_fields['permalink']);
        } else {
            $this->_fields['permalink'] = strval($uri);
        }

        return $this;
    }

    // }}}
    // {{{ setUserIp()

    /**
     * Sets the IP address of the user posting this comment
     *
     * The IP address is automatically set to the IP address from the current
     * page request when this comment is created. Use this method to set the
     * IP address to something different or if the current request does not have
     * an IP address set.
     *
     * @param string $ipAddress the IP address of the user posting this
     *                          comment.
     *
     * @return Services_Akismet2_Comment the comment object.
     */
    public function setUserIp($ipAddress)
    {
        if ($ipAddress === null) {
            unset($this->_fields['user_ip']);
        } else {
            $this->_fields['user_ip'] = strval($ipAddress);
        }

        return $this;
    }

    // }}}
    // {{{ setUserAgent()

    /**
     * Sets the user agent of the user posting this comment
     *
     * The user agent is automatically set to the user agent from the current
     * page request when this comment is created. Use this method to set the
     * user agent to something different or if the current request does not
     * have a user agent set.
     *
     * @param string $userAgent the user agent of the user posting this
     *                          comment.
     *
     * @return Services_Akismet2_Comment the comment object.
     */
    public function setUserAgent($userAgent)
    {
        if ($userAgent === null) {
            unset($this->_fields['user_agent']);
        } else {
            $this->_fields['user_agent'] = strval($userAgent);
        }

        return $this;
    }

    // }}}
    // {{{ setHttpReferer()

    /**
     * Sets the HTTP referer of the user posting this comment
     *
     * The HTTP referer is automatically set to the HTTP referer from the
     * current page request when this comment is created. Use this method to set
     * the HTTP referer to something different or if the current request does
     * not have a HTTP referer set.
     *
     * @param string $httpReferer the HTTP referer of the user posting this
     *                            comment.
     *
     * @return Services_Akismet2_Comment the comment object.
     */
    public function setHttpReferer($httpReferer)
    {
        if ($httpReferer === null) {
            unset($this->_fields['referrer']);
        } else {
            $this->_fields['referrer'] = strval($httpReferer);
        }

        return $this;
    }

    // }}}
}

// }}}

?>
