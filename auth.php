<?php
require_once 'conexion.php';

// Obtener datos del formulario y sanitizarlos
function sanitize_input($data) {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['accion']) && $_GET['accion'] == 'getUsers') {
    // Recupera todos los usuarios de la base de datos
    $query = "SELECT * FROM usuarios";
    $result = mysqli_query($conn, $query);
    if ($result) {
        $users = array();
        while ($row = mysqli_fetch_assoc($result)) {
            $users[] = $row;
        }
        echo json_encode($users);
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['accion']) && $_POST['accion'] == 'registro') {
    // Crea un nuevo usuario en la base de datos
    $username = sanitize_input($_POST['username']);
    $password = $_POST['password'];
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);

    // Verifica la longitud del username
    if (strlen($username) < 3 || strlen($username) > 32) {
        echo "El username debe tener entre 3 y 32 caracteres";
        exit();
    }

    // Verifica la longitud de la contraseña
    if (strlen($password) < 8) {
        echo "La contraseña debe tener al menos 8 caracteres";
        exit();
    }

    // Verifica el formato del email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "El email no es válido";
        exit();
    }

    // Hash la contraseña utilizando password_hash()
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $query = "INSERT INTO usuarios (username, password, email) VALUES ('$username', '$hashed_password', '$email')";
    $result = mysqli_query($conn, $query);
    if ($result) {
        echo "Usuario creado correctamente";
    } else {
        echo "Error al crear usuario: " . mysqli_error($conn);
    }
}

if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['accion']) && $_GET['accion'] == 'getUser' && isset($_GET['id'])) {
    // Recupera un usuario específico por ID de la base de datos
    $id = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);
    $query = "SELECT * FROM usuarios WHERE id = '$id'";
    $result = mysqli_query($conn, $query);
    if ($result) {
        $user = mysqli_fetch_assoc($result);
        echo json_encode($user);
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}

if ($_SERVER["REQUEST_METHOD"] == "PUT" && isset($_GET['accion']) && $_GET['accion'] == 'actualizar' && isset($_GET['id'])) {
    // Actualiza un usuario específico por ID en la base de datos
    $id = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);
    $username = sanitize_input($_POST['username']);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $query = "UPDATE usuarios SET username = '$username', email = '$email' WHERE id = '$id'";
    $result = mysqli_query($conn, $query);
    if ($result) {
        echo "Usuario actualizado correctamente";
    } else {
        echo "Error al actualizar usuario: " . mysqli_error($conn);
    }
}

if ($_SERVER["REQUEST_METHOD"] == "DELETE" && isset($_GET['accion']) && $_GET['accion'] == 'eliminar' && isset($_GET['id'])) {
    // Elimina un usuario específico por ID de la base de datos
    $id = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);
    $query = "DELETE FROM usuarios WHERE id = '$id'";
    $result = mysqli_query($conn, $query);
    if ($result) {
        echo "Usuario eliminado correctamente";
    } else {
        echo "Error al eliminar usuario: " . mysqli_error($conn);
    }
}

// Validación de inicio de sesión
if ($_SERVER["REQUEST_METHOD"] == "POST" && !isset($_POST['accion'])) {
    $username = sanitize_input($_POST['username']);
    $password = $_POST['password']; // No sanitizamos la contraseña aquí

    // Verifica las credenciales con la base de datos
    $query = "SELECT * FROM usuarios WHERE username = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        // Verifica la contraseña utilizando password_verify()
        if (password_verify($password, $user['password'])) {
            // Si las credenciales son correctas, redirigir a la página de presentación
            header('Location: presentacion.html');
            exit();
        } else {
            // Si las credenciales son incorrectas, redirigir a la página de registro
            header('Location: registro.html');
            exit();
        }
    } else {
        // Si el usuario no existe, redirigir a la página de registro
        header('Location: registro.html');
        exit();
    }
}

// Mostrar errores si existen
if (isset($error)) {
    echo '<p style="color: rojo;" >' . $error . '</p>';
}
?>
