<?php

namespace projectorangebox\config;

use PDO;
use PDOException;
use projectorangebox\config\ConfigFile;
use projectorangebox\config\ConfigInterface;
use projectorangebox\config\exceptions\MissingConfig;

/*
CREATE TABLE `config` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `group` varchar(32) NOT NULL,
  `key` varchar(255) NOT NULL,
  `value` text NOT NULL,
  `enabled` tinyint(1) unsigned DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
*/

class ConfigFileDatabase extends ConfigFile implements ConfigInterface
{
	protected $pdo = null;
	protected $isLoaded = [];
	protected $pdoOptions = [
		PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
		PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
		PDO::ATTR_EMULATE_PREPARES   => false,
	];

	public function _get(string $notation,/* mixed */ $default = null) /* mixed */
	{
		/* Are they trying to attach a database? */
		if (isset($this->config['database'])) {
			/* try to connect to the database */
			$this->connectDB($this->config['database']);

			/* Have we loaded this database config group yet? */
			list($fileGroup, $key) = explode('.', $notation, 2);

			/* parent is file based - load file based */
			parent::loadFile($fileGroup);

			/* than try to load the entire database group */
			$this->getGroup($fileGroup, $this->config['database']['tablename']);
		}

		/* parent is file based */
		return parent::get($notation, $default);
	}

	protected function connectDB(array $config)
	{
		/* is PDO setup? */
		if (!$this->pdo) {
			/* check for required to make the connection */
			if (is_array($missing = array_keys_exists(['dsn', 'user', 'password', 'tablename'], $config))) {
				throw new MissingConfig('array key(s) "' . implode('","', $missing) . '" missing.');
			}

			/* DSN mysql:host=localhost;dbname=example;port=3306 */
			try {
				$this->pdo = new PDO($config['dsn'], $config['user'], $config['password'], $this->pdoOptions);
			} catch (PDOException $exception) {
				throw new PDOException($exception->getMessage(), (int)$exception->getCode());
			}
		}
	}

	protected function getGroup(string $group, string $tablename)
	{
		/* did we already load this from the database? */
		if (!isset($this->isLoaded[$group])) {
			/* run the query */
			$statement = $this->pdo->prepare("SELECT * FROM `" . $tablename . "` where `group`=:group and `enabled` = 1");

			$statement->execute(['group' => $group]);

			while ($record = $statement->fetch()) {
				$this->set($record['group'] . '.' . $record['key'], convert_to_real($record['value']));
			}

			/* mark as already loaded */
			$this->isLoaded[$group] = true;
		}
	}
} /* end class */
