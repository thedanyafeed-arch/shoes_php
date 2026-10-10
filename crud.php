<?php
session_start();

// Проверка авторизации
if (!isset($_SESSION['auth'])) {
    header("Location: login.php");
    exit;
}

// Проверка прав администратора
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}
include_once "include/db_connect.php";

// Получение данных для редактирования
$edit_data = null;
$edit_row_id = null;

if (isset($_POST['edit_id'])) {
    $edit_id = intval($_POST['id']);
    $q = mysqli_query($link, "SELECT * FROM shoes WHERE id = '$edit_id'");
    if ($q && mysqli_num_rows($q) > 0) {
        $edit_data = mysqli_fetch_assoc($q);
        $edit_row_id = $edit_data['id'];
    }
}

if (isset($_GET['edit_id'])) {
    $edit_id = intval($_GET['edit_id']);
    $q = mysqli_query($link, "SELECT * FROM shoes WHERE id = '$edit_id'");
    if ($q && mysqli_num_rows($q) > 0) {
        $edit_data = mysqli_fetch_assoc($q);
        $edit_row_id = $edit_data['id'];
    }
}

// Сброс режима редактирования
if (isset($_GET['cancel_edit'])) {
    header("Location: shoes_admin.php");
    exit;
}

// ДОБАВЛЕНИЕ ЗАПИСИ С ЗАГРУЗКОЙ ФОТО
if (isset($_POST["add_shoe"])) {
    if (!empty($_POST['name']) && !empty($_POST['price'])) {
        $name = mysqli_real_escape_string($link, $_POST['name']);
        $price = floatval($_POST['price']);
        $rating = intval($_POST['rating']) ?: 5;
        
        $filename = "";
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $allowed = array('jpg', 'jpeg', 'png', 'gif');
            $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            
            if (in_array($ext, $allowed)) {
                $filename = uniqid() . '.' . $ext;
                $upload_path = "images/" . $filename;
                
                if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                    // Файл загружен успешно
                } else {
                    $message = "<div class='alert alert-danger'>Ошибка загрузки файла!</div>";
                    $filename = "";
                }
            } else {
                $message = "<div class='alert alert-warning'>Разрешены только JPG, JPEG, PNG, GIF!</div>";
            }
        }
        
        if (!empty($filename)) {
            $query = mysqli_query($link, "SELECT * FROM shoes WHERE name= '$name' AND price= '$price'");
            if (mysqli_num_rows($query) == 0) {
                $sql = "INSERT INTO shoes(name, price, rating, image) VALUES('$name', '$price', '$rating', '$filename')";
                $result = mysqli_query($link, $sql);
                $message = $result ? "<div class='alert alert-success'>Запись добавлена!</div>" : "<div class='alert alert-danger'>Ошибка добавления!</div>";
            } else {
                $message = "<div class='alert alert-warning'>Запись уже существует!</div>";
            }
        }
    } else {
        $message = "<div class='alert alert-danger'>Заполните все обязательные поля!</div>";
    }
}

// ИЗМЕНЕНИЕ ЗАПИСИ
if (isset($_POST["update_shoe"])) {
    if (!empty($_POST['id']) && !empty($_POST['name']) && !empty($_POST['price'])) {
        $id = intval($_POST['id']);
        $name = mysqli_real_escape_string($link, $_POST['name']);
        $price = floatval($_POST['price']);
        $rating = intval($_POST['rating']) ?: 5;
        
        $old_image = "";
        $result = mysqli_query($link, "SELECT image FROM shoes WHERE id = '$id'");
        if ($result && $row = mysqli_fetch_assoc($result)) {
            $old_image = $row['image'];
        }
        
        $filename = $old_image;
        
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $allowed = array('jpg', 'jpeg', 'png', 'gif');
            $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            
            if (in_array($ext, $allowed)) {
                $filename = uniqid() . '.' . $ext;
                $upload_path = "images/" . $filename;
                
                if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                    if (!empty($old_image) && file_exists("images/" . $old_image)) {
                        unlink("images/" . $old_image);
                    }
                } else {
                    $filename = $old_image;
                }
            }
        }
        
        $query = mysqli_query($link, "SELECT * FROM shoes WHERE id= '$id'");
        $query_dup = mysqli_query($link, "SELECT * FROM shoes WHERE name= '$name' AND price= '$price' AND id != '$id'");

        if (mysqli_num_rows($query) != 0 && mysqli_num_rows($query_dup) == 0) {
            $result = mysqli_query($link, "UPDATE shoes SET name= '$name', price= '$price', rating= '$rating', image= '$filename' WHERE id= '$id'");
            $message = $result ? "<div class='alert alert-success'>Запись обновлена!</div>" : "<div class='alert alert-danger'>Ошибка обновления!</div>";
        } else {
            $message = mysqli_num_rows($query) == 0 ? "<div class='alert alert-danger'>Запись не существует!</div>" : "<div class='alert alert-warning'>Дубликат записи!</div>";
        }
    } else {
        $message = "<div class='alert alert-danger'>Заполните обязательные поля!</div>";
    }
}

