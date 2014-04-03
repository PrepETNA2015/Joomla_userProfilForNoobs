<?php

class UserProfileConf
{
	public function addField($type) {

		// ADD FIELDS in fields.xml
		$dom = new DOMDocument;
		$dom->preserveWhiteSpace = false;
		$dom->formatOutput = true;
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
				$mustInsert = false;
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
		echo $dom->saveXML();
		$dom->save('fields.xml');

		// TODO
		// ADD FIELDS in profile.xml
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

$bar->addField('phone');









?>