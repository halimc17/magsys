<?
/**
 * KEBUN MODULE MENU TEST SCRIPT - SIMPLE VERSION
 * Tests all menu items in the KEBUN module by checking file existence
 */

// Get menu data from database
require_once('config/connection.php');

?>
<!DOCTYPE html>
<html>
<head>
<title>KEBUN Module Test Report</title>
<style>
body { font-family: Arial, sans-serif; margin: 20px; background-color: #f5f5f5; }
h1 { color: #1E3A8A; }
h2 { color: #1E40AF; margin-top: 30px; }
table { border-collapse: collapse; width: 100%; margin-top: 10px; background-color: white; }
th, td { border: 1px solid #ddd; padding: 8px; text-align: left; font-size: 12px; }
th { background-color: #1E3A8A; color: white; }
tr:nth-child(even) { background-color: #f2f2f2; }
.success { background-color: #d4edda; color: #155724; font-weight: bold; }
.failed { background-color: #f8d7da; color: #721c24; font-weight: bold; }
.skipped { background-color: #fff3cd; color: #856404; font-weight: bold; }
.summary { margin-top: 30px; padding: 20px; background-color: #e7f3ff; border-left: 4px solid #1E3A8A; }
.notes { font-size: 11px; color: #666; }
</style>
</head>
<body>

<h1>KEBUN MODULE TEST REPORT</h1>
<p>Generated: <?=date('Y-m-d H:i:s')?></p>

<?
// Get all kebun menu items from database
$categories = array(
    338 => 'TRANSAKSI',
    339 => 'LAPORAN',
    340 => 'PROSES',
    342 => 'SETUP'
);

$total = 0;
$success = 0;
$failed = 0;
$skipped = 0;
$all_failed = array();

foreach ($categories as $parent_id => $category_name) {
    echo "<h2>$category_name</h2>\n";
    echo "<table>\n";
    echo "<tr><th>#</th><th>ID</th><th>Menu Name</th><th>Action/File</th><th>Hidden</th><th>Status</th><th>Notes</th></tr>\n";

    // Get menu items for this category
    $sql = "SELECT id, caption, action, hide FROM ".$dbname.".menu
            WHERE parent=$parent_id
            ORDER BY urut, id";
    $res = mysql_query($sql);

    $counter = 1;
    while ($row = mysql_fetch_object($res)) {
        $total++;
        $status = '';
        $status_class = '';
        $notes = '';
        $file = $row->action . '.php';
        $filepath = __DIR__ . '/' . $file;

        // Test the menu item
        if ($row->hide == 1) {
            $status = 'SKIPPED';
            $status_class = 'skipped';
            $notes = 'Menu item is hidden';
            $skipped++;
        }
        else if ($row->action == 'NULL' || $row->action == '' || $row->action == 'Action...') {
            $status = 'SKIPPED';
            $status_class = 'skipped';
            $notes = 'No action defined (placeholder)';
            $skipped++;
        }
        else if (file_exists($filepath)) {
            $status = 'SUCCESS';
            $status_class = 'success';
            $notes = 'File exists';
            $success++;
        }
        else {
            $status = 'FAILED';
            $status_class = 'failed';
            $notes = 'File not found: ' . $file;
            $failed++;
            $all_failed[] = array(
                'category' => $category_name,
                'id' => $row->id,
                'caption' => $row->caption,
                'file' => $file,
                'notes' => $notes
            );
        }

        echo "<tr>";
        echo "<td>$counter</td>";
        echo "<td>{$row->id}</td>";
        echo "<td>{$row->caption}</td>";
        echo "<td>$file</td>";
        echo "<td>" . ($row->hide == 1 ? 'YES' : 'NO') . "</td>";
        echo "<td class='$status_class'>$status</td>";
        echo "<td class='notes'>$notes</td>";
        echo "</tr>\n";

        $counter++;
    }

    echo "</table>\n";
}

// Summary
echo "<div class='summary'>\n";
echo "<h2>TEST SUMMARY</h2>\n";
echo "<p><strong>Total Menu Items Tested:</strong> $total</p>\n";

if ($total > 0) {
    $success_pct = round(($success/$total)*100, 2);
    $failed_pct = round(($failed/$total)*100, 2);
    $skipped_pct = round(($skipped/$total)*100, 2);

    echo "<p><strong>Successful:</strong> $success ($success_pct%)</p>\n";
    echo "<p><strong>Failed:</strong> $failed ($failed_pct%)</p>\n";
    echo "<p><strong>Skipped (Hidden/No Action):</strong> $skipped ($skipped_pct%)</p>\n";
}
echo "</div>\n";

// Failed items detail
if ($failed > 0) {
    echo "<h2>FAILED ITEMS DETAIL</h2>\n";
    echo "<table>\n";
    echo "<tr><th>Category</th><th>Menu ID</th><th>Menu Name</th><th>File</th><th>Error Details</th></tr>\n";

    foreach ($all_failed as $item) {
        echo "<tr>";
        echo "<td>{$item['category']}</td>";
        echo "<td>{$item['id']}</td>";
        echo "<td>{$item['caption']}</td>";
        echo "<td>{$item['file']}</td>";
        echo "<td>{$item['notes']}</td>";
        echo "</tr>\n";
    }

    echo "</table>\n";
}

?>

</body>
</html>