// УДАЛЕНИЕ ЗАПИСИ
if (isset($_POST["delete_shoe"])) {
    if (!empty($_POST['id'])) {
        $id = intval($_POST['id']);
        
        $result = mysqli_query($link, "SELECT image FROM shoes WHERE id= '$id'");
        if ($result && $row = mysqli_fetch_assoc($result)) {
            $image = $row['image'];
            if (!empty($image) && file_exists("images/" . $image)) {
                unlink("images/" . $image);
            }
        }
        
        $query = mysqli_query($link, "SELECT * FROM shoes WHERE id= '$id'");
        if (mysqli_num_rows($query) != 0) {
            $result = mysqli_query($link, "DELETE FROM shoes WHERE id= '$id'");
            $message = $result ? "<div class='alert alert-success'>Запись удалена!</div>" : "<div class='alert alert-danger'>Ошибка удаления!</div>";
        } else {
            $message = "<div class='alert alert-danger'>Запись не существует!</div>";
        }
    } else {
        $message = "<div class='alert alert-danger'>Укажите ID записи!</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
   <head>
      <!-- basic -->
      <meta charset="utf-8">
      <meta http-equiv="X-UA-Compatible" content="IE=edge">
      <!-- mobile metas -->
      <meta name="viewport" content="width=device-width, initial-scale=1">
      <meta name="viewport" content="initial-scale=1, maximum-scale=1">
      <!-- site metas -->
      <title>Shoes Admin</title>
      <meta name="keywords" content="">
      <meta name="description" content="">
      <meta name="author" content="">
      <!-- bootstrap css -->
      <link rel="stylesheet" href="css/bootstrap.min.css">
      <!-- style css -->
      <link rel="stylesheet" href="css/style.css">
      <!-- Responsive-->
      <link rel="stylesheet" href="css/responsive.css">
      <!-- fevicon -->
      <link rel="icon" href="images/fevicon.png" type="image/gif" />
      <!-- Scrollbar Custom CSS -->
      <link rel="stylesheet" href="css/jquery.mCustomScrollbar.min.css">
      <!-- Tweaks for older IEs-->
      <link rel="stylesheet" href="https://netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.css">
      <!-- owl stylesheets --> 
      <link rel="stylesheet" href="css/owl.carousel.min.css">
      <link rel="stylesheet" href="css/owl.theme.default.min.css">
      <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fancybox/2.1.5/jquery.fancybox.min.css" media="screen">
      
      <style>
          .admin-container { padding: 30px 0; }
          .admin-title { text-align: center; margin-bottom: 30px; color: #333; }
          .admin-table { width: 100%; border-collapse: collapse; background: #fff; margin: 20px 0; }
          .admin-table th { background: #ff4e5b; color: #fff; padding: 15px; text-align: left; font-weight: 600; }
          .admin-table td { padding: 12px 15px; border-bottom: 1px solid #eee; }
          .admin-table tr:hover { background: #f9f9f9; }
          .admin-table tr.is-editing { background: #fff8e1; }
          .cell-input { width: 100%; padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
          .cell-input:focus { border-color: #ff4e5b; outline: none; }
          .preview-img { max-width: 100px; max-height: 70px; border-radius: 4px; border: 1px solid #eee; }
          .rating-display { color: #ffc107; }
          .price-display { color: #ff4e5b; font-weight: 600; }
          .action-btn { padding: 6px 15px; margin: 2px; border: none; border-radius: 4px; cursor: pointer; font-size: 13px; }
          .btn-add { background: #28a745; color: #fff; }
          .btn-add:hover { background: #218838; }
          .btn-edit { background: #007bff; color: #fff; }
          .btn-edit:hover { background: #0056b3; }
          .btn-delete { background: #dc3545; color: #fff; }
          .btn-delete:hover { background: #c82333; }
          .btn-cancel { background: #6c757d; color: #fff; }
          .btn-cancel:hover { background: #5a6268; }
          .file-input { padding: 5px; }
          .alert { padding: 12px 20px; margin: 20px 0; border-radius: 4px; }
          .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
          .alert-danger { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
          .alert-warning { background: #fff3cd; color: #856404; border: 1px solid #ffeeba; }
          .back-link { display: inline-block; margin-top: 20px; padding: 10px 25px; background: #333; color: #fff; text-decoration: none; border-radius: 4px; }
          .back-link:hover { background: #555; }
      </style>
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
                          <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNavAltMarkup" aria-controls="navbarNavAltMarkup" aria-expanded="false" aria-label="Toggle navigation">
                              <span class="navbar-toggler-icon"></span>
                          </button>
                          <div class="collapse navbar-collapse" id="navbarNavAltMarkup">
                              <div class="navbar-nav">
                                  <a class="nav-item nav-link" href="index.php">Home</a>
                                  <a class="nav-item nav-link" href="collection.html">Collection</a>
                                  <a class="nav-item nav-link active" href="shoes_admin.php">Admin Panel</a>
                                  <a class="nav-item nav-link" href="contact.html">Contact</a>
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
      
      <!-- admin section start -->
      <div class="collection_section layout_padding admin-container">
          <div class="container">
              <h1 class="admin-title new_text"><strong>Управление обувью</strong></h1>
              
              <?php if (isset($message)) echo $message; ?>
              
              <div class="table-responsive">
                  <table class="admin-table">
                      <thead>
                          <tr>
                              <th>ID</th>
                              <th>Название</th>
                              <th>Цена</th>
                              <th>Рейтинг</th>
                              <th>Изображение</th>
                              <th>Действия</th>
                          </tr>
                      </thead>
                      <tbody>
                          <!-- Форма добавления -->
                          <tr class="is-editing">
                              <form method="POST" enctype="multipart/form-data">
                                  <td><input class="cell-input" type="text" name="id" value="" readonly placeholder="Auto"></td>
                                  <td><input class="cell-input" type="text" name="name" value="" required placeholder="Название"></td>
                                  <td><input class="cell-input" type="number" step="0.01" name="price" value="" required placeholder="0.00"></td>
                                  <td><input class="cell-input" type="number" name="rating" value="5" min="1" max="5"></td>
                                  <td><input class="cell-input file-input" type="file" name="image" accept="image/*" required></td>
                                  <td>
                                      <button class="action-btn btn-add" type="submit" name="add_shoe">Добавить</button>
                                  </td>
                              </form>
                          </tr>
                          
                          <?php
                          mysqli_query($link, "SET NAMES utf8");
                          $res = mysqli_query($link, 'SELECT * FROM shoes ORDER BY id ASC');
                          
                          if ($res) {
                              while ($shoe = mysqli_fetch_assoc($res)) {
                                  $row_id = $shoe['id'];
                                  
                                  // Режим редактирования
                                  if ($edit_row_id !== null && (string)$edit_row_id === (string)$row_id) {
                                      echo "<tr class='is-editing'>";
                                      echo "<form method='POST' enctype='multipart/form-data'>";
                                      echo "<td><input class='cell-input' type='text' name='id' value='" . htmlspecialchars($shoe['id']) . "' readonly></td>";
                                      echo "<td><input class='cell-input' type='text' name='name' value='" . htmlspecialchars($shoe['name']) . "' required></td>";
                                      echo "<td><input class='cell-input' type='number' step='0.01' name='price' value='" . htmlspecialchars($shoe['price']) . "' required></td>";
                                      echo "<td><input class='cell-input' type='number' name='rating' value='" . htmlspecialchars($shoe['rating']) . "' min='1' max='5'></td>";
                                      echo "<td>";
                                      echo "<div style='margin-bottom:5px;'><img src='images/" . htmlspecialchars($shoe['image']) . "' class='preview-img' alt='img'></div>";
                                      echo "<input class='cell-input file-input' type='file' name='image' accept='image/*'>";
                                      echo "</td>";
                                      echo "<td>";
                                      echo "<button class='action-btn btn-edit' type='submit' name='update_shoe'>Сохранить</button> ";
                                      echo "<a class='action-btn btn-cancel' href='shoes_admin.php'>Отмена</a>";
                                      echo "</td>";
                                      echo "</form>";
                                      echo "</tr>";
                                      continue;
                                  }
                                  
                                  // Обычный режим
                                  echo "<tr>";
                                  echo "<td><strong>" . htmlspecialchars($shoe['id']) . "</strong></td>";
                                  echo "<td>" . htmlspecialchars($shoe['name']) . "</td>";
                                  echo "<td class='price-display'>$" . number_format($shoe['price'], 2) . "</td>";
                                  echo "<td class='rating-display'>" . str_repeat('★', $shoe['rating']) . " ({$shoe['rating']}/5)</td>";
                                  echo "<td><img src='images/" . htmlspecialchars($shoe['image']) . "' class='preview-img' alt='img' onerror=\"this.style.display='none'\"></td>";
                                  echo "<td>";
                                  echo "<form method='POST' style='display:inline;'>";
                                  echo "<input type='hidden' name='id' value='" . htmlspecialchars($shoe['id']) . "'>";
                                  echo "<button class='action-btn btn-edit' type='submit' name='edit_id'>Изменить</button> ";
                                  echo "<button class='action-btn btn-delete' type='submit' name='delete_shoe' onclick=\"return confirm('Удалить запись?');\">Удалить</button>";
                                  echo "</form>";
                                  echo "</td>";
                                  echo "</tr>";
                              }
                              mysqli_free_result($res);
                          }
                          ?>
                      </tbody>
                  </table>
              </div>
              
              <a href="index.php" class="back-link">Вернуться на сайт</a>
          </div>
      </div>
      <!-- admin section end -->
      
      <!-- section footer start -->
      <div class="section_footer">
          <div class="container">
              <div class="mail_section">
                  <div class="row">
                      <div class="col-sm-6 col-lg-2">
                          <div><a href="#"><img src="images/footer-logo.png" alt="Footer Logo"></a></div>
                      </div>
                      <div class="col-sm-6 col-lg-2">
                          <div class="footer-logo"><img src="images/phone-icon.png" alt="Phone"><span class="map_text">(71) 1234567890</span></div>
                      </div>
                      <div class="col-sm-6 col-lg-3">
                          <div class="footer-logo"><img src="images/email-icon.png" alt="Email"><span class="map_text">Demo@gmail.com</span></div>
                      </div>
                      <div class="col-sm-6 col-lg-3">
                          <div class="social_icon">
                              <ul>
                                  <li><a href="#"><img src="images/fb-icon.png" alt="Facebook"></a></li>
                                  <li><a href="#"><img src="images/twitter-icon.png" alt="Twitter"></a></li>
                                  <li><a href="#"><img src="images/in-icon.png" alt="LinkedIn"></a></li>
                                  <li><a href="#"><img src="images/google-icon.png" alt="Google"></a></li>
                              </ul>
                          </div>
                      </div>
                      <div class="col-sm-2"></div>
                  </div>
              </div> 
              <div class="footer_section_2">
                  <div class="row">
                      <div class="col-sm-4 col-lg-2">
                          <p class="dummy_text"> ipsum dolor sit amet, consectetur ipsum dolor sit amet, consectetur  ipsum dolor sit amet,</p>
                      </div>
                      <div class="col-sm-4 col-lg-2">
                          <h2 class="shop_text">Address </h2>
                          <div class="image-icon"><img src="images/map-icon.png" alt="Map"><span class="pet_text">No 40 Baria Sreet 15/2 NewYork City, NY, United States.</span></div>
                      </div>
                      <div class="col-sm-4 col-md-6 col-lg-3">
                          <h2 class="shop_text">Our Company </h2>
                          <div class="delivery_text">
                              <ul>
                                  <li>Delivery</li>
                                  <li>Legal Notice</li>
                                  <li>About us</li>
                                  <li>Secure payment</li>
                                  <li>Contact us</li>
                              </ul>
                          </div>
                      </div>
                      <div class="col-sm-6 col-lg-3">
                          <h2 class="adderess_text">Products</h2>
                          <div class="delivery_text">
                              <ul>
                                  <li>Prices drop</li>
                                  <li>New products</li>
                                  <li>Best sales</li>
                                  <li>Contact us</li>
                                  <li>Sitemap</li>
                              </ul>
                          </div>
                      </div>
                      <div class="col-sm-6 col-lg-2">
                          <h2 class="adderess_text">Newsletter</h2>
                          <div class="form-group">
                              <input type="text" class="enter_email" placeholder="Enter Your email" name="Name">
                          </div>
                          <button class="subscribr_bt">Subscribe</button>
                      </div>
                  </div>
              </div> 
          </div>
      </div>
      <!-- section footer end -->
      
      <div class="copyright">2019 All Rights Reserved. <a href="https://html.design">Free html Templates</a></div>

      <!-- Javascript files-->
      <script src="js/jquery.min.js"></script>
      <script src="js/popper.min.js"></script>
      <script src="js/bootstrap.bundle.min.js"></script>
      <script src="js/jquery-3.0.0.min.js"></script>
      <script src="js/plugin.js"></script>
      <!-- sidebar -->
      <script src="js/jquery.mCustomScrollbar.concat.min.js"></script>
      <script src="js/custom.js"></script>
      <!-- javascript --> 
      <script src="js/owl.carousel.js"></script>
      <script src="https://cdnjs.cloudflare.com/ajax/libs/fancybox/2.1.5/jquery.fancybox.min.js"></script>
   </body>
</html>