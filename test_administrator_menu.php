<?php
// Test script for ADMINISTRATOR menu items
session_start();

// Simulate login session - set all required session variables
$_SESSION['namauser'] = 'kingking.firdaus';
$_SESSION['language'] = 'ID';
$_SESSION['security'] = 'off'; // Full access for testing
$_SESSION['DIE'] = time() + (25 * 60); // 25 minutes from now
$_SESSION['MAXLIFETIME'] = 25 * 60; // 25 minutes
$_SESSION['standard'] = array('username' => 'kingking.firdaus', 'access_level' => 1);
$_SESSION['access_type'] = 'level';
$_SESSION['org'] = array('holding' => 'TESTHOLDING');
$_SESSION['empl'] = array('name' => 'Test User', 'lokasitugas' => 'TEST');

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

echo "<h1>ADMINISTRATOR MODULE TEST REPORT</h1>";
echo "<p>Testing Date: " . date('Y-m-d H:i:s') . "</p>";
echo "<p>Total Menu Items: " . count($menu_items) . "</p>";
echo "<hr>";

$success_count = 0;
$failed_count = 0;
$failed_items = array();

foreach ($menu_items as $item) {
    echo "<h3>Testing: " . $item['name'] . "</h3>";
    echo "<ul>";
    echo "<li><strong>Menu ID:</strong> " . $item['id'] . "</li>";
    echo "<li><strong>Action:</strong> " . ($item['action'] ?: 'N/A (Parent Menu)') . "</li>";

    // Skip parent menus
    if (empty($item['action']) || $item['action'] == 'Action...') {
        echo "<li><strong>Status:</strong> <span style='color:blue;'>SKIPPED (Parent Menu)</span></li>";
        echo "</ul>";
        echo "<hr>";
        continue;
    }

    // Determine file path
    $file_path = $item['action'];
    if (!strpos($file_path, '.php')) {
        $file_path .= '.php';
    }

    $full_path = __DIR__ . '/' . $file_path;
    echo "<li><strong>File Path:</strong> " . $file_path . "</li>";
    echo "<li><strong>Full Path:</strong> " . $full_path . "</li>";

    // Check if file exists
    if (file_exists($full_path)) {
        echo "<li><strong>File Exists:</strong> <span style='color:green;'>YES</span></li>";

        // Try to include and capture output
        ob_start();
        $php_errors = array();

        include($full_path);
        $output = ob_get_clean();

        echo "<li><strong>Output Length:</strong> " . strlen($output) . " bytes</li>";
        echo "<li><strong>Status:</strong> <span style='color:green;'>SUCCESS</span></li>";
        $success_count++;
    } else {
        echo "<li><strong>File Exists:</strong> <span style='color:red;'>NO</span></li>";
        echo "<li><strong>Status:</strong> <span style='color:red;'>FAILED - File Not Found</span></li>";
        $failed_count++;
        $failed_items[] = array(
            'name' => $item['name'],
            'error' => 'File not found: ' . $full_path
        );
    }

    echo "</ul>";
    echo "<hr>";
}

// Summary
echo "<h2>SUMMARY</h2>";
echo "<ul>";
echo "<li><strong>Total Menu Items Tested:</strong> " . (count($menu_items) - 3) . " (excluding 3 parent menus)</li>";
echo "<li><strong>Successful:</strong> <span style='color:green;font-size:20px;'>" . $success_count . "</span></li>";
echo "<li><strong>Failed:</strong> <span style='color:red;font-size:20px;'>" . $failed_count . "</span></li>";
echo "</ul>";

if (count($failed_items) > 0) {
    echo "<h3>Failed Items Details:</h3>";
    echo "<ol>";
    foreach ($failed_items as $failed) {
        echo "<li><strong>" . $failed['name'] . "</strong><br>";
        echo "Error: " . htmlspecialchars($failed['error']) . "</li>";
    }
    echo "</ol>";
}
?>
