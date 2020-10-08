<?php

namespace projectorangebox\validate;

use Closure;
use Exception;
use projectorangebox\log\LoggerTrait;

class Validate implements ValidateInterface
{
	use LoggerTrait;

	/**
	 * Storage the current validations error string usually in sprintf format
	 *
	 * @var string
	 */
	protected $errorString = '';

	/**
	 * Storage for the Human readable version of the field name can be used in the error string as sprintf parameter 1 ie. Last_name Last Name
	 *
	 * @var string
	 */
	protected $errorHuman = '';

	/**
	 * Storage for the Error options which can be used in the error string as sprintf parameter 2 ie. [1,34,67] becomes 1, 34, 67
	 *
	 * @var string
	 */
	protected $errorParams = '';

	/**
	 * Storage for the field value which can be used in the error string as sprintf parameter 3
	 *
	 * @var string
	 */
	protected $errorFieldValue = '';

	/**
	 * Storage for the current field data being validated
	 *
	 * @var array
	 */
	protected $fieldData = [];

	/**
	 * Local reference of validate configuration
	 *
	 * @var array
	 */
	protected $config = [];

	/**
	 * Local reference of Orange Error Object
	 *
	 */
	protected $errors = [];

	/**
	 * $rules
	 *
	 * @var array
	 */
	protected $rules = [];

	/**
	 * $errorsFormatterClosure
	 *
	 * @var null
	 */
	protected $errorsFormatterClosure = null;

	/**
	 *
	 * Constructor
	 *
	 * @access public
	 *
	 * @param array $config []
	 *
	 */
	public function __construct(array $config)
	{
		$this->log('info', 'Validate Class Initialized');

		/* merge the passed into array over the default configuration */
		$this->config = array_replace(require __DIR__ . '/config.php', $config);

		/* use the default formatter or the one supplied */
		$this->errorsFormatterClosure = $config['errors formatter'] ?? function ($errors) {
			return [
				'success' => !(bool)count($errors),
				'count' => count($errors),
				'keys' => \array_keys($errors),
				'errors' => $errors,
				'timestamp' => date('c'),
			];
		};
	}

	/**
	 *
	 * Attach a validation rule as a Closure
	 *
	 * @access public
	 *
	 * @param string $name
	 * @param closure $closure
	 *
	 * @return Validate
	 *
	 * #### Example
	 * ```php
	 * ci('validate')->attach('filter_lower',function(&$field, $options) { return strtolower($field); });
	 * ci('validate')->attach('return_true',function(&$field, $options) { return true; });
	 * ```
	 */
	public function add(string $name, string $nameSpace): ValidateInterface
	{
		$type = (substr($name, 0, 7) == 'filter_') ? 'filter' : 'rules';

		$this->config[$type][$name] = $nameSpace;

		return $this;
	}

	/**
	 * filter - one time filter
	 *
	 * Process & Return
	 *
	 * @param mixed $input
	 * @param mixed $rules
	 * @return void
	 */
	public function filter(&$data, $rules) /* mixed */
	{
		$this->reset();

		if (!is_array($data)) {
			$data = [0 => $data];
			$internal = true;
		} else {
			$internal = false;
		}

		if (!is_array($rules)) {
			$rules = [0 => $rules];
		}

		$this->setData($data)->setRules($rules)->run();

		return ($internal) ? $data[0] : $data;
	}

	/**
	 * variable - one time validation
	 *
	 * Process & Return
	 *
	 * @param mixed $input
	 * @param mixed $rules
	 * @return void
	 */
	public function isValid(&$data, $rules): bool
	{
		$this->reset();

		if (!is_array($data)) {
			$data = [0 => $data];
		}

		if (!is_array($rules)) {
			$rules = [0 => $rules];
		}

		return $this->setData($data)->setRules($rules)->run()->success();
	}

	/**
	 * Method setData
	 *
	 * @param array $fields [explicite description]
	 *
	 * @return ValidateInterface
	 */
	public function setData(array &$fields): ValidateInterface
	{
		$this->fieldData = &$fields;

		return $this;
	}

	/**
	 * Method setRules
	 *
	 * @param array $rules [explicite description]
	 * @param string $key [explicite description]
	 *
	 * @return ValidateInterface
	 */
	public function setRules(array $rules, string $key = '0'): ValidateInterface
	{
		foreach ($rules as $k => $v) {
			$rulesToUse = $v['rules'] ?? $v;

			$humanToUse = $v['label'] ?? $k;
			$humanToUse = $v['human'] ?? $humanToUse;

			$fieldToUse = $v['field'] ?? $k;

			$this->rules[$key][$fieldToUse] = ['rule' => $rulesToUse, 'human' => $humanToUse, 'field' => $fieldToUse];
		}

		return $this;
	}

	/**
	 * success
	 *
	 * @return void
	 */
	public function success(): bool
	{
		return count($this->errors) == 0;
	}

	/**
	 * Method reset
	 *
	 * @return ValidateInterface
	 */
	public function reset(): ValidateInterface
	{
		$this->errors = [];
		$this->rules = [];

		return $this;
	}

	/**
	 * Method errors
	 *
	 * @param bool $formatted [explicite description]
	 *
	 * @return array
	 */
	public function errors(bool $formatted = false): array
	{
		return ($formatted) ? ($this->errorsFormatterClosure)($this->errors) : $this->errors;
	}

	/**
	 * Method errorsFormatter
	 *
	 * @param Closure $closure [explicite description]
	 *
	 * @return ValidateInterface
	 */
	public function errorsFormatter(Closure $closure): ValidateInterface
	{
		$this->errorsFormatterClosure = $closure;

		return $this;
	}

