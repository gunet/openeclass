<?php
/*
 *  ========================================================================
 *  * Open eClass
 *  * E-learning and Course Management System
 *  * ========================================================================
 *  * Copyright 2003-2024, Greek Universities Network - GUnet
 *  *
 *  * Open eClass is an open platform distributed in the hope that it will
 *  * be useful (without any warranty), under the terms of the GNU (General
 *  * Public License) as published by the Free Software Foundation.
 *  * The full license can be read in "/info/license/license_gpl.txt".
 *  *
 *  * Contact address: GUnet Asynchronous eLearning Group
 *  *                  e-mail: info@openeclass.org
 *  * ========================================================================
 *
 */

/**
 * \file
 * \brief
 * Default starting point of OAI Data Provider for a human to check.
 *
 * OAI Data Provider is not designed for human to retrieve data but it is possible to use this page to test and check the functionality of current implementation.
 * This page provides a summary of the OAI-PMH and the implementation.
 *
 */
$MY_URI = 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER['SCRIPT_NAME'];
$pos = strrpos($MY_URI, '/');
$MY_URI = substr($MY_URI, 0, $pos) . '/oai2.php';
?>
<html>
    <head>
        <title>php-oai2 Data Provider</title>
    </head>
    <body>

        <h3>php-oai2 Data Provider</h3>
        <p>This is an implementation of an <a href="http://www.openarchives.org/OAI/openarchivesprotocol.html" target="_blank">OAI-PMH 2.0 Data Provider</a>, written in <a href="http://www.php.net" title="PHP's website" target="_blank">PHP</a>.</p>

        <p>This implementation completely complies to <a href="http://www.openarchives.org/OAI/openarchivesprotocol.html" target="_blank">OAI-PMH 2.0</a>, including the support of on-the-fly output compression which may significantly
            reduce the amount of data being transfered.</p>

        <p> This package has been inspired by <a href='http://code.google.com/p/oai-pmh-2/' target="_blank">PHP OAI-PMH 2.0 Data Provider</a>.
            Some of the functions and algorithms used in this code were transplanted from that implementation.<p>

        <p>For requirements and instructions to install and configure, please reference <a href="doc/index.html">the documentation</a>.</p>

        <p>Once you have setup your Data Provider, you can the easiliy check the generated answers (it will be XML) of your Data Provider
            by clicking on the <a href="#tests">test links below</a>. </p>

        <p>
        <dl>
            <dt>Query and check your Data-Provider</dt>
            <dd><a href="<?php echo $MY_URI; ?>?verb=Identify">Identify</a></dd>
            <dd><a href="<?php echo $MY_URI; ?>?verb=ListMetadataFormats">ListMetadataFormats</a></dd>
            <dd><a href="<?php echo $MY_URI; ?>?verb=ListSets">ListSets</a></dd>
            <dd><a href="<?php echo $MY_URI; ?>?verb=ListIdentifiers&amp;metadataPrefix=oai_dc">ListIdentifiers</a></dd>
            <dd><a href="<?php echo $MY_URI; ?>?verb=ListRecords&amp;metadataPrefix=oai_dc">ListRecords</a></dd>
            </dt>
        </dl>
    </p>

</body>
</html>
