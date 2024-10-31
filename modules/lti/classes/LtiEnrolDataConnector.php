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

require_once 'modules/lti/ltiprovider/src/ToolProvider/ConsumerNonce.php';
require_once 'modules/lti/ltiprovider/src/ToolProvider/Context.php';
require_once 'modules/lti/ltiprovider/src/ToolProvider/DataConnector/DataConnector.php';
require_once 'modules/lti/ltiprovider/src/ToolProvider/ResourceLink.php';
require_once 'modules/lti/ltiprovider/src/ToolProvider/ResourceLinkShare.php';
require_once 'modules/lti/ltiprovider/src/ToolProvider/ResourceLinkShareKey.php';
require_once 'modules/lti/ltiprovider/src/ToolProvider/ToolConsumer.php';
require_once 'modules/lti/ltiprovider/src/ToolProvider/ToolProxy.php';
require_once 'modules/lti/ltiprovider/src/ToolProvider/User.php';

use IMSGlobal\LTI\ToolProvider;
use IMSGlobal\LTI\ToolProvider\ConsumerNonce;
use IMSGlobal\LTI\ToolProvider\Context;
use IMSGlobal\LTI\ToolProvider\DataConnector\DataConnector;
use IMSGlobal\LTI\ToolProvider\ResourceLink;
use IMSGlobal\LTI\ToolProvider\ResourceLinkShare;
use IMSGlobal\LTI\ToolProvider\ResourceLinkShareKey;
use IMSGlobal\LTI\ToolProvider\ToolConsumer;
use IMSGlobal\LTI\ToolProvider\ToolProxy;
use IMSGlobal\LTI\ToolProvider\User;

/**
 * Extends the IMS Tool provider library data connector.
 */
class LtiEnrolDataConnector extends DataConnector {

    /** @var string Tool consumer table name. */
    protected $consumertable;
    /** @var string Context table name. */
    protected $contexttable;
    /** @var string Consumer nonce table name. */
    protected $noncetable;
    /** @var string Resource link table name. */
    protected $resourcelinktable;
    /** @var string Resource link share key table name. */
    protected $sharekeytable;
    /** @var string Tool proxy table name. */
    protected $toolproxytable;
    /** @var string User result table name. */
    protected $userresulttable;

    /**
     * data_connector constructor.
     */
    public function __construct() {
        parent::__construct(null, 'lti_publish_');

        // Set up table names.
        $this->consumertable = $this->dbTableNamePrefix . DataConnector::CONSUMER_TABLE_NAME;
        $this->contexttable = $this->dbTableNamePrefix . DataConnector::CONTEXT_TABLE_NAME;
        $this->noncetable = $this->dbTableNamePrefix . DataConnector::NONCE_TABLE_NAME;
        $this->resourcelinktable = $this->dbTableNamePrefix . DataConnector::RESOURCE_LINK_TABLE_NAME;
        $this->sharekeytable = $this->dbTableNamePrefix . DataConnector::RESOURCE_LINK_SHARE_KEY_TABLE_NAME;
        $this->toolproxytable = $this->dbTableNamePrefix . DataConnector::TOOL_PROXY_TABLE_NAME;
        $this->userresulttable = $this->dbTableNamePrefix . DataConnector::USER_RESULT_TABLE_NAME;
    }

    /**
     * Load tool consumer object.
     *
     * @param ToolConsumer $consumer ToolConsumer object
     * @return boolean True if the tool consumer object was successfully loaded
     */
    public function loadToolConsumer($consumer): bool {
        $id = $consumer->getRecordId();

        if (!empty($id)) {
            $result = Database::get()->querySingle("SELECT * FROM " . $this->consumertable . " WHERE id = ?d", $id);
        } else {
            $key256 = DataConnector::getConsumerKey($consumer->getKey());
            $result = Database::get()->querySingle("SELECT * FROM " . $this->consumertable . " WHERE consumerkey256 = ?s", $key256);
        }

        if ($result) {
            if (empty($key256) || empty($result->consumerkey) || ($consumer->getKey() === $result->consumerkey)) {
                $this->build_tool_consumer_object($result, $consumer);
                return true;
            }
        }

        return false;
    }

