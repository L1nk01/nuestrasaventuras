<?php
require_once dirname(__DIR__, 2) . '/model/config.php';
require_once dirname(__DIR__, 2) . '/controllers/ImageController.php';

global $database;
$imageController = new ImageController($database);

$images = $imageController->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Galeria</title>

  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"
    defer></script>

  <!-- JQuery -->
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"
    integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous" defer></script>

  <!-- Stylesheets -->
  <link rel="stylesheet" href="/public/stylesheets/fonts.css">
  <link rel="stylesheet" href="/public/stylesheets/styles.css">
  <link rel="stylesheet" href="/public/stylesheets/breakpoints.css">

  <!-- Scripts -->
  <script src="/public/src/index.js" defer></script>
</head>

<body>
  <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">Subir una imagen</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form id="upload-picture-form" action="/image/upload" method="post" enctype="multipart/form-data"
            autocomplete="off">
            <div id="uploaded-picture-preview">
              <img src="" alt="" />
              <button type="button" class="btn btn-danger">Eliminar imagen</button>
            </div>
            <span class="file-status">No se ha elegido un archivo</span>
            <label class="btn btn-secondary w-100" for="upload-picture-button">Elegir archivo</label>
            <input type="file" id="upload-picture-button" name="new-picture"
              accept=".jpg, .jpeg, .gif, .png, .webp, .heic" hidden required>
            <div>
              <label for="image-title">Título</label>
              <input type="text" id="image-title" name="image-title">
            </div>
            <div>
              <label for="image-description">Descripción</label>
              <textarea id="image-description" name="description" rows="4" cols="50"></textarea>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="button" class="btn btn-primary" id="submit-picture-form">Guardar imagen</button>
        </div>
      </div>
    </div>
  </div>

  <section class="navbar">
    <img src="/public/assets/brand/nuestras_aventuras.svg" alt="Nuestras Aventuras" />
    <ul>
      <li><a href="/">Inicio</a></li>
      <li class="selected"><a href="/page/gallery">Galeria</a></li>
      <li><a href="">Nuestra Historia</a></li>
      <li><a href="">Planes Futuros</a></li>
      <li><a href="">Notas</a></li>
    </ul>
  </section>

  <section class="gallery">
    <button class="default-button full-width-button" type="button" class="btn btn-primary" data-bs-toggle="modal"
      data-bs-target="#exampleModal">
      <img src="/public/assets/icons/upload.svg" />Subir una imagen
    </button>

    <div id="images-container">
      <?php
      if (isset($images['error'])) {
        echo "<p>Error: " . htmlspecialchars($images['error']) . "</p>";
      } else {
        foreach ($images as $image) {
          $imagePath = '../model/uploads/images/' . $image['filename'];
          echo "<img src='$imagePath' alt='" . htmlspecialchars($image['title']) . "' />";
        }
      }
      ?>
    </div>
  </section>
</body>

</html>