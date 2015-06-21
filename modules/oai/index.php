<?php
/* ! \mainpage notitle
 *
 * \section intro_sec Introduction
 *
 * This is an implementation for an OAI-PMH 2.0 Data Provider (sometimes, repository is used exchangeablly) written in PHP.
 *
 * This implementation completely complies to OAI-PMH 2.0, including
 * the support of on-the-fly output compression which may significantly
 * reduce the amount of data being transfered.
 *
 * This package has been inspired by PHP OAI Data Provider developed by Heinrich Stamerjohanns at University of Oldenburg.  
 * Some of the functions and algorithms used in this code were transplanted from his  implementation at http://physnet.uni-oldenburg.de/oai/.
 *
 * Database support is supported through Open eClass Database subsystem.
 *
 * The repository can be quite easily configured by just editing 
 * oaidp-config.php, most possible values and options are explained.
 *
 * \section req_sec Requirements
 * - A running web server + PHP version 5.0 or above.
 * - A database can be connected by via Open eClass.
 *
 * \section install_sec Installation
 *
 * - Copy the the files in source package to a location under your
 * 	 document root of your web server. The directory structure should be
 * 	 preserved. 
 * - Change to that directory (e.g. cd /var/www/html/oai).
 * - Allow your webserver to write to the token directory.
 * 	 The default token directory is /tmp which does not need any attention.
 * - Edit oaidp-config.php. Almost all possible options are 
 * 	 explained. It is assumed that basic elements of a record are stored in 
 * 	 one simple table. You can find sql examples of table definition in doc folder.
 * 	 If your data is organized differently, you have to adjust the <i>Query</i> functions 
 *  to reflect it and even develop your own code.
 * - Check your oai site through a web browser. e.g. : \code http://localhost/oai/ \endcode
 * - SELinux needs special treatments for database connection and other permission.
 *
 * \section struct_sec Structure
  The system includes files for individual functionality and utility classes and functions to get it work.
  - Controller
  - oai2.php
  - Individual functionalities:
  - identify.php: identifies the data provider. Responses to <B>Identify</B>.
  - listmetadataformats.php: lists supported metadata formats, e.g. dc or rif-cs. Responses to <B>ListMetadataFormats</B>.
  - listsets.php: lists supported sets, e.g. Activity, Collection or Party. Responses to <B>ListSets</B>.
  - listrecords.php: lists a group of records without details. Responses to <B>ListRecords</B>. It also serves to <B>ListIdentifiers</B> which only returns identifiers.
  - getrecord.php: gets an individual record. Responses to <B>GetRecord</B>.
  - Utility classes
  - xml_creater.php which includes classess ANDS_XML, ANDS_Error_XML, ANDS_Response_XML
  - Utility functions
  - oaidp-util.php
  - Support to different metadataformats in your own systems. One example is provided with the package: record_dc.php. They are helpers and need information from the real records. They need to be devloped for your particular system.
  - Configurations
  - oaidp-config.php

 *
 * \author Jianfeng Li
 * \version 1.1
 * \date 2010-2011
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