    /**
     * Save tool consumer object.
     *
     * @param ToolConsumer $consumer Consumer object
     * @return boolean True if the tool consumer object was successfully saved
     */
    public function saveToolConsumer($consumer): bool {
        $key = $consumer->getKey();
        $key256 = DataConnector::getConsumerKey($key);
        if ($key === $key256) {
            $key = null;
        }
        $protected = ($consumer->protected) ? 1 : 0;
        $enabled = ($consumer->enabled) ? 1 : 0;
        $profile = (!empty($consumer->profile)) ? json_encode($consumer->profile) : null;
        $settingsvalue = serialize($consumer->getSettings());
        $now = time();
        $consumer->updated = $now;

        $id = $consumer->getRecordId();

        if (empty($id)) {
            $consumer->created = $now;
            $q = Database::get()->query("INSERT INTO " . $this->consumertable . " (
                name, 
                consumerkey256, 
                consumerkey,
                secret,
                ltiversion,
                consumername,
                consumerversion,
                consumerguid,
                profile,
                toolproxy,
                settings,
                protected,
                enabled,
                enablefrom,
                enableuntil,
                lastaccess,
                created,
                updated) VALUES (?s, ?s, ?s, ?s, ?s, ?s, ?s, ?s, ?s, ?s, ?s, ?d, ?d, ?d, ?d, ?d, ?d, ?d)",
                $consumer->name,
                $key256,
                $key,
                $consumer->secret,
                $consumer->ltiVersion,
                $consumer->consumerName,
                $consumer->consumerVersion,
                $consumer->consumerGuid,
                $profile,
                $consumer->toolProxy,
                $settingsvalue,
                $protected,
                $enabled,
                $consumer->enableFrom,
                $consumer->enableUntil,
                $consumer->lastAccess,
                $consumer->created,
                $consumer->updated
            );
            $id = $q->lastInsertID;
            if ($id) {
                $consumer->setRecordId($id);
                return true;
            }
        } else {
            Database::get()->query("UPDATE " . $this->consumertable . " 
                SET name = ?s, 
                consumerkey256 = ?s, 
                consumerkey = ?s,
                secret = ?s,
                ltiversion = ?s,
                consumername = ?s,
                consumerversion = ?s,
                consumerguid = ?s,
                profile = ?s,
                toolproxy = ?s,
                settings = ?s,
                protected = ?d,
                enabled = ?d,
                enablefrom = ?d,
                enableuntil = ?d,
                lastaccess = ?d,
                created = ?d,
                updated = ?d WHERE id = ?d",
                $consumer->name,
                $key256,
                $key,
                $consumer->secret,
                $consumer->ltiVersion,
                $consumer->consumerName,
                $consumer->consumerVersion,
                $consumer->consumerGuid,
                $profile,
                $consumer->toolProxy,
                $settingsvalue,
                $protected,
                $enabled,
                $consumer->enableFrom,
                $consumer->enableUntil,
                $consumer->lastAccess,
                $consumer->created,
                $consumer->updated,
                $id
            );
            return true;
        }

