<?
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
session_start();

OPEN_BODY('Test Menu Dropdown');
require_once('master_mainMenu.php');

OPEN_BOX('', 'Test Nested Dropdown Menu');
?>

<div class="alert alert-info">
    <h5>Test Instructions:</h5>
    <ol>
        <li>Hover over any main menu item (MASTER, BUDGET, etc.)</li>
        <li>Look for submenu items with a triangle arrow (►)</li>
        <li>Hover over those submenu items</li>
        <li>The nested submenu should appear to the right of the parent submenu</li>
    </ol>

    <p class="mt-3"><strong>Expected Behavior:</strong></p>
    <ul>
        <li>Main menu dropdown appears below the main menu item</li>
        <li>Nested submenu (level 2+) appears to the RIGHT of its parent item</li>
        <li>All menus stay visible when hovering between parent and child</li>
    </ul>
</div>

<div class="card mt-3">
    <div class="card-header">Browser Console Log</div>
    <div class="card-body">
        <p>Press <kbd>F12</kbd> to open browser Developer Tools and check the Console tab for any errors.</p>
    </div>
</div>

<?
CLOSE_BOX();
CLOSE_BODY();
?>
