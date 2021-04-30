<?php

namespace projectorangebox\model;

use Medoo\Medoo;
use projectorangebox\validate\ValidateInterface;

abstract class DatabaseModel extends Model
{
	protected $config = [];
	protected $db;
	protected $validate;
	protected $tablename = '';
	protected $primaryId = null;
	protected $primaryColumn = null;
	protected $hasValidate = false;
	protected $is_active = false;
	protected $listColumns = '*';

	/**
	 * Undocumented function
	 *
	 * @param array $config
	 */
	public function __construct(array $config)
	{
		/* merge the passed into array over the default configuration */
		$this->config = buildConfig($config, [], __DIR__ . '/DefaultConfig.php');

		mustBe($this->config['db'], Medoo::class);

		$this->db = $this->config['db'];

		if (isset($this->config['validateService'])) {
			mustBe($this->config['validateService'], ValidateInterface::class);

			$this->validate = $this->config['validateService'];

			$this->hasValidate = true;
		}
	}

	/**
	 * Undocumented function
	 *
	 * @param string $acceptable
	 *
	 * @return void
	 */
	public function dieOnError(string $acceptable = '')
	{
		$error = $this->db->error();

		if (!in_array($error[0], explode(',', $acceptable . ',00000'))) {
			echo 'Database Error: ' . \implode(' ', $error) . PHP_EOL;

			$DBT = \debug_backtrace()[0];

			unset($DBT['object']);
			unset($DBT['type']);
			unset($DBT['args']);

			foreach ($DBT as $key => $value) {
				echo '  ' . $key . ': ' . $value . EOL;
			}

			exit(1);
		}
	}

	/**
	 * Undocumented function
	 *
	 * @param string $acceptable
	 *
	 * @return void
	 */
	public function throwOnError(string $acceptable = '')
	{
		$error = $this->db->error();

		if (!in_array($error[0], explode(',', $acceptable . ',00000'))) {
			throw new \Exception('Database Error: ' . \implode(' ', $error));
		}
	}

	/**
	 * Undocumented function
	 *
	 * @return bool
	 */
	public function hasError(): bool
	{
		$error = $this->db->error();

		return ($error[0] == '00000');
	}

	/* crud */

	protected function _create(array $columns): int
	{
		$this->db->insert($this->tablename, $columns);

		return $this->db->id();
	}

	protected function _update(int $id, array $columns): bool
	{
		return $this->_updateBy([$this->primaryColumn => $id], $columns);
	}

	protected function _updateBy(array $columnKey, array $columns): bool
	{
		return ($this->db->update($this->tablename, $columns, $columnKey)->rowCount() > 0);
	}

	protected function _read(int $id): array
	{
		return $this->_readBy([$this->primaryColumn => $id]);
	}

	protected function _readBy(array $columnKey): array
	{
		return $this->db->get($this->tablename, '*', $columnKey);
	}

	protected function _has(string $columnName, string $columnValue = null): bool
	{
		if ($columnValue === null) {
			$columnValue = $columnName;
			$columnName = $this->primaryColumn;
		}

		return $this->db->has($this->tablename, [$columnName => $columnValue]);
	}

	protected function _delete(int $id): bool
	{
		return $this->_deleteBy([$this->primaryColumn => $id]);
	}

	protected function _deleteBy(array $columnKey): bool
	{
		return ($this->db->delete($this->tablename, $columnKey)->rowCount() > 0);
	}

	protected function _replace(int $id, array $columns): bool
	{
		return $this->_replaceBy([$this->primaryColumn => $id], $columns);
	}

	protected function _replaceBy(array $columnKey, array $columns): bool
	{
		return ($this->db->replace($this->tablename, $columns, $columnKey)->rowCount() > 0);
	}

	protected function _list(): bool
	{
		$where = ($this->is_active) ? ['is_active' => 1] : null;

		return $this->db->select($this->tablename, $this->listColumns, $where);
	}
} /* end class */
