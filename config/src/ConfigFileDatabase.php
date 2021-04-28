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
	protected $databaseGroupLoaded = [];
	protected $pdoOptions = [
		PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
		PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
		PDO::ATTR_EMULATE_PREPARES   => false,
	];

	public function get(string $notation,/* mixed */ $default = null) /* mixed */
	{
		/* Have we loaded this database config group yet? */
		list($filenameGroup) = explode('.', strtolower($notation), 1);

		/* load the file first */
		parent::loadFile($filenameGroup);

		/* Are they trying to attach a database? */
		if (isset($this->config['config']['database'])) {
			/* than try to load the database group over it */
			$this->getGroup($this->config['config']['database'], $filenameGroup);
		}

		/* Then get it via array */
		return parent::get($notation, $default);
	}

	protected function connectDB(array $config): PDO
	{
		/* Did we connect to the database yet? */
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

		return $this->pdo;
	}

	protected function getGroup(array $config, string $group)
	{
		/* did we already load this from the database? */
		if (!isset($this->databaseGroupLoaded[$group])) {
			/* try to connect to the database */
			$statement = $this->connectDB($config)->prepare("SELECT key,value FROM `" . $this->config['config']['database']['tablename'] . "` where `group`=:group and `enabled` = 1");

			/* run the query */
			$statement->execute(['group' => $group]);

			while ($record = $statement->fetch()) {
				$this->set($group . '.' . $record['key'], convert_to_real($record['value']));
			}

			/* mark as already loaded */
			$this->databaseGroupLoaded[$group] = true;
		}
	}
} /* end class */
