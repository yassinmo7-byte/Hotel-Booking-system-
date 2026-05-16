
include("connection.php");


$action = "";
if (isset($_POST['action'])) $action = $_POST['action'];
elseif (isset($_GET['action'])) $action = $_GET['action'];


if ($action === "add_guest") {

    $name  = $_POST["Full_Name"] ?? "";
    $phone = $_POST["Guest_Phone"] ?? "";
    $email = $_POST["Guest_Email"] ?? "";
    $nat   = $_POST["Nationality"] ?? "";
    $addr  = $_POST["Address"] ?? "";
    $idnum = $_POST["ID_Document_Number"] ?? "";

    if (empty($name) || empty($phone) || empty($email) || empty($nat) || empty($addr) || empty($idnum)) {
        echo "<p style='color:red;'>Please fill all fields</p>";
    } else {

        //  Check if ID already exists
        $check = "SELECT * FROM guest WHERE ID_Document_Number = '$idnum'";
        $check_result = mysqli_query($conn, $check);

        if (mysqli_num_rows($check_result) > 0) {
            echo "<p style='color:red;'>Guest already exists (Duplicated ID Number)</p>";
        } else {

            // Insert new guest
            $sql = "INSERT INTO guest (Full_Name, Guest_Phone, Guest_Email, Nationality, Address, ID_Document_Number)
                    VALUES ('$name', '$phone', '$email', '$nat', '$addr', '$idnum')";

            if (mysqli_query($conn, $sql)) {
                echo "<p style='color:green;'>Guest added successfully!</p>";
            } else {
                echo "<p style='color:red;'>Error: " . mysqli_error($conn) . "</p>";
            }
        }
    }
}


if ($action === "search_guest") {

    $name = $_GET["q_name"] ?? "";

    if (empty($name)) {
        echo "<p style='color:red;'>Enter a name</p>";
    } else {

        $sql = "
        SELECT guest.*, booking.Room_ID
        FROM guest
        LEFT JOIN booking ON guest.Guest_ID = booking.Guest_ID
        WHERE guest.Full_Name LIKE '%$name%'
        ";

        $result = mysqli_query($conn, $sql);

        echo "<h3>Search Results:</h3>";

        if (!$result || mysqli_num_rows($result) == 0) {
            echo "<p style='color:red;'>No guests found</p>";
        } else {
            echo "<table border='1' cellpadding='8'>
                    <tr>
                        <th>ID</th><th>Name</th><th>Phone</th>
                        <th>Email</th><th>Booked Room</th>
                    </tr>
                ";

            while ($row = mysqli_fetch_assoc($result)) {

                $room = $row['Room_ID'] ? $row['Room_ID'] : "No Booking";

                echo "<tr>
                        <td>{$row['Guest_ID']}</td>
                        <td>{$row['Full_Name']}</td>
                        <td>{$row['Guest_Phone']}</td>
                        <td>{$row['Guest_Email']}</td>
                        <td>$room</td>
                    </tr>";
            }
            echo "</table>";
        }
    }
}


if ($action === "delete_guest") {

    $id = $_POST["Guest_ID"] ?? "";

    if (empty($id)) {
        echo "<p style='color:red;'>Enter Guest ID</p>";
    } else {

        //  Check if guest has a booking
        $check = "SELECT Room_ID FROM booking WHERE Guest_ID = '$id'";
        $res = mysqli_query($conn, $check);

        if (mysqli_num_rows($res) > 0) {
            $row = mysqli_fetch_assoc($res);
            $room_id = $row['Room_ID'];

            
            mysqli_query($conn, "DELETE FROM booking WHERE Guest_ID = '$id'");

           
            mysqli_query($conn, "UPDATE room SET Room_Status = 'Available' WHERE Room_ID = '$room_id'");
        }

        
        $sql = "DELETE FROM guest WHERE Guest_ID = '$id'";

        if (mysqli_query($conn, $sql)) {
            echo "<p style='color:green;'>Guest deleted and room updated if needed.</p>";
        } else {
            echo "<p style='color:red;'>Error: " . mysqli_error($conn) . "</p>";
        }
    }
}




// ADD ROOM

if ($action === "add_room") {

    $num   = $_POST["Room_Number"] ?? "";
    $type  = $_POST["Room_Type"] ?? "";
    $bed   = $_POST["Bed_Type"] ?? "";
    $price = $_POST["Price_Per_Night"] ?? "";
    $stat  = $_POST["Room_Status"] ?? "";

    if (empty($num) || empty($type) || empty($bed) || empty($price) || empty($stat)) {
        echo "<p style='color:red;'>Please fill all fields</p>";
    } else {

        $sql = "INSERT INTO room (Room_Number, Room_Type, Bed_Type, Price_Per_Night, Room_Status)
                VALUES ('$num', '$type', '$bed', '$price', '$stat')";

        if (mysqli_query($conn, $sql)) {
            echo "<p style='color:green;'>Room added successfully!</p>";
        } else {
            echo "<p style='color:red;'>Error: " . mysqli_error($conn) . "</p>";
        }
    }
}




// UPDATE ROOM

