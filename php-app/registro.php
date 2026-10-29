<?php
/**
 * registro.php — Sistema de Registro de Contacto (PHP).
 *
 * Cumple la rúbrica grupal:
 *  - Valida método POST (405 si no lo es).
 *  - Sanitiza y valida entradas (filter_input + htmlspecialchars → anti-XSS).
 *  - Almacena los contactos en $_SESSION (varios registros acumulados).
 *  - Devuelve una respuesta con TABLA de todos los registros.
 */

session_start();

// --- Validación de método: solo POST ---
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // 405 Method Not Allowed
    exit('Método no permitido. Use el formulario (POST).');
}

// --- Sanitización y validación con filter_input() ---
$nombre  = trim(filter_input(INPUT_POST, 'nombre', FILTER_SANITIZE_SPECIAL_CHARS));
$correo  = filter_input(INPUT_POST, 'correo', FILTER_VALIDATE_EMAIL);
$mensaje = trim(filter_input(INPUT_POST, 'mensaje', FILTER_SANITIZE_SPECIAL_CHARS));

$errores = [];
if ($nombre === '')       { $errores[] = 'El nombre es obligatorio.'; }
if ($correo === false)    { $errores[] = 'El correo electrónico no es válido.'; }
if ($mensaje === '')      { $errores[] = 'El mensaje es obligatorio.'; }

// --- Almacenamiento en sesión (solo si no hay errores) ---
if (empty($errores)) {
    if (!isset($_SESSION['registros'])) {
        $_SESSION['registros'] = [];
    }
    $_SESSION['registros'][] = [
        'nombre'  => $nombre,
        'correo'  => $correo,
        'mensaje' => $mensaje,
        'fecha'   => date('Y-m-d H:i:s'),
    ];
}

$registros = $_SESSION['registros'] ?? [];

// Helper para escapar salida (anti-XSS)
function e($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

header('Content-Type: text/html; charset=utf-8');
?>
<!doctype html>
<html lang="es">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Registros · PHP</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet" />
    <style>
      * { margin: 0; padding: 0; box-sizing: border-box; }
      body {
        font-family: "Poppins", sans-serif; min-height: 100vh; padding: 2rem 1.5rem;
        background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 50%, #38bdf8 100%);
      }
      .caja {
        background: #fff; width: 100%; max-width: 760px; margin: 0 auto; border-radius: 20px;
        padding: 2.5rem; box-shadow: 0 25px 60px -15px rgba(0,0,0,.4); animation: aparecer .5s ease;
      }
      @keyframes aparecer { from { opacity:0; transform: translateY(20px);} to {opacity:1; transform:translateY(0);} }
      .cabecera { text-align: center; margin-bottom: 1.5rem; }
      .emoji { font-size: 2.4rem; }
      h1 { font-size: 1.4rem; color: #1e293b; margin-top: .3rem; }
      .desc { color: #94a3b8; font-size: .85rem; margin-top: .25rem; }
      .lista-err { list-style: none; margin-bottom: 1.5rem; }
      .lista-err li {
        padding: .75rem 1rem; background: #fef2f2; border-left: 3px solid #ef4444;
        border-radius: 8px; margin-bottom: .5rem; color: #b91c1c; font-size: .88rem;
      }
      .contador {
        display: inline-block; background: #dbeafe; color: #1d4ed8; font-size: .78rem;
        font-weight: 600; padding: .3rem .9rem; border-radius: 999px; margin-bottom: 1rem;
      }
      table { width: 100%; border-collapse: collapse; font-size: .88rem; }
      thead th {
        background: #1d4ed8; color: #fff; text-align: left; padding: .75rem .9rem; font-weight: 600;
      }
      thead th:first-child { border-radius: 10px 0 0 0; }
      thead th:last-child { border-radius: 0 10px 0 0; }
      tbody td { padding: .7rem .9rem; border-bottom: 1px solid #eef2f7; color: #334155; }
      tbody tr:nth-child(even) { background: #f8fafc; }
      .vacio { text-align: center; color: #94a3b8; padding: 2rem; }
      .volver {
        display: block; text-align: center; margin-top: 1.5rem; padding: .85rem; border-radius: 12px;
        background: #f1f5f9; color: #334155; text-decoration: none; font-weight: 600; font-size: .9rem; transition: background .2s;
      }
      .volver:hover { background: #e2e8f0; }
    </style>
  </head>
  <body>
    <div class="caja">
      <div class="cabecera">
        <div class="emoji">📇</div>
        <h1>Registros de Contacto (PHP)</h1>
        <p class="desc">Almacenados en <strong>$_SESSION</strong> del servidor</p>
      </div>

      <?php if (!empty($errores)): ?>
        <ul class="lista-err">
          <?php foreach ($errores as $err): ?>
            <li><?= e($err) ?></li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>

      <span class="contador">Total de registros: <?= count($registros) ?></span>

      <?php if (empty($registros)): ?>
        <p class="vacio">Aún no hay contactos registrados.</p>
      <?php else: ?>
        <table>
          <thead>
            <tr><th>#</th><th>Nombre</th><th>Correo</th><th>Mensaje</th><th>Fecha</th></tr>
          </thead>
          <tbody>
            <?php foreach ($registros as $i => $r): ?>
              <tr>
                <td><?= $i + 1 ?></td>
                <td><?= e($r['nombre']) ?></td>
                <td><?= e($r['correo']) ?></td>
                <td><?= e($r['mensaje']) ?></td>
                <td><?= e($r['fecha']) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>

      <a class="volver" href="index.html">← Registrar otro contacto</a>
    </div>
  </body>
</html>
