<?php
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/course/moodleform_mod.php');

class mod_fieldpractice_mod_form extends moodleform_mod {
    public function definition(): void {
        global $CFG;

        $mform = $this->_form;

        $mform->addElement('header', 'general', get_string('general', 'form'));
        $mform->addElement('text', 'name', get_string('name'), ['size' => '64']);
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');

        $this->standard_intro_elements();

        $mform->addElement('text', 'location_name', 'Địa điểm Vườn Thực hành / Garden Station', ['size' => '64']);
        $mform->setType('location_name', PARAM_TEXT);
        $mform->setDefault('location_name', 'Vườn Thực nghiệm Nông nghiệp Công nghệ cao Mầm Xanh');

        $mform->addElement('text', 'target_lat', 'Vĩ độ GPS (Lat)', ['size' => '24']);
        $mform->setType('target_lat', PARAM_TEXT);
        $mform->setDefault('target_lat', '11.940419');

        $mform->addElement('text', 'target_lng', 'Kinh độ GPS (Lng)', ['size' => '24']);
        $mform->setType('target_lng', PARAM_TEXT);
        $mform->setDefault('target_lng', '108.458313');

        $this->standard_grading_coursemodule_elements();
        $this->standard_coursemodule_elements();
        $this->add_action_buttons();
    }
}
