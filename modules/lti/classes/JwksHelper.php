<?php

/* ========================================================================
 * Open eClass
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2024  Greek Universities Network - GUnet
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

use Firebase\JWT\JWT;

const EXT_LTI_PRIVATEKEY = "ext_lti_privatekey";
const EXT_LTI_KID = "ext_lti_kid";

class JwksHelper {

    /**
     * See https://www.imsglobal.org/spec/security/v1p1#approved-jwt-signing-algorithms.
     */
    private static array $ltiSupportedAlgs = [
        'RS256' => 'RSA',
        'RS384' => 'RSA',
        'RS512' => 'RSA',
        'ES256' => 'EC',
        'ES384' => 'EC',
        'ES512' => 'EC'
    ];

    /**
     * This function checks if a private key has been generated for this installation.
     * If the key does not exist it generates a new one.
     * @return void
     */
    public static function verifyPrivateKeyExists(): void {
        $key = get_config(EXT_LTI_PRIVATEKEY);

        // If a valid key is already generated, no need to verify.
        if (empty($key)) {

            // Create the private key.
            $kid = bin2hex(openssl_random_pseudo_bytes(10));
            set_config(EXT_LTI_KID, $kid);
            $config = array(
                "digest_alg" => "sha256",
                "private_key_bits" => 2048,
                "private_key_type" => OPENSSL_KEYTYPE_RSA
            );
            $res = openssl_pkey_new($config);
            openssl_pkey_export($res, $privatekey);

            if (!empty($privatekey)) {
                set_config(EXT_LTI_PRIVATEKEY, $privatekey);
            } else {
                error_log('LTI 1.3 requires valid PHP OpenSSL support. Please make sure PHP is properly compiled and OpenSSL is properly installed and configured on your system.');
            }
        }
    }

    /**
     * Returns the private key for signing outgoing JWT.
     *
     * @return array keys are kid and key in PEM format.
     */
    public static function getPrivateKey(): array {
        $privatekey = get_config(EXT_LTI_PRIVATEKEY);
        $kid = get_config(EXT_LTI_KID);
        return [
            "key" => $privatekey,
            "kid" => $kid
        ];
    }

    /**
     * Returns the JWK Key Set for this installation.
     * @return array keyset holding the site public key.
     * @throws Exception
     */
    public static function getJwks(): array {
        $jwks = array('keys' => array());

        $privatekey = self::getPrivateKey();
        $res = openssl_pkey_get_private($privatekey['key']);
        $details = openssl_pkey_get_details($res);

        // Avoid passing null values to base64_encode.
        if (!isset($details['rsa']['e']) || !isset($details['rsa']['n'])) {
            throw new Exception("LTI Error: essential openssl keys not set");
        }

        $jwk = array();
        $jwk['kty'] = 'RSA';
        $jwk['alg'] = 'RS256';
        $jwk['kid'] = $privatekey['kid'];
        $jwk['e'] = rtrim(strtr(base64_encode($details['rsa']['e']), '+/', '-_'), '=');
        $jwk['n'] = rtrim(strtr(base64_encode($details['rsa']['n']), '+/', '-_'), '=');
        $jwk['use'] = 'sig';

        $jwks['keys'][] = $jwk;
        return $jwks;
    }

    /**
     * Take an array of JWKS keys and infer the 'alg' property for a single key, if missing, based on an input JWT.
     *
     * This only sets the 'alg' property for a single key when all the following conditions are met:
     * - The key's 'kid' matches the 'kid' provided in the JWT's header.
     * - The key's 'alg' is missing.
     * - The JWT's header 'alg' matches the algorithm family of the key (the key's kty).
     * - The JWT's header 'alg' matches one of the approved LTI asymmetric algorithms.
     *
     * Keys not matching the above are left unchanged.
     *
     * @param array $jwks the keyset array.
     * @param string $jwt the JWT string.
     * @return array the fixed keyset array.
     * @throws Exception
     */
    public static function fixJwksAlg(array $jwks, string $jwt): array {
        $jwtparts = explode('.', $jwt);
        $jwtheader = json_decode(JWT::urlsafeB64Decode($jwtparts[0]), true);
        if (!isset($jwtheader['kid'])) {
            throw new Exception('LTI Error: kid must be provided in JWT header.');
        }

        foreach ($jwks['keys'] as $index => $key) {
            // Only fix the key being referred to in the JWT.
            if ($jwtheader['kid'] != $key['kid']) {
                continue;
            }

            // Only fix the key if the alg is missing.
            if (!empty($key['alg'])) {
                continue;
            }

            // The header alg must match the key type (family) specified in the JWK's kty.
            if (!isset(static::$ltiSupportedAlgs[$jwtheader['alg']]) ||
                static::$ltiSupportedAlgs[$jwtheader['alg']] != $key['kty']) {
                throw new Exception('LTI Error: Alg specified in the JWT header is incompatible with the JWK key type');
            }

            $jwks['keys'][$index]['alg'] = $jwtheader['alg'];
        }

        return $jwks;
    }

}