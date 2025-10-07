<?
require_once('config/connection.php');

echo "
<nav class=\"navbar navbar-expand-lg navbar-dark bg-primary-custom mb-3\" style=\"background-color:#1E3A8A !important;\">
	<div class=\"container-fluid\">
		<button class=\"navbar-toggler\" type=\"button\" data-bs-toggle=\"collapse\" data-bs-target=\"#mainNavbar\"
				aria-controls=\"mainNavbar\" aria-expanded=\"false\" aria-label=\"Toggle navigation\">
			<span class=\"navbar-toggler-icon\"></span>
		</button>
		<div class=\"collapse navbar-collapse\" id=\"mainNavbar\">
			<ul class=\"navbar-nav me-auto mb-2 mb-lg-0\">";

//get menu for user by auth type or level
if($_SESSION['security']=='off')
{
	$ssq='';
}
else if($_SESSION['access_type']=='detail')
{
	$ssq=" and id in (".$_SESSION['allpriv'].")";
}
else
{
    $ssq=" and access_level >=".$_SESSION['standard']['access_level'];
}

if($_SESSION['language']=='EN'){
    $cell="id, type, class, caption2 as caption, action, access_level, parent, urut, hide, lastupdate, lastuser";
}
else if($_SESSION['language']=='MY'){
    $cell="id, type, class, caption3 as caption, action, access_level, parent, urut, hide, lastupdate, lastuser";
}
else{
     $cell="id, type, class, caption as caption, action, access_level, parent, urut, hide, lastupdate, lastuser";
}

// Recursive function to build menu
function buildMenu($parent_id, $level, $dbname, $cell, $ssq) {
    $str="select ".$cell." from ".$dbname.".menu
          where parent=".$parent_id."  ".$ssq."
          and hide=0 order by urut";
    $res=mysql_query($str);

    if(mysql_num_rows($res)==0) return;

    // Determine menu class based on level
    $ulClass = 'dropdown-menu';

    echo "<ul class=\"".$ulClass."\">";

    while($row=mysql_fetch_object($res)) {
        if($row->class=='devider') {
            echo "<li><hr class=\"dropdown-divider\"></li>";
        }
        else if($row->class=='title') {
            echo "<li><h6 class=\"dropdown-header\">".$row->caption."</h6></li>";
        }
        else {
            // Check if has children
            $str_check="select count(*) as cnt from ".$dbname.".menu where parent=".$row->id." ".$ssq." and hide=0";
            $res_check=mysql_query($str_check);
            $has_children = (mysql_fetch_object($res_check)->cnt > 0);

            if($has_children) {
                // Add 'dropend' class for all nested items (level >= 1)
                $liClass = ($level >= 1) ? 'dropend' : '';
                echo "<li class=\"".$liClass."\">
                        <a class=\"dropdown-item dropdown-toggle\" href=\"#\" id=\"menu".$row->id."\" data-bs-toggle=\"dropdown\" aria-expanded=\"false\">
                            <i class=\"bi bi-star-fill\" style=\"font-size:8px;color:#FFC107;\"></i> ".$row->caption."
                        </a>";
                buildMenu($row->id, $level+1, $dbname, $cell, $ssq);
                echo "</li>";
            }
            else {
                echo "<li><a class=\"dropdown-item\" href=\"javascript:do_load('".$row->action."')\">
                        <i class=\"bi bi-star-fill\" style=\"font-size:8px;color:#FFC107;\"></i> ".$row->caption."
                      </a></li>";
            }
        }
    }

    echo "</ul>";
}

// Build master menu (level 0)
$str_m1="select ".$cell." from ".$dbname.".menu
         where type='master' ".$ssq."
		 and hide=0 order by urut";
$res_m1=mysql_query($str_m1);

while($bar_m1=mysql_fetch_object($res_m1))
{
    $master_id=$bar_m1->id;
    echo "<li class=\"nav-item dropdown\">
            <a class=\"nav-link dropdown-toggle\" href=\"#\" id=\"menu".$master_id."\" role=\"button\" data-bs-toggle=\"dropdown\" aria-expanded=\"false\">
                ".strtoupper($bar_m1->caption)."
            </a>";

    buildMenu($master_id, 1, $dbname, $cell, $ssq);

    echo "</li>";
}

echo"
			</ul>
			<div class=\"d-flex align-items-center gap-2\">
				<span class='badge bg-light text-dark'>
					<i class='bi bi-person-circle'></i> ".$_SESSION['standard']['username']."
				</span>
				<span class='badge bg-info text-dark'>".$_SESSION['empl']['name']."</span>
				<span class='badge bg-light text-dark'>".$_SESSION['empl']['lokasitugas']."</span>
				<button class=\"btn btn-outline-light btn-sm ms-2\" onclick=\"logout()\" title=\"Logout\">
					<i class=\"bi bi-box-arrow-right\"></i> LOGOUT
				</button>
			</div>
		</div>
	</div>
</nav>

<div class=\"container-fluid\">
";
?>

<div id='progress' style='display:none;'>
	Please wait.....! <br>
	<img src='images/progress.gif'>
</div>

<?/*license here */if(MD5($_SESSION['org']['holding'])!='70f1d810d4bbb35fc7c9f84beaef04eb'){session_destroy();exit();} ?>
