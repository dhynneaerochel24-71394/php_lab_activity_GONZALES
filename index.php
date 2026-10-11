
<?php
// DATABASE CONNECTION
require_once __DIR__ . '/dbcon.php';

$error = "";

// GET EDIT USER ID
$edit_id = filter_input(INPUT_GET, 'edit', FILTER_VALIDATE_INT);
$edit_id = ($edit_id && $edit_id > 0) ? $edit_id : 0;

// CREATE OR UPDATE USER
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    $id = ($id && $id > 0) ? $id : 0;

    if ($name === "" || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid name and email.";
        $edit_id = $id;
    } else {

        if ($id > 0) {
            // UPDATE USER
            $stmt = $conn->prepare(
                "UPDATE users SET name = ?, email = ? WHERE id = ?"
            );
            $stmt->bind_param("ssi", $name, $email, $id);
            $success_msg = "updated";
        } else {
            // ADD NEW USER
            $stmt = $conn->prepare(
                "INSERT INTO users (name, email) VALUES (?, ?)"
            );
            $stmt->bind_param("ss", $name, $email);
            $success_msg = "added";
        }

        if ($stmt->execute()) {
            $stmt->close();
            header("Location: index.php?msg=" . $success_msg);
            exit();
        } else {
            $error = "Unable to save user. Please try again.";
            $edit_id = $id;
            $stmt->close();
        }
    }
}

// DEFAULT FORM VALUES
$form_name = "";
$form_email = "";

// LOAD SELECTED USER FOR EDITING
if ($edit_id > 0) {

    $stmt = $conn->prepare(
        "SELECT name, email FROM users WHERE id = ?"
    );
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $stmt->bind_result($db_name, $db_email);

    if ($stmt->fetch()) {
        $form_name = $db_name;
        $form_email = $db_email;
    } else {
        $edit_id = 0;
    }

    $stmt->close();
}

// PRESERVE FORM VALUES IF VALIDATION FAILS
if ($_SERVER["REQUEST_METHOD"] === "POST" && $error !== "") {
    $form_name = $name;
    $form_email = $email;
}

// READ REGISTERED USERS
$result = $conn->query(
    "SELECT * FROM users ORDER BY id ASC"
);

// SUCCESS MESSAGES
$messages = [
    "added"   => "User added successfully!",
    "updated" => "User updated successfully!",
    "deleted" => "User deleted successfully!"
];

$msg = $_GET['msg'] ?? '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>User Management System</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <style>
        body {
            background-color: #f5f9ff;
            font-family: Arial, sans-serif;
        }

        .main-container {
            max-width: 1600px;
            margin: 60px auto;
        }

        .page-title {
            text-align: center;
            font-size: 38px;
            font-weight: 400;
            margin-bottom: 65px;
            color: #222;
        }

        .section-title {
            font-size: 25px;
            font-weight: 500;
            margin-bottom: 20px;
        }

        .form-box {
            background-color: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 3px 12px rgba(0,0,0,0.05);
        }

        .form-label {
            font-size: 17px;
            margin-bottom: 10px;
        }

        .form-control {
            padding: 12px;
            border-radius: 6px;
        }

        .submit-btn {
            width: 100%;
            padding: 12px;
            background-color: #0866ff;
            border: none;
            font-size: 17px;
        }

        .submit-btn:hover {
            background-color: #0052cc;
        }

        .table th {
            font-weight: bold;
            padding: 14px 10px;
        }

        .table td {
            padding: 14px 10px;
            vertical-align: middle;
        }

        .action-buttons {
            white-space: nowrap;
        }

        .alert {
            border-radius: 6px;
        }
    </style>
</head>

<body>

<div class="container-fluid main-container px-4">

    <h1 class="page-title">
        User Management System
    </h1>

    <div class="row g-5">

        <!-- LEFT SIDE - ADD OR EDIT USER -->
        <div class="col-lg-5">

            <h3 class="section-title">
                <?= $edit_id > 0 ? "Edit User" : "Add New User" ?>
            </h3>

            <div class="form-box">

                <!-- SUCCESS MESSAGE -->
                <?php if (isset($messages[$msg])): ?>
                    <div class="alert alert-success alert-dismissible fade show"
                         role="alert">

                        <?= htmlspecialchars($messages[$msg]) ?>

                        <button type="button"
                                class="btn-close"
                                data-bs-dismiss="alert"
                                aria-label="Close">
                        </button>
                    </div>
                <?php endif; ?>

                <!-- ERROR MESSAGE -->
                <?php if ($error !== ""): ?>
                    <div class="alert alert-danger">
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <!-- ADD / EDIT FORM -->
                <form method="POST" action="index.php">

                    <input type="hidden"
                           name="id"
                           value="<?= (int)$edit_id ?>">

                    <div class="mb-4">
                        <label for="name" class="form-label">
                            Full Name
                        </label>

                        <input type="text"
                               id="name"
                               name="name"
                               class="form-control"
                               placeholder="Enter Full Name"
                               value="<?= htmlspecialchars($form_name) ?>"
                               maxlength="255"
                               required>
                    </div>

                    <div class="mb-4">
                        <label for="email" class="form-label">
                            Email Address
                        </label>

                        <input type="email"
                               id="email"
                               name="email"
                               class="form-control"
                               placeholder="Enter Email Address"
                               value="<?= htmlspecialchars($form_email) ?>"
                               maxlength="255"
                               required>
                    </div>

                    <button type="submit"
                            class="btn btn-primary submit-btn">
                        Submit
                    </button>

                    <?php if ($edit_id > 0): ?>
                        <a href="index.php"
                           class="btn btn-secondary w-100 mt-2">
                            Cancel Edit
                        </a>
                    <?php endif; ?>

                </form>
            </div>
        </div>

        <!-- RIGHT SIDE - REGISTERED USERS -->
        <div class="col-lg-7">

            <h3 class="section-title">
                Registered Users
            </h3>

            <div class="table-responsive">
                <table class="table table-hover">

                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Joined</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>

                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php $counter = 1; ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= $counter++ ?></td>

                                <td>
                                    <?= htmlspecialchars($row['name']) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($row['email']) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars(
                                        date("M d, Y",
                                        strtotime($row['created_at']))
                                    ) ?>
                                </td>

                                <td class="action-buttons">

                                    <!-- EDIT ON THE SAME DASHBOARD -->
                                    <a class="btn btn-success btn-sm"
                                       href="index.php?edit=<?= (int)$row['id'] ?>">
                                        Edit
                                    </a>

                                    <!-- DELETE USER -->
                                    <a href="delete.php?id=<?= (int)$row['id'] ?>"
                                       class="btn btn-danger btn-sm"
                                       onclick="return confirm('Are you sure you want to delete this user?');">
                                        Delete
                                    </a>

                                </td>
                            </tr>
                        <?php endwhile; ?>

                    <?php else: ?>
                        <tr>
                            <td colspan="5"
                                class="text-center text-muted py-4">
                                No registered users yet.
                            </td>
                        </tr>
                    <?php endif; ?>

                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
