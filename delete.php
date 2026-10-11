
<?php
require_once "dbcon.php";

$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    header("Location: index.php");
    exit;
}

$stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();

$deleted = $stmt->affected_rows > 0;
$stmt->close();

if ($deleted) {
    header("Location: index.php?msg=deleted");
} else {
    header("Location: index.php");
}
exit;
?>
