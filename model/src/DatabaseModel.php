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

	public function __construct(array $config)
	{
		/* merge the passed into array over the default configuration */
		$this->config = array_replace(require __DIR__ . '/config.php', $config);

		mustBe($this->config['db'], Medoo::class);

		$this->db = $this->config['db'];

		if (isset($this->config['validateService'])) {
			mustBe($this->config['validateService'], ValidateInterface::class);

			$this->validate = $this->config['validateService'];
		}
	}

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

	public function throwOnError(string $acceptable = '')
	{
		$error = $this->db->error();

		if (!in_array($error[0], explode(',', $acceptable . ',00000'))) {
			throw new \Exception('Database Error: ' . \implode(' ', $error));
		}
	}

	public function hasError(): bool
	{
		$error = $this->db->error();

		return ($error[0] == '00000');
	}
} /* end class */
