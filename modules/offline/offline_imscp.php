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

function offline_create_manifest($downloadDir) {
    global $course_id, $currentCourseName;

    $course_description = strip_tags(Database::get()->querySingle("select description from course where id = ?d", $course_id)->description);

    $f = fopen($downloadDir . '/imsmanifest.xml', 'w');
    fwrite($f, '<?xml version = "1.0" encoding = "UTF-8"?>
                    <manifest xmlns = "http://www.imsglobal.org/xsd/imscp_v1p1"
                    	xmlns:imsmd = "http://www.imsglobal.org/xsd/imsmd_v1p2"
                    	xmlns:xsi = "http://www.w3.org/2001/XMLSchema-instance"
                        xsi:schemaLocation = "http://www.imsglobal.org/xsd/imscp_v1p1 http://www.imsglobal.org/xsd/imscp_v1p1.xsd http://www.imsglobal.org/xsd/imsmd_v1p2 http://www.imsglobal.org/xsd/imsmd_v1p2.xsd "
                    	identifier="Manifest1"
                       	version="IMS CP 1.1.4">' . "\n");
    fwrite($f, create_metadata($currentCourseName, $course_description));
    fwrite($f, create_itemTree($currentCourseName));
    fwrite($f, create_resources($downloadDir));
    fwrite($f, "</manifest>\n");
    fclose($f);
}

function create_metadata($title, $description) {
    global $language;

    $metadata = "<metadata>"."\n"
        ."<schema>IMS Content</schema>"."\n"
        ."<schemaversion>1.1.4</schemaversion>"."\n"
        ."<imsmd:lom>"."\n"
        ."<imsmd:general>"."\n";

    $metadata .= "<imsmd:title>"."\n"
        ."<imsmd:langstring xml:lang=\"". $language ."\">"
        .htmlspecialchars($title)
        ."</imsmd:langstring>"."\n"
        ."</imsmd:title>"."\n";

    $metadata .= "<imsmd:language>". $language ."</imsmd:language>"."\n";

    $metadata .= "<imsmd:description>"."\n"
        ."<imsmd:langstring xml:lang=\"". $language ."\">"
        .htmlspecialchars($description)
        ."</imsmd:langstring>"."\n"
        ."</imsmd:description>"."\n";

    $metadata .= "<imsmd:structure>"."\n"
        ."<imsmd:source>"."\n"
        ."<imsmd:langstring xml:lang=\"x-none\">LOMv1.0</imsmd:langstring>"."\n"
        ."</imsmd:source>"."\n"
        ."<imsmd:value>"."\n"
        ."<imsmd:langstring xml:lang=\"x-none\">Hierarchical</imsmd:langstring>"."\n"
        ."</imsmd:value>"."\n"
        ."</imsmd:structure>";

    $metadata .= "</imsmd:general>"."\n"
        ."</imsmd:lom>"."\n"
        ."</metadata>"."\n";

    return $metadata;
}

function create_itemTree($title) {
    global $course_id;

    $itemTree = '<organizations default="A1">' . "\n"
        .'<organization identifier="A1" structure = "hierarchical">' . "\n"
        .'<title>' . $title . '</title>' . "\n";

    $itemTree .= '<item identifier="I_' . $course_id . '" isvisible="true" ';
    $itemTree .= 'identifierref="R_' . $course_id . '" ';
    $itemTree .= '>' . "\n";
    $itemTree .= '    <title>' . htmlspecialchars($title) . '</title>' . "\n";
    $itemTree .= '</item>' . "\n";

    $itemTree .= '</organization>' . "\n"
        .'</organizations>' . "\n";

    return $itemTree;
}

function create_resources($downloadDir) {
    global $course_id;

    $resources = "<resources>\n";
    $resources .= '<resource identifier="R_' . $course_id . '" type="webcontent" '
        . ' href="index.html">' . "\n"
        . '  <file href="index.html" />' . "\n";

    /** @var SplFileInfo[] $files */
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($downloadDir),
        RecursiveIteratorIterator::LEAVES_ONLY
    );

    foreach ($files as $name => $file) {
        if (!$file->isDir()) {
            $filePath = $file->getRealPath();
            $relativePath = substr($filePath, strlen($downloadDir) + 1);

            if ($relativePath == "index.html" || $relativePath == "imsmanifest.xml" || $relativePath == "imscp_v1p2.xsd") {
                continue;
            }

            $resources .= '  <file href="' . $relativePath . '" />' . "\n";
        }
    }

    $resources .= "</resource>\n";
    $resources .= '</resources>' . "\n";

    return $resources;
}
