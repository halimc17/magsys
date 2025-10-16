<?php
// Test script to check all PEMASARAN menu items
require_once('config/connection.php');

// Start session
session_start();

// Set session variables for testing (simulate logged-in user)
$_SESSION['namauser'] = 'kingking.firdaus';
$_SESSION['language'] = 'ID';
$_SESSION['access_type'] = 'detail';
$_SESSION['access_level'] = 1;

// Get all PEMASARAN menu items from database
$query = "
SELECT
    m1.id,
    m1.caption,
    m1.action,
    m2.caption as parent_menu,
    m1.type
FROM ".$dbname.".menu m1
LEFT JOIN ".$dbname.".menu m2 ON m1.parent = m2.id
WHERE m1.parent IN (349, 350, 353)
ORDER BY m1.parent, m1.urut
";

$result = mysql_query($query);

$menus = array(
    'Transaksi' => array(),
    'Laporan' => array(),
    'Setup' => array()
);

while($row = mysql_fetch_object($result)) {
    $menus[$row->parent_menu][] = array(
        'id' => $row->id,
        'caption' => $row->caption,
        'action' => $row->action,
        'type' => $row->type
    );
}

// Output as JSON for easy parsing
header('Content-Type: application/json');
echo json_encode($menus);
?>
