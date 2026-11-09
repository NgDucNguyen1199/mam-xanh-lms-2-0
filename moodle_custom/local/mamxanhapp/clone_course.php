<?php
// ===================================================================
// Mầm Xanh LMS 2.0 - 1-Click Local Course Cloner (Moodle Backup/Restore API)
// ===================================================================
require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');
require_once($CFG->dirroot . '/course/lib.php');
require_once($CFG->libdir . '/gradelib.php');
require_once($CFG->dirroot . '/backup/util/includes/backup_includes.php');
require_once($CFG->dirroot . '/backup/util/includes/restore_includes.php');

local_mamxanhapp_handle_lang_switch();
require_login();

$syscontext = context_system::instance();
local_mamxanhapp_require_capability_or_403(
    'local/mamxanhapp:manage',
    $syscontext,
    'Chức năng Nhân bản Khóa học Mẫu theo Địa phương chỉ dành cho Cán bộ kỹ thuật và Quản trị viên.',
    '1-Click Course Cloner is restricted to Technical Officers (CBKT) and System Administrators.'
);

$en = local_mamxanhapp_is_en();

// Load source courses (prioritize MASTER-AGRITECH-TPL, CHAMSOC01, CHEBIEN01 first)
$rawcourses = $DB->get_records_select('course', 'id <> :siteid', ['siteid' => SITEID], 'id ASC');
$priority = ['MASTER-AGRITECH-TPL' => 1, 'CHAMSOC01' => 2, 'CHEBIEN01' => 3];
usort($rawcourses, static function($a, $b) use ($priority) {
    $pa = $priority[$a->shortname] ?? 99;
    $pb = $priority[$b->shortname] ?? 99;
    return $pa <=> $pb ?: ($a->id <=> $b->id);
});

// Load available regional Cohorts
$cohorts = $DB->get_records('cohort', null, 'name ASC');
$cohortcounts = [];
foreach ($cohorts as $ch) {
    $cohortcounts[(int)$ch->id] = $DB->count_records('cohort_members', ['cohortid' => $ch->id]);
}

