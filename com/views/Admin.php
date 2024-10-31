<?php

global $sgpt;
$sgpt->includeView('View');

class SGPT_AdminView extends SGPT_View
{
	public function configureLayouts($mainLayout)
	{
		return array($mainLayout);
	}

	public static function render($layout, $params=array())
	{
		$view = new self();
		$view->prepareView($layout, $params);
		$view->output();
	}
}