        return false;
    }

    /**
     * Delete tool consumer object and related records.
     *
     * @param ToolConsumer $consumer Consumer object
     * @return boolean True if the tool consumer object was successfully deleted
     */
    public function deleteToolConsumer($consumer): bool {
        $consumerpk = $consumer->getRecordId();

        // Delete any nonce values for this consumer.
        Database::get()->query("DELETE FROM " . $this->noncetable . " WHERE consumerid = ?d", $consumerpk);

        // Delete any outstanding share keys for resource links for this consumer.
        Database::get()->query("DELETE FROM " . $this->sharekeytable . " WHERE resourcelinkid IN (
            SELECT rl.id 
              FROM " . $this->resourcelinktable . " rl 
             WHERE rl.consumerid = ?d
        )", $consumerpk);

        // Delete any outstanding share keys for resource links for contexts in this consumer.
        Database::get()->query("DELETE FROM " . $this->sharekeytable . " WHERE resourcelinkid IN (
                SELECT rl.id
                FROM " . $this->resourcelinktable . " rl
          INNER JOIN " . $this->contexttable . " c
                  ON rl.contextid = c.id
               WHERE c.consumerid = ?d
        )", $consumerpk);

        // Delete any users in resource links for this consumer.
        Database::get()->query("DELETE FROM " . $this->userresulttable . " WHERE resourcelinkid IN (
            SELECT rl.id
              FROM " . $this->resourcelinktable . " rl
             WHERE rl.consumerid = ?d
        )", $consumerpk);

        // Delete any users in resource links for contexts in this consumer.
        Database::get()->query("DELETE FROM " . $this->userresulttable . " WHERE resourcelinkid IN (
                SELECT rl.id
                  FROM " . $this->resourcelinktable . " rl
            INNER JOIN " . $this->contexttable . " c
                    ON rl.contextid = c.id
                 WHERE c.consumerid = ?d
        )", $consumerpk);

        // Update any resource links for which this consumer is acting as a primary resource link.
        $updaterecords = Database::get()->queryArray("SELECT * FROM " . $this->resourcelinktable . " WHERE primaryresourcelinkid IN (
            SELECT rl.id
              FROM " . $this->resourcelinktable . " rl
             WHERE rs.consumerid = ?d 
        )", $consumerpk);
        foreach ($updaterecords as $record) {
            Database::get()->query("UPDATE " . $this->resourcelinktable . " SET primaryresourcelinkid = NULL, shareapproved = NULL WHERE id = ?d", $record->id);
        }

        // Update any resource links for contexts in which this consumer is acting as a primary resource link.
        $updaterecords = Database::get()->queryArray("SELECT * FROM " . $this->resourcelinktable . " WHERE primaryresourcelinkid IN (
                SELECT rl.id
                  FROM " . $this->resourcelinktable . " rl
            INNER JOIN " . $this->contexttable . " c
                    ON rl.contextid = c.id
                 WHERE c.consumerid = ?d
        )", $consumerpk);
        foreach ($updaterecords as $record) {
            Database::get()->query("UPDATE " . $this->resourcelinktable . " SET primaryresourcelinkid = NULL, shareapproved = NULL WHERE id = ?d", $record->id);
        }

        // Delete any resource links for contexts in this consumer.
        Database::get()->query("DELETE FROM " . $this->resourcelinktable . " WHERE contextid IN (
            SELECT c.id
              FROM " . $this->contexttable . " c
             WHERE c.consumerid = ?d
        )", $consumerpk);

        // Delete any resource links for this consumer.
        Database::get()->query("DELETE FROM " . $this->resourcelinktable . " WHERE consumerid = ?d", $consumerpk);

        // Delete any contexts for this consumer.
        Database::get()->query("DELETE FROM " . $this->contexttable . " WHERE consumerid = ?d", $consumerpk);

        // Delete consumer.
        Database::get()->query("DELETE FROM " . $this->consumertable . " WHERE id = ?d", $consumerpk);

        $consumer->initialize();

        return true;
    }

    /**
     * Load all tool consumers from the database.
     * @return array
     */
    public function getToolConsumers(): array {
        $consumers = [];

        $rsconsumers = Database::get()->queryArray("SELECT * FROM " . $this->consumertable . " ORDER BY name");
        foreach ($rsconsumers as $row) {
            $consumer = new ToolProvider\ToolConsumer($row->consumerkey, $this);
            $this->build_tool_consumer_object($row, $consumer);
            $consumers[] = $consumer;
        }

        return $consumers;
    }

    /*
     * ToolProxy methods.
     */

    /**
     * Load the tool proxy from the database.
     *
     * @param ToolProxy $toolProxy
     * @return bool
     */
    public function loadToolProxy($toolProxy): bool {
        return false;
    }

    /**
     * Save the tool proxy to the database.
     *
     * @param ToolProxy $toolProxy
     * @return bool
     */
    public function saveToolProxy($toolProxy): bool {
        return false;
    }

    /**
     * Delete the tool proxy from the database.
     *
     * @param ToolProxy $toolProxy
     * @return bool
     */
    public function deleteToolProxy($toolProxy): bool {
        return false;
    }

    /*
     * Context methods.
     */

    /**
     * Load context object.
     *
     * @param Context $context Context object
     * @return boolean True if the context object was successfully loaded
     */
    public function loadContext($context): bool {
        if (!empty($context->getRecordId())) {
            $row = Database::get()->querySingle("SELECT * FROM " . $this->contexttable . " WHERE id = ?d", $context->getRecordId());
        } else {
            $row = Database::get()->querySingle("SELECT * FROM " . $this->contexttable . " 
                WHERE consumerid = ?d 
                  AND lticontextkey = ?s", $context->getConsumer()->getRecordId(), $context->ltiContextId);
        }
        if ($row) {
            $context->setRecordId($row->id);
            $context->setConsumerId($row->consumerid);
            $context->ltiContextId = $row->lticontextkey;
            $context->type = $row->type;
            $settings = unserialize($row->settings);
            if (!is_array($settings)) {
                $settings = array();
            }
            $context->setSettings($settings);
            $context->created = $row->created;
            $context->updated = $row->updated;
            return true;
        }

        return false;
    }

    /**
     * Save context object.
     *
     * @param Context $context Context object
     * @return boolean True if the context object was successfully saved
     */
    public function saveContext($context): bool {
        $now = time();
        $context->updated = $now;
        $settingsvalue = serialize($context->getSettings());
        $id = $context->getRecordId();
        $consumerpk = $context->getConsumer()->getRecordId();

        $isinsert = empty($id);
        if ($isinsert) {
            $context->created = $now;
            $q = Database::get()->query("INSERT INTO " . $this->contexttable . " (
                consumerid,
                lticontextkey,
                type,
                settings,
                created,
                updated) VALUES (?d, ?s, ?s, ?s, ?d, ?d)",
                $consumerpk,
                $context->ltiContextId,
                $context->type,
                $settingsvalue,
                $context->created,
                $context->updated
            );
            $id = $q->lastInsertID;
            if ($id) {
                $context->setRecordId($id);
                return true;
            }
        } else {
            Database::get()->query("UPDATE " . $this->contexttable . " 
                SET consumerid = ?d,
                lticontextkey = ?s,
                type = ?s,
                settings = ?s,
                created = ?d,
                updated = ?d WHERE id = ?d",
                $consumerpk,
                $context->ltiContextId,
                $context->type,
                $settingsvalue,
                $context->created,
                $context->updated,
                $id
            );
            return true;
        }

        return false;
    }

    /**
     * Delete context object.
     *
     * @param Context $context Context object
     * @return boolean True if the Context object was successfully deleted
     */
    public function deleteContext($context): bool {
        $contextid = $context->getRecordId();

        $where = " WHERE resourcelinkid IN (
            SELECT rl.id
              FROM " . $this->resourcelinktable . " rl
             WHERE rl.contextid = ?d
        )";

        // Delete any outstanding share keys for resource links for this context.
        Database::get()->query("DELETE FROM " . $this->sharekeytable . $where, $contextid);

        // Delete any users in resource links for this context.
        Database::get()->query("DELETE FROM " . $this->userresulttable . $where, $contextid);

        // Update any resource links for which this consumer is acting as a primary resource link.
        $updaterecords = Database::get()->queryArray("SELECT * FROM " . $this->resourcelinktable . " WHERE primaryresourcelinkid (
            SELECT rl.id
              FROM " . $this->resourcelinktable . " rl
             WHERE rl.contextid = ?d
        )", $contextid);
        foreach ($updaterecords as $record) {
            Database::get()->query("UPDATE " . $this->resourcelinktable . " SET primaryresourcelinkid = NULL, shareapproved = NULL WHERE id = ?d", $record->id);
        }

        // Delete any resource links for this context.
        Database::get()->query("DELETE FROM " . $this->resourcelinktable . " WHERE contextid = ?d", $contextid);

        // Delete context.
        Database::get()->query("DELETE FROM " . $this->contexttable . " WHERE id = ?d", $contextid);

        $context->initialize();

        return true;
    }

    /*
     * ResourceLink methods
     */

    /**
     * Load resource link object.
     *
     * @param ResourceLink $resourceLink ResourceLink object
     * @return boolean True if the resource link object was successfully loaded
     */
    public function loadResourceLink($resourceLink): bool {
        $resourceid = $resourceLink->getRecordId();

        if (!empty($resourceid)) {
            $row = Database::get()->querySingle("SELECT * FROM " . $this->resourcelinktable . " WHERE id = ?d", $resourceid);
        } else if (!empty($resourceLink->getContext())) {
            $row = Database::get()->querySingle("SELECT * FROM " . $this->resourcelinktable . " 
                    WHERE contextid = ?d 
                      AND ltiresourcelinkkey = ?s",
                $resourceLink->getContext()->getRecordId(),
                $resourceLink->getId()
            );
        } else {
            $row = Database::get()->querySingle("SELECT r.* 
                        FROM " . $this->resourcelinktable . " r
             LEFT OUTER JOIN " . $this->contexttable . " c
                          ON r.contextid = c.id
                       WHERE (r.consumerid = ?d OR c.consumerid = ?d)
                         AND r.ltiresourcelinkkey = ?d",
                $resourceLink->getConsumer()->getRecordId(),
                $resourceLink->getConsumer()->getRecordId(),
                $resourceLink->getId()
            );
        }
        if ($row) {
            $resourceLink->setRecordId($row->id);
            if (!is_null($row->contextid)) {
                $resourceLink->setContextId($row->contextid);
            } else {
                $resourceLink->setContextId(null);
            }
            if (!is_null($row->consumerid)) {
                $resourceLink->setConsumerId($row->consumerid);
            } else {
                $resourceLink->setConsumerId(null);
            }
            $resourceLink->ltiResourceLinkId = $row->ltiresourcelinkkey;
            $settings = unserialize($row->settings);
            if (!is_array($settings)) {
                $settings = array();
            }
            $resourceLink->setSettings($settings);
            if (!is_null($row->primaryresourcelinkid)) {
                $resourceLink->primaryResourceLinkId = $row->primaryresourcelinkid;
            } else {
                $resourceLink->primaryResourceLinkId = null;
            }
            $resourceLink->shareApproved = (is_null($row->shareapproved)) ? null : ($row->shareapproved == 1);
            $resourceLink->created = $row->created;
            $resourceLink->updated = $row->updated;
            return true;
        }

        return false;
    }

    /**
     * Save resource link object.
     *
     * @param ResourceLink $resourceLink Resource_Link object
     * @return boolean True if the resource link object was successfully saved
     */
    public function saveResourceLink($resourceLink): bool {
        if (is_null($resourceLink->shareApproved)) {
            $approved = null;
        } else if ($resourceLink->shareApproved) {
            $approved = 1;
        } else {
            $approved = 0;
        }
        if (empty($resourceLink->primaryResourceLinkId)) {
            $primaryresourcelinkid = null;
        } else {
            $primaryresourcelinkid = $resourceLink->primaryResourceLinkId;
        }
        $now = time();
        $resourceLink->updated = $now;
        $settingsvalue = serialize($resourceLink->getSettings());
        if (!empty($resourceLink->getContext())) {
            $consumerid = null;
            $contextid = $resourceLink->getContext()->getRecordId();
        } else if (!empty($resourceLink->getContextId())) {
            $consumerid = null;
            $contextid = $resourceLink->getContextId();
        } else {
            $consumerid = $resourceLink->getConsumer()->getRecordId();
            $contextid = null;
        }
        $id = $resourceLink->getRecordId();

        if (empty($id)) {
            $resourceLink->created = $now;
            $q = Database::get()->query("INSERT INTO " . $this->resourcelinktable . " (
                contextid, 
                consumerid, 
                ltiresourcelinkkey,
                settings,
                primaryresourcelinkid,
                shareapproved,
                created,
                updated) VALUES (?d, ?d, ?s, ?s, ?d, ?d, ?d, ?d)",
                $contextid,
                $consumerid,
                $resourceLink->getId(),
                $settingsvalue,
                $primaryresourcelinkid,
                $approved,
                $resourceLink->created,
                $resourceLink->updated
            );
            $id = $q->lastInsertID;
            if ($id) {
                $resourceLink->setRecordId($id);
                return true;
            }
        } else {
            Database::get()->query("UPDATE " . $this->resourcelinktable . "
                SET contextid = ?d, 
                consumerid = ?d, 
                ltiresourcelinkkey = ?s,
                settings = ?s,
                primaryresourcelinkid = ?d,
                shareapproved = ?d,
                created = ?d,
                updated = ?d WHERE id = ?d",
                $contextid,
                $consumerid,
                $resourceLink->getId(),
                $settingsvalue,
                $primaryresourcelinkid,
                $approved,
                $resourceLink->created,
                $resourceLink->updated,
                $id
            );
            return true;
        }

        return false;
    }

    /**
     * Delete resource link object.
     *
     * @param ResourceLink $resourceLink ResourceLink object
     * @return boolean True if the resource link object and its related records were successfully deleted.
     *                 Otherwise, a DML exception is thrown.
     */
    public function deleteResourceLink($resourceLink): bool {
        $resourcelinkid = $resourceLink->getRecordId();

        // Delete any outstanding share keys for resource links for this consumer.
        Database::get()->query("DELETE FROM " . $this->sharekeytable . " WHERE resourcelinkid = ?d", $resourcelinkid);

        // Delete users.
        Database::get()->query("DELETE FROM " . $this->userresulttable . " WHERE resourcelinkid = ?d", $resourcelinkid);

        // Update any resource links for which this is the primary resource link.
        $updaterecords = Database::get()->queryArray("SELECT * FROM " . $this->resourcelinktable . " WHERE primaryresourcelinkid = ?d", $resourcelinkid);
        foreach ($updaterecords as $record) {
            $record->primaryresourcelinkid = null;
            Database::get()->query("UPDATE " . $this->resourcelinktable . " SET primaryresourcelinkid = NULL WHERE id = ?d", $record->id);
        }

        // Delete resource link.
        Database::get()->query("DELETE FROM " . $this->resourcelinktable . " WHERE id = ?d", $resourcelinkid);

        $resourceLink->initialize();

        return true;
    }

    /**
     * Get array of user objects.
     *
     * Obtain an array of User objects for users with a result sourcedId.  The array may include users from other
     * resource links which are sharing this resource link.  It may also be optionally indexed by the user ID of a specified scope.
     *
     * @param ResourceLink $resourceLink Resource link object
     * @param boolean $localOnly True if only users within the resource link are to be returned
     *                           (excluding users sharing this resource link)
     * @param int $idScope Scope value to use for user IDs
     * @return array Array of User objects
     */
    public function getUserResultSourcedIDsResourceLink($resourceLink, $localOnly, $idScope): array {
        $users = [];

        // Where clause for the subquery.
        $subwhere = " (id = ?d AND primaryresourcelinkid IS NULL) ";
        if (!$localOnly) {
            $subwhere .= " OR (primaryresourcelinkid = :resourcelinkid2 AND shareapproved = 1)";
            $subwhere .= " OR (primaryresourcelinkid = ?d AND shareapproved = 1)";
        }

        // Fetch records.
        $sql = "SELECT id, ltiresultsourcedid, ltiuserkey, created, updated
                  FROM " . $this->userresulttable . "
                 WHERE resourcelinkid IN (
                    SELECT id
                      FROM " . $this->resourcelinktable . "
                     WHERE " . $subwhere . "
        )";
        if ($localOnly) {
            $rs = Database::get()->queryArray($sql, $resourceLink->getRecordId());
        } else {
            $rs = Database::get()->queryArray($sql, $resourceLink->getRecordId(), $resourceLink->getRecordId());
        }
        foreach ($rs as $row) {
            $user = User::fromResourceLink($resourceLink, $row->ltiuserkey);
            $user->setRecordId($row->id);
            $user->ltiResultSourcedId = $row->ltiresultsourcedid;
            $user->created = $row->created;
            $user->updated = $row->updated;
            if (is_null($idScope)) {
                $users[] = $user;
            } else {
                $users[$user->getId($idScope)] = $user;
            }
        }

        return $users;
    }

    /**
     * Get array of shares defined for this resource link.
     *
     * @param ResourceLink $resourceLink ResourceLink object
     * @return array Array of ResourceLinkShare objects
     */
    public function getSharesResourceLink($resourceLink): array {
        $shares = [];

        $records = Database::get()->queryArray("SELECT id, shareapproved, consumerid FROM " . $this->resourcelinktable . " 
            WHERE primaryresourcelinkid = ?d",
            $resourceLink->getRecordId()
        );
        foreach ($records as $record) {
            $share = new ResourceLinkShare();
            $share->resourceLinkId = $record->id;
            $share->approved = $record->shareapproved == 1;
            $shares[] = $share;
        }

        return $shares;
    }

    /*
     * ConsumerNonce methods
     */

    /**
     * Load nonce object.
     *
     * @param ConsumerNonce $nonce Nonce object
     * @return boolean True if the nonce object was successfully loaded
     */
    public function loadConsumerNonce($nonce): bool {
        // Delete any expired nonce values.
        $now = time();
        Database::get()->query("DELETE FROM " . $this->noncetable . " WHERE expires <= ?d", $now);

        // Load the nonce.
        $result = Database::get()->querySingle("SELECT * FROM " . $this->noncetable . " 
            WHERE consumerid = ?d AND value = ?s",
            $nonce->getConsumer()->getRecordId(),
            $nonce->getValue()
        );

        return !empty($result);
    }

    /**
     * Save nonce object.
     *
     * @param ConsumerNonce $nonce Nonce object
     * @return boolean True if the nonce object was successfully saved
     */
    public function saveConsumerNonce($nonce): bool {
        Database::get()->query("INSERT INTO " . $this->noncetable . " (consumerid, value, expires) VALUES (?d, ?s, ?d)",
            $nonce->getConsumer()->getRecordId(),
            $nonce->getValue(),
            $nonce->expires
        );
        return true;
    }

    /*
     * ResourceLinkShareKey methods.
     */

    /**
     * Load resource link share key object.
     *
     * @param ResourceLinkShareKey $shareKey ResourceLink share key object
     * @return boolean True if the resource link share key object was successfully loaded
     */
    public function loadResourceLinkShareKey($shareKey): bool {
        // Clear expired share keys.
        $now = time();
        Database::get()->query("DELETE FROM " . $this->sharekeytable . " WHERE expires <= ?d", $now);

        // Load share key.
        $sharekeyrecord = Database::get()->querySingle("SELECT resourcelinkid, autoapprove, expires FROM " . $this->sharekeytable . " 
            WHERE sharekey = ?s",
            $shareKey->getId()
        );
        if ($sharekeyrecord) {
            if ($sharekeyrecord->resourcelinkid == $shareKey->resourceLinkId) {
                $shareKey->autoApprove = $sharekeyrecord->autoapprove == 1;
                $shareKey->expires = $sharekeyrecord->expires;
                return true;
            }
        }

        return false;
    }

    /**
     * Save resource link share key object.
     *
     * @param ResourceLinkShareKey $shareKey Resource link share key object
     * @return boolean True if the resource link share key object was successfully saved
     */
    public function saveResourceLinkShareKey($shareKey): bool {
        if ($shareKey->autoApprove) {
            $approve = 1;
        } else {
            $approve = 0;
        }

        Database::get()->query("INSERT INTO " . $this->sharekeytable . " (sharekey, resourcelinkid, autoapprove, expires) 
            VALUES (?s, ?d, ?d, ?d)",
            $shareKey->getId(),
            $shareKey->resourceLinkId,
            $approve,
            $shareKey->expires
        );

        return true;
    }

    /**
     * Delete resource link share key object.
     *
     * @param ResourceLinkShareKey $shareKey Resource link share key object
     * @return boolean True if the resource link share key object was successfully deleted
     */
    public function deleteResourceLinkShareKey($shareKey): bool {
        Database::get()->query("DELETE FROM " . $this->sharekeytable . " WHERE sharekey = ?s", $shareKey->getId());
        $shareKey->initialize();
        return true;
    }

    /*
     * User methods
     */

    /**
     * Load user object.
     *
     * @param User $user User object
     * @return boolean True if the user object was successfully loaded
     */
    public function loadUser($user): bool {
        $userid = $user->getRecordId();
        $fields = 'id, resourcelinkid, ltiuserkey, ltiresultsourcedid, created, updated';
        if (!empty($userid)) {
            $row = Database::get()->querySingle("SELECT " . $fields . " FROM " . $this->userresulttable . " WHERE id = ?d", $userid);
        } else {
            $resourcelinkid = $user->getResourceLink()->getRecordId();
            $userid = $user->getId(ToolProvider\ToolProvider::ID_SCOPE_ID_ONLY);
            $row = Database::get()->querySingle("SELECT " . $fields . " FROM " . $this->userresulttable . " 
                WHERE resourcelinkid = ?d AND ltiuserkey = ?s",
                $resourcelinkid,
                $userid
            );
        }
        if ($row) {
            $user->setRecordId($row->id);
            $user->setResourceLinkId($row->resourcelinkid);
            $user->ltiUserId = $row->ltiuserkey;
            $user->ltiResultSourcedId = $row->ltiresultsourcedid;
            $user->created = $row->created;
            $user->updated = $row->updated;
            return true;
        }

        return false;
    }

    /**
     * Save user object.
     *
     * @param User $user User object
     * @return boolean True if the user object was successfully saved
     */
    public function saveUser($user): bool {
        $now = time();
        $isinsert = is_null($user->created);
        $user->updated = $now;

        if ($isinsert) {
            $user->created = $now;
            $q = Database::get()->query("INSERT INTO " . $this->userresulttable . " (
                resourcelinkid,
                ltiuserkey,
                ltiresultsourcedid,
                created,
                updated) VALUES (?d, ?s, ?s, ?d, ?d)",
                $user->getResourceLink()->getRecordId(),
                $user->getId(ToolProvider\ToolProvider::ID_SCOPE_ID_ONLY),
                $user->ltiResultSourcedId,
                $user->created,
                $user->updated
            );
            $id = $q->lastInsertID;
            if ($id) {
                $user->setRecordId($id);
                return true;
            }
        } else {
            Database::get()->query("UPDATE " . $this->userresulttable . " SET
                resourcelinkid = ?d,
                ltiuserkey = ?s,
                ltiresultsourcedid = ?s,
                created = ?d,
                updated = ?d WHERE id = ?d",
                $user->getResourceLink()->getRecordId(),
                $user->getId(ToolProvider\ToolProvider::ID_SCOPE_ID_ONLY),
                $user->ltiResultSourcedId,
                $user->created,
                $user->updated,
                $user->getRecordId()
            );
            return true;
        }

        return false;
    }

    /**
     * Delete user object.
     *
     * @param User $user User object
     * @return boolean True if the user object was successfully deleted
     */
    public function deleteUser($user): bool {
        Database::get()->query("DELETE FROM " . $this->userresulttable . " WHERE id = ?d", $user->getRecordId());
        $user->initialize();
        return true;
    }

    /**
     * Fetches the list of Context objects that are linked to a ToolConsumer.
     *
     * @param ToolConsumer $consumer
     * @return Context[]
     */
    public function get_contexts_from_consumer(ToolConsumer $consumer): array {
        $contexts = [];
        $contextrecords = Database::get()->queryArray("SELECT lticontextkey FROM " . $this->contexttable . " WHERE consumerid = ?d", $consumer->getRecordId());
        foreach ($contextrecords as $record) {
            $context = Context::fromConsumer($consumer, $record->lticontextkey);
            $contexts[] = $context;
        }
        return $contexts;
    }

    /**
     * Fetches a resource link record that is associated with a ToolConsumer.
     *
     * @param ToolConsumer $consumer
     * @return ResourceLink
     */
    public function get_resourcelink_from_consumer(ToolConsumer $consumer): ResourceLink {
        $resourcelink = null;
        $resourcelinkrecord = Database::get()->querySingle("SELECT ltiresourcelinkkey FROM " . $this->resourcelinktable . " WHERE consumerid = ?d", $consumer->getRecordId());
        if ($resourcelinkrecord) {
            $resourcelink = ResourceLink::fromConsumer($consumer, $resourcelinkrecord->ltiresourcelinkkey);
        }
        return $resourcelink;
    }

    /**
     * Fetches a resource link record that is associated with a Context object.
     *
     * @param Context $context
     * @return ResourceLink
     */
    public function get_resourcelink_from_context(Context $context): ResourceLink {
        $resourcelink = null;
        $resourcelinkrecord = Database::get()->querySingle("SELECT ltiresourcelinkkey FROM " . $this->resourcelinktable . " WHERE contextid = ?d", $context->getRecordId());
        if ($resourcelinkrecord) {
            $resourcelink = ResourceLink::fromContext($context, $resourcelinkrecord->ltiresourcelinkkey);
        }
        return $resourcelink;
    }

    /**
     * Builds a ToolConsumer object from a record object from the DB.
     *
     * @param array|DBResult $record The DB record object.
     * @param ToolConsumer $consumer
     */
    protected function build_tool_consumer_object($record, ToolConsumer $consumer) {
        $consumer->setRecordId($record->id);
        $consumer->name = $record->name;
        $key = empty($record->consumerkey) ? $record->consumerkey256 : $record->consumerkey;
        $consumer->setKey($key);
        $consumer->secret = $record->secret;
        $consumer->ltiVersion = $record->ltiversion;
        $consumer->consumerName = $record->consumername;
        $consumer->consumerVersion = $record->consumerversion;
        $consumer->consumerGuid = $record->consumerguid;
        $consumer->profile = json_decode($record->profile);
        $consumer->toolProxy = $record->toolproxy;
        $settings = unserialize($record->settings);
        if (!is_array($settings)) {
            $settings = array();
        }
        $consumer->setSettings($settings);
        $consumer->protected = $record->protected == 1;
        $consumer->enabled = $record->enabled == 1;
        $consumer->enableFrom = null;
        if (!is_null($record->enablefrom)) {
            $consumer->enableFrom = $record->enablefrom;
        }
        $consumer->enableUntil = null;
        if (!is_null($record->enableuntil)) {
            $consumer->enableUntil = $record->enableuntil;
        }
        $consumer->lastAccess = null;
        if (!is_null($record->lastaccess)) {
            $consumer->lastAccess = $record->lastaccess;
        }
        $consumer->created = $record->created;
        $consumer->updated = $record->updated;
    }
}
