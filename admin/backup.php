<?php
require_once __DIR__ . '/includes/guard.php';

$backupDir = DTC_ROOT . '/backups';
if (!is_dir($backupDir)) mkdir($backupDir, 0755, true);

/* Generate backup on POST */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $timestamp = date('Ymd_His');
    $sqlFile   = $backupDir . '/db_' . $timestamp . '.sql';
    $zipFile   = $backupDir . '/uploads_' . $timestamp . '.zip';

    /* --- Database dump --- */
    $cfg = [
        'host' => 'localhost',
        'db'   => 'donutstec_db',
        'user' => 'root',
        'pass' => '',
    ];

    $dbDumpCmd = null;
    // Try mysqldump first
    $mysqldump = 'C:\\xampp\\mysql\\bin\\mysqldump.exe';
    if (file_exists($mysqldump)) {
        $cmd = sprintf(
            '"%s" --user=%s --password=%s --host=%s --single-transaction %s > "%s" 2>&1',
            $mysqldump, escapeshellarg($cfg['user']),
            escapeshellarg($cfg['pass']), escapeshellarg($cfg['host']),
            escapeshellarg($cfg['db']), $sqlFile
        );
        @exec($cmd);
    }

    // Fallback: pure PHP dump
    if (!file_exists($sqlFile) || filesize($sqlFile) < 100) {
        $db = Database::getConnection();
        $sql = "-- DonutsTecLiberia DB Backup\n-- Generated: " . date('Y-m-d H:i:s') . "\n\n";
        $sql .= "SET FOREIGN_KEY_CHECKS=0;\n\n";
        $tables = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        foreach ($tables as $t) {
            $create = $db->query("SHOW CREATE TABLE `$t`")->fetch();
            $sql .= "DROP TABLE IF EXISTS `$t`;\n" . $create['Create Table'] . ";\n\n";
            $rows = $db->query("SELECT * FROM `$t`")->fetchAll();
            if ($rows) {
                $cols = array_keys($rows[0]);
                $colList = '`' . implode('`,`', $cols) . '`';
                foreach ($rows as $row) {
                    $vals = array_map(fn($v) => $v === null ? 'NULL' : $db->quote($v), $row);
                    $sql .= "INSERT INTO `$t` ($colList) VALUES (" . implode(',', $vals) . ");\n";
                }
                $sql .= "\n";
            }
        }
        $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";
        file_put_contents($sqlFile, $sql);
    }

    /* --- Uploads zip --- */
    if (class_exists('ZipArchive')) {
        $zip = new ZipArchive();
        if ($zip->open($zipFile, ZipArchive::CREATE) === true) {
            $files = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator(UPLOAD_DIR, RecursiveDirectoryIterator::SKIP_DOTS)
            );
            foreach ($files as $file) {
                if ($file->isFile()) {
                    $zip->addFile($file->getRealPath(), 'uploads/' . $file->getFilename());
                }
            }
            $zip->close();
        }
    }

    log_activity('Generated backup: ' . $timestamp, current_user()['id']);
    set_flash('success', 'Backup created: db_' . $timestamp . '.sql' . (file_exists($zipFile) ? ' + uploads_' . $timestamp . '.zip' : ''));
    redirect(BASE_URL . '/admin/backup.php');
}

/* List existing backups */
$backups = [];
if (is_dir($backupDir)) {
    foreach (scandir($backupDir) as $f) {
        if ($f === '.' || $f === '..') continue;
        $backups[] = [
            'name' => $f,
            'size' => filesize($backupDir . '/' . $f),
            'date' => date('Y-m-d H:i:s', filemtime($backupDir . '/' . $f)),
        ];
    }
    usort($backups, fn($a, $b) => strcmp($b['date'], $a['date']));
}

$page_title  = 'Backups';
$active_page = '';
include __DIR__ . '/includes/admin_header.php';
?>

<div class="row g-4">
  <div class="col-lg-5">
    <div class="card card-dtc">
      <div class="card-body">
        <h5 class="fw-bold text-dtc-night mb-3"><i class="bi bi-hdd-stack me-2"></i> Create Backup</h5>
        <p class="small text-muted">Generates a database SQL dump + zipped uploads folder.</p>
        <form method="POST">
          <?= csrf_field() ?>
          <button class="btn btn-dtc-primary w-100">
            <i class="bi bi-download me-1"></i> Run Backup Now
          </button>
        </form>
        <div class="small text-muted mt-3">
          <strong>Storage:</strong> <code><?= e(str_replace(DTC_ROOT, '', $backupDir)) ?></code>
        </div>
      </div>
    </div>
  </div>
  <div class="col-lg-7">
    <div class="card card-dtc">
      <div class="card-body">
        <h5 class="fw-bold text-dtc-night mb-3">Existing Backups</h5>
        <?php if (empty($backups)): ?>
          <p class="small text-muted">No backups yet.</p>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table table-sm align-middle mb-0">
              <thead class="small text-muted"><tr><th>File</th><th>Size</th><th>Date</th><th></th></tr></thead>
              <tbody>
                <?php foreach ($backups as $b): ?>
                  <tr>
                    <td class="small"><?= e($b['name']) ?></td>
                    <td class="small"><?= number_format($b['size'] / 1024, 1) ?> KB</td>
                    <td class="small text-muted"><?= e($b['date']) ?></td>
                    <td>
                      <a href="<?= BASE_URL ?>/admin/download_backup.php?file=<?= urlencode($b['name']) ?>"
                         class="btn btn-sm btn-dtc-outline"><i class="bi bi-download"></i></a>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>