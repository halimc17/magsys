<?php
// Simple test script for ADMINISTRATOR menu items - just check file existence
require_once('config/connection.php');

// Define all ADMINISTRATOR menu items from database query
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

echo "<html><head><style>
body { font-family: Arial, sans-serif; margin: 20px; }
h1 { color: #1E3A8A; }
h2 { color: #1E40AF; margin-top: 30px; }
h3 { color: #333; margin-top: 20px; border-bottom: 2px solid #EA580C; padding-bottom: 5px; }
.success { color: green; font-weight: bold; }
.failed { color: red; font-weight: bold; }
.skipped { color: blue; font-weight: bold; }
table { border-collapse: collapse; width: 100%; margin-top: 10px; }
th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
th { background-color: #1E3A8A; color: white; }
tr:nth-child(even) { background-color: #f2f2f2; }
.summary-box { background-color: #f9fafb; border: 2px solid #1E3A8A; padding: 20px; margin: 20px 0; }
</style></head><body>";

echo "<h1>ADMINISTRATOR MODULE TEST REPORT</h1>";
echo "<p><strong>Testing Date:</strong> " . date('Y-m-d H:i:s') . "</p>";
echo "<p><strong>Total Menu Items:</strong> " . count($menu_items) . "</p>";
echo "<hr>";

$success_count = 0;
$failed_count = 0;
$skipped_count = 0;
$failed_items = array();

echo "<table>";
echo "<tr><th>No</th><th>Menu Name</th><th>Menu ID</th><th>Action/File</th><th>File Path</th><th>File Exists</th><th>Status</th></tr>";

$no = 1;
foreach ($menu_items as $item) {
    echo "<tr>";
    echo "<td>" . $no++ . "</td>";
    echo "<td>" . htmlspecialchars($item['name']) . "</td>";
    echo "<td>" . $item['id'] . "</td>";

    // Skip parent menus
    if (empty($item['action']) || $item['action'] == 'Action...') {
        echo "<td colspan='4' class='skipped'>SKIPPED (Parent Menu)</td>";
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
        $success_count++;
    } else {
        echo "<td class='failed'>NO</td>";
        echo "<td class='failed'>FAILED</td>";
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
echo "<div class='summary-box'>";
echo "<h2>SUMMARY</h2>";
echo "<table style='width: 50%;'>";
echo "<tr><th>Metric</th><th>Count</th></tr>";
echo "<tr><td>Total Menu Items</td><td>" . count($menu_items) . "</td></tr>";
echo "<tr><td>Parent Menus (Skipped)</td><td class='skipped'>" . $skipped_count . "</td></tr>";
echo "<tr><td>Tested</td><td>" . ($success_count + $failed_count) . "</td></tr>";
echo "<tr><td>Successful</td><td class='success'>" . $success_count . "</td></tr>";
echo "<tr><td>Failed</td><td class='failed'>" . $failed_count . "</td></tr>";
$success_rate = ($success_count + $failed_count) > 0 ? round(($success_count / ($success_count + $failed_count)) * 100, 2) : 0;
echo "<tr><td>Success Rate</td><td><strong>" . $success_rate . "%</strong></td></tr>";
echo "</table>";
echo "</div>";

if (count($failed_items) > 0) {
    echo "<h2>Failed Items Details</h2>";
    echo "<table>";
    echo "<tr><th>No</th><th>Menu Name</th><th>Expected File</th><th>Full Path</th></tr>";
    $no = 1;
    foreach ($failed_items as $failed) {
        echo "<tr>";
        echo "<td>" . $no++ . "</td>";
        echo "<td>" . htmlspecialchars($failed['name']) . "</td>";
        echo "<td>" . htmlspecialchars($failed['file']) . "</td>";
        echo "<td>" . htmlspecialchars($failed['full_path']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<div class='summary-box' style='border-color: #16A34A;'>";
    echo "<h2 style='color: #16A34A;'>All menu items passed! No files missing.</h2>";
    echo "</div>";
}

echo "</body></html>";
?>
