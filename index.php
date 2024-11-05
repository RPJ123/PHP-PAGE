<?php
$dataFile = 'users.json';

function loadUsers() {
    global $dataFile;
    if (file_exists($dataFile)) {
        $jsonData = file_get_contents($dataFile);   
        return json_decode($jsonData, true) ?? [];
    }
    return [];
}

function saveUsers($users) {
    global $dataFile;
    file_put_contents($dataFile, json_encode($users));
}

// Handle form submission to add or edit a user
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['name'], $_POST['email'], $_POST['place'], $_POST['class'], $_POST['college'], $_POST['department'])) {
    $name = htmlspecialchars($_POST['name']);
    $email = htmlspecialchars($_POST['email']);
    $place = htmlspecialchars($_POST['place']);
    $class = htmlspecialchars($_POST['class']);
    $college = htmlspecialchars($_POST['college']);
    $department = htmlspecialchars($_POST['department']);
    $id = isset($_POST['id']) ? $_POST['id'] : null;

    $users = loadUsers();

    if ($id) {
        foreach ($users as &$user) {
            if ($user['id'] === $id) {
                $user['name'] = $name;
                $user['email'] = $email;
                $user['place'] = $place;
                $user['class'] = $class;
                $user['college'] = $college;
                $user['department'] = $department;
                break;
            }
        }
    } else {
        $newUser = [
            'id' => uniqid(),
            'name' => $name,
            'email' => $email,
            'place' => $place,
            'class' => $class,
            'college' => $college,
            'department' => $department
        ];
        $users[] = $newUser;
    }

    saveUsers($users);
}

// Handle AJAX delete operation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['deleteId'])) {
    $idToDelete = $_POST['deleteId'];
    $users = loadUsers();

    $updatedUsers = array_filter($users, function ($user) use ($idToDelete) {
        return $user['id'] !== $idToDelete;
    });

    if (count($updatedUsers) !== count($users)) {
        saveUsers($updatedUsers);
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'User not found.']);
    }
    exit;
}

// Load users to display
$users = loadUsers();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - Dark Theme</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<div class="form-container">
    <form method="POST" id="userForm">
        <input type="hidden" name="id" id="userId">
        <label>Name: <input type="text" name="name" id="userName" required></label><br>
        <label>Email: <input type="email" name="email" id="userEmail" required></label><br>
        <label>Place: <input type="text" name="place" id="userPlace" required></label><br>
        <label>Class: <input type="text" name="class" id="userClass" required></label><br>
        <label>College: <input type="text" name="college" id="userCollege" required></label><br>
        <label>Department: <input type="text" name="department" id="userDepartment" required></label><br>
        <input type="submit" value="Save">
    </form>
</div>

<div class="users-container">
    <h3>User List:</h3>
    <div id="userData">
        <?php foreach ($users as $user): ?>
            <div class="user" data-id="<?= $user['id']; ?>">
                <span><strong>Name:</strong> <?= htmlspecialchars($user['name']); ?></span><br>
                <span><strong>Email:</strong> <?= htmlspecialchars($user['email']); ?></span><br>
                <span><strong>Place:</strong> <?= htmlspecialchars($user['place']); ?></span><br>
                <span><strong>Class:</strong> <?= htmlspecialchars($user['class']); ?></span><br>
                <span><strong>College:</strong> <?= htmlspecialchars($user['college']); ?></span><br>
                <span><strong>Department:</strong> <?= htmlspecialchars($user['department']); ?></span><br>
                <button onclick="editUser('<?= $user['id']; ?>', '<?= htmlspecialchars($user['name']); ?>', '<?= htmlspecialchars($user['email']); ?>', '<?= htmlspecialchars($user['place']); ?>', '<?= htmlspecialchars($user['class']); ?>', '<?= htmlspecialchars($user['college']); ?>', '<?= htmlspecialchars($user['department']); ?>')">Edit</button>
                <button onclick="deleteUser('<?= $user['id']; ?>')">Delete</button>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
// Inline editing
function editUser(id, name, email, place, classVal, college, department) {
    document.getElementById('userId').value = id;
    document.getElementById('userName').value = name;
    document.getElementById('userEmail').value = email;
    document.getElementById('userPlace').value = place;
    document.getElementById('userClass').value = classVal;
    document.getElementById('userCollege').value = college;
    document.getElementById('userDepartment').value = department;
}

// AJAX delete operation
function deleteUser(id) {
    if (confirm('Are you sure you want to delete this user?')) {
        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'index.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        
        xhr.onload = function () {
            if (xhr.status === 200) {
                const response = JSON.parse(xhr.responseText);
                if (response.status === 'success') {
                    document.querySelector(`.user[data-id="${id}"]`).remove();
                } else {
                    alert('User not found.');
                }
            } else {
                alert('Error communicating with server.');
            }
        };

        xhr.send('deleteId=' + encodeURIComponent(id));
    }
}
</script>

</body>
</html>
