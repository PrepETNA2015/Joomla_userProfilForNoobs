<?php

class UserProfileConf
{
	public function addField($type) {

		$dom = new DOMDocument;
		$dom->preserveWhiteSpace = false;
		$dom->formatOutput = true;

		// ADD FIELDS in fields.xml
		$dom->load('fields.xml');
		$root = $dom->documentElement;
		$fields = $root->getElementsByTagName('field');

		$count = 0;
		foreach ($fields as $field) {
			$fieldType = substr($field->textContent, 0, $this->numOffset($field->textContent));
			if ($fieldType == $type) {
				$count++;
			}
		}

		$mustInsert = false;
		$multiCloning = false;
		$i = 0;
		foreach ($fields as $field) {
			if ($mustInsert == true) {
				foreach ($clones as $clone) {
					$root->insertBefore($clone, $field);
				}
				break;
			}
			$fieldType = substr($field->textContent, 0, $this->numOffset($field->textContent));
			if ($fieldType == $type || $multiCloning == true) {
				$fieldNum = intval(substr($field->textContent, $this->numOffset($field->textContent)));
				if ($fieldNum == $count) {
					if ($field->getAttribute('class') == 'address') {
						$clones[$i] = $field->cloneNode(true);
						$text = substr($clones[$i]->textContent, 0, $this->numOffset($clones[$i]->textContent));
						$num = intval(substr($clones[$i]->textContent, $this->numOffset($clones[$i]->textContent))) + 1;
						$clones[$i++]->nodeValue = $text.$num;
						$multiCloning = true;
						if ($i > 4) {
							$mustInsert = true;
							$multiCloning = false;
						}
					}
					else {
						$clones[$i] = $field->cloneNode(true);
						$text = substr($clones[$i]->textContent, 0, strlen($type));
						$num = intval(substr($clones[$i]->textContent, strlen($type))) + 1;
						$clones[$i]->nodeValue = $text.$num;
						$mustInsert = true;
					}
				}
			}
		}
		$dom->saveXML();
		$dom->save('fields.xml');

		// ADD FIELDS in profile.xml
		$type = strtoupper($type);
		$dom->load('profile.xml');
		$root = $dom->documentElement;
		$fields = $root->getElementsByTagName('field');
		$xpath = new DOMXpath($dom);

		$count = 0;
		$scanned = false;
		foreach ($fields as $field) {
			if ($field->getAttribute('type') == 'spacer') {
				if ($scanned == true) {
					break;
				}
				$scanned = true;
			}
			$fieldType = substr(substr($field->getAttribute('description'), 23, -5), 0, $this->numOffset(substr($field->getAttribute('description'), 23, -5)));
			if ($fieldType == $type) {
			 	$count++;
			}
		}

		$mustInsert = false;
		$multiCloning = false;
		$i = 0;
		foreach ($fields as $field) {
			if ($mustInsert == true) {
				$path = $field->getNodePath();
				$node = $xpath->query($path)->item(0);
				foreach ($clones as $clone) {
					$node->parentNode->insertBefore($clone, $node);
				}
				$i = 0;
				$mustInsert = false;
			}
			$fieldType = substr(substr($field->getAttribute('description'), 23, -5), 0, $this->numOffset(substr($field->getAttribute('description'), 23, -5)));
			if ($fieldType == $type || $multiCloning == true) {
				$fieldNum = intval(substr(substr($field->getAttribute('description'), 23, -5), $this->numOffset(substr($field->getAttribute('description'), 23, -5))));
				if ($fieldNum == $count) {
					if ($field->getAttribute('class') == 'address') {
						$clones[$i] = $field->cloneNode(true);
						$text = substr($field->getAttribute('description'), 0, 23).$fieldType;
						$num =  $fieldNum + 1 .substr($field->getAttribute('description'), -5);
						$clones[$i++]->setAttribute('description', $text.$num);
						$multiCloning = true;
						if ($i > 4) {
							$mustInsert = true;
							$multiCloning = false;
						}
					}
					else {
						$clones[$i] = $field->cloneNode(true);
						$text = substr($field->getAttribute('description'), 0, 23).$fieldType;
						$num =  $fieldNum + 1 .substr($field->getAttribute('description'), -5);
						$clones[$i++]->setAttribute('description', $text.$num);
						$mustInsert = true;
					}
				}
			}
		}

		$dom->saveXML();
		$dom->save('profile.xml');
		// TODO
		// ADD FIELDS in profile/profile.xml
		// ADD FIELDS in lang
	}

	private function numOffset($fieldContent) {
		for ($i = 0; $i < strlen($fieldContent); $i++) {
			if (ctype_digit($fieldContent[$i])) {
				return ($i);
			}
		}
	}
}

$bar = new UserProfileConf;

$bar->addField('address');









?>