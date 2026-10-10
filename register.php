<?php
session_start();
include_once "include/db_connect.php";

if (isset($_SESSION['auth'])) {
    header("Location: index.php");
    exit;
}

$errors = array();
$username = "";

if (isset($_POST['reg_user'])) {
    $username = mysqli_real_escape_string($link, $_POST['username']);
    $password_1 = $_POST['password_1'];
    $password_2 = $_POST['password_2'];

    if (empty($username)) array_push($errors, "Требуется имя пользователя");
    if (empty($password_1)) array_push($errors, "Требуется пароль");
    if ($password_1 != $password_2) array_push($errors, "Пароли не совпадают");

    if (count($errors) == 0) {
        $check = mysqli_query($link, "SELECT * FROM users WHERE username='$username'");
        if (mysqli_num_rows($check) > 0) {
            array_push($errors, "Пользователь с таким именем уже существует");
        }
    }

    if (count($errors) == 0) {
        mysqli_query($link, "INSERT INTO users (username, password, role) VALUES('$username', '$password_1', 'user')");
        $_SESSION['success'] = "Регистрация успешна! Теперь вы можете войти.";
        header("Location: login.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Регистрация - Shoes</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/responsive.css">
    <link rel="icon" href="images/fevicon.png" type="image/gif" />
    <link rel="stylesheet" href="css/jquery.mCustomScrollbar.min.css">
    <link rel="stylesheet" href="https://netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.css">
    <link rel="stylesheet" href="css/owl.carousel.min.css">
    <link rel="stylesheet" href="css/owl.theme.default.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fancybox/2.1.5/jquery.fancybox.min.css" media="screen">
</head>
<body class="main-layout">
    
    <!-- header section start -->
    <div class="header_section header_main">
        <div class="container">
            <div class="row">
                <div class="col-sm-3">
                    <div class="logo"><a href="index.php"><img src="images/logo.png" alt="Logo"></a></div>
                </div>
                <div class="col-sm-9">
                    <nav class="navbar navbar-expand-lg navbar-light bg-light">
                        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNavAltMarkup">
                            <span class="navbar-toggler-icon"></span>
                        </button>
                        <div class="collapse navbar-collapse" id="navbarNavAltMarkup">
                            <div class="navbar-nav">
                                <a class="nav-item nav-link" href="index.php">Home</a>
                                <a class="nav-item nav-link" href="collection.html">Collection</a>
                                <a class="nav-item nav-link" href="index.php">Shoes</a>
                                <a class="nav-item nav-link" href="contact.html">Contact</a>
                                
                                <?php if (isset($_SESSION['auth'])): ?>
                                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                                        <a class="nav-item nav-link" href="shoes_admin.php">Админ-панель</a>
                                    <?php endif; ?>
                                    <a class="nav-item nav-link" href="logout.php">Logout</a>
                                <?php else: ?>
                                    <a class="nav-item nav-link" href="login.php">Login</a>
                                    <a class="nav-item nav-link active" href="register.php">Register</a>
                                <?php endif; ?>
                                
                                <a class="nav-item nav-link last" href="#"><img src="images/search_icon.png" alt="Search"></a>
                                <a class="nav-item nav-link last" href="contact.html"><img src="images/shop_icon.png" alt="Shop"></a>
                            </div>
                        </div>
                    </nav>
                </div>
            </div>
        </div>
    </div>
    <!-- header section end -->
    
    <!-- registration section -->
    <div class="collection_section layout_padding">
        <div class="container">
            <div class="row">
                <div class="col-md-6 offset-md-3">
                    <div class="main_form">
                        <h1 class="new_text" style="text-align: center; margin-bottom: 30px;">Регистрация</h1>
                        
                        <?php if (count($errors) > 0): ?>
                            <div class="alert alert-danger">
                                <ul style="margin: 0; padding-left: 20px;">
                                    <?php foreach ($errors as $error): ?>
                                        <li><?php echo $error; ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" action="register.php">
                            <div class="form-group">
                                <label>Имя пользователя</label>
                                <input type="text" class="form-control" name="username" value="<?php echo htmlspecialchars($username); ?>" required>
                            </div>
                            <div class="form-group">
                                <label>Пароль</label>
                                <input type="password" class="form-control" name="password_1" required>
                            </div>
                            <div class="form-group">
                                <label>Подтвердите пароль</label>
                                <input type="password" class="form-control" name="password_2" required>
                            </div>
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary" name="reg_user" style="width: 100%; background: #ff4e5b; border: none; padding: 12px;">Регистрация</button>
                            </div>
                        </form>
                        
                        <p style="text-align: center; margin-top: 20px;">
                            Уже есть аккаунт? <a href="login.php" style="color: #ff4e5b;">Войти</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- end registration section -->
    
    <!-- footer -->
    <div class="section_footer">
        <div class="container">
            <div class="copyright">2019 All Rights Reserved. <a href="https://html.design">Free html Templates</a></div>
        </div>
    </div>

    <script src="js/jquery.min.js"></script>
    <script src="js/bootstrap.bundle.min.js"></script>
    <script src="js/custom.js"></script>
</body>
</html>