<?php

namespace projectorangebox\validate;

use projectorangebox\request\RequestInterface;
use projectorangebox\validate\ValidateInterface;

class Request
{
	protected $config = [];

	/**
	 * $input
	 *
	 * @var undefined
	 */
	protected $requestService;

	/**
	 * $validate
	 *
	 * @var undefined
	 */
	protected $validateService;

	/**
	 * __construct
	 *
	 * @param mixed $validateService
	 * @return void
	 */
	public function __construct(array $config)
	{
		$this->config = $config;

		$this->validateService = $this->config['validateService'];
		$this->requestService = $this->config['requestService'];

		mustBe($this->validateService, ValidateInterface::class);
		mustBe($this->requestService, RequestInterface::class);
	}

	public function errors(bool $formatted = false): array
	{
		return $this->validateService->errors($formatted);
	}

	public function has(): bool
	{
		return (bool)(count($this->validateService->errors()));
	}

	/**
	 * is_valid
	 *
	 * @param array $keysRules
	 * @return void
	 */
	public function isValid(array $rules): bool
	{
		$fields = $this->requestService->request();

		return $this->validateService->setRules($rules)->setData($fields)->run()->success();
	}

	/**
	 * filter
	 *
	 * @param array $keysRules
	 * @return void
	 */
	public function filter(array $rules): array
	{
		$fields = $this->requestService->request();

		$this->validateService->setRules($rules)->setData($fields)->run();

		$this->requestService->set('request', $fields);

		return $fields;
	}
} /* end class */
