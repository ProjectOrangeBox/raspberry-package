<?php

namespace projectorangebox\model;

use Exception;
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
	protected $is_active = false;
	protected $listColumns = '*';
	protected $readColumns = '*';
	protected $rules = [];
	protected $columns = [];

	/**
	 * Undocumented function
	 *
	 * @param array $config
	 */
	public function __construct(array $config)
	{
		/* merge the passed into array over the default configuration */
		$this->config = mergeConfig(__DIR__ . '/DefaultConfig.php', $config);

		mustBe($this->config['db'], Medoo::class);

		$this->db = $this->config['db'];

		if (isset($this->config['validateService'])) {
			mustBe($this->config['validateService'], ValidateInterface::class);

			$this->validate = $this->config['validateService'];
		}
	}

	/**
	 * Undocumented function
	 *
	 * @param string $acceptable
	 *
	 * @return void
	 */
	protected function _dieOnDBError(string $acceptable = '')
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
	protected function _throwOnDBError(string $acceptable = '')
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
	protected function _hasDBError(): bool
	{
		$error = $this->db->error();

		return ($error[0] == '00000');
	}

	/* crud */

	protected function _create(array $columns, string $ruleSetName = 'create'): int
	{
		$this->columns = $columns;

		if (!$this->_isValidate($ruleSetName)) {
			/* we got a error so return 0 (false) and bail */
			return 0;
		}

		$this->db->insert($this->tablename, $this->columns);

		return $this->db->id();
	}

	protected function _update(int $id, array $columns, string $ruleSetName = 'update'): bool
	{
		return $this->_updateBy([$this->primaryColumn => $id], $columns, $ruleSetName);
	}

	protected function _updateBy(array $columnKey, array $columns, string $ruleSetName = 'update'): bool
	{
		$this->columns = $columns;

		if (!$this->_isValidate($ruleSetName)) {
			/* we got a error so return false and bail */
			return false;
		}

		return ($this->db->update($this->tablename, $columns, $columnKey)->rowCount() > 0);
	}

	protected function _read(int $id): array
	{
		return $this->_readBy([$this->primaryColumn => $id]);
	}

	protected function _readBy(array $columnKey): array
	{
		return $this->db->get($this->tablename, $this->readColumns, $columnKey);
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

	protected function _replace(int $id, array $columns, string $ruleSetName = 'update'): bool
	{
		return $this->_replaceBy([$this->primaryColumn => $id], $columns, $ruleSetName);
	}

	protected function _replaceBy(array $columnKey, array $columns, string $ruleSetName = 'update'): bool
	{
		$this->columns = $columns;

		if (!$this->_isValidate($ruleSetName)) {
			/* we got a error so return false and bail */
			return false;
		}

		return ($this->db->replace($this->tablename, $columns, $columnKey)->rowCount() > 0);
	}

	protected function _list(): array
	{
		$where = ($this->is_active) ? ['is_active' => 1] : null;

		return $this->db->select($this->tablename, $this->listColumns, $where);
	}

	protected function _isValidate(string $ruleSetName): bool
	{
		$isValid = true;

		if ($this->validate) {
			if (!isset($this->ruleSets[$ruleSetName])) {
				throw new Exception('Rule set "' . $ruleSetName . '" was not found.');
			}

			$fields = explode(',', $this->ruleSets[$ruleSetName]);

			$ruleSet = [];
			$onlyColumns = [];

			foreach ($fields as $field) {
				if (!isset($this->rules[$field])) {
					throw new Exception('Rule "' . $field . '" not found.');
				}

				$onlyColumns[] = (isset($this->rules[$field]['field'])) ? $this->rules[$field]['field'] : $field;

				$ruleSet[$field] = $this->rules[$field];
			}

			/* remove all columns not in this array and add them if they are missing as null */
			$this->_only($onlyColumns);

			$isValid = $this->validate->isValid($this->columns, $ruleSet);
		}

		return $isValid;
	}

	/* only columns in the rule set are used all others discared any empty are given null as a value */
	protected function _only(array $keys): void
	{
		$only = [];

		foreach ($keys as $key) {
			$only[$key] = (isset($this->columns[$key])) ? $this->columns[$key] : null;
		}

		/* reassign because columns was passed by reference */
		$this->columns = $only;
	}
} /* end class */
