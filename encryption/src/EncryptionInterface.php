<?php

namespace projectorangebox\encryption;

interface EncryptionInterface
{
  public function __construct(array $config);

  public function encrypt(string $data, string $keyFile = null): string;
  public function decrypt(string $data, string $keyFile = null): string;

  public function create(array $userConfig = []): array;

  public function base62_encode(string $data): string;
  public function base62_decode(string $data): string;
}
