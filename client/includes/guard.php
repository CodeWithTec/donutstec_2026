<?php
/**
 * Client portal guard — must be included at the top of every client page.
 */
require_once __DIR__ . '/../../includes/functions.php';

require_login();
if (current_user_role() !== 'client') {
    set_flash('danger', 'Access denied. Client area only.');
    redirect(BASE_URL . '/admin/dashboard.php');
}