<?php
declare(strict_types=1);
require __DIR__.'/includes/ui.php';
$user=msw_require_user();$uid=(int)$user['id'];
msw_fob_resolve_due_dispatches($uid,8);
msw_bot_simulation_pulse(null,8);
msw_redirect('fob_globe.php');
