# Services_Akismet2 #
This package provides an object-oriented interface to the [Akismet REST
API](http://akismet.com/development/api/). The Akismet API is used to detect
and to filter spam comments posted on weblogs.

There are several anti-spam service providers that use the Akismet API. To use
the API, you will need an API key from such a provider. Example providers
include [Wordpress](http://wordpress.com) and
[TypePad](http://antispam.typepad.com).

[Services_Akismet2](http://pear.php.net/package/Services_Akismet2) has been
migrated from [PEAR SVN](https://svn.php.net/repository/pear/packages/Services_Akismet2).

## Documentation ##

### Quick Example
```php
<?php

require_once 'Services/Akismet2.php';
require_once 'Services/Akismet2/Comment.php';

$comment = new Services_Akismet2_Comment(
    array(
        'comment_author'       => 'Test Author',
        'comment_author_email' => 'test@example.com',
        'comment_author_url'   => 'http://example.com/',
        'comment_content'      => 'Hello, World!'
    )
);

$apiKey  = 'AABBCCDDEEFF';
$akismet = new Services_Akismet2('http://blog.example.com/', $apiKey);
if ($akismet->isSpam($comment)) {
    // rather than simply ignoring the spam comment, it is recommended
    // to save the comment and mark it as spam in case the comment is a
    // false positive.
} else {
    // save comment as normal comment
}

?>
```

### Further Documentation ###
* [High-Level Documentation](http://pear.php.net/manual/en/package.webservices.services-akismet2.php)
* [Detailed API Documentation](http://pear.php.net/package/Services_Akismet2/docs/latest/)

## Bugs and Issues ##
Please report all new issues via the [PEAR bug tracker](http://pear.php.net/bugs/search.php?cmd=display&package_name[]=Services_Akismet2).

Please submit pull requests for your bug reports!

## Testing ##
To test, run either
$ phpunit tests/
  or
$ pear run-tests -r

## Building ##
To build, simply
$ pear package

## Installing ##
To install from scratch
$ pear install package.xml

To upgrade
$ pear upgrade -f package.xml
