<?php
// Simple file existence test for ADMINISTRATOR menu items
// No database connection or session required

// Define all ADMINISTRATOR menu items
$menu_items = array(
    // Parent Menu
    array('name' => 'Menu Manager (Parent)', 'action' => '', 'id' => 13),

    // Menu Manager submenu
    array('name' => 'Menu Settings', 'action' => 'main_menuSettings', 'id' => 20),
    array('name' => 'User Privilege', 'action' => 'main_userPrivillages', 'id' => 21),
    array('name' => 'Privileges by Table', 'action' => 'main_privilage_by_table', 'id' => 967),
    array('name' => 'Copy Privileges', 'action' => 'main_copy_privileges', 'id' => 851),
    array('name' => 'Parent-Child Menu Arranger', 'action' => 'main_parentChild', 'id' => 22),
    array('name' => 'Detail Akses', 'action' => 'main_detailakses', 'id' => 515),
    array('name' => 'Admin List', 'action' => 'admin_list', 'id' => 1143),

    // Users Settings (Parent)
    array('name' => 'Users Settings (Parent)', 'action' => 'Action...', 'id' => 14),

    // Users Settings submenu
    array('name' => 'Add New User', 'action' => 'main_newUser', 'id' => 51),
    array('name' => 'Active/Deactive/Delete User', 'action' => 'main_activeUser', 'id' => 52),
    array('name' => 'Reset Password', 'action' => 'main_resetPassword', 'id' => 53),

    // Direct menu items
    array('name' => 'Organization Chart', 'action' => 'main_orgChart', 'id' => 16),
    array('name' => 'Language Settings', 'action' => 'main_languageSettings', 'id' => 266),
    array('name' => 'N.P.W.P Perusahaan', 'action' => 'setup_org_npwp', 'id' => 487),

    // Tools (Parent)
    array('name' => 'Tools (Parent)', 'action' => 'tool_admin', 'id' => 971),

    // Tools submenu
    array('name' => 'Revisi PO', 'action' => 'log_updatepo.php', 'id' => 1062),
    array('name' => 'Admin Tools', 'action' => 'tool_admin', 'id' => 1063),
    array('name' => 'AutoR/K Checker', 'action' => 'tool_mutasi_check.php', 'id' => 1064),

    // Direct menu items
    array('name' => 'Reset HM/KM', 'action' => 'tool_resethmkm', 'id' => 1048),
    array('name' => 'User Activity Log', 'action' => 'main_user_activity.php', 'id' => 1144)
);

