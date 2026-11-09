<?php
require_once __DIR__ . '/../../includes/functions.php';

require_login();
if (current_user_role() !== 'admin') {
    set_flash('danger', 'Access denied. Admin only.');
    redirect(BASE_URL . '/client/dashboard.php');
}