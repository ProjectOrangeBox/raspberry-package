<?php

namespace projectorangebox\auth;

use Exception;
use projectorangebox\model\DatabaseModel;

/* needs validation */

class UserMeta extends DatabaseModel
{
	protected $userId = 0;
	protected $tablename = 'users_metadata';
	protected $config = [];

	public function __construct(array $config)
	{
		$this->config = $config;

		$this->userId = &$config['userId'];

		$this->tablename = $config['auth']['user metadata'];

		$this->userService = $config['userService'];

		parent::__construct($config);
	}

	public function refresh(int $userId): bool
	{
		$this->userId = $userId;

		return true;
	}

	public function create(array $columns): int
	{
		return $this->_create($columns);
	}

	public function update(array $columns): bool
	{
		return $this->_update($this->userId, $columns);
	}

	public function read(): array
	{
		return $this->_read($this->userId);
	}

	public function delete(): bool
	{
		return $this->_delete($this->userId);
	}
} /* end class */
