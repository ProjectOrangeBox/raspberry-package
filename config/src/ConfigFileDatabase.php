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

  public function get(string $notation,/* mixed */ $default = null) /* mixed */
  {
    /* Are they trying to attach a database? */
    if (isset($this->config['config']['database'])) {
      /* try to connect to the database */
      $this->connectDB();

      /* Have we loaded this database config group yet? */
      $notationParts = explode('.', $notation, 2);

      /* load file based */
      parent::loadFile($notationParts[0]);

      /* than try to load the entire database group */
      $this->getGroup($notationParts[0]);
    }

    return parent::get($notation, $default);
  }

  protected function connectDB()
  {
    /* is PDO setup? */
    if (!$this->pdo) {
      /* check for required to make the connection */
      foreach (['dsn', 'user', 'password', 'tablename'] as $required) {
        if (!isset($this->config['config']['database'][$required])) {
          throw new MissingConfig($required);
        }
      }

      /* mysql:host=localhost;dbname=example;port=3306 */
      try {
        $this->pdo = new PDO($this->config['config']['database']['dsn'], $this->config['config']['database']['user'], $this->config['config']['database']['password'], $this->pdoOptions);
      } catch (PDOException $exception) {
        throw new PDOException($exception->getMessage(), (int)$exception->getCode());
      }
    }
  }

  protected function getGroup(string $group)
  {
    /* did we already load this from the database? */
    if (!isset($this->isLoaded[$group])) {
      $this->isLoaded[$group] = true;
      /* run the query */
      $statement = $this->pdo->prepare("SELECT * FROM `" . $this->config['config']['database']['tablename'] . "` where `group`=:group and `enabled` = 1");

      $statement->execute(['group' => $group]);

      while ($record = $statement->fetch()) {
        $this->set($record['group'] . '.' . $record['key'], convert_to_real($record['value']));
      }
    }
  }
} /* end class */