if ($action === "update_room") {

    $id    = $_POST["Room_ID"] ?? "";
    $num   = $_POST["Room_Number"] ?? "";
    $type  = $_POST["Room_Type"] ?? "";
    $bed   = $_POST["Bed_Type"] ?? "";
    $price = $_POST["Price_Per_Night"] ?? "";
    $stat  = $_POST["Room_Status"] ?? "";

    if (empty($id) && empty($num)) {
        echo "<p style='color:red;'>Enter Room ID or Room Number</p>";
    } else {

        $where = !empty($id) ? "Room_ID='$id'" : "Room_Number='$num'";

        $sql = "UPDATE room SET 
                Room_Type='$type',
                Bed_Type='$bed',
                Price_Per_Night='$price',
                Room_Status='$stat'
                WHERE $where";

        if (mysqli_query($conn, $sql)) {
            echo "<p style='color:green;'>Room updated.</p>";
        } else {
            echo "<p style='color:red;'>Error: " . mysqli_error($conn) . "</p>";
        }
    }
}




// SEARCH AVAILABLE ROOMS (GET)

if ($action === "search_available_rooms") {

    $sql = "SELECT * FROM room WHERE Room_Status='Available'";

    if (!empty($_GET["min_price"])) {
        $min = floatval($_GET["min_price"]);
        $sql .= " AND Price_Per_Night >= $min";
    }

    if (!empty($_GET["max_price"])) {
        $max = floatval($_GET["max_price"]);
        $sql .= " AND Price_Per_Night <= $max";
    }

    $result = mysqli_query($conn, $sql);

    echo "<h3>Available Rooms:</h3>";

    if (!$result || mysqli_num_rows($result) == 0) {
        echo "<p style='color:red;'>No rooms found</p>";
    } else {
        echo "<table border='1' cellpadding='8'>
                <tr>
                    <th>ID</th><th>Number</th><th>Type</th><th>Bed</th><th>Price</th>
                </tr>";

        while ($r = mysqli_fetch_assoc($result)) {
            echo "<tr>
                    <td>{$r['Room_ID']}</td>
                    <td>{$r['Room_Number']}</td>
                    <td>{$r['Room_Type']}</td>
                    <td>{$r['Bed_Type']}</td>
                    <td>{$r['Price_Per_Night']}</td>
                 </tr>";
        }
        echo "</table>";
    }
}


// MAKE BOOKING 

if ($action === "make_booking") {

    $guest  = $_POST["Guest_ID"] ?? "";
    $room   = $_POST["Room_ID"] ?? "";
    $in     = $_POST["CheckIn_Date"] ?? "";
    $out    = $_POST["CheckOut_Date"] ?? "";
    $guests = $_POST["Number_of_Guests"] ?? "";
    $nights = $_POST["Number_of_Nights"] ?? "";
    $total  = $_POST["Total_Price"] ?? "";

    if (empty($guest) || empty($room) || empty($in) || empty($out)) {
        echo "<p style='color:red;'>Fill all fields</p>";
    } else {

        // Check if Guest exists
        $check_guest = "SELECT Guest_ID FROM guest WHERE Guest_ID = '$guest'";
        $guest_result = mysqli_query($conn, $check_guest);

        if (mysqli_num_rows($guest_result) == 0) {
            echo "<p style='color:red;'>Guest ID does not exist!</p>";
            exit;
        }

        // Check if Room is Available
        $check_room = "SELECT Room_Status FROM room WHERE Room_ID = '$room'";
        $room_result = mysqli_query($conn, $check_room);

        if (mysqli_num_rows($room_result) == 0) {
            echo "<p style='color:red;'>Room ID does not exist!</p>";
            exit;
        }

        $room_data = mysqli_fetch_assoc($room_result);

        if ($room_data['Room_Status'] !== 'Available') {
            echo "<p style='color:red;'>Room is already booked or not available!</p>";
            exit;
        }

        
        $sql = "INSERT INTO booking 
                (Guest_ID, Room_ID, CheckIn_Date, CheckOut_Date, Number_of_Guests, Number_of_Nights, Total_Price, Booking_Status)
                VALUES ('$guest', '$room', '$in', '$out', '$guests', '$nights', '$total', 'Confirmed')";

        if (mysqli_query($conn, $sql)) {

            
            $booking_id = mysqli_insert_id($conn);

            //  Update Room Status to "Booked"
            mysqli_query($conn, "UPDATE room SET Room_Status='Booked' WHERE Room_ID='$room'");

            //  Add Payment Record (Pending or Paid based on user choice)
            $payment_method = $_POST["Payment_Method"] ?? "Cash";
            $payment_status = $_POST["Payment_Status"] ?? "Pending"; 
            $payment_date = date('Y-m-d H:i:s');
            
            $payment_sql = "INSERT INTO payment 
                            (Booking_ID, Payment_Date, Amount_Paid, Payment_Method, Payment_Status)
                            VALUES ('$booking_id', '$payment_date', '$total', '$payment_method', '$payment_status')";
            
            mysqli_query($conn, $payment_sql);

            echo "<p style='color:green;'>✅ Booking created successfully! Payment record added.</p>";
        } else {
            echo "<p style='color:red;'>Error: " . mysqli_error($conn) . "</p>";
        }
    }
}





