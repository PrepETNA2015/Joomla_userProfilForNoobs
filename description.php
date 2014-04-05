<?php
/*
*PATH: libraries/joomla/form/fields
*/
/**
 * @package     Joomla.Platform
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;


class JFormFieldDescription extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  11.1
	 */
	protected $type = 'Description';

	/**
	 * Method to get the field label markup.
	 *
	 * @return  string  The field label markup.
	 *
	 * @since   11.1
	 */
protected function getInput()
	{
	
		// Initialize some field attributes	
		$class = !empty($this->class) ? ' class="' . $this->class . '"' : '';
		$text = "(Those buttons will duplicate or delete <br /> adress,city,region,country,zip code,phone fields <br /> every time you click)";
		// Including fallback code for HTML5 non supported browsers.
		JHtml::_('jquery.framework');

		return '<p id="' . $this->id .'"' . $class . '>' . $text . '</p>';
	}
	
}
