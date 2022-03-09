<?php

/*
 * This file is part of EC-CUBE
 *
 * Copyright(c) EC-CUBE CO.,LTD. All Rights Reserved.
 *
 * http://www.ec-cube.co.jp/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Eccube\Security\Core\Encoder;

use Eccube\Common\EccubeConfig;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;

class PasswordEncoder implements PasswordEncoderInterface
{
    /**
     * @var string
     */
    public $auth_magic;

    /**
     * @var string
     */
    public $auth_type;

    /**
     * @var string
     */
    public $password_hash_algos;

    public function __construct(EccubeConfig $eccubeConfig)
    {
        $this->auth_magic = $eccubeConfig->get('eccube_auth_magic');
        $this->auth_type = $eccubeConfig->get('eccube_auth_type');
        $this->password_hash_algos = $eccubeConfig->get('eccube_password_hash_algos');
    }

    /**
     * Set Auth Magic.
     *
     * @param $authMagic
     */
    public function setAuthMagic($authMagic)
    {
        $this->auth_magic = $authMagic;
    }

    /**
     * Checks a raw password against an encoded password.
     *
     * @param string $encoded An encoded password
     * @param string $raw A raw password
     * @param string $salt The salt
     *
     * @return bool true if the password is valid, false otherwise
     */
    public function isPasswordValid($encoded, $raw, $salt)
    {
        if ($encoded == '') {
            return false;
        }

        if ($this->auth_type == 'PLAIN') {
            if ($raw === $encoded) {
                return true;
            }
        } else {
            // 旧バージョン(2.11未満)からの移行を考慮
            if (empty($salt)) {
                $hash = sha1($raw.':'.$this->auth_magic);
            } else {
                $hash = $this->encodePassword($raw, $salt);
            }

            if ($hash === $encoded) {
                return true;
            }
        }

        return false;
    }

    /**
     * Encodes the raw password.
     *
     * @param string $raw The password to encode
     * @param string $salt The salt
     *
     * @return string The encoded password
     */
    public function encodePassword_origianl($raw, $salt)
    {
        if ($salt == '') {
            $salt = $this->auth_magic;
        }
        if ($this->auth_type == 'PLAIN') {
            $res = $raw;
        } else {
            $res = hash_hmac($this->password_hash_algos, $raw.':'.$this->auth_magic, $salt);
        }

        return $res;
    }

    function encodePassword( $str, $salt = NULL){

        $salt = '';
        $type = 0;
        //$hash = md5(trim($str));
        $hash_algos = ( defined('USCES_HASH_ALGOS') ) ? USCES_HASH_ALGOS : 'md5';
        $secret_key = ( defined('USCES_SECRET_KEY') ) ? USCES_SECRET_KEY : '';
        if( $secret_key ) {
            $hash = hash_hmac( $hash_algos, $str.$salt, $secret_key );
        } else {
            $hash = hash( $hash_algos, $str.$salt );
        }
        $args = compact( 'str', 'salt', 'type' );
    
        // $result = apply_filters( 'usces_filter_get_hash', $hash, $args );
        // print_r($str.'->'.$salt.'->'.$hash.'->'.$args.'->'.$secret_key.'->'.$hash_algos); exit;
    
        return $hash;
    }

    /**
     * saltを生成する.
     *
     * @param int $length
     *
     * @return string
     */
    public function createSalt($length = 5)
    {
        return bin2hex(openssl_random_pseudo_bytes($length));
    }
}
