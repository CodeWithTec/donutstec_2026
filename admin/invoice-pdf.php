<?php
require_once __DIR__ . '/includes/guard.php';
$db = Database::getConnection();

$id = (int)($_GET['id'] ?? 0);
$s = $db->prepare("
  SELECT i.*, p.title AS project_title, p.id AS project_id,
         u.full_name AS client_name, u.email AS client_email, u.company_name, u.phone
  FROM invoices i
  JOIN projects p ON i.project_id = p.id
  JOIN users u ON p.client_id = u.id
  WHERE i.id = ? LIMIT 1
");
$s->execute([$id]);
$inv = $s->fetch();
if (!$inv) { set_flash('danger', 'Invoice not found.'); redirect(BASE_URL . '/admin/invoices.php'); }

/* Load Dompdf if available */
$dompdfReady = false;
if (file_exists(DTC_ROOT . '/vendor/autoload.php')) {
    require_once DTC_ROOT . '/vendor/autoload.php';
    $dompdfReady = class_exists('Dompdf\\Dompdf');
}

$html = build_invoice_html($inv);

if (!$dompdfReady) {
    /* Fallback: serve as printable HTML */
    echo $html . '<script>window.print();</script>';
    exit;
}

$dompdf = new Dompdf\Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream('invoice-' . $inv['invoice_no'] . '.pdf', ['Attachment' => true]);

/* ---------- Shared template ---------- */
function build_invoice_html(array $inv): string {
    $statusColor = ['paid'=>'#28a745','unpaid'=>'#ffc107','overdue'=>'#dc3545','cancelled'=>'#6c757d'][$inv['status']] ?? '#6c757d';
    $rows = '<tr><td style="padding:8px;border:1px solid #ddd;">Software development — ' . htmlspecialchars($inv['project_title']) . '</td>'
          . '<td style="padding:8px;border:1px solid #ddd;text-align:right;">$' . number_format((float)$inv['amount'], 2) . '</td></tr>';
    return '<!DOCTYPE html><html><body style="font-family:Segoe UI,sans-serif;padding:40px;color:#212529;">'
         . '<div style="display:flex;justify-content:space-between;border-bottom:3px solid #151B54;padding-bottom:16px;">'
         . '<div><h1 style="margin:0;color:#151B54;">Donuts<span style="color:#1E90FF;">Tec</span> Liberia</h1>'
         . '<div style="color:#6C757D;font-size:12px;">Monrovia, Liberia · info@donutstec.com</div></div>'
         . '<div style="text-align:right;"><h2 style="margin:0;color:#151B54;">INVOICE</h2>'
         . '<div><strong>' . htmlspecialchars($inv['invoice_no']) . '</strong></div>'
         . '<div style="color:' . $statusColor . ';font-weight:bold;text-transform:uppercase;">' . htmlspecialchars($inv['status']) . '</div></div>'
         . '</div>'
         . '<div style="display:flex;justify-content:space-between;margin:30px 0;">'
         . '<div><div style="color:#6C757D;font-size:12px;">BILLED TO</div>'
         . '<strong>' . htmlspecialchars($inv['client_name']) . '</strong><br>'
         . htmlspecialchars($inv['company_name'] ?: '') . '<br>'
         . htmlspecialchars($inv['client_email']) . '<br>'
         . htmlspecialchars($inv['phone'] ?: '') . '</div>'
         . '<div style="text-align:right;"><div style="color:#6C757D;font-size:12px;">DATE / DUE</div>'
         . date('M d, Y', strtotime($inv['created_at'])) . '<br>'
         . ($inv['due_date'] ? 'Due ' . date('M d, Y', strtotime($inv['due_date'])) : '—') . '</div>'
         . '</div>'
         . '<table style="width:100%;border-collapse:collapse;margin-top:20px;">'
         . '<thead><tr style="background:#151B54;color:#fff;">'
         . '<th style="padding:8px;text-align:left;">Description</th>'
         . '<th style="padding:8px;text-align:right;">Amount</th></tr></thead>'
         . '<tbody>' . $rows . '</tbody>'
         . '<tfoot><tr><td style="padding:8px;border:1px solid #ddd;text-align:right;font-weight:bold;">TOTAL</td>'
         . '<td style="padding:8px;border:1px solid #ddd;text-align:right;font-weight:bold;font-size:18px;">$' . number_format((float)$inv['amount'], 2) . '</td></tr></tfoot>'
         . '</table>'
         . ($inv['notes'] ? '<div style="margin-top:20px;color:#6C757D;font-size:13px;">' . nl2br(htmlspecialchars($inv['notes'])) . '</div>' : '')
         . '<div style="margin-top:60px;text-align:center;color:#6C757D;font-size:11px;">Thank you for your business! — DonutsTecLiberia</div>'
         . '</body></html>';
}