?>
<!DOCTYPE html>
<html>
<head>
<title>ADMINISTRATOR MODULE TEST REPORT</title>
<style>
body { font-family: Arial, sans-serif; margin: 20px; background-color: #f9fafb; }
.container { max-width: 1200px; margin: 0 auto; background-color: white; padding: 30px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
h1 { color: #1E3A8A; border-bottom: 3px solid #EA580C; padding-bottom: 10px; }
h2 { color: #1E40AF; margin-top: 30px; }
.success { color: #16A34A; font-weight: bold; }
.failed { color: #DC2626; font-weight: bold; }
.skipped { color: #3B82F6; font-style: italic; }
table { border-collapse: collapse; width: 100%; margin-top: 20px; }
th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
th { background-color: #1E3A8A; color: white; font-weight: bold; }
tr:nth-child(even) { background-color: #f2f2f2; }
tr:hover { background-color: #e5e7eb; }
.summary-box { background-color: #f0f9ff; border: 2px solid #1E3A8A; padding: 20px; margin: 20px 0; border-radius: 5px; }
.metric-table { width: 60%; margin: 0 auto; }
.success-box { background-color: #f0fdf4; border-color: #16A34A; }
.failed-box { background-color: #fef2f2; border-color: #DC2626; }
</style>
</head>
<body>
<div class="container">

<h1>ADMINISTRATOR MODULE TEST REPORT</h1>
<p><strong>Testing Date:</strong> <?php echo date('Y-m-d H:i:s'); ?></p>
<p><strong>Total Menu Items:</strong> <?php echo count($menu_items); ?></p>
<p><strong>Test Type:</strong> File Existence Check</p>
<hr>

<?php
$success_count = 0;
$failed_count = 0;
$skipped_count = 0;
$failed_items = array();

echo "<table>";
echo "<tr><th>#</th><th>Menu Name</th><th>Menu ID</th><th>Action</th><th>Expected File</th><th>File Exists</th><th>Status</th><th>URL (if accessible)</th></tr>";

$no = 1;
foreach ($menu_items as $item) {
    echo "<tr>";
    echo "<td>" . $no++ . "</td>";
    echo "<td>" . htmlspecialchars($item['name']) . "</td>";
    echo "<td>" . $item['id'] . "</td>";

    // Skip parent menus
    if (empty($item['action']) || $item['action'] == 'Action...') {
        echo "<td colspan='5' class='skipped'>SKIPPED (Parent Menu - No Direct Action)</td>";
        echo "</tr>";
        $skipped_count++;
        continue;
    }

    echo "<td>" . htmlspecialchars($item['action']) . "</td>";

    // Determine file path
    $file_path = $item['action'];
    if (strpos($file_path, '.php') === false) {
        $file_path .= '.php';
    }

    $full_path = __DIR__ . '/' . $file_path;
    echo "<td>" . htmlspecialchars($file_path) . "</td>";

    // Check if file exists
    if (file_exists($full_path)) {
        echo "<td class='success'>YES</td>";
        echo "<td class='success'>SUCCESS</td>";
        echo "<td><a href='http://localhost/erpmill/" . htmlspecialchars($file_path) . "' target='_blank'>View</a></td>";
        $success_count++;
    } else {
        echo "<td class='failed'>NO</td>";
        echo "<td class='failed'>FAILED</td>";
        echo "<td>-</td>";
        $failed_count++;
        $failed_items[] = array(
            'name' => $item['name'],
            'file' => $file_path,
            'full_path' => $full_path
        );
    }

    echo "</tr>";
}

echo "</table>";

// Summary
$total_tested = $success_count + $failed_count;
$success_rate = $total_tested > 0 ? round(($success_count / $total_tested) * 100, 2) : 0;
$summary_class = ($failed_count == 0) ? 'success-box' : (($failed_count > 0 && $success_count > 0) ? '' : 'failed-box');

echo "<div class='summary-box " . $summary_class . "'>";
echo "<h2>TEST SUMMARY</h2>";
echo "<table class='metric-table'>";
echo "<tr><th>Metric</th><th>Count</th><th>Percentage</th></tr>";
echo "<tr><td>Total Menu Items</td><td>" . count($menu_items) . "</td><td>100%</td></tr>";
echo "<tr><td>Parent Menus (Skipped)</td><td class='skipped'>" . $skipped_count . "</td><td>" . round(($skipped_count / count($menu_items)) * 100, 1) . "%</td></tr>";
echo "<tr><td>Actual Menu Items Tested</td><td>" . $total_tested . "</td><td>" . round(($total_tested / count($menu_items)) * 100, 1) . "%</td></tr>";
echo "<tr><td>Files Found (Success)</td><td class='success'>" . $success_count . "</td><td class='success'>" . $success_rate . "%</td></tr>";
echo "<tr><td>Files Missing (Failed)</td><td class='failed'>" . $failed_count . "</td><td class='failed'>" . round(100 - $success_rate, 2) . "%</td></tr>";
echo "</table>";
echo "</div>";

if (count($failed_items) > 0) {
    echo "<div class='summary-box failed-box'>";
    echo "<h2>FAILED ITEMS - FILES NOT FOUND</h2>";
    echo "<table>";
    echo "<tr><th>#</th><th>Menu Name</th><th>Expected File</th><th>Full Path</th></tr>";
    $no = 1;
    foreach ($failed_items as $failed) {
        echo "<tr>";
        echo "<td>" . $no++ . "</td>";
        echo "<td>" . htmlspecialchars($failed['name']) . "</td>";
        echo "<td class='failed'>" . htmlspecialchars($failed['file']) . "</td>";
        echo "<td style='font-size: 11px;'>" . htmlspecialchars($failed['full_path']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    echo "</div>";
} else {
    echo "<div class='summary-box success-box'>";
    echo "<h2 style='color: #16A34A;'>✓ ALL MENU ITEMS PASSED!</h2>";
    echo "<p>All " . $success_count . " menu items have their corresponding PHP files present in the system.</p>";
    echo "</div>";
}

echo "<div class='summary-box'>";
echo "<h2>NOTES</h2>";
echo "<ul>";
echo "<li>This test only checks if the PHP files exist in the file system.</li>";
echo "<li>It does NOT test if the pages load correctly or if there are PHP/JavaScript errors.</li>";
echo "<li>To test actual page functionality, you need to login to the system and click each menu item manually.</li>";
echo "<li>Parent menus are skipped as they don't have direct actions (they only contain submenus).</li>";
echo "</ul>";
echo "</div>";
?>

</div>
</body>
</html>