	/**
	 * run
	 *
	 * @param mixed $rules
	 * @param mixed &$fields
	 * @param mixed string
	 * @return void
	 */
	public function run(string $namedGroup = '0'): ValidateInterface
	{
		if (!isset($this->rules[$namedGroup])) {
			throw new Exception('Validate rule group "' . $namedGroup . '" was not found.');
		}

		/* process each field and rule as a single rule, field, and human label */
		foreach ($this->rules[$namedGroup] as $rule) {
			$this->single($rule['field'], $rule['rule'], $rule['human']);
		}

		return $this;
	}

	/**
	 *
	 * Run Validation rules on a single field value
	 *
	 * @access public
	 *
	 * @param $rules
	 * @param &$field
	 * @param string $human null
	 *
	 * @return Validate
	 *
	 */
	protected function single(string $key, string $rules, string $human = null): Validate
	{
		$rules = explode('|', $rules);

		/* do we have any rules? */
		if (count($rules)) {
			/* field value before any validations / filters */
			if (!isset($this->fieldData[$key])) {
				$this->fieldData[$key] = null;
			}

			$this->errorFieldValue =  $this->fieldData[$key];

			foreach ($rules as $rule) {
				if ($this->processRule($key, $rule, $human) === false) {
					break; /* break from for each */
				}
			}
		}

		return $this;
	}

	protected function processRule(string $key, string $rule, string $human): bool
	{
		/* no rule? exit processing of the $rules array */
		if (empty($rule)) {
			$this->log('debug', 'No rule provied to validate against.');

			return false;
		}

		/* do we have this special rule? */
		if ($rule == 'allow_empty' && empty($this->fieldData[$key])) {
			$this->log('debug', 'Allow Empty validation rule skipping the rest because the field is empty.');

			return false;
		}

		$param = '';

		if (preg_match(';(?<rule>.*)\[(?<param>.*)\];', $rule, $matches, PREG_OFFSET_CAPTURE, 0)) {
			$rule = $matches['rule'][0];
			$param = $matches['param'][0];
		}

		$this->makeHumanLookNice($human, $rule);
		$this->makeParamsLookNice($param);

		/* take action on a validation or filter - filters MUST always start with "filter_" */
		return (substr(strtolower($rule), 0, 7) == 'filter_') ? $this->_filter($key, $rule, $param) : $this->_validation($key, $rule, $param);
	}

	protected function makeHumanLookNice($human, $rule)
	{
		/* do we have a human readable field name? if not then try to make one */
		$this->errorHuman = ($human) ? $human : strtolower(str_replace('_', ' ', $rule));
	}

	protected function makeParamsLookNice($param)
	{
		/* try to format the parameters into something human readable incase they need this in there error message  */
		if (is_array($param)) {
			$this->errorParams = implode(', ', $param);
		} elseif (strpos($param, ',') !== false) {
			$this->errorParams = str_replace(',', ', ', $param);
		}

		if (($pos = strrpos($this->errorParams, ', ')) !== false) {
			$this->errorParams = substr_replace($this->errorParams, ' or ', $pos, 2);
		}
	}

	/**
	 *
	 * Run a filter rule.
	 * Filters always start with the filter_ prefix
	 * Filters always return true (success)
	 * if you need to register a error use a validation
	 *
	 * @access protected
	 *
	 * @param &$field
	 * @param string $rule
	 * @param string $param null
	 *
	 * @return bool
	 *
	 */
	protected function _filter(string $key, string $rule, string $param = null): bool
	{
		/* filters start with filter_ */
		$shortRule = substr($rule, 7);

		if ($namedService = $this->find($shortRule, 'filters')) {
			(new $namedService($this->fieldData))->filter($this->fieldData[$key], $param);
		} elseif (function_exists($shortRule)) {
			$this->fieldData[$key] = ($param) ? $shortRule($this->fieldData[$key], $param) : $shortRule($this->fieldData[$key]);
		} else {
			throw new Exception('Could not locate the filter named "' . $rule . '".');
		}

		/* filters don't fail */
		return true;
	}

	/**
	 *
	 * Run a validation rule.
	 * returns true on success and false on error
	 *
	 * @access protected
	 *
	 * @param &$field
	 * @param string $rule
	 * @param string $param null
	 *
	 * @return bool
	 *
	 */
	protected function _validation(string $key, string $rule, string $param = null): bool
	{
		/* rules don't start with anything */
		$shortRule = $rule;

		/* default error */
		$this->errorString = '%s is not valid.';

		if ($namedService = $this->find($shortRule, 'rules')) {
			$success = (new $namedService($this->fieldData, $this->errorString))->validate($this->fieldData[$key], $param);
		} elseif (function_exists($shortRule)) {
			$success = ($param) ? $shortRule($this->fieldData[$key], $param) : $shortRule($this->fieldData[$key]);
		} else {
			throw new Exception('Could not locate the validate rule "' . $rule . '".');
		}

		/* if success is really really false then it's an error */
		if ($success === false) {
			/**
			 * sprintf argument 1 human name for field
			 * sprintf argument 2 human version of options (computer generated)
			 * sprintf argument 3 field value
			 */
			$this->errors[$this->errorHuman] = sprintf($this->errorString, $this->errorHuman, $this->errorParams, $this->errorFieldValue);
		} else {
			/* not a boolean then it's something useable */
			if (!is_bool($success)) {
				$this->fieldData[$key] = $success;

				$success = true;
			}
		}

		return $success;
	}

	protected function find(string $className, string $type) /* mixed */
	{
		return $this->config[$type][$className] ?? false;
	}
} /* end class */
