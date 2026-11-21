<?php
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/fieldpractice/backup/moodle2/backup_fieldpractice_stepslib.php');

class backup_fieldpractice_activity_task extends backup_activity_task {
    protected function define_my_settings(): void {
    }

    protected function define_my_steps(): void {
        $this->add_step(new backup_fieldpractice_activity_structure_step('fieldpractice_structure', 'fieldpractice.xml'));
    }

    public static function encode_content_links($content) {
        return $content;
    }
}
