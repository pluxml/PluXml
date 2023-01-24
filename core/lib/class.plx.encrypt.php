<?php
/**
 * Classe plxEncrypt responsable du cryptage et décryptage de données
 *
 * @package PLX
 * @author	Stephane F, Jean-Pierre Pourrez "bazooka07"
 **/

# https://github.com/abcarroll/hack3r3d-php-openssl-cryptor/blob/master/src/Cryptor.php

class plxEncrypt {
	const FORMAT_B64 = true; # either HEX

	private static function _key() {
		return
		  dechex(filemtime(__DIR__ . '/class.plx.motor.php') & 0x7fffff)
		. dechex(filemtime(__DIR__ . '/class.plx.admin.php') & 0x7fffff)
		. dechex(filemtime(__DIR__ . '/class.plx.show.php') & 0x7fffff);

	}

	private static function _getCipherMethod() {
		$methods = openssl_get_cipher_methods(true);
		foreach(['aes-256-ctr', 'aes-192-ctr', 'aes-128-ctr', 'aes256',] as $f) {
			if(in_array($f, $methods)) {
				return $f;
			}
		}

		return false;
	}

	public static function encryptId($str, $class='') {
		$cipher_algo = self::_getCipherMethod();
		if($cipher_algo) {
			$iv_num_bytes = openssl_cipher_iv_length($cipher_algo);
			$iv = openssl_random_pseudo_bytes($iv_num_bytes);
			$hash_key = openssl_digest(self::_key(), 'sha256', true);
			$encrypt = openssl_encrypt(
				$str,
				$cipher_algo,
				$hash_key,
				OPENSSL_RAW_DATA,
				$iv
			);

			if(self::FORMAT_B64) {
				return base64_encode($iv . $encrypt);
			} else {
				$parts = unpack('H*', $iv . $encrypt);
				if(isset($parts[1])) {
					return $parts[1];
				}
			}
		}

		# No encryption
		return $str;
	}

	public static function decryptId($str, $class='') {
		$cipher_algo = self::_getCipherMethod();
		if($cipher_algo) {
			$iv_num_bytes = openssl_cipher_iv_length($cipher_algo);
			$raw = self::FORMAT_B64 ? base64_decode($str) : pack('H*', $str);
			if(strlen($raw) > $iv_num_bytes) {
				$iv = substr($raw, 0, $iv_num_bytes);
				$raw = substr($raw, $iv_num_bytes);
				$hash_key = openssl_digest(self::_key(), 'sha256', true);
				return openssl_decrypt(
					$raw,
					$cipher_algo,
					$hash_key, # key for encryption
					OPENSSL_RAW_DATA,
					$iv
				);
			}

			return false;
		}


		# No encryption
		return $str;
	}

}
