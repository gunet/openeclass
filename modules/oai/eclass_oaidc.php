<?php

/* ========================================================================
 * Open eClass
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2014  Greek Universities Network - GUnet
 * A full copyright notice can be read in "/info/copyright.txt".
 * For a full list of contributors, see "credits.txt".
 *
 * Open eClass is an open platform distributed in the hope that it will
 * be useful (without any warranty), under the terms of the GNU (General
 * Public License) as published by the Free Software Foundation.
 * The full license can be read in "/info/license/license_gpl.txt".
 *
 * Contact address: GUnet Asynchronous eLearning Group,
 *                  Network Operations Center, University of Athens,
 *                  Panepistimiopolis Ilissia, 15784, Athens, Greece
 *                  e-mail: info@openeclass.org
 * ========================================================================
 */

require_once('xml_creater.php');

class ECLASS_OAIDC {

    /**
     * \var $oai_pmh 
     * Type: ANDS_Response_XML. Assigned by constructor. \see __construct
     */
    private $oai_pmh;

    /**
     * \var $working_node
     * Type: DOMElement. Assigned by constructor. \see __construct	
     */
    protected $working_node;

    public function __construct($eclass_response_doc, $metadata_node) {
        $this->oai_pmh = $eclass_response_doc;
        $this->working_node = $metadata_node;
        $this->createOAIDCHeader();
    }

    /**
     * This is the general entrance of creating actual content.
     * When anything goes wrong, e.g. found no record, or $set_name is not recognised, an exception will be thrown.
     *
     * \param $set_name Type: string. The name of set is going to be created.
     * \param $key Type: string. The main identifier used in the system. There can be other identifiers.
     */
    public function create_obj_node($set_name, $key) {
        $set_name = strtolower($set_name);
        if (in_array($set_name, prepare_set_names())) {
            try {
                $record = Database::get()->querySingle("select * from oai_record where oai_identifier = ?s", $key);
                $metadata = Database::get()->queryArray("select * from oai_metadata where oai_record = ?d", $record->id);
                
                $meta_record = array();
                foreach ($metadata as $meta_row) {
                    $meta_record[$meta_row->field] = $meta_row->value;
                }
                
                foreach ($meta_record as $rkey => $rvalue) {
                    if (!strncmp($rkey, 'dc_', 3)) {
                        $is_serialized = false;
                        $valArr = @unserialize(base64_decode($rvalue));

                        if ($valArr !== false) {
                            $is_serialized = true;
                        }

                        if ($is_serialized) {
                            foreach ($valArr as $vkey => $vvalue) {
                                // handle multi-dimensional arrays for combined multi-lang & simple multiplicity
                                if (is_array($vvalue)) {
                                    foreach ($vvalue as $vkey2 => $vvalue2) {
                                        $added_node = $this->oai_pmh->addChild($this->working_node, str_replace("dc_", "dc:", $rkey), $vvalue2);
                                        // numeric vkeys show simple multiplicity
                                        // string vkeys show multi-lang multiplicity requiring xml:lang attribute
                                        if (!is_numeric($vkey2)) {
                                            $added_node->setAttribute('xml:lang', $vkey2);
                                        }
                                    }
                                } else {
                                    $added_node = $this->oai_pmh->addChild($this->working_node, str_replace("dc_", "dc:", $rkey), $vvalue);
                                    // numeric vkeys show simple multiplicity
                                    // string vkeys show multi-lang multiplicity requiring xml:lang attribute
                                    if (!is_numeric($vkey)) {
                                        $added_node->setAttribute('xml:lang', $vkey);
                                    }
                                }
                            }
                        } else {
                            $this->oai_pmh->addChild($this->working_node, str_replace("dc_", "dc:", $rkey), $rvalue);
                        }
                    }
                }
            } catch (PDOException $e) {
                echo "$key returned no record.\n";
                echo $e->getMessage();
            }
        } else {
            throw new Exception('Wrong set name was used: ' . $set_name);
        }
    }

    /**
     * Create a registryObjects node to hold individual registryObject's.
     *  This is only a holder node.
     */
    private function createOAIDCHeader() {
        $this->working_node = $this->oai_pmh->addChild($this->working_node, 'oai_dc:dc');
        $this->working_node->setAttribute('xmlns:oai_dc', "http://www.openarchives.org/OAI/2.0/oai_dc/");
        $this->working_node->setAttribute('xmlns:dc', "http://purl.org/dc/elements/1.1/");
        $this->working_node->setAttribute('xmlns:xsi', "http://www.w3.org/2001/XMLSchema-instance");
        $this->working_node->setAttribute('xsi:schemaLocation', 'http://www.openarchives.org/OAI/2.0/oai_dc/ http://www.openarchives.org/OAI/2.0/oai_dc.xsd');
    }

}
