<?php
// Database config
$host = 'localhost';
$db   = 'db_cpsuppeiv2';
$user = 'root';
$pass = 'r@@t';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set headers to download the SQL file
header('Content-Type: application/sql');
header('Content-Disposition: attachment; filename="enduser_property_split_export.sql"');

echo "-- SQL Export generated from enduser_property_split on " . date('Y-m-d H:i:s') . "\n\n";

$sql = "SELECT * FROM enduser_property_split ORDER BY id ASC";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $qty = (int)$row['qty'];
        $serials = array_map('trim', explode(';', $row['serial_number']));
        $itemCost = floatval(str_replace(',', '', $row['item_cost']));
        $itemCostFormatted = number_format($itemCost, 2, '.', '');
        $totalCostFormatted = number_format($itemCost, 2, '.', ''); // each row gets same cost
        $basePropNo = $row['property_no_generated'];
        
        for ($i = 0; $i < $qty; $i++) {
            $serial = isset($serials[$i]) && $serials[$i] !== '' ? $serials[$i] : 'N/A';
            $suffix = chr(65 + $i); // A, B, C...
            $newPropNo = $basePropNo . '-' . $suffix;

            echo "INSERT INTO `enduser_property_split` (`purch_id`, `office_id`, `location`, `item_id`, `item_descrip`, `item_model`, `serial_number`, `date_acquired`, `unit_id`, `qty`, `item_cost`, `total_cost`, `properties_id`, `categories_id`, `property_id`, `item_number`, `property_no_generated`, `selected_account_id`, `status`, `remarks`, `date_stat`, `price_stat`, `person_accnt`, `person_accnt1`, `serial_owned`, `person_accnt_name`, `print_stat`, `date_return`, `supply_type`, `created_at`, `updated_at`) VALUES ("
                . (is_null($row['purch_id']) ? "NULL" : "'" . $conn->real_escape_string($row['purch_id']) . "'") . ", "
                . (is_null($row['office_id']) ? "NULL" : "'" . $conn->real_escape_string($row['office_id']) . "'") . ", "
                . (is_null($row['location']) ? "NULL" : "'" . $conn->real_escape_string($row['location']) . "'") . ", "
                . (is_null($row['item_id']) ? "NULL" : "'" . $conn->real_escape_string($row['item_id']) . "'") . ", "
                . "'" . $conn->real_escape_string($row['item_descrip']) . "', "
                . "'" . $conn->real_escape_string($row['item_model']) . "', "
                . "'" . $conn->real_escape_string($serial) . "', "
                . "'" . $conn->real_escape_string($row['date_acquired']) . "', "
                . "'" . $conn->real_escape_string($row['unit_id']) . "', "
                . "'1', "
                . "'$itemCostFormatted', "
                . "'$totalCostFormatted', "
                . "'" . $conn->real_escape_string($row['properties_id']) . "', "
                . "'" . $conn->real_escape_string($row['categories_id']) . "', "
                . "'" . $conn->real_escape_string($row['property_id']) . "', "
                . "'" . $conn->real_escape_string($row['item_number']) . "', "
                . "'" . $conn->real_escape_string($newPropNo) . "', "
                . "'" . $conn->real_escape_string($row['selected_account_id']) . "', "
                . "'" . $conn->real_escape_string($row['status']) . "', "
                . "'" . $conn->real_escape_string($row['remarks']) . "', "
                . (is_null($row['date_stat']) ? "NULL" : "'" . $conn->real_escape_string($row['date_stat']) . "'") . ", "
                . "'" . $conn->real_escape_string($row['price_stat']) . "', "
                . "'" . $conn->real_escape_string($row['person_accnt']) . "', "
                . (is_null($row['person_accnt1']) ? "NULL" : "'" . $conn->real_escape_string($row['person_accnt1']) . "'") . ", "
                . (is_null($row['serial_owned']) ? "NULL" : "'" . $conn->real_escape_string($row['serial_owned']) . "'") . ", "
                . "'" . $conn->real_escape_string($row['person_accnt_name']) . "', "
                . "'" . $conn->real_escape_string($row['print_stat']) . "', "
                . (is_null($row['date_return']) ? "NULL" : "'" . $conn->real_escape_string($row['date_return']) . "'") . ", "
                . "'" . $conn->real_escape_string($row['supply_type']) . "', "
                . (is_null($row['created_at']) ? "NULL" : "'" . $conn->real_escape_string($row['created_at']) . "'") . ", "
                . (is_null($row['updated_at']) ? "NULL" : "'" . $conn->real_escape_string($row['updated_at']) . "'")
                . ");\n";
        }
    }
} else {
    echo "-- No data found.\n";
}

$conn->close();
exit;
?>
🧪 Next Step:

- Save this as export_enduser_property_split.php
- Update your DB login credentials
- Run it from your browser (e.g. http://localhost/export_enduser_property_split.php)
- It will download a .sql file containing all split rows

Let me know if you want to include a LIMIT or filter, or redirect this output to a folder instead of download.
