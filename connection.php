<?php
$db_server = "localhost";
$db_user   = "root";
$db_pass   = "";
//$db_name   = "students";
$db_name   = "hotel_booking_system";


mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = mysqli_connect($db_server, $db_user, $db_pass, $db_name);
    
 } catch (mysqli_sql_exception $e) {
    
    die("Connection failed: " . $e->getMessage());
}
?> 


