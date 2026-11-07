<?php
defined('MOODLE_INTERNAL') || die();

class restore_fieldpractice_activity_structure_step extends restore_activity_structure_step {
    protected function define_structure(): array {
        $paths = [];
        $paths[] = new restore_path_element('fieldpractice', '/activity/fieldpractice');
        if ($this->get_setting_value('userinfo')) {
            $paths[] = new restore_path_element('fieldpractice_sub', '/activity/fieldpractice/submissions/submission');
        }
        return $this->prepare_activity_structure($paths);
    }

    protected function process_fieldpractice($data): void {
        global $DB;
        $data = (object)$data;
        $data->course = $this->get_courseid();
        $newid = $DB->insert_record('fieldpractice', $data);
        $this->apply_activity_instance($newid);
    }

    protected function process_fieldpractice_sub($data): void {
        global $DB;
        $data = (object)$data;
        $data->fieldpracticeid = $this->get_new_parentid('fieldpractice');
        $data->userid = $this->get_mappingid('user', $data->userid);
        $data->graderid = $this->get_mappingid('user', $data->graderid) ?: 0;
        if ($data->userid) {
            $DB->insert_record('fieldpractice_sub', $data);
        }
    }

    protected function after_execute(): void {
        $this->add_related_files('mod_fieldpractice', 'intro', null);
    }
}
