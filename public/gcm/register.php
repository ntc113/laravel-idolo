<?php 
$regId = $_GET['regId'];
 
$con = mysql_connect("127.0.0.1","usport_gcm","usportgcm@123");
if(!$con){
	die('MySQL connection failed'.mysql_error());
}

$db = mysql_select_db("usport_gcm",$con);
if(!$db){
	die('Database selection failed'.mysql_error());
}

$sql = "INSERT INTO tblRegistration (registration_id) values ('$regId')";

if(!mysql_query($sql, $con)){
	die('MySQL query failed'.mysql_error());
}
 
mysql_close($con);