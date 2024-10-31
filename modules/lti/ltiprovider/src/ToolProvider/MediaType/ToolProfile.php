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

namespace IMSGlobal\LTI\ToolProvider\MediaType;
use IMSGlobal\LTI\ToolProvider\ToolProvider;

/**
 * Class to represent an LTI Tool Profile
 *
 * @author  Stephen P Vickers <svickers@imsglobal.org>
 * @copyright  IMS Global Learning Consortium Inc
 * @date  2016
 * @version  3.0.0
 * @license  GNU Lesser General Public License, version 3 (<http://www.gnu.org/licenses/lgpl.html>)
 */
class ToolProfile
{

    public $product_instance;

/**
 * Class constructor.
 *
 * @param ToolProvider $toolProvider   Tool Provider object
 */
    function __construct($toolProvider)
    {

        $this->lti_version = 'LTI-2p0';

        if (!empty($toolProvider->product)) {
            $this->product_instance = new \stdClass;
        }
        if (!empty($toolProvider->product->id)) {
            $this->product_instance->guid = $toolProvider->product->id;
        }
        if (!empty($toolProvider->product->name)) {
            $this->product_instance->product_info = new \stdClass;
            $this->product_instance->product_info->product_name = new \stdClass;
            $this->product_instance->product_info->product_name->default_value = $toolProvider->product->name;
            $this->product_instance->product_info->product_name->key = 'tool.name';
        }
        if (!empty($toolProvider->product->description)) {
            $this->product_instance->product_info->description = new \stdClass;
            $this->product_instance->product_info->description->default_value = $toolProvider->product->description;
            $this->product_instance->product_info->description->key = 'tool.description';
        }
        if (!empty($toolProvider->product->url)) {
            $this->product_instance->guid = $toolProvider->product->url;
        }
        if (!empty($toolProvider->product->version)) {
            $this->product_instance->product_info->product_version = $toolProvider->product->version;
        }
        if (!empty($toolProvider->vendor)) {
            $this->product_instance->product_info->product_family = new \stdClass;
            $this->product_instance->product_info->product_family->vendor = new \stdClass;
        }
        if (!empty($toolProvider->vendor->id)) {
            $this->product_instance->product_info->product_family->vendor->code = $toolProvider->vendor->id;
        }
        if (!empty($toolProvider->vendor->name)) {
            $this->product_instance->product_info->product_family->vendor->vendor_name = new \stdClass;
            $this->product_instance->product_info->product_family->vendor->vendor_name->default_value = $toolProvider->vendor->name;
            $this->product_instance->product_info->product_family->vendor->vendor_name->key = 'tool.vendor.name';
        }
        if (!empty($toolProvider->vendor->description)) {
            $this->product_instance->product_info->product_family->vendor->description = new \stdClass;
            $this->product_instance->product_info->product_family->vendor->description->default_value = $toolProvider->vendor->description;
            $this->product_instance->product_info->product_family->vendor->description->key = 'tool.vendor.description';
        }
        if (!empty($toolProvider->vendor->url)) {
            $this->product_instance->product_info->product_family->vendor->website = $toolProvider->vendor->url;
        }
        if (!empty($toolProvider->vendor->timestamp)) {
            $this->product_instance->product_info->product_family->vendor->timestamp = date('Y-m-d\TH:i:sP', $toolProvider->vendor->timestamp);
        }

        $this->resource_handler = array();
        foreach ($toolProvider->resourceHandlers as $resourceHandler) {
            $this->resource_handler[] = new ResourceHandler($toolProvider, $resourceHandler);
        }
        if (!empty($toolProvider->baseUrl)) {
            $this->base_url_choice = array();
            $this->base_url_choice[] = new \stdClass;
            $this->base_url_choice[0]->default_base_url = $toolProvider->baseUrl;
        }

    }

}
