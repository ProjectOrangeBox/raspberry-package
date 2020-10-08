<?php

namespace application\controllers;

use projectorangebox\dispatcher\Controller;

class handlebars extends Controller
{
	public function index(): string
	{
		return $this->handlebars->render('welcome', $this->getData());
	}

	protected function getData()
	{
		return array(
			'page_title' => 'Current Projects',
			'uppercase' => 'lowercase words',
			'projects' => array(
				array(
					'name' => 'Acme Site',
					'assignees' => array(
						array('name' => 'Dan', 'age' => 21),
						array('name' => 'Phil', 'age' => 12),
						array('name' => 'Don', 'age' => 34),
						array('name' => 'Pete', 'age' => 18),
					),
				),
				array(
					'name' => 'Lex',
					'contributors' => array(
						array('name' => 'Dan', 'age' => 18),
						array('name' => 'Ziggy', 'age' => 16),
						array('name' => 'Jerel', 'age' => 7)
					),
				),
			),
		);
	}
} /* end class */
