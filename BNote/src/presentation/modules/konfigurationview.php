<?php
/**
 * View for configuration module.
 * @author matti
 *
 */
class KonfigurationView extends CrudView {

	/**
	 * Create the start view.
	 */
	function __construct($ctrl) {
		$this->setController($ctrl);
		$this->setEntityName("Konfiguration");
	}

	function start() {
		Writing::h1("Konfiguration");
		Writing::p("Bitte klicke auf eine Zeile um deren Wert zu ändern.");
		
		// instrument configuration
		$istr = new Link($this->modePrefix() . "instruments", "Instrumente");
		$istr->addIcon("music_file");
		$istr->write();
		
		$parameter = $this->getData()->getActiveParameter();

		$table = new Table($parameter);
		$table->renameHeader("caption", "Parameter");
		$table->renameHeader("value", "Wert");
		$table->setEdit("param");
		$table->removeColumn("param");
		$table->changeMode("edit");
		$table->write();
	}
	
	function edit() {
		$this->checkID();
		
		// header
		Writing::h2("Konfiguration");
		
		// show form
		$this->editEntityForm();
		
		// back button
		$this->verticalSpace();
		$this->backToStart();
		$this->verticalSpace();
	}
	
	function editEntityForm() {
		$param = $this->getData()->findByIdNoRef($_GET["id"]);
		$default = $param["value"];
		$form = new Form($this->getData()->getParameterCaption($_GET["id"]),
				$this->modePrefix() . "edit_process&id=" . $_GET["id"]);
		
		if($_GET["id"] == "default_contact_group") {
			Writing::p("Jeder neu registrierte Benutzer wird dieser Gruppe zugeordnet.");
			$dd = new Dropdown("value");
			$groups = $this->getData()->adp()->getGroups();
			foreach($groups as $i => $group) {
				if($i == 0) continue;
				$dd->addOption($group["name"], $group["id"]);
			}
			$dd->setSelected($default);
			$form->addElement("Wert", $dd);
		}
		else {
			// default case
			$form->addElement("Wert", new Field("value", $default, $this->getData()->getParameterType($_GET["id"])));
		}
		
		$form->write();
	}
	
	public function edit_process() {
		$this->checkID();
	
		// update
		$this->getData()->update($_GET["id"], $_POST);
	
		// show success
		new Message($this->getEntityName() . " ge&auml;ndert",
				"Der Eintrag wurde erfolgreich ge&auml;ndert.");
	
		// back button
		$this->backToStart();
	}
	
}

?>