$cloneresult = null;
$cloneerror = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && optional_param('do_clone', 0, PARAM_INT) === 1) {
    require_sesskey();
    @core_php_time_limit::raise(300);
    raise_memory_limit(MEMORY_EXTRA);

    $sourceid    = required_param('sourcecourseid', PARAM_INT);
    $newfullname = trim(required_param('newfullname', PARAM_TEXT));
    $rawshort    = trim(optional_param('newshortname', '', PARAM_ALPHANUMEXT));
    $cohortid    = optional_param('cohortid', 0, PARAM_INT);

    $sourcecourse = $DB->get_record('course', ['id' => $sourceid], '*', IGNORE_MISSING);
    if (!$sourcecourse || (int)$sourcecourse->id === SITEID) {
        $cloneerror = local_mamxanhapp_t('Khóa học gốc không hợp lệ.', 'Invalid source course selected.');
    } else if ($newfullname === '') {
        $cloneerror = local_mamxanhapp_t('Vui lòng nhập Tên lớp tập huấn địa phương mới.', 'Please enter a valid new local course name.');
    } else {
        // Build unique shortname if blank or already taken
        if ($rawshort === '') {
            $ascii = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $newfullname) ?: 'LOPMOI';
            $slug = strtoupper(preg_replace('/[^A-Za-z0-9]+/', '-', $ascii));
            $slug = trim(substr($slug, 0, 22), '-') ?: 'LOCAL';
            $rawshort = $slug . '-' . date('Hi');
        }
        $finalshort = strtoupper($rawshort);
        $suffix = 2;
        while ($DB->record_exists('course', ['shortname' => $finalshort])) {
            $finalshort = strtoupper($rawshort) . '-' . $suffix;
            $suffix++;
        }

        try {
            $t0 = microtime(true);
            $admin = get_admin();
            $executorid = (int)$admin->id;
            $targetcatid = (int)$sourcecourse->category ?: 1;

            $backupsettings = [
                'activities'       => 1,
                'blocks'           => 0,
                'filters'          => 1,
                'users'            => 0,
                'enrolments'       => backup::ENROL_NEVER,
                'role_assignments' => 0,
                'comments'         => 0,
                'userscompletion'  => 0,
                'logs'             => 0,
                'grade_histories'  => 0,
            ];

            // 1. Execute Moodle Backup Controller on source template course
            $bc = new backup_controller(
                backup::TYPE_1COURSE,
                $sourcecourse->id,
                backup::FORMAT_MOODLE,
                backup::INTERACTIVE_NO,
                backup::MODE_SAMESITE,
                $executorid
            );

            foreach ($backupsettings as $sname => $sval) {
                if ($bc->get_plan()->setting_exists($sname)) {
                    $setting = $bc->get_plan()->get_setting($sname);
                    if ($setting->get_status() == backup_setting::NOT_LOCKED) {
                        $setting->set_value($sval);
                    }
                }
            }

            $backupid       = $bc->get_backupid();
            $backupbasepath = $bc->get_plan()->get_basepath();
            $bc->execute_plan();
            $results = $bc->get_results();
            $backupfile = $results['backup_destination'] ?? null;
            $bc->destroy();

            if (!file_exists($backupbasepath . '/moodle_backup.xml') && $backupfile) {
                $backupfile->extract_to_pathname(get_file_packer('application/vnd.moodle.backup'), $backupbasepath);
            }

            // 2. Create target local course & run Moodle Restore Controller
            $newcourseid = restore_dbops::create_new_course($newfullname, $finalshort, $targetcatid);

            $rc = new restore_controller(
                $backupid,
                $newcourseid,
                backup::INTERACTIVE_NO,
                backup::MODE_SAMESITE,
                $executorid,
                backup::TARGET_NEW_COURSE
            );

            foreach ($backupsettings as $sname => $sval) {
                if ($rc->get_plan()->setting_exists($sname)) {
                    $setting = $rc->get_plan()->get_setting($sname);
                    if ($setting->get_status() == backup_setting::NOT_LOCKED) {
                        $setting->set_value($sval);
                    }
                }
            }

            $rc->execute_precheck();
            $rc->execute_plan();
            $rc->destroy();

            if ($backupfile) {
                $backupfile->delete();
            }
            if (empty($CFG->keeptempdirectoriesonbackup) && is_dir($backupbasepath)) {
                fulldelete($backupbasepath);
            }

            // 3. Finalize metadata, completion, Gradebook category, Badge & Cohort Enrolment
            $newcourse = $DB->get_record('course', ['id' => $newcourseid], '*', MUST_EXIST);
            $newcourse->fullname         = $newfullname;
            $newcourse->shortname        = $finalshort;
            $newcourse->idnumber         = $finalshort;
            $newcourse->visible          = 1;
            $newcourse->enablecompletion = 1;
            $newcourse->showgrades       = 1;
            $newcourse->startdate        = time();
            $DB->update_record('course', $newcourse);

            $coursecatgrade = grade_category::fetch_course_category($newcourseid);
            $coursecatgrade->load_grade_item();

            // Ensure Manual + Self + Cohort enrolment & enrol CBKT + Selected Regional Farmers
            $manualplugin = enrol_get_plugin('manual');
            $selfplugin   = enrol_get_plugin('self');
            $enrolinstances = enrol_get_instances($newcourseid, false);
            $manualinst = null;
            foreach ($enrolinstances as $ei) {
                if ($ei->enrol === 'manual') {
                    $manualinst = $ei;
                    if ((int)$ei->status !== ENROL_INSTANCE_ENABLED) {
                        $manualplugin->update_status($ei, ENROL_INSTANCE_ENABLED);
                    }
                } else if ($ei->enrol === 'self' && $selfplugin) {
                    $selfplugin->update_status($ei, ENROL_INSTANCE_ENABLED);
                }
            }
            if (!$manualinst) {
                $mid = $manualplugin->add_default_instance($newcourse);
                $manualinst = $DB->get_record('enrol', ['id' => $mid]);
            }

            $cbktrole    = $DB->get_record('role', ['shortname' => 'canbokythuat']);
            $studentrole = $DB->get_record('role', ['shortname' => 'student'], '*', MUST_EXIST);

            if ($manualinst && $cbktrole) {
                if (!is_siteadmin($USER->id)) {
                    $manualplugin->enrol_user($manualinst, $USER->id, $cbktrole->id);
                }
                $cbktpilot = $DB->get_record('user', ['username' => 'cbkt_pilot']);
                if ($cbktpilot) {
                    $manualplugin->enrol_user($manualinst, $cbktpilot->id, $cbktrole->id);
                }
            }

            $enrolledfarmers = 0;
            $cohortname = local_mamxanhapp_t('Chưa gán nhóm (Tự ghi danh mở)', 'Open Self-Enrolment');
            if ($cohortid > 0 && isset($cohorts[$cohortid])) {
                $cohortobj = $cohorts[$cohortid];
                $cohortname = $cohortobj->name;

                // Attach Cohort Sync plugin if available, plus enrol existing members immediately
                $cohortplugin = enrol_get_plugin('cohort');
                if ($cohortplugin) {
                    $cohortplugin->add_instance($newcourse, [
                        'name'       => 'Cohort: ' . $cohortobj->name,
                        'status'     => ENROL_INSTANCE_ENABLED,
                        'customint1' => $cohortobj->id,
                        'roleid'     => $studentrole->id,
                    ]);
                    if (file_exists($CFG->dirroot . '/enrol/cohort/locallib.php')) {
                        require_once($CFG->dirroot . '/enrol/cohort/locallib.php');
                        $trace = new null_progress_trace();
                        enrol_cohort_sync($trace, $newcourseid);
                    }
                }
                $members = $DB->get_records('cohort_members', ['cohortid' => $cohortobj->id]);
                foreach ($members as $mem) {
                    if ($manualinst) {
                        $manualplugin->enrol_user($manualinst, $mem->userid, $studentrole->id);
                    }
                    $enrolledfarmers++;
                }
            }

            // Create active Course Badge for cloned local course
            if (!$DB->record_exists('badge', ['courseid' => $newcourseid])) {
                $now = time();
                $DB->insert_record('badge', (object)[
                    'name'            => 'Chứng nhận Tập huấn ' . $finalshort,
                    'description'     => 'Huy hiệu công nhận hoàn thành khóa tập huấn địa phương ' . $newfullname,
                    'timecreated'     => $now,
                    'timemodified'    => $now,
                    'usercreated'     => $executorid,
                    'usermodified'    => $executorid,
                    'issuername'      => 'Mầm Xanh LMS 2.0',
                    'issuerurl'       => $CFG->wwwroot,
                    'issuercontact'   => 'admin@mamxanh.vn',
                    'type'            => 2,
                    'courseid'        => $newcourseid,
                    'message'         => 'Chúc mừng bạn đã hoàn thành chương trình tập huấn địa phương!',
                    'messagesubject'  => 'Đã mở khóa Huy hiệu Tập huấn Địa phương!',
                    'attachment'      => 1,
                    'notification'    => 0,
                    'status'          => 1,
                    'version'         => '2.0',
                    'language'        => 'vi',
                    'imageauthorname' => 'Mam Xanh AgTech',
                    'imageauthoremail'=> 'admin@mamxanh.vn',
                    'imageauthorurl'  => $CFG->wwwroot,
                    'imagecaption'    => $finalshort,
                ]);
            }

            rebuild_course_cache($newcourseid, true);
            $elapsed = round(microtime(true) - $t0, 2);

            // Count cloned modules by type for visual audit summary
            $modcounts = $DB->get_records_sql(
                "SELECT m.name, COUNT(cm.id) AS cnt
                   FROM {course_modules} cm
                   JOIN {modules} m ON m.id = cm.module
                  WHERE cm.course = :cid AND cm.deletioninprogress = 0
               GROUP BY m.name",
                ['cid' => $newcourseid]
            );
            $restrictedsecs = $DB->count_records_select(
                'course_sections',
                "course = :cid AND section > 0 AND availability IS NOT NULL AND availability <> ''",
                ['cid' => $newcourseid]
            );

            $cloneresult = (object)[
                'newcourseid'    => $newcourseid,
                'fullname'       => $newfullname,
                'shortname'      => $finalshort,
                'sourcename'     => $sourcecourse->shortname . ' — ' . $sourcecourse->fullname,
                'cohortname'     => $cohortname,
                'enrolledcount'  => $enrolledfarmers,
                'elapsed'        => $elapsed,
                'modcounts'      => $modcounts,
                'restrictedsecs' => $restrictedsecs,
                'courseurl'      => (new moodle_url('/course/view.php', ['id' => $newcourseid]))->out(false),
            ];
        } catch (Throwable $ex) {
            $cloneerror = $ex->getMessage();
        }
    }
}

