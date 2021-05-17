<?php

namespace projectorangebox\auth;

use projectorangebox\model\DatabaseModel;

/* needs validation */

class Resources extends DatabaseModel
{
	protected $tablename = 'acl_roles';
	protected $config = [];

	public function __construct(array $config)
	{
		$this->config = $config;

		$this->tablename = $config['auth']['resource table'];

		$this->userService = $config['userService'];

		parent::__construct($config);
	}

	public function create(string $key, string $description, string $group): int
	{
		return $this->_create(['key' => $key, 'description' => $description, 'group' => $group]);
	}

	public function update(int $id, array $columns): bool
	{
		return $this->_update($id, $columns);
	}

	public function updateByKey(string $key, array $columns): bool
	{
		return $this->_updateBy(['key' => $key], $columns);
	}

	public function read(int $id): array
	{
		return $this->_read($id);
	}

	public function readByKey(string $key): array
	{
		return $this->_readBy(['key' => $key]);
	}

	public function delete(int $id): bool
	{
		return $this->_delete($id);
	}

	public function deleteByGroup(string $group): int
	{
		return $this->_deleteBy(['group' => $group]);
	}

	public function list(): array
	{
		return $this->_list();
	}

	public function listByGroup(string $group): array
	{
		return $this->db->select($this->tablename, $this->listColumns, ['group' => $group]);
	}
} /* end class */
