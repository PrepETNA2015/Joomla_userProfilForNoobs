<?php

// JPATH_BASE/plugins/user/profile

//defined('JPATH_BASE') or die;

class UserProfileConf
{
	private $file;

	public function addField($type) {

		// ADD FIELDS in xmls files
		$files = array('fields.xml', 'profiles/profile.xml', 'profile.xml');

		// init DomDocument with indentation
		$dom = new DOMDocument;
		$dom->preserveWhiteSpace = false;
		$dom->formatOutput = true;

		// loop through xmls files
		foreach ($files as $key => $file) {

			$this->file = $file;

			// load current file fields
			$dom->load($file);
			$xpath = new DOMXpath($dom);
			$root = $dom->documentElement;
			$fields = $root->getElementsByTagName('field');

			// init some logic vars
			$count = $this->countFieldsOfThisType($type, $fields);
			$mustInsert = false;
			$multiCloning = false;
			$i = 0;

			// loop through all fields and add field if found
			foreach ($fields as $field) {

				// insertBefore part
				if ($mustInsert == true) {
					$path = $field->getNodePath();
					$node = $xpath->query($path)->item(0);
					foreach ($clones as $clone) {
						$node->parentNode->insertBefore($clone, $node);
					}
					$i = 0;
					$mustInsert = false;
				}

				// comparison & cloning part
				$fieldType = $this->getFieldType($field);
				if ($fieldType == $type || $multiCloning == true) {
					$fieldNum = $this->getFieldNum($field);
					if ($fieldNum == $count) {
						if ($field->getAttribute('class') == 'address') {
							$clones[$i] = $this->cloneField($field, $fieldNum, $fieldType);
							$i++;
							$multiCloning = true;
							if ($i > 4) {
								$mustInsert = true;
								$multiCloning = false;
							}
						}
						else {
							$clones[$i] = $this->cloneField($field, $fieldNum, $fieldType);
							$mustInsert = true;
						}
					}
				}
			}

			// save the xml file
			$dom->saveXML();
			$dom->save('fields.xml');
		}

		// get type to the right format
		$type = strtoupper($type);
		if ($type == 'WEBSITE') {
			$type = 'WEB_SITE';
		}
		else if ($type == 'FAVORITEBOOK') {
			$type = 'FAVORITE_BOOK';
		}
		else if ($type == 'ABOUTME') {
			$type = 'ABOUT_ME';
		}

		// $fd = fopend(JPATH_BASE."/administrator/language/en-GB/en-GB.plg_user_profile.ini", "r+");
		$fd = fopen('C:\\Program Files (x86)\\EasyPHP-DevServer-14.1VC11\\data\\localweb\\projects\\administrator\\language\\en-GB\\en-GB.plg_user_profile.ini', 'r+');
		if ($fd) {
			// load the file
			$rowList = new SplDoublyLinkedList();
			while (($row = fgets($fd)) !== false) {
				$rowList->push($row);
		    }

			$rowList = $this->addInLang($type, $rowList);

			rewind($fd);
			foreach ($rowList as $row) {
				if (fwrite($fd, $row) === false) {
					echo "fok";
				}
			}

		    if (!feof($fd)) {
		        echo "Erreur: fgets() failed\n";
    		}
			fclose($fd);
		}
	}

	private function addInLang($type, $rowList) {

		    // count fields of this type
		    $count = 0;
		    foreach ($rowList as $row) {
				if (($rowName = strstr($row, $type)) !== false) {
			    	$count++;
			    }
			}
			$count /= 2;

			// search & add
		    foreach ($rowList as $key => $row) {
				if (($rowName = strstr($row, $type)) !== false) {
			    	$numOffset = $this->numOffset($rowName);
			    	$rowNum = intval(substr($rowName, $numOffset));
			    	if ($rowNum == $count) {
			    		$cloneBase = substr($row, 0, 23).substr($rowName, 0, $numOffset);
			    		$cloneNum = $rowNum + 1;
			    		$cloneValue = substr($row, $this->numOffset($row) + 1);
			    		$cloneValue = substr($cloneValue, 0, $this->numOffset($cloneValue)).$cloneNum;
			    		$clone = $cloneBase.$cloneNum.$cloneValue."\"\n";
			    		$rowList->add($key + 2, $clone);
			    	}
			    }
			}

			if ($type == 'ADDRESS') {
				$rowList = $this->addInLang('CITY', $rowList);
				$rowList = $this->addInLang('REGION', $rowList);
				$rowList = $this->addInLang('COUNTRY', $rowList);
				$rowList = $this->addInLang('POSTAL_CODE', $rowList);
			}

			return ($rowList);
	}

	private function parseFieldName($field) {
		if ($this->file == 'fields.xml') {
			$name['name'] = $field->textContent;
			$name['numOffset'] = $this->numOffset($field->textContent);
		}
		else if ($this->file == 'profiles/profile.xml') {
			$name['name'] = $field->getAttribute('name');
			$name['numOffset'] = $this->numOffset($field->getAttribute('name'));
		}
		else if ($this->file == 'profile.xml') {
			$name['name'] = substr(strstr($field->getAttribute('name'), '_'), 1);
			$name['numOffset'] = $this->numOffset($name['name']);
		}
		else {
			$name['name'] = '';
			$name['numOffset'] = 0;
		}
		return ($name);
	}

	private function getFieldNum($field) {
		$name = $this->parseFieldName($field);
		return  (substr($name['name'], $name['numOffset']));
	}

	private function getFieldType($field) {
		$name = $this->parseFieldName($field);
		return  (substr($name['name'], 0, $name['numOffset']));
	}

	private function cloneField($field, $fieldNum, $fieldType) {
		$clone = $field->cloneNode(true);
		$num = $fieldNum + 1;

		if ($this->file == 'fields.xml') {
			$value = substr($clone->textContent, 0, $this->numOffset($clone->textContent)).$num;
			$clone->nodeValue = $value;
		}
		else if ($this->file == 'profile.xml' || $this->file == 'profiles/profile.xml') {
			$name = substr($field->getAttribute('name'), 0, $this->numOffset($field->getAttribute('name'))).$num;
			$clone->setAttribute('name', $name);
			$desc = substr($field->getAttribute('description'), 0, 23).strtoupper($fieldType).$num.substr($field->getAttribute('description'), -5);
			$clone->setAttribute('description', $desc);
			$label = substr($field->getAttribute('label'), 0, 23).strtoupper($fieldType).$num.substr($field->getAttribute('label'), -6);
			$clone->setAttribute('label', $label);
			if ($this->file == 'profiles/profile.xml') {
				$id = substr($field->getAttribute('id'), 0, $this->numOffset($field->getAttribute('id'))).$num;
				$clone->setAttribute('id', $id);
			}
		}

		return ($clone);
	}

	private function countFieldsOfThisType($type, $fields) {
		$count = 0;
		$scanned = false;
		foreach ($fields as $field) {
			// handle the profile.xml case where fields are listed twice
			if ($field->getAttribute('type') == 'spacer') {
				if ($scanned == true) {
					break;
				}
				$scanned = true;
			}
			$fieldType = $this->getFieldType($field);
			if ($fieldType == $type) {
			 	$count++;
			}
		}
		return ($count);
	}

	private function numOffset($str) {
		for ($i = 0; $i < strlen($str); $i++) {
			if (ctype_digit($str[$i])) {
				return ($i);
			}
		}
	}
}

$bar = new UserProfileConf;

$bar->addField('address');









?>