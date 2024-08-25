<?php

require_once dirname(__DIR__, 1) . '/model/Connection.php';
require_once dirname(__DIR__, 1) . '/utils/FileUtils.php';

class ImageController
{
  private $dbh;

  public function __construct($config)
  {
    $this->dbh = Connection::getInstance($config)->getConnection();
  }

  public function upload()
  {
    $allowed_types = array(
      "image/jpg",
      "image/jpeg",
      "image/gif",
      "image/png",
      "image/webp",
      "image/heic"
    );

    if (isset($_FILES['new-picture']) && $_FILES['new-picture']['error'] === UPLOAD_ERR_OK) {
      $file = $_FILES['new-picture'];

      $file['name'] = FileUtils::generateRandomFilename(16) . '.' . pathinfo($file['name'], PATHINFO_EXTENSION);

      $mimetype = $file['type'];
      $filesize = $file['size'];
      $title = isset($_POST['image-title']) ? htmlspecialchars($_POST['image-title']) : '';
      $description = isset($_POST['description']) ? htmlspecialchars($_POST['description']) : '';

      if (!in_array($mimetype, $allowed_types)) {
        header("Location: /page/not_supported_file_type");
        exit();
      }

      if (!is_dir(dirname(__DIR__, 1) . "/model/uploads/images")) {
        mkdir(dirname(__DIR__, 1) . "/model/uploads/images", 0777);
      }

      try {
        $this->dbh->beginTransaction();

        $stmt = $this->dbh->prepare("INSERT INTO posts () VALUES ()");
        $stmt->execute();

        $postId = $this->dbh->lastInsertId();

        move_uploaded_file($file['tmp_name'], dirname(__DIR__, 1) . '/model/uploads/images/' . $file['name']);

        $stmt = $this->dbh->prepare("INSERT INTO images (post_id, filename, filetype, filesize, title, description) VALUES (?, ?, ?, ?, ?, ?)");

        $stmt->bindParam(1, $postId);
        $stmt->bindParam(2, $file['name']);
        $stmt->bindParam(3, $mimetype);
        $stmt->bindParam(4, $filesize);
        $stmt->bindParam(5, $title);
        $stmt->bindParam(6, $description);

        $stmt->execute();

        $this->dbh->commit();

        header("Location: /page/gallery");
        exit();
      } catch (Exception $e) {
        $this->dbh->rollBack();
        echo "Error al guardar la información en el servidor " . $e->getMessage();
      }
    } else {
      header("Location: /page/no_file_selected");
      exit();
    }
  }

  public function fetchAll(): ?array
  {
    $query = "SELECT image_id, filename, title FROM images";

    try {
      $stmt = $this->dbh->prepare($query);
      $stmt->execute();

      $images = $stmt->fetchAll(PDO::FETCH_ASSOC);

      return $images;

      // if ($images) {
      //   foreach ($images as $image) {
      //     $imagePath = '../model/uploads/images/' . $image['filename'];
      //     echo "<img src='$imagePath' alt='" . htmlspecialchars($image['title']) . "' />";
      //   }
      // } else {
      //   echo "<p>No images found.</p>";
      // }
    } catch (Exception $e) {
      return [
        'data' => [null],
        'error' => "Error fetching images: " . $e->getMessage()
      ];
    }
  }

  public function fetch($imageId): ?array
  {
    $query = "SELECT i.image_id, i.filename, i.title, i.description, p.created_at, p.updated_at
              FROM images i
              JOIN posts p ON i.post_id = p.post_id
              WHERE i.image_id = :image_id";

    try {
      $stmt = $this->dbh->prepare($query);

      $stmt->bindParam(':image_id', $imageId, PDO::PARAM_INT);
      $stmt->execute();

      $image = $stmt->fetch(PDO::FETCH_ASSOC);

      return $image ?: null;
      // if ($image) {
      //   $imagePath = '../model/uploads/images/' . $image['filename'];
      //   echo "<img src='$imagePath' alt='" . htmlspecialchars($image['title']) . "' />";
      //   echo "<h3>" . htmlspecialchars($image['title']) . "</h3>";
      //   echo "<p>" . htmlspecialchars($image['description']) . "</p>";
      //   echo "<p>Uploaded on: " . $image['created_at'] . "</p>";
      //   echo "<p>Last updated: " . $image['updated_at'] . "</p>";
      //   echo "</div>";
      // } else {
      //   echo "<p>Image not found.</p>";
      // }

    } catch (Exception $e) {
      return null;
    }
  }
}