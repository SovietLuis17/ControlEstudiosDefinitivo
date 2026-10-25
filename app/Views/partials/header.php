<?php
// app/Views/partials/header.php
// Encabezado global HTML: incluye meta tag CSRF (SEC-01), Bootstrap 5.3, SweetAlert2 y el Sistema de Diseño UPT Valencia
$pageTitle = $pageTitle ?? 'Control de Estudios | UPT Valencia';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <!-- Bootstrap 5.3 Local -->
    <link href="<?= BASE_URL ?>assets/css/bootstrap.min.css" rel="stylesheet">
    <!-- SweetAlert2 Local -->
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/sweetalert2.min.css" />
    <!-- Sistema de Diseño Institucional UPT Valencia -->
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/app.css" />
    <script src="<?= BASE_URL ?>assets/js/sweetalert2@11.js"></script>
</head>