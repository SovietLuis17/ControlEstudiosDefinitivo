<?php
// app/Config/Institucion.php
// Datos de la institucion que aparecen en el encabezado de los reportes
// y parametros institucionales globales (como el cupo maximo por seccion)
// sin necesidad de modificar ni romper las tablas de la base de datos en produccion.

namespace App\Config;

class Institucion
{
    // Nombre que aparecera centrado en el encabezado de los reportes
    public const NOMBRE = 'Universidad Politecnica Territorial de Valencia';

    // Lema o subtitulo del sistema
    public const LEMA = 'Sistema de Control de Estudios';

    // Ruta absoluta del logo institucional (PNG o JPG)
    // Si el archivo no existe, los reportes se generan solo con texto
    public const LOGO = __DIR__ . '/../../public/assets/img/logo.jpg';

    // Cupo maximo estandar de estudiantes por seccion (aplicado en capa de negocio
    // sobre la tabla actual `estudiantes.seccion_id` sin requerir cambios en BD)
    public const CUPO_MAXIMO_SECCION = 40;
}