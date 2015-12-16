<?php
$str="select * from ".$dbname.".admin_list where username='".$_SESSION['standard']['username']."'";
If(mysql_num_rows(mysql_query($str))==0) {
	exit("Error: you are not administrator, please login as administrator");
}