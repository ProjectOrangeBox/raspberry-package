<?php

return [
  'public key file' => '/support/keys/public.key',
  'private key file' => '/support/keys/private.key',

  'config' => [
    "digest_alg" => "sha512",
    'private_key_bits' => 4096,
    'private_key_type' => OPENSSL_KEYTYPE_RSA,
  ],
];
