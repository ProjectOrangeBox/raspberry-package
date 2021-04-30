<?php

namespace projectorangebox\auth;

use projectorangebox\model\DatabaseModel;

/* needs validation */

class Roles extends DatabaseModel
{
	protected $tablename = 'acl_roles';
	protected $joinTableName = 'acl_role_resource';
	protected $config = [];

	public function __construct(array $config)
	{
		$this->config = $config;

		$this->tablename = $config['auth']['role table'];

		$this->userService = $config['userService'];

		parent::__construct($config);
	}

	public function create(string $name, string $description): int
	{
		return $this->_create(['name' => $name, 'description' => $description]);
	}

	public function update(int $id, array $columns): bool
	{
		return $this->_update($id, $columns);
	}

	public function read(int $id): array
	{
		return $this->_read($id);
	}

	public function readByName(string $name): array
	{
		return $this->_readBy(['name' => $name]);
	}

	public function delete(int $id): bool
	{
		return $this->_delete($id);
	}

	public function deleteByName(string $name): bool
	{
		return $this->_deleteBy(['name' => $name]);
	}

	public function list(): bool
	{
		return $this->_list();
	}

	/* manage resources */

	public function addResourceToRole(int $roleId, int $resourceId): bool
	{
		$this->db->insert($this->joinTableName, ['role_id' => $roleId, 'resource_id' => $resourceId]);

		return $this->db->id();
	}

	public function removeResourceFromRole(int $roleId, int $resourceId): bool
	{
		return ($this->db->delete($this->joinTableName, ['role_id' => $roleId, 'resource_id' => $resourceId])->rowCount() > 0);
	}

	public function replaceRoleResources(int $roleId, array $resourceIds): bool
	{
		$this->db->action(function ($db) use ($roleId, $resourceIds) {
			/* delete all the current */
			$db->delete($this->joinTableName, ['role_id' => $roleId]);

			foreach ($resourceIds as $resourceId) {
				$db->insert($this->joinTableName, ['role_id' => $roleId, 'resource_id' => $resourceId]);
			}

			return true;
		});

		return true;
	}
} /* end class */
