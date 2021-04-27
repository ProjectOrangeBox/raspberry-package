<?php

namespace projectorangebox\encryption;

use FS;
use projectorangebox\encryption\EncryptionException as Exception;

class Encryption implements EncryptionInterface
{
	protected $config = [];

	/**
	 * Method __construct
	 *
	 * @param array $config
	 *
	 * @return void
	 */
	public function __construct(array $config)
	{
		/* merge the passed into array over the default configuration */
		$this->config = array_replace(require __DIR__ . '/DefaultConfig.php', $config);
	}

	/**
	 * Method encrypt
	 *
	 * @param string $data string to encrypt
	 * @param string $keyFile optional public key location
	 *
	 * @return string
	 */
	public function encrypt(string $data, string $keyFile = null): string
	{
		$keyFile = $keyFile ?? ($this->config['public key file'] ?? '/support/keys/public.key');

		/* get absolute path */
		$keyFile = FS::resolve($keyFile);

		if (!file_exists($keyFile)) {
			throw new Exception('Could not locate public key file');
		}

		if (!$keyResource = openssl_pkey_get_public($keyFile)) {
			throw new Exception('Could not get public key');
		}

		$details = openssl_pkey_get_details($keyResource);

		$length = ceil($details['bits'] / 8) - 11;

		$output = '';

		while ($data) {
			$chunk = substr($data, 0, $length);
			$data = substr($data, $length);
			$encrypted = '';

			if (!openssl_public_encrypt($chunk, $encrypted, $keyResource)) {
				throw new Exception('Failed to encrypt data');
			}

			$output .= $encrypted;
		}

		openssl_free_key($keyResource);

		/* convert it to something human workable */
		return $this->base62_encode($output);
	}

	/**
	 * Method decrypt
	 *
	 * @param string $data string to decrypt
	 * @param string $keyFile optional private file location
	 *
	 * @return string
	 */
	public function decrypt(string $data, string $keyFile = null): string
	{
		/* convert it from something human workable */
		$data = $this->base62_decode($data);

		$keyFile = $keyFile ?? ($this->config['private key file'] ?? '/support/keys/private.key');

		/* get absolute path */
		$keyFile = FS::resolve($keyFile);

		if (!file_exists($keyFile)) {
			throw new Exception('Count not locate private key file');
		}

		if (!$keyResource = openssl_pkey_get_private($keyFile)) {
			throw new Exception('Could not get private key');
		}

		$details = openssl_pkey_get_details($keyResource);

		$length = ceil($details['bits'] / 8);

		$output = '';

		while ($data) {
			$chunk = substr($data, 0, $length);
			$data = substr($data, $length);
			$decrypted = '';

			if (!openssl_private_decrypt($chunk, $decrypted, $keyResource)) {
				throw new Exception('Failed to decrypt data');
			}

			$output .= $decrypted;
		}

		openssl_free_key($keyResource);

		return $output;
	}

	/**
	 * Method create
	 *
	 * @param array $userConfig optional ssl configuration
	 *
	 * @return array
	 */
	public function create(array $userConfig = []): array
	{
		$defaultConfig = [
			"digest_alg" => "sha512",
			'private_key_bits' => 4096,
			'private_key_type' => OPENSSL_KEYTYPE_RSA,
		];

		$configargs = $userConfig ?? ($this->config['config'] ?? $defaultConfig);

		$resource = openssl_pkey_new($configargs);

		$privateKey = '';
		openssl_pkey_export($resource, $privateKey);

		$publicKey = openssl_pkey_get_details($resource);
		$publicKey = $publicKey['key'];

		openssl_free_key($resource);

		return ['public' => $publicKey, 'private' => $privateKey];
	}

	/**
	 * Method base62_encode
	 *
	 * @param string $data string to encode to base62
	 *
	 * @return string
	 */
	public function base62_encode(string $data): string
	{
		$outstring = '';
		$len = strlen($data);

		for ($i = 0; $i < $len; $i += 8) {
			$chunk = substr($data, $i, 8);
			$outlen = ceil((strlen($chunk) * 8) / 6);
			$x = bin2hex($chunk);
			$number = ltrim($x, '0');
			if ($number === '') {
				$number = '0';
			}
			$w = gmp_strval(gmp_init($number, 16), 62);
			$pad = str_pad($w, $outlen, '0', STR_PAD_LEFT);
			$outstring .= $pad;
		}

		return $outstring;
	}

	/**
	 * Method base62_decode
	 *
	 * @param string $data string to convert from base62
	 *
	 * @return string
	 */
	public function base62_decode(string $data): string
	{
		$outstring = '';
		$len = strlen($data);

		for ($i = 0; $i < $len; $i += 11) {
			$chunk = substr($data, $i, 11);
			$outlen = floor((strlen($chunk) * 6) / 8);
			$number = ltrim($chunk, '0');
			if ($number === '') {
				$number = '0';
			}
			$y = gmp_strval(gmp_init($number, 62), 16);
			$pad = str_pad($y, $outlen * 2, '0', STR_PAD_LEFT);
			$outstring .= pack('H*', $pad);
		}

		return $outstring;
	}
} /* end class */
