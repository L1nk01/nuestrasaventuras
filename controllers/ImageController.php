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

      $file_extension = "." . pathinfo($file['name'], PATHINFO_EXTENSION); // .jpg, .png, .jpeg...
      $file['name'] = FileUtils::generateRandomFilename(16);
      $mimetype = $file['type']; // image/jpg, image/png, image/jpeg
      $file_size = $file['size']; // in bytes
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

        $imagePath = dirname(__DIR__, 1) . '/model/uploads/images/' . $file['name'] . $file_extension;
        move_uploaded_file($file['tmp_name'], $imagePath);

        $image = new Imagick($imagePath);
        $image->setImageCompressionQuality(70); // 70% of the original quality
        $image->writeImage($imagePath);
        $image->clear();
        $image->destroy();

        $stmt = $this->dbh->prepare("INSERT INTO images (post_id, file_name, file_extension, file_size, title, description) VALUES (?, ?, ?, ?, ?, ?)");

        $stmt->bindParam(1, $postId);
        $stmt->bindParam(2, $file['name']);
        $stmt->bindParam(3, $file_extension);
        $stmt->bindParam(4, $file_size);
        $stmt->bindParam(5, $title);
        $stmt->bindParam(6, $description);

        $stmt->execute();

        if ($this->createThumbnail($imagePath, 500, 500)) {
          $this->dbh->commit();
          header("Location: /page/gallery");
          exit();
        } else {
          $this->dbh->rollBack();
          echo "Error saving thumbnail.";
        }

      } catch (Exception $e) {
        $this->dbh->rollBack();
        echo "Error while saving data to server " . $e->getMessage();
      }
    } else {
      header("Location: /page/no_file_selected");
      exit();
    }
  }

  public function fetch(?int $imageId = null): ?array
  {
    $query = "SELECT i.image_id, i.file_name, i.file_extension, i.title, i.description, p.created_at, p.updated_at
    FROM images i
    JOIN posts p ON i.post_id = p.post_id";

    if ($imageId !== null) {
      $query .= " WHERE i.image_id = :image_id";
    }

    $query .= " ORDER BY p.created_at DESC";

    try {
      $stmt = $this->dbh->prepare($query);

      if ($imageId !== null) {
        $stmt->bindParam(':image_id', $imageId, PDO::PARAM_INT);
      }

      $stmt->execute();

      $images = $stmt->fetchAll(PDO::FETCH_ASSOC);

      return $images;
    } catch (Exception $e) {
      return [
        'data' => [null],
        'error' => "Error fetching images: " . $e->getMessage()
      ];
    }
  }
  
  private function createThumbnail($source, $width, $height) {
    try {
      $image = new Imagick($source);

      $image->cropThumbnailImage($width, $height);

      $image->setImageCompressionQuality(50);

      $image->setImageFormat('jpeg');

      if (!is_dir(dirname(__DIR__, 1) . "/model/uploads/thumbnails")) {
        mkdir(dirname(__DIR__, 1) . "/model/uploads/thumbnails", 0777);
      }

      $thumbnailPath = dirname(__DIR__, 1) . "/model/uploads/thumbnails/" . pathinfo($source, PATHINFO_FILENAME) . "_thumbnail.jpeg";
      $image->writeImage($thumbnailPath);

      $image->clear();
      $image->destroy();

      return true;
    } catch (Exception $e) {
      echo "Error creating thumbnail: " . $e->getMessage();
      return false;
    }
  }

  // Estos dos métodos quedarán aquí enterrados por si los necesito en el futuro,
  // espero que no olvide su existencia.

  // public function fetchAll(): ?array
  // {
  //   $query = "SELECT image_id, filename, title FROM images";

  //   try {
  //     $stmt = $this->dbh->prepare($query);
  //     $stmt->execute();

  //     $images = $stmt->fetchAll(PDO::FETCH_ASSOC);

  //     return $images;
  //   } catch (Exception $e) {
  //     return [
  //       'data' => [null],
  //       'error' => "Error fetching images: " . $e->getMessage()
  //     ];
  //   }
  // }

//   public function fetch($imageId): ?array
//   {
//     $query = "SELECT i.image_id, i.filename, i.title, i.description, p.created_at, p.updated_at
//               FROM images i
//               JOIN posts p ON i.post_id = p.post_id
//               WHERE i.image_id = :image_id";

//     try {
//       $stmt = $this->dbh->prepare($query);

//       $stmt->bindParam(':image_id', $imageId, PDO::PARAM_INT);
//       $stmt->execute();

//       $image = $stmt->fetch(PDO::FETCH_ASSOC);

//       return $image ?: null;
//     } catch (Exception $e) {
//       return null;
//     }
//   }
}