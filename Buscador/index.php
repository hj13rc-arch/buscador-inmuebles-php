<?php
$datos = json_decode(file_get_contents(__DIR__ . '/data-1.json'), true, 512, JSON_THROW_ON_ERROR);
$ciudades = array_unique(array_column($datos, 'Ciudad'));
$tipos = array_unique(array_column($datos, 'Tipo'));
sort($ciudades);
sort($tipos);

function precioNumerico(array $inmueble): float
{
    return (float) str_replace(['$', ','], '', $inmueble['Precio']);
}

function parametro(string $nombre): string
{
    return is_string($_GET[$nombre] ?? null) ? $_GET[$nombre] : '';
}

function escapar(string $texto): string
{
    return htmlspecialchars($texto, ENT_QUOTES, 'UTF-8');
}

$precioMaximo = (int) ceil(max(array_map('precioNumerico', $datos)));
$accion = parametro('accion');
$ciudad = parametro('ciudad');
$tipo = parametro('tipo');
$rango = parametro('precio');
$minimo = 0;
$maximo = $precioMaximo;
if (preg_match('/^([0-9]+);([0-9]+)$/D', $rango, $valores)) {
    $minimo = (int) $valores[1];
    $maximo = (int) $valores[2];
}
$error = $accion === 'buscar' && ($minimo > $maximo || ($rango !== '' && !isset($valores[2])));
$resultados = [];
if ($accion === 'todos') {
    $resultados = $datos;
} elseif ($accion === 'buscar' && !$error) {
    $resultados = array_filter($datos, static function (array $inmueble) use ($ciudad, $tipo, $minimo, $maximo): bool {
        $precio = precioNumerico($inmueble);
        return $precio >= $minimo && $precio <= $maximo
            && ($ciudad === '' || $inmueble['Ciudad'] === $ciudad)
            && ($tipo === '' || $inmueble['Tipo'] === $tipo);
    });
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <link type="text/css" rel="stylesheet" href="css/materialize.min.css"  media="screen,projection"/>
  <link type="text/css" rel="stylesheet" href="css/customColors.css"  media="screen,projection"/>
  <link type="text/css" rel="stylesheet" href="css/ion.rangeSlider.css"  media="screen,projection"/>
  <link type="text/css" rel="stylesheet" href="css/ion.rangeSlider.skinFlat.css"  media="screen,projection"/>
  <link type="text/css" rel="stylesheet" href="css/index.css"  media="screen,projection"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Buscador de bienes raíces</title>
</head>

<body>

  <div class="contenedor">
    <div class="card rowTitulo">
      <h1>Buscador</h1>
    </div>
    <div class="colFiltros">
      <form action="index.php" method="get" id="formulario">
        <div class="filtrosContenido">
          <div class="tituloFiltros">
            <h5>Realiza una búsqueda personalizada</h5>
          </div>
          <div class="filtroCiudad input-field">
            <label for="selectCiudad">Ciudad:</label>
            <select name="ciudad" id="selectCiudad">
              <option value="">Elige una ciudad</option>
            <?php foreach ($ciudades as $opcion): ?>
              <option value="<?= escapar($opcion) ?>" <?= $ciudad === $opcion ? 'selected' : '' ?>><?= escapar($opcion) ?></option>
            <?php endforeach; ?>
            </select>
          </div>
          <div class="filtroTipo input-field">
            <label for="selectTipo">Tipo:</label><br>
            <select name="tipo" id="selectTipo">
              <option value="">Elige un tipo</option>
            <?php foreach ($tipos as $opcion): ?>
              <option value="<?= escapar($opcion) ?>" <?= $tipo === $opcion ? 'selected' : '' ?>><?= escapar($opcion) ?></option>
            <?php endforeach; ?>
            </select>
          </div>
          <div class="filtroPrecio">
            <label for="rangoPrecio">Precio:</label>
            <input type="text" id="rangoPrecio" name="precio" value="<?= $error ? 0 : min($minimo, $precioMaximo) ?>;<?= $error ? $precioMaximo : min($maximo, $precioMaximo) ?>" data-max="<?= $precioMaximo ?>" />
          </div>
          <div class="botonField">
            <button type="submit" class="btn white" name="accion" value="buscar" id="submitButton">Buscar</button>
          </div>
        </div>
      </form>
    </div>

    <div class="colContenido">
      <div class="tituloContenido card">
        <h5>Resultados de la búsqueda:</h5>
        <div class="divider"></div>
        <a href="index.php?accion=todos" class="btn-flat waves-effect" id="mostrarTodos">Mostrar todos</a>
      </div>
      <div class="resultados" aria-label="Resultados">
        <?php if ($error): ?>
          <p role="alert">Indicá un rango de precios válido.</p>
        <?php elseif ($accion === 'buscar' || $accion === 'todos'): ?>
          <p id="totalResultados"><?= count($resultados) ?> inmuebles encontrados</p>
          <?php foreach ($resultados as $inmueble): ?>
            <article class="card horizontal itemMostrado" data-id="<?= escapar((string) $inmueble['Id']) ?>">
              <img src="img/home.jpg" alt="" loading="lazy">
              <div class="card-stacked">
                <div class="card-content">
                  <p><strong>Dirección</strong> <?= escapar($inmueble['Direccion']) ?></p>
                  <p><strong>Ciudad</strong> <?= escapar($inmueble['Ciudad']) ?></p>
                  <p><strong>Teléfono</strong> <?= escapar($inmueble['Telefono']) ?></p>
                  <p><strong>Código postal</strong> <?= escapar($inmueble['Codigo_Postal']) ?></p>
                  <p><strong>Tipo</strong> <?= escapar($inmueble['Tipo']) ?></p>
                  <p><strong>Precio</strong> <span class="precioTexto"><?= escapar($inmueble['Precio']) ?></span></p>
                </div>
              </div>
            </article>
          <?php endforeach; ?>
        <?php else: ?>
          <p>Elegí los filtros o pulsá Mostrar todos.</p>
        <?php endif; ?>
      </div>

    </div>
  </div>

  <script type="text/javascript" src="js/jquery-3.0.0.js"></script>
  <script type="text/javascript" src="js/ion.rangeSlider.min.js"></script>
  <script type="text/javascript" src="js/materialize.min.js"></script>
  <script type="text/javascript" src="js/index.js"></script>
</body>
</html>


