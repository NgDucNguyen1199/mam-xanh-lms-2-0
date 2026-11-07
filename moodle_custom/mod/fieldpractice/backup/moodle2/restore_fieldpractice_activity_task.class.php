<?php
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/fieldpractice/backup/moodle2/restore_fieldpractice_stepslib.php');

class restore_fieldpractice_activity_task extends restore_activity_task {
    protected function define_my_settings(): void {
    }

    protected function define_my_steps(): void {
        $this->add_step(new restore_fieldpractice_activity_structure_step('fieldpractice_structure', 'fieldpractice.xml'));
    }

    public static function define_decode_contents(): array {
        return [
            new restore_decode_content('fieldpractice', ['intro'], 'fieldpractice'),
        ];
    }

    public static function define_decode_rules(): array {
        return [];
    }
}
