<?php
require_once('config/connection.php');
echo"<div class='position-fixed top-0 end-0 p-2 bg-light' style='z-index:1030;margin-top:45px;margin-right:15px;'>
		<span class='badge bg-primary'>
			<i class='bi bi-person-circle'></i> ".$_SESSION['standard']['username']."
		</span>
		<span class='badge bg-info text-dark'>".$_SESSION['empl']['name']."</span>
		<span class='badge bg-secondary'>".$_SESSION['empl']['lokasitugas']."</span>
	</div>";
echo "
<nav class=\"navbar navbar-expand-lg navbar-dark bg-primary-custom mb-3\" style=\"background-color:#275370 !important;\">
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


$arrow_location="<img src='images/menu/arrow_4.gif' class=arrow>";
$str_m1="select ".$cell." from ".$dbname.".menu
         where type='master' ".$ssq."
		 and hide=0 order by urut";

$res_m1=mysql_query($str_m1);
while($bar_m1=mysql_fetch_object($res_m1))
{
        $master_id=$bar_m1->id;
        echo"<li class=\"nav-item dropdown\">
                <a class=\"nav-link dropdown-toggle\" href=\"#\" id=\"menu".$master_id."\" role=\"button\" data-bs-toggle=\"dropdown\" aria-expanded=\"false\">
                    ".strtoupper($bar_m1->caption)."
                </a>";
        //=======================================================
                $str_m2="select ".$cell." from ".$dbname.".menu
                         where parent=".$master_id."  ".$ssq."
                                  and hide=0 order by urut";
                $res_m2=mysql_query($str_m2);
                if(mysql_num_rows($res_m2)>0)
                {
                        echo"<ul class=\"dropdown-menu\" aria-labelledby=\"menu".$master_id."\">";
                        while($bar_m2=mysql_fetch_object($res_m2))
                        {
                                $master_m2=$bar_m2->id;
                                if($bar_m2->class=='devider')
                                  echo"<li><hr class=\"dropdown-divider\"></li>";
                                else if($bar_m2->class=='title')
                                  echo"<li><h6 class=\"dropdown-header\">".$bar_m2->caption."</h6></li>";
                            else
                                        {

                                                if($bar_m2->type=='parent')
                                                {
                                                 echo "<li class=\"dropend\"><a class=\"dropdown-item dropdown-toggle\" href=\"#\" id=\"menu".$master_m2."\" data-bs-toggle=\"dropdown\" aria-expanded=\"false\">
                                                         <i class=\"bi bi-star-fill\" style=\"font-size:8px;color:#FFC107;\"></i> ".$bar_m2->caption."
                                                      </a>";
                                                     //===============================================
                                                         $str_m3="select ".$cell." from ".$dbname.".menu
                                                                 where parent=$master_m2  ".$ssq."
                                                                          and hide=0 order by urut";
                                                        $res_m3=mysql_query($str_m3);
                                                        if(mysql_num_rows($res_m3)>0)
                                                        {
                                                                echo"<ul class=\"dropdown-menu\" aria-labelledby=\"menu".$master_m2."\">";
                                                                while($bar_m3=mysql_fetch_object($res_m3))
                                                                {
                                                                        $master_m3=$bar_m3->id;
                                                                        if($bar_m3->class=='devider')
                                                                          echo"<li><hr class=\"dropdown-divider\"></li>";
                                                                        else if($bar_m3->class=='title')
                                                                          echo"<li><h6 class=\"dropdown-header\">".$bar_m3->caption."</h6></li>";
                                                                    else
                                                                                {

                                                                                        if($bar_m3->type=='parent')
                                                                                        {
                                                                                        echo "<li class=\"dropend\"><a class=\"dropdown-item dropdown-toggle\" href=\"#\" id=\"menu".$master_m3."\" data-bs-toggle=\"dropdown\" aria-expanded=\"false\">
                                                                                                <i class=\"bi bi-star-fill\" style=\"font-size:8px;color:#FFC107;\"></i> ".$bar_m3->caption."
                                                                                              </a>";
                                                                                             //===============================================
                                                                                                 $str_m4="select ".$cell." from ".$dbname.".menu
                                                                                                         where parent=$master_m3  ".$ssq."
                                                                                                                  and hide=0 order by urut";

                                                                                                $res_m4=mysql_query($str_m4);
                                                                                                if(mysql_num_rows($res_m4)>0)
                                                                                                {
                                                                                                        echo"<ul class=\"dropdown-menu\" aria-labelledby=\"menu".$master_m3."\">";
                                                                                                        while($bar_m4=mysql_fetch_object($res_m4))
                                                                                                        {
                                                                                                                $master_m4=$bar_m4->id;
                                                                                                                if($bar_m4->class=='devider')
                                                                                                                  echo"<li><span class=\"qmdivider qmdividerx\" ></span></li>";
                                                                                                                else if($bar_m4->class=='title')
                                                                                                                  echo"<li><span class=\"qmtitle\" >".$bar_m4->caption."</span></li>";
                                                                                                            else
                                                                                                                  {
                                                                                                                        if($bar_m4->type=='parent')
                                                                                                                        {
                                                                                                                        echo "<li><a class=\"qmparent\" href=\"javascript:void(0);\"><img src=images/menu/star.png style='border:0px;vertical-align:middle;height:11px'> ".$bar_m4->caption."  ".$arrow_location."</a>";
                                                                                                                             //===============================================
                                                                                                                                 $str_m5="select ".$cell." from ".$dbname.".menu
                                                                                                                                         where parent=$master_m4  ".$ssq."
                                                                                                                                                  and hide=0 order by urut";
                                                                                                                                $res_m5=mysql_query($str_m5);
                                                                                                                                if(mysql_num_rows($res_m5)>0)
                                                                                                                                {
                                                                                                                                        echo"<ul>";
                                                                                                                                        while($bar_m5=mysql_fetch_object($res_m5))
                                                                                                                                        {
                                                                                                                                                $master_m5=$bar_m5->id;
                                                                                                                                                if($bar_m5->class=='devider')
                                                                                                                                                  echo"<li><span class=\"qmdivider qmdividerx\" ></span></li>";
                                                                                                                                                else if($bar_m5->class=='title')
                                                                                                                                                  echo"<li><span class=\"qmtitle\" >".$bar_m5->caption."</span></li>";
                                                                                                                                            else
                                                                                                                                                  {
                                                                                                                                                                if($bar_m5->type=='parent')
                                                                                                                                                                {
                                                                                                                                                                echo "<li><a class=\"qmparent\" href=\"javascript:void(0);\"><img src=images/menu/star.png style='border:0px;vertical-align:middle;height:11px'> ".$bar_m5->caption."  ".$arrow_location."</a>";
                                                                                                                                                                     //===============================================
                                                                                                                                                                         $str_m6="select ".$cell." from ".$dbname.".menu
                                                                                                                                                                                 where parent=$master_m5   ".$ssq."
                                                                                                                                                                                          and hide=0 order by urut";
                                                                                                                                                                        $res_m6=mysql_query($str_m6);
                                                                                                                                                                        if(mysql_num_rows($res_m6)>0)
                                                                                                                                                                        {
                                                                                                                                                                                echo"<ul>";
                                                                                                                                                                                while($bar_m6=mysql_fetch_object($res_m6))
                                                                                                                                                                                {
                                                                                                                                                                                        $master_m6=$bar_m6->id;
                                                                                                                                                                                        if($bar_m6->class=='devider')
                                                                                                                                                                                          echo"<li><span class=\"qmdivider qmdividerx\" ></span></li>";
                                                                                                                                                                                        else if($bar_m6->class=='title')
                                                                                                                                                                                          echo"<li><span class=\"qmtitle\" >".$bar_m6->caption."</span></li>";
                                                                                                                                                                                    else
                                                                                                                                                                                          {
                                                                                                                                                                                                if($bar_m6->type=='parent')
                                                                                                                                                                                                {
                                                                                                                                                                                                echo "<li><a class=\"qmparent\" href=\"javascript:void(0);\"><img src=images/menu/star.png style='border:0px;vertical-align:middle;height:11px'> ".$bar_m6->caption."  ".$arrow_location."</a>";
                                                                                                                                                                                                     //===============================================
                                                                                                                                                                                                         $str_m7="select ".$cell." from ".$dbname.".menu
                                                                                                                                                                                                                 where parent=$master_m6  ".$ssq."
                                                                                                                                                                                                                          and hide=0 order by urut";
                                                                                                                                                                                                        $res_m7=mysql_query($str_m7);
                                                                                                                                                                                                        if(mysql_num_rows($res_m7)>0)
                                                                                                                                                                                                        {
                                                                                                                                                                                                                echo"<ul>";
                                                                                                                                                                                                                while($bar_m7=mysql_fetch_object($res_m7))
                                                                                                                                                                                                                {
                                                                                                                                                                                                                        $master_m7=$bar_m7->id;
                                                                                                                                                                                                                        if($bar_m7->class=='devider')
                                                                                                                                                                                                                          echo"<li><span class=\"qmdivider qmdividerx\" ></span></li>";
                                                                                                                                                                                                                        else if($bar_m7->class=='title')
                                                                                                                                                                                                                          echo"<li><span class=\"qmtitle\" >".$bar_m7->caption."</span></li>";
                                                                                                                                                                                                                    else
                                                                                                                                                                                                                          echo "<li><a href=\"javascript:do_load('".$bar_m7->action."')\"><img src=images/menu/star.png style='border:0px;vertical-align:middle;height:11px'> ".$bar_m7->caption."</a></li>";
                                                                                                                                                                                                                }
                                                                                                                                                                                                                echo"</ul>";
                                                                                                                                                                                                        }
                                                                                                                                                                                                         //===============================================
                                                                                                                                                                                                echo "</li>";
                                                                                                                                                                                                }
                                                                                                                                                                                                else
                                                                                                                                                                                                {
                                                                                                                                                                                                 echo "<li><a href=\"javascript:do_load('".$bar_m6->action."')\"><img src=images/menu/star.png style='border:0px;vertical-align:middle;height:11px'> ".$bar_m6->caption."</a></li>";
                                                                                                                                                                                                }
                                                                                                                                                                                          }
                                                                                                                                                                                }
                                                                                                                                                                                echo"</ul>";
                                                                                                                                                                        }
                                                                                                                                                                         //===============================================
                                                                                                                                                                echo "</li>";
                                                                                                                                                                }
                                                                                                                                                                else
                                                                                                                                                                {
                                                                                                                                                                 echo "<li><a href=\"javascript:do_load('".$bar_m5->action."')\"><img src=images/menu/star.png style='border:0px;vertical-align:middle;height:11px'> ".$bar_m5->caption."</a></li>";
                                                                                                                                                                }
                                                                                                                                                  }

                                                                                                                                        }
                                                                                                                                        echo"</ul>";
                                                                                                                                }
                                                                                                                                 //===============================================
                                                                                                                        echo "</li>";
                                                                                                                        }
                                                                                                                        else
                                                                                                                        {
                                                                                                                         echo "<li><a href=\"javascript:do_load('".$bar_m4->action."')\"><img src=images/menu/star.png style='border:0px;vertical-align:middle;height:11px'> ".$bar_m4->caption."</a></li>";
                                                                                                                        }

                                                                                                                  }

                                                                                                        }
                                                                                                        echo"</ul>";
                                                                                                }
                                                                                                 //===============================================
                                                                                        echo "</li>";
                                                                                        }
                                                                                        else
                                                                                        {
                                                                                         echo "<li><a class=\"dropdown-item\" href=\"javascript:do_load('".$bar_m3->action."')\">
                                                                                                 <i class=\"bi bi-star-fill\" style=\"font-size:8px;color:#FFC107;\"></i> ".$bar_m3->caption."
                                                                                               </a></li>";
                                                                                        }

                                                                                }
                                                                }
                                                                echo"</ul>";
                                                        }
                                                         //===============================================
                                                echo "</li>";

                                                }
                                                else
                                                {
                                                 echo "<li><a class=\"dropdown-item\" href=\"javascript:do_load('".$bar_m2->action."')\">
                                                         <i class=\"bi bi-star-fill\" style=\"font-size:8px;color:#FFC107;\"></i> ".$bar_m2->caption."
                                                       </a></li>";
                                                }

                                        }
                        }
                        echo"</ul>";
                }
        //=========================================================
        echo"</li>";
}
echo"
			</ul>
			<div class=\"d-flex\">
				<button class=\"btn btn-outline-light btn-sm\" onclick=\"logout()\" title=\"Logout\">
					<i class=\"bi bi-box-arrow-right\"></i> LOGOUT
				</button>
			</div>
		</div>
	</div>
</nav>
";
?>

<div id='progress' style='display:none;'>
	Please wait.....! <br>
	<img src='images/progress.gif'>
</div>

<?php /*license here */if(MD5($_SESSION['org']['holding'])!='70f1d810d4bbb35fc7c9f84beaef04eb'){session_destroy();exit();} ?>
