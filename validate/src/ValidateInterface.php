<?php

namespace projectorangebox\validate;

interface ValidateInterface
{
	public function __construct(array $config);
	public function add(string $name, string $nameSpace): ValidateInterface;
	public function filter(&$input, $rules); /* mixed */
	public function isValid(&$input, $rules): bool;
	public function setData(array &$fields): ValidateInterface;
	public function setRules(array $rules, string $key = '0'): ValidateInterface;
	public function run(string $namedGroup = '0'): ValidateInterface;
	public function success(): bool;
	public function reset(): ValidateInterface;
	public function errors(): array;
}
