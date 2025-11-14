<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Judson&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="https://cdn-uicons.flaticon.com/2.4.0/uicons-solid-straight/css/uicons-solid-straight.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body, html {
            height: 100%;
            font-family: 'Judson', serif;
        }

        .container {
            background-image: url('wallpaper.jpg');
            background-size: cover;
            background-position: center;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            position: relative;
        }

        .overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.4);
        }

        .login-form {
            background: white;
            padding: 40px;
            border-radius: 20px;
            z-index: 2;
            text-align: center;
            width: 100%;
            max-width: 400px;
            box-shadow: 0px 4px 10px rgba(0,0,0,0.3);
            position: relative;
        }

        .login-form h2 {
            margin-bottom: 20px;
            font-size: 35px;
        }

        .form-group {
            margin-bottom: 20px;
            position: relative;
        }

        .form-group input {
            width: 100%;
            padding: 10px 12px 10px 40px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 14px;
        }

        .form-group i {
            position: absolute;
            top: 50%;
            left: 12px;
            transform: translateY(-50%);
            color: #888;
        }

        .login-button {
            width: 100%;
            padding: 10px;
            background-color: #330000;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            margin-top: 10px;
        }

        .login-button:hover {
            background-color: #550000;
        }

        .logo {
            position: absolute;
            top: 20px;
            left: 40px;
            height: 35px;
            z-index: 2;
        }
    </style>
</head>
<body>

<?php
if (isset($_GET['pesan'])) {
    if ($_GET['pesan'] == "gagal") {
?>
    <script>
        Swal.fire({
            icon: "error",
            text: "Incorrect username or password!",
        });
    </script>
<?php
    } elseif ($_GET['pesan'] == "login_berhasil") {
?>
    <script>
        Swal.fire({
            icon: "success",
            title: "Login successful",
            showConfirmButton: false,
            timer: 1500
        });
    </script>
<?php
    } elseif ($_GET['pesan'] == "logout") {
?>
    <script>
        Swal.fire({
            icon: "success",
            title: "Logout successful!",
            showConfirmButton: false,
            timer: 1500
        });
    </script>
<?php
    } elseif ($_GET['pesan'] == "belum_login") {
?>
    <script>
        Swal.fire({
            icon: "warning",
            text: "You must login first!",
        });
    </script>
<?php
    }
}
?>

<div class="container">
    <div class="overlay"></div>
    <img src="logoputih.png" alt="Kingland Logo" class="logo">

    <form class="login-form" method="POST" action="login_proses.php">
        <h2>Log In for<br> KingLand</h2>

        <div class="form-group">
            <i class="fas fa-user"></i>
            <input type="text" name="username" placeholder="Enter your username" required>
        </div>

        <div class="form-group">
            <i class="fas fa-lock"></i>
            <input type="password" name="password" placeholder="Enter your password" required>
        </div>

        <button type="submit" class="login-button">Log In</button>
    </form>
</div>

</body>
</html>
