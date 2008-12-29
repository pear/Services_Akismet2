<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * This is the package.xml generator for Services_Akismet2
 *
 * PHP version 5
 *
 * LICENSE:
 *
 * Copyright (c) 2007-2008 silverorange
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
 * @link      http://pear.php.net/package/Services_Akismet2
 */

require_once 'PEAR/PackageFileManager2.php';
PEAR::setErrorHandling(PEAR_ERROR_DIE);

$api_version     = '2.0.0';
$api_state       = 'alpha';

$release_version = '2.0.0alpha1';
$release_state   = 'alpha';
$release_notes   =
    " * BC break to use HTTP_Request2.\n" .
    " * Added shortcut constructor for comment class.\n" .
    " * Added ability to set API endpoint.";

$description =
    "This package provides an object-oriented interface to the Akismet REST " .
    "API. Akismet is used to detect and to filter spam comments posted on " .
    "weblogs. Though the use of Akismet is not specific to Wordpress, you " .
    "will need a Wordpress API key from http://wordpress.com/api-keys/ to " .
    "use this package.\n\n" .
    "Akismet is free for personal use and a license may be purchased for " .
    "commercial or high-volume applications.\n\n" .
    "This package is derived from the miPHP Akismet class written by Bret " .
    "Kuhns for use in PHP 4. This package requires PHP 5.2.1.";

$package = new PEAR_PackageFileManager2();

$package->setOptions(array(
    'filelistgenerator'     => 'cvs',
    'simpleoutput'          => true,
    'baseinstalldir'        => '/',
    'packagedirectory'      => './',
    'dir_roles'             => array(
        'Services'          => 'php',
        'Services/Akismet2' => 'php',
        'tests'             => 'test'
    ),
    'ignore'                => array(
        'package.php',
    ),
));

$package->setPackage('Services_Akismet2');
$package->setExtends('Services_Akismet');
$package->setSummary('PHP client for the Akismet REST API');
$package->setDescription($description);
$package->setChannel('pear.php.net');
$package->setPackageType('php');
$package->setLicense('MIT',
    'http://www.opensource.org/licenses/mit-license.html');

$package->setNotes($release_notes);
$package->setReleaseVersion($release_version);
$package->setReleaseStability($release_state);
$package->setAPIVersion($api_version);
$package->setAPIStability($api_state);

$package->addMaintainer('lead', 'gauthierm', 'Mike Gauthier',
    'mike@silverorange.com');

$package->addReplacement('Services/Akismet.php', 'package-info',
    '@api-version@', 'api-version');

$package->addReplacement('Services/Akismet.php', 'package-info',
    '@name@', 'name');

$package->setPhpDep('5.2.1');

$package->addPackageDepWithChannel('required', 'HTTP_Request2',
    'pear.php.net', '0.1.0');

$package->setPearinstallerDep('1.4.0');
$package->generateContents();

if (   isset($_GET['make'])
    || (isset($_SERVER['argv']) && @$_SERVER['argv'][1] == 'make')
) {
    $package->writePackageFile();
} else {
    $package->debugPackageFile();
}

?>