$PAGE->set_context($syscontext);
$PAGE->set_url(new moodle_url('/local/mamxanhapp/clone_course.php'));
$PAGE->set_pagelayout('standard');
$PAGE->set_title(local_mamxanhapp_t('Nhân bản Khóa học Địa phương (1-Click Cloner) - Mầm Xanh LMS 2.0', '1-Click Local Course Cloner - Mam Xanh LMS 2.0'));
$PAGE->set_heading(local_mamxanhapp_t('Công cụ Nhân bản Khóa học Mẫu theo Địa phương (1-Click Course Cloner)', '1-Click Master Course Cloner for Local Regions'));

echo $OUTPUT->header();
?>

<div class="mx-cloner-wrapper" style="padding-bottom: 60px;">
    <!-- Top Header Banner -->
    <section style="background:linear-gradient(135deg,#1B4332 0%,#2E7D32 100%);color:#FFF;border-radius:24px;padding:24px;margin-bottom:22px;box-shadow:0 12px 28px rgba(27,67,50,0.16);">
        <div class="d-flex flex-wrap justify-content-between align-items-center" style="gap:16px;">
            <div>
                <span style="display:inline-block;background:rgba(255,255,255,0.2);padding:5px 14px;border-radius:999px;font-size:0.84rem;font-weight:800;margin-bottom:8px;">
                    MOODLE BACKUP & RESTORE CONTROLLER ENGINE
                </span>
                <h2 style="color:#FFF;font-weight:900;font-size:1.55rem;margin-bottom:6px;">
                    <?php echo s(local_mamxanhapp_t(
                        'Nhân bản Khóa học Mẫu theo Địa phương / Nhóm Nông dân (1-Click)',
                        '1-Click Regional Course Cloner from Master Template'
                    )); ?>
                </h2>
                <p style="color:#D8F3DC;margin:0;font-size:0.95rem;">
                    <?php echo s(local_mamxanhapp_t(
                        'Sao chép nguyên vẹn 100% Lộ trình tuần tự (Restrict Access), Video thực tế, Tài liệu PDF xem trực tiếp, H5P, SCORM 1.2, Thực hành Vườn & Ngân hàng câu hỏi Quiz chỉ trong vài giây.',
                        'Clone 100% of Sequential Learning Path rules, HD Videos, Inline PDFs, H5P, SCORM 1.2, Garden Practice & Quiz Question Bank in seconds.'
                    )); ?>
                </p>
            </div>
            <div>
                <a href="<?php echo (new moodle_url('/local/mamxanhapp/manage.php'))->out(false); ?>" class="btn btn-light" style="min-height:48px;display:inline-flex;align-items:center;border-radius:14px;font-weight:800;color:#1B4332;padding:0 18px;">
                    ← <?php echo s(local_mamxanhapp_t('Quay lại Dashboard CBKT', 'Back to CBKT Dashboard')); ?>
                </a>
            </div>
        </div>
    </section>

    <?php if ($cloneerror !== ''): ?>
    <div class="alert alert-danger" style="border-radius:16px;font-weight:700;border-width:2px;">
        <?php echo s($cloneerror); ?>
    </div>
    <?php endif; ?>

    <?php if ($cloneresult !== null): ?>
    <!-- Clone Success Result Card -->
    <section id="mx-clone-success-card" style="background:#E8F5E9;border:3px solid #2E7D32;border-radius:24px;padding:24px;margin-bottom:24px;box-shadow:0 10px 26px rgba(46,125,50,0.14);">
        <div class="d-flex flex-wrap justify-content-between align-items-start" style="gap:16px;">
            <div>
                <span style="background:#1B4332;color:#FFF;padding:5px 14px;border-radius:999px;font-weight:900;font-size:0.82rem;">
                    <?php echo s(local_mamxanhapp_t('NHÂN BẢN THÀNH CÔNG 100%', 'CLONED SUCCESSFULLY 100%')); ?> • <?php echo s($cloneresult->elapsed . 's'); ?>
                </span>
                <h3 style="color:#1B4332;font-weight:900;font-size:1.4rem;margin:10px 0 6px;">
                    <?php echo s($cloneresult->fullname); ?>
                    <code style="font-size:0.95rem;background:#FFF;padding:3px 10px;border-radius:8px;color:#1B4332;">[<?php echo s($cloneresult->shortname); ?>]</code>
                </h3>
                <p style="color:#263238;margin-bottom:10px;font-weight:600;">
                    • <?php echo s(local_mamxanhapp_t('Khóa học gốc:', 'Source Template:')); ?> <strong><?php echo s($cloneresult->sourcename); ?></strong><br>
                    • <?php echo s(local_mamxanhapp_t('Nhóm nông dân địa phương (Cohort):', 'Assigned Farmer Cohort:')); ?>
                    <strong><?php echo s($cloneresult->cohortname); ?></strong>
                    (<?php echo (int)$cloneresult->enrolledcount; ?> <?php echo s(local_mamxanhapp_t('học viên đã ghi danh tự động', 'farmers auto-enrolled')); ?>)<br>
                    • <?php echo s(local_mamxanhapp_t('Chuyên đề khóa mở tuần tự (Restrict Access):', 'Sequential Restricted Sections:')); ?>
                    <strong><?php echo (int)$cloneresult->restrictedsecs; ?> <?php echo s(local_mamxanhapp_t('chuyên đề tự động ánh xạ ID mới', 'sections auto-remapped')); ?></strong>
                </p>

                <div class="d-flex flex-wrap" style="gap:8px;">
                    <?php foreach ($cloneresult->modcounts as $mname => $mc): ?>
                    <span style="background:#FFFFFF;border:1.5px solid #81C784;color:#1B4332;padding:5px 12px;border-radius:999px;font-weight:800;font-size:0.84rem;">
                        <?php echo s(strtoupper($mname)); ?>: <?php echo (int)$mc->cnt; ?>
                    </span>
                    <?php endforeach; ?>
                </div>
            </div>

            <div>
                <a href="<?php echo $cloneresult->courseurl; ?>" id="mx-open-cloned-course-btn" class="btn btn-success" style="min-height:54px;display:inline-flex;align-items:center;gap:8px;border-radius:16px;font-weight:900;font-size:1.02rem;background:#1B4332;border-color:#1B4332;padding:0 24px;">
                    <?php echo s(local_mamxanhapp_t('Mở Khóa học Địa phương Vừa Tạo →', 'Open Newly Cloned Local Course →')); ?>
                </a>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- 1-Click Clone Form + Existing Courses Catalog -->
    <div class="row">
        <div class="col-12 col-lg-7 mb-4">
            <section style="background:#FFFFFF;border:2px solid #C8E6C9;border-radius:24px;padding:24px;box-shadow:0 8px 22px rgba(27,67,50,0.06);">
                <h3 style="color:#1B4332;font-weight:900;font-size:1.25rem;margin-bottom:16px;">
                    <?php echo s(local_mamxanhapp_t('Khởi tạo Lớp Tập huấn Địa phương Mới (1-Chạm)', 'Create New Regional Training Class (1-Click)')); ?>
                </h3>

                <form method="post" action="<?php echo (new moodle_url('/local/mamxanhapp/clone_course.php'))->out(false); ?>" id="mx-course-cloner-form">
                    <input type="hidden" name="sesskey" value="<?php echo sesskey(); ?>">
                    <input type="hidden" name="do_clone" value="1">

                    <div class="mb-3">
                        <label for="mx-clone-source" style="font-weight:800;color:#1B4332;margin-bottom:6px;display:block;">
                            1. <?php echo s(local_mamxanhapp_t('Chọn Khóa học Mẫu / Khóa học gốc cần nhân bản:', 'Select Master Template / Source Course:')); ?>
                        </label>
                        <select name="sourcecourseid" id="mx-clone-source" class="form-control" style="width:100%;max-width:100%;min-height:50px;border-radius:14px;border:2px solid #81C784;font-weight:700;" required>
                            <?php foreach ($rawcourses as $rc): ?>
                            <option value="<?php echo (int)$rc->id; ?>">
                                [<?php echo s($rc->shortname); ?>] <?php echo s(format_string($rc->fullname)); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="mx-clone-fullname" style="font-weight:800;color:#1B4332;margin-bottom:6px;display:block;">
                            2. <?php echo s(local_mamxanhapp_t('Tên Lớp Tập huấn Địa phương Mới:', 'New Local Training Course Name:')); ?>
                        </label>
                        <input type="text" name="newfullname" id="mx-clone-fullname" class="form-control"
                               style="min-height:50px;border-radius:14px;border:2px solid #81C784;font-weight:700;"
                               placeholder="VD: Tập huấn Nông dân Đơn Dương - Lâm Đồng Đợt 2"
                               value="Tập huấn Nông dân Đơn Dương - Lâm Đồng Đợt 2" required>
                    </div>

                    <div class="row">
                        <div class="col-12 col-md-5 mb-3">
                            <label for="mx-clone-shortname" style="font-weight:800;color:#1B4332;margin-bottom:6px;display:block;">
                                3. <?php echo s(local_mamxanhapp_t('Mã Lớp (Tùy chọn):', 'Course Code (Optional):')); ?>
                            </label>
                            <input type="text" name="newshortname" id="mx-clone-shortname" class="form-control"
                                   style="min-height:50px;border-radius:14px;border:2px solid #C8E6C9;font-weight:700;"
                                   placeholder="VD: DONDUONG-LD-02">
                        </div>

                        <div class="col-12 col-md-7 mb-3">
                            <label for="mx-clone-cohort" style="font-weight:800;color:#1B4332;margin-bottom:6px;display:block;">
                                4. <?php echo s(local_mamxanhapp_t('Gán Tổ Hợp tác / Nhóm Nông dân (Cohort):', 'Assign Regional Farmer Cohort:')); ?>
                            </label>
                            <select name="cohortid" id="mx-clone-cohort" class="form-control" style="width:100%;max-width:100%;min-height:50px;border-radius:14px;border:2px solid #81C784;font-weight:700;">
                                <?php foreach ($cohorts as $ch): ?>
                                <option value="<?php echo (int)$ch->id; ?>">
                                    <?php echo s($ch->name); ?> (<?php echo (int)($cohortcounts[(int)$ch->id] ?? 0); ?> <?php echo s(local_mamxanhapp_t('nông hộ', 'farmers')); ?>)
                                </option>
                                <?php endforeach; ?>
                                <option value="0"><?php echo s(local_mamxanhapp_t('-- Chưa gán Cohort (Mở tự ghi danh) --', '-- No Cohort (Open Self-Enrolment) --')); ?></option>
                            </select>
                        </div>
                    </div>

                    <button type="submit" id="mx-clone-submit-btn" class="btn btn-success w-100 mt-2"
                            style="min-height:54px;border-radius:16px;font-weight:900;font-size:1.05rem;background:#1B4332;border-color:#1B4332;">
                        <?php echo s(local_mamxanhapp_t('Nhân bản ngay (Clone Khóa học Địa phương)', 'Clone Course Now (1-Click Backup & Restore)')); ?>
                    </button>
                </form>
            </section>
        </div>

        <div class="col-12 col-lg-5 mb-4">
            <section style="background:#F4FBF6;border:2px solid #B7E4C7;border-radius:24px;padding:22px;height:100%;">
                <h4 style="color:#1B4332;font-weight:900;font-size:1.12rem;margin-bottom:12px;">
                    <?php echo s(local_mamxanhapp_t('Quy chuẩn Khóa học Mẫu được Bảo toàn 100% khi Nhân bản', '100% Preserved Master Course Blueprint')); ?>
                </h4>
                <ul style="padding-left:18px;line-height:1.75;color:#14281D;font-weight:600;margin-bottom:16px;">
                    <li><strong>Bước 1 (Chuyên đề 1):</strong> Video MP4 thực tế có phụ đề <code>.vtt</code> + Xem trực tiếp Sổ tay PDF kỹ thuật (<code>RESOURCELIB_DISPLAY_EMBED</code>).</li>
                    <li><strong>Bước 2 (Chuyên đề 2):</strong> Gói tương tác chuẩn <strong>H5P</strong> (<code>mod_h5pactivity</code>) + Gói bài giảng chuẩn <strong>SCORM 1.2</strong> (<code>mod_scorm</code>) — tự động mở khóa khi xong Bước 1.</li>
                    <li><strong>Bước 3 (Chuyên đề 3):</strong> Thực hành tại vườn <code>mod_fieldpractice</code> (Điểm danh QR xoay vòng + GPS + Rubric 4 tiêu chí) — mở khóa khi xong Bước 2.</li>
                    <li><strong>Bước 4 (Chuyên đề 4):</strong> Bài kiểm tra Quiz 12 câu (Ngân hàng câu hỏi đầy đủ) — đạt <code>&ge; 70%</code> tự động mở khóa Chứng nhận Số QR <code>HMAC-SHA256</code>.</li>
                </ul>
                <div style="background:#FFFFFF;border-radius:16px;padding:14px;border:1.5px solid #A5D6A7;font-size:0.88rem;color:#2E7D32;font-weight:700;">
                    <?php echo s(local_mamxanhapp_t(
                        'Mẹo cho Cán bộ Kỹ thuật: Sau khi nhân bản, mọi ID điều kiện mở khóa tuần tự (Availability JSON) và Sổ điểm Gradebook đều được Moodle tự động ánh xạ sang lớp địa phương mới!',
                        'Tip: All Restrict Access module IDs and Gradebook categories are automatically remapped to the new local course!'
                    )); ?>
                </div>
            </section>
        </div>
    </div>
</div>

<?php
echo $OUTPUT->footer();
