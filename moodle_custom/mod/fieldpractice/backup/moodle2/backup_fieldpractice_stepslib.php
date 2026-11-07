<?php
defined('MOODLE_INTERNAL') || die();

class backup_fieldpractice_activity_structure_step extends backup_activity_structure_step {
    protected function define_structure() {
        $userinfo = $this->get_setting_value('userinfo');

        $fieldpractice = new backup_nested_element('fieldpractice', ['id'], [
            'name', 'intro', 'introformat', 'location_name',
            'target_lat', 'target_lng', 'grade', 'qr_secret',
            'timecreated', 'timemodified',
        ]);

        $subs = new backup_nested_element('submissions');
        $sub = new backup_nested_element('submission', ['id'], [
            'userid', 'checkin_code', 'gps_lat', 'gps_lng',
            'checkin_note', 'checkin_time', 'crit_prep', 'crit_tech',
            'crit_biosafe', 'crit_quality', 'total_grade', 'feedback',
            'graderid', 'graded_time',
        ]);

        $fieldpractice->add_child($subs);
        $subs->add_child($sub);

        $fieldpractice->set_source_table('fieldpractice', ['id' => backup::VAR_ACTIVITYID]);

        if ($userinfo) {
            $sub->set_source_table('fieldpractice_sub', ['fieldpracticeid' => backup::VAR_PARENTID]);
            $sub->annotate_ids('user', 'userid');
            $sub->annotate_ids('user', 'graderid');
        }

        $fieldpractice->annotate_files('mod_fieldpractice', 'intro', null);
        return $this->prepare_activity_structure($fieldpractice);
    }
}
