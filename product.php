<!DOCTYPE html>
<html>
<body>

<?php
require_once 'marketplace_common.php';

$db = marketplace_db();

$sql = "SELECT user_id, username, email FROM users";
$result = $db->query($sql);


if ($result->num_rows > 0) {
    // output data of each row
    while($row = $result->fetch_assoc()) {
        print "<br> id: ". $row["user_id"]. "<br> - Name: ". $row["username"]. "<br> - Email: " . $row["email"] . "<br>";
     
    }
} else {
    print "0 results";
}


        ?> 



</body>
</html>
