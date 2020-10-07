<?php

namespace projectorangebox\pear\pears;

use projectorangebox\pear\PearAbstract;
use projectorangebox\pear\PearInterface;

class PearEnd extends PearAbstract implements PearInterface
{
	public function render()
	{
		$fragments = $this->view->getData('_fragments');

		if (!count($fragments)) {
			throw new \Exception('Cannot end section because you are not in a section.');
		}

		/* Pop the element off the end of array */
		$record = array_pop($fragments);

		list($key, $mode) = $record;

		/* put them back with the last fragment removed */
		$this->view->data('_fragments', $fragments);

		/* Flush the output buffer, return it as a string and turn off output buffering */
		$buffer = ob_get_clean();

		/* get the current content if any */
		$content = $this->view->getData($key);

		/* what to they want to do with the buffer? */
		switch ($mode) {
			case 'append':
				$this->view->data($key, $content . $buffer);
				break;
			case 'prepend':
				$this->view->data($key, $buffer . $content);
				break;
			case 'replace':
				$this->view->data($key, $buffer);
				break;
		}
	}
} /* end plugin */