if ($action === "update_payment") {

    $payment_id = $_POST["Payment_ID"] ?? "";
    $status = $_POST["Payment_Status"] ?? "";

    if (empty($payment_id) || empty($status)) {
        echo "<p style='color:red;'>Enter Payment ID and Status</p>";
    } else {

        //  Check if Payment exists
        $check = "SELECT * FROM payment WHERE Payment_ID = '$payment_id'";
        $result = mysqli_query($conn, $check);

        if (mysqli_num_rows($result) == 0) {
            echo "<p style='color:red;'>Payment ID not found!</p>";
            exit;
        }

        //  Update Payment Status
        $sql = "UPDATE payment SET Payment_Status = '$status' WHERE Payment_ID = '$payment_id'";

        if (mysqli_query($conn, $sql)) {
            echo "<p style='color:green;'>✅ Payment status updated to: $status</p>";
        } else {
            echo "<p style='color:red;'>Error: " . mysqli_error($conn) . "</p>";
        }
    }
}




// CANCEL BOOKING 

if ($action === "cancel_booking") {

    $id = $_POST["Booking_ID"] ?? "";

    if (empty($id)) {
        echo "<p style='color:red;'>Enter Booking ID</p>";
    } else {

        //  Get Room_ID from Booking
        $get_room = "SELECT Room_ID FROM booking WHERE Booking_ID = '$id'";
        $result = mysqli_query($conn, $get_room);

        if (mysqli_num_rows($result) == 0) {
            echo "<p style='color:red;'>Booking ID not found!</p>";
            exit;
        }

        $booking_data = mysqli_fetch_assoc($result);
        $room_id = $booking_data['Room_ID'];

        //  Update Booking Status to "Cancelled"
        $sql = "UPDATE booking SET Booking_Status='Cancelled' WHERE Booking_ID='$id'";

        if (mysqli_query($conn, $sql)) {

            //  Set Room back to "Available"
            mysqli_query($conn, "UPDATE room SET Room_Status='Available' WHERE Room_ID='$room_id'");

            //  Update Payment Status to "Cancelled"
            mysqli_query($conn, "UPDATE payment SET Payment_Status='Cancelled' WHERE Booking_ID='$id'");

            echo "<p style='color:green;'>✅ Booking cancelled, room is available, and payment cancelled!</p>";
        } else {
            echo "<p style='color:red;'>Error: " . mysqli_error($conn) . "</p>";
        }
    }
}


// VIEW PAYMENTS (GET)

if ($action === "view_all_payments") {

    $result = mysqli_query($conn, "SELECT * FROM payment");

    echo "<h3>Payments List:</h3>";

    if (!$result || mysqli_num_rows($result) == 0) {
        echo "<p style='color:red;'>No payments</p>";
    } else {

        echo "<table border='1' cellpadding='8'>
                <tr><th>ID</th><th>Booking</th><th>Amount</th><th>Status</th></tr>";

        while ($p = mysqli_fetch_assoc($result)) {
            echo "<tr>
                    <td>{$p['Payment_ID']}</td>
                    <td>{$p['Booking_ID']}</td>
                    <td>{$p['Amount_Paid']}</td>
                    <td>{$p['Payment_Status']}</td>
                  </tr>";
        }
        echo "</table>";
    }
}



// GUEST BOOKING HISTORY (GET)

if ($action === "guest_booking_history") {

    $guestID = $_GET["Guest_ID"] ?? "";
    $email   = $_GET["Guest_Email"] ?? "";

    if (!empty($guestID)) {
        $sql = "SELECT * FROM booking WHERE Guest_ID='$guestID'";
    } elseif (!empty($email)) {
        $sql = "SELECT booking.* 
                FROM booking, guest 
                WHERE guest.Guest_ID = booking.Guest_ID
                AND guest.Guest_Email = '$email'";
    } else {
        echo "<p style='color:red;'>Enter Guest ID or Email</p>";
        exit;
    }

    $result = mysqli_query($conn, $sql);

    echo "<h3>Booking History:</h3>";

    if (!$result || mysqli_num_rows($result) == 0) {
        echo "<p style='color:red;'>No bookings found</p>";
    } else {

        echo "<table border='1' cellpadding='8'>
                <tr>
                    <th>ID</th><th>Room</th><th>Check-In</th><th>Check-Out</th><th>Status</th>
                </tr>";

        while ($b = mysqli_fetch_assoc($result)) {
            echo "<tr>
                    <td>{$b['Booking_ID']}</td>
                    <td>{$b['Room_ID']}</td>
                    <td>{$b['CheckIn_Date']}</td>
                    <td>{$b['CheckOut_Date']}</td>
                    <td>{$b['Booking_Status']}</td>
                 </tr>";
        }

        echo "</table>";
    }
}



 echo "<a href='index.html' style='display:inline-block; margin-top:10px; text-decoration:none; background:#007bff; color:white; padding:8px 15px; border-radius:5px;'>🔙 Back to Home</a>";
