<?php
define('CLI_SCRIPT', true);
require_once('/var/www/html/config.php');

$blocks = $DB->get_records_select('block_instances', "pagetypepattern LIKE '%my-index%'", [], 'id ASC');
foreach ($blocks as $b) {
    $ctx = $DB->get_record('context', ['id' => $b->parentcontextid]);
    echo "ID: {$b->id} | Block: {$b->blockname} | Region: {$b->defaultregion} | CtxLevel: {$ctx->contextlevel} | InstanceID: {$ctx->instanceid}\n";
}

$sysctx = context_system::instance();
echo "System context ID: {$sysctx->id}\n";
