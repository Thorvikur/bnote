<?php

/**
 * Data Access Class for configuration data.
 * @author matti
 *
 */
class KonfigurationData extends AbstractData {
	
	private $parameterConfig = array();
	
	private $parameterExclude = array();
	
	/**
	 * Build data provider.
	 */
	function __construct() {
		$this->fields = array(
				"param" => array("Parameter", FieldType::CHAR),
				"value" => array("Wert", FieldType::CHAR),
				"is_active" => array("Aktiv", FieldType::BOOLEAN)
		);
	
		$this->references = array(
				"default_contact_group" => "group"
		);
	
		$this->table = "configuration";
	
		$this->parameterConfig = array(
				"rehearsal_start" => array("Probenbeginn", 96),
				"rehearsal_duration" => array("Probendauer in min", FieldType::INTEGER),
				"default_contact_group" => array("Standardgruppe", FieldType::REFERENCE),
				"auto_activation" => array("Automatische Benutzeraktivierung", FieldType::BOOLEAN),
				"share_nonadmin_viewmode" => array("Share-Lesemodus für Nicht-Administratoren", FieldType::BOOLEAN)
		);
		
		$this->parameterExclude = array(
				"instrument_category_filter"
		);
		
		$this->init();
	}
	
	function getActiveParameter() {
		$query = "SELECT param, value FROM " . $this->table . " WHERE is_active = 1";
		$res = $this->database->getSelection($query);
		$params = array();
		array_push($params, array("param", "caption", "value"));
		for($i = 1; $i < count($res); $i++) {
			if(in_array($res[$i]["param"], $this->parameterExclude)) continue;
			
			$param = array(
					"param" => $res[$i]["param"],
					"caption" => $this->getParameterCaption($res[$i]["param"]),
					"value" => $this->replaceParameterValue($res[$i]["param"], $res[$i]["value"])
			);
			array_push($params, $param);			
		}
		
		return $params;
	}
	
	/**
	 * Converts the value of a parameter for view purposes.
	 * @param string $param Name of the parameter.
	 * @param string $value Original value of the parameter.
	 * @return string New value, by default the $value input.
	 */
	private function replaceParameterValue($param, $value) {
		if($param == "default_contact_group") {
			return $this->adp()->getGroupName($value);
		}
		else if($this->getParameterType($param) == FieldType::BOOLEAN) {
			return ($value == 1) ? "ja" : "nein";
		}
		else {
			return $value;
		}
	}
	
	function getParameterCaption($param) {
		$caption = $this->parameterConfig[$param][0];
		if($caption == null || $caption == "") return $param;
		else return $caption;
	}
	
	function getParameterType($param) {
		$type = $this->parameterConfig[$param][1];
		if($type == null) return FieldType::CHAR;
		return $type;
	}
	
	function findByIdNoRef($id) {
		$query = "SELECT * FROM " . $this->table . " WHERE param = \"$id\"";
		return $this->database->getRow($query);
	}
	
	function update($id, $values) {
		$val = $values["value"];
		
		// convert values to be saved to database
		if($this->getParameterType($id) == 96) {
			$val = $values["value_hour"] . ":" . $values["value_minute"];
		}
		if($id == "auto_activation") {
			$val = (isset($_POST["value"])) ? "1" : "0";
		}
		
		// save to db
		$query = "UPDATE configuration SET value = \"$val\" WHERE param = \"$id\"";
		$this->database->execute($query);
	}
	
}

?>