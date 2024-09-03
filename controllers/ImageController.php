<?php

require_once dirname(__DIR__, 1) . '/model/Connection.php';
require_once dirname(__DIR__, 1) . '/utils/FileUtils.php';

/**
 * Class ImageController
 *
 * Controller for managing image uploads, storage, and retrieval.
 * Also handles thumbnail generation and displaying image galleries.
 */
class ImageController
{
  /**
   * @var PDO $dbh Database connection.
   */
  private $dbh;

  /**
   * ImageController constructor.
   *
   * Initializes the database connection using the provided configuration.
   *
   * @param array $config Database configuration.
   */
  public function __construct($config)
  {
    $this->dbh = Connection::getInstance($config)->getConnection();
  }

  /**
   * Handles the upload of an image.
   *
   * This method processes the image uploaded by the user, saves it to the server,
   * stores the related information in the database, and creates a thumbnail for the image.
   * If an error occurs at any point, the transaction is rolled back.
   *
   * @return void
   */
  public function upload(): void
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

        if ($mimetype !== "image/jpeg" && $mimetype !== "image/gif") {
          $image = new Imagick($imagePath);
          $image->setImageCompressionQuality(70); // 70% of the original quality
          $image->writeImage($imagePath);
          $image->clear();
          $image->destroy();
        }

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

  /**
   * Creates a thumbnail for the given image.
   *
   * This method generates a thumbnail with the specified width and height, and saves it
   * to the server. The thumbnail is created with reduced quality to save space.
   *
   * @param string $source The path to the source image.
   * @param int $width The width of the thumbnail.
   * @param int $height The height of the thumbnail.
   *
   * @return bool Returns true on success, false on failure.
   */
  private function createThumbnail(string $source, int $width, int $height): bool
  {
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

  /**
   * Fetches a list of image thumbnails.
   *
   * This method retrieves all image thumbnails from the database, ordered by creation date.
   *
   * @return array Returns an associative array of images, or an array with an error message.
   */
  public function fetchThumbnails(): array
  {
    // image_id to store it as data attribute to find the server
    // file_name to use the correct image on each thumbnail
    // title for the alt attribute of the image
    // created_at to order each image from newest to oldest
    $query = "SELECT i.image_id, i.file_name, i.title, p.created_at
              FROM images i
              JOIN posts p ON i.post_id = p.post_id
              ORDER BY p.created_at DESC";

    try {
      $stmt = $this->dbh->prepare($query);
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

  /**
   * Fetches image data by ID.
   *
   * This method retrieves the details of an image by its ID and returns the information as JSON.
   * If no ID is provided or an error occurs, an error message is returned.
   *
   * @return void
   */
  public function fetch(): void
  {
    $imageId = isset($_GET['id']) ? intval($_GET['id']) : null;

    if ($imageId === null) {
      echo json_encode(['error' => 'No image ID provided.']);
      return;
    }

    $imageData = $this->fetchImageById($imageId);

    echo json_encode($imageData);
  }

  /**
   * Fetches image details from the database by image ID.
   *
   * This method retrieves image details, including its metadata and associated post information, by image ID.
   *
   * @param int $imageId The ID of the image to retrieve.
   *
   * @return array|null Returns an associative array of image details, or null if not found.
   */
  private function fetchImageById(int $imageId): ?array
  {
    $query = "SELECT i.image_id, i.file_name, i.file_extension, i.title, i.description, p.created_at, p.updated_at
    FROM images i
    JOIN posts p ON i.post_id = p.post_id
    WHERE i.image_id = :image_id";

    try {
      $stmt = $this->dbh->prepare($query);
      $stmt->bindParam(':image_id', $imageId, PDO::PARAM_INT);
      $stmt->execute();

      $image = $stmt->fetch(PDO::FETCH_ASSOC);

      return $image ?: null;
    } catch (Exception $e) {
      return ['error' => "Error obteniendo la imagen: " . $e->getMessage()];
    }
  }

  public function download(): void
  {
    $imageId = isset($_GET['id']) ? intval($_GET['id']) : null;

    if ($imageId === null) {
      echo "ID de imagen no proporcionado.";
      return;
    }

    $query = "SELECT file_name, file_extension FROM images WHERE image_id = :image_id";

    try {
      $stmt = $this->dbh->prepare($query);
      $stmt->bindParam(':image_id', $imageId, PDO::PARAM_INT);
      $stmt->execute();

      $image = $stmt->fetch(PDO::FETCH_ASSOC);

      if ($image) {
        $filePath = dirname(__DIR__, 1) . '/model/uploads/images/' . $image['file_name'] . $image['file_extension'];

        if (file_exists($filePath)) {
          header('Content-Description: File Transfer');
          header('Content-Type: application/octet-stream');
          header('Content-Disposition: attachment; filename="' . basename($filePath) . '"');
          header('Expires: 0');
          header('Cache-Control: must-revalidate');
          header('Pragma: public');
          header('Content-Length: ' . filesize($filePath));
          flush();
          readfile($filePath);
          exit;
        } else {
          echo "Archivo no encontrado.";
        }
      } else {
        echo "Imagen no encontrada.";
      }
    } catch (Exception $e) {
      echo "Error al obtener la imagen: " . $e->getMessage();
    }
  }
}
