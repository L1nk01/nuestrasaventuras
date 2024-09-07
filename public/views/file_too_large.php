<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Solicitud no procesada</title>
  <link rel="icon" href="/public/assets/icons/favicon.ico" type="image/x-icon">

  <!-- Stylesheets -->
  <link rel="stylesheet" href="/public/stylesheets/fonts.css">
  <link rel="stylesheet" href="/public/stylesheets/styles.css">
  <link rel="stylesheet" href="/public/stylesheets/breakpoints.css">

  <!-- Scripts -->
  <script src="/public/src/index.js" defer></script>
</head>

<body>
  <section class="error-page">
    <img src="/public/assets/icons/sad_face.svg"/>
    <h1>La imagen seleccionada es demasiado grande</h1>
    <p>El tamaño máximo permitido son 5MB</p>
    <button class="default-button" onclick="history.back()">Volver a la página anterior</button>
  </section>
</body>

</html>