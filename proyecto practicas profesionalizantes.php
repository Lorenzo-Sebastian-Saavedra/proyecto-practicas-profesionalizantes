<html>
<head>
  <style>
    /* Estilos CSS para la interfaz */
    body {
      font-family: Arial, sans-serif;
      background-color: lightblue;
    }
    h1 {
      text-align: center;
      color: white;
    }
    form {
      margin: 20px;
      padding: 20px;
      border: 1px solid black;
      background-color: white;
    }
    input {
      margin: 10px;
    }
    table {
      margin: 20px;
      border-collapse: collapse;
    }
    th, td {
      padding: 10px;
      border: 1px solid black;
    }
  </style>
</head>
<body>
  <h1>Plataforma para la gestión de vacaciones</h1>
  
  <?php
// Código PHP para conectar con la base de datos y mostrar la interfaz de la plataforma

// Datos de conexión con la base de datos
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "vacaciones";

// Crear la conexión con la base de datos
$conn = new mysqli($servername, $username, $password, $dbname);

// Comprobar si la conexión es exitosa
if ($conn->connect_error) {
  die("Conexión fallida: " . $conn->connect_error);
}

// Iniciar una sesión
session_start();

// Función para calcular las vacaciones disponibles de un empleado según su fecha de ingreso
function calcular_vacaciones($fecha_ingreso) {
  // Obtener la fecha actual
  $fecha_actual = date("Y-m-d");
  
  // Calcular la diferencia en años entre la fecha actual y la fecha de ingreso
  $diferencia = date_diff(date_create($fecha_ingreso), date_create($fecha_actual));
  $anios = $diferencia->format("%y");
  
  // Asignar las vacaciones disponibles según los años trabajados
  if ($anios < 1) {
    $vacaciones = 10;
  } elseif ($anios < 2) {
    $vacaciones = 15;
  } elseif ($anios < 5) {
    $vacaciones = 20;
  } else {
    $vacaciones = 25;
  }
  
  // Devolver el número de vacaciones disponibles
  return $vacaciones;
}

// Función para mostrar un pequeño calendario con las vacaciones solicitadas o aprobadas por los empleados
function mostrar_calendario($conn) {
  // Obtener el año y el mes actuales
  $anio = date("Y");
  $mes = date("m");
  
  // Obtener el número de días del mes actual
  $dias = cal_days_in_month(CAL_GREGORIAN, $mes, $anio);
  
  // Crear una tabla con el calendario
  echo "<table border='1'>";
  echo "<tr><th colspan='7'>Calendario de vacaciones</th></tr>";
  echo "<tr><td>L</td><td>M</td><td>X</td><td>J</td><td>V</td><td>S</td><td>D</td></tr>";
  
  // Recorrer los días del mes actual
  for ($i = 1; $i <= $dias; $i++) {
    // Obtener la fecha actual en formato YYYY-MM-DD
    $fecha = $anio . "-" . $mes . "-" . sprintf("%02d", $i);
    
    // Obtener el día de la semana de la fecha actual (0 para domingo, 6 para sábado)
    $dia_semana = date("w", strtotime($fecha));
    
    // Si es el primer día del mes, crear una nueva fila y rellenar con espacios vacíos hasta el día de la semana correspondiente
    if ($i == 1) {
      echo "<tr>";
      for ($j = 0; $j < $dia_semana; $j++) {
        echo "<td></td>";
      }
    }
    
    // Crear una celda con el número del día
    echo "<td>$i</td>";
    
    // Si es el último día del mes, rellenar con espacios vacíos hasta el final de la fila y cerrarla
    if ($i == $dias) {
      for ($j = $dia_semana + 1; $j < 7; $j++) {
        echo "<td></td>";
      }
      echo "</tr>";
    }
    
    // Si es sábado, cerrar la fila y crear una nueva
    if ($dia_semana == 6) {
      echo "</tr>";
      echo "<tr>";
    }
    
    // Consultar la base de datos para obtener las solicitudes de vacaciones que coincidan con la fecha actual
    $sql = "SELECT e.nombre, s.estado FROM solicitudes s JOIN empleados e ON s.id_empleado = e.id WHERE s.fecha_inicio <= '$fecha' AND s.fecha_fin >= '$fecha'";
    $result = $conn->query($sql);
    
    // Si hay resultados, mostrarlos debajo de la celda del día
    if ($result->num_rows > 0) {
      echo "<tr>";
      // Recorrer los resultados y mostrar el nombre del empleado y el estado de la solicitud
      while($row = $result->fetch_assoc()) {
        $nombre = $row["nombre"];
        $estado = $row["estado"];
        // Si el estado es pendiente, mostrarlo en amarillo
        if ($estado == "pendiente") {
          echo "<td bgcolor='yellow'>$nombre ($estado)</td>";
        }
        // Si el estado es aprobada, mostrarlo en verde
        elseif ($estado == "aprobada") {
          echo "<td bgcolor='green'>$nombre ($estado)</td>";
        }
        // Si el estado es rechazada, mostrarlo en rojo
        elseif ($estado == "rechazada") {
          echo "<td bgcolor='red'>$nombre ($estado)</td>";
        }
      }
      echo "</tr>";
    }
  }
  
  // Cerrar la tabla del calendario
  echo "</table>";
}

// Comprobar si se ha enviado el formulario de inicio de sesión
if (isset($_POST["login"])) {
  // Obtener el nombre y la contraseña introducidos por el usuario
  $nombre = $_POST["nombre"];
  $password = $_POST["password"];
  
  // Consultar la base de datos para comprobar si existe un empleado con ese nombre y contraseña
  $sql = "SELECT * FROM empleados WHERE nombre = '$nombre' AND password = '$password'";
  $result = $conn->query($sql);
  
  // Si hay un resultado, iniciar sesión como ese empleado y guardar su id y nombre en variables de sesión
  if ($result->num_rows == 1) {
    $row = $result->fetch_assoc();
    $_SESSION["id"] = $row["id"];
    $_SESSION["nombre"] = $row["nombre"];
    
    // Mostrar un mensaje de bienvenida al empleado
    echo "Bienvenido/a, " . $_SESSION["nombre"] . ".<br>";
    
    // Mostrar un botón para cerrar sesión
echo "<form method='post'>";
echo "<input type='submit' name='logout' value='Cerrar sesión'>";
echo "</form>";

// Comprobar si el empleado es el administrador o jefe (Carlos)
if ($_SESSION["nombre"] == "Carlos") {
  // Mostrar un enlace para ver las solicitudes pendientes de vacaciones
  echo "<a href='?accion=ver_solicitudes'>Ver solicitudes pendientes</a><br>";
}
else {
  // Mostrar un enlace para solicitar vacaciones
  echo "<a href='?accion=solicitar_vacaciones'>Solicitar vacaciones</a><br>";
}

// Mostrar el calendario con las vacaciones solicitadas o aprobadas por los empleados
mostrar_calendario($conn);
  ?>
  
  <?php
  // Comprobar si se ha enviado el formulario de solicitud de vacaciones
  if (isset($_POST["solicitar"])) {
    // Obtener el id del empleado que solicita las vacaciones
    $id_empleado = $_SESSION["id"];
    
    // Obtener la fecha de inicio y la fecha de fin introducidas por el empleado
    $fecha_inicio = $_POST["fecha_inicio"];
    $fecha_fin = $_POST["fecha_fin"];
    
    // Comprobar si las fechas son válidas (no vacías, no anteriores a la fecha actual, no superiores a un año desde la fecha actual)
    if ($fecha_inicio == "" || $fecha_fin == "" || $fecha_inicio < date("Y-m-d") || $fecha_fin < date("Y-m-d") || $fecha_inicio > date("Y-m-d", strtotime("+1 year")) || $fecha_fin > date("Y-m-d", strtotime("+1 year"))) {
      // Mostrar un mensaje de error
      echo "Las fechas no son válidas. Por favor, introduce unas fechas correctas.<br>";
    }
    else {
      // Calcular el número de días solicitados
      $diferencia = date_diff(date_create($fecha_inicio), date_create($fecha_fin));
      $dias_solicitados = $diferencia->format("%a") + 1;
      
      // Consultar la base de datos para obtener las vacaciones disponibles del empleado
      $sql = "SELECT vacaciones_disponibles FROM empleados WHERE id = '$id_empleado'";
      $result = $conn->query($sql);
      $row = $result->fetch_assoc();
      $vacaciones_disponibles = $row["vacaciones_disponibles"];
      
      // Comprobar si el número de días solicitados es menor o igual que las vacaciones disponibles
      if ($dias_solicitados <= $vacaciones_disponibles) {
        // Insertar la solicitud en la base de datos con estado pendiente y sin comentario
        $sql = "INSERT INTO solicitudes (id_empleado, fecha_inicio, fecha_fin, estado, comentario) VALUES ('$id_empleado', '$fecha_inicio', '$fecha_fin', 'pendiente', NULL)";
        if ($conn->query($sql) === TRUE) {
          // Mostrar un mensaje de confirmación
          echo "Tu solicitud ha sido enviada. Espera a que el administrador o jefe la apruebe o rechace.<br>";
        } else {
          // Mostrar un mensaje de error
          echo "Error al enviar la solicitud: " . $conn->error . "<br>";
        }
        
        // Actualizar las vacaciones disponibles del empleado restando los días solicitados
        $vacaciones_disponibles -= $dias_solicitados;
        $sql = "UPDATE empleados SET vacaciones_disponibles = '$vacaciones_disponibles' WHERE id = '$id_empleado'";
        if ($conn->query($sql) === TRUE) {
          // Mostrar un mensaje con las vacaciones disponibles actualizadas
          echo "Ahora tienes " . $vacaciones_disponibles . " días de vacaciones disponibles.<br>";
        } else {
          // Mostrar un mensaje de error
          echo "Error al actualizar las vacaciones disponibles: " . $conn->error . "<br>";
        }
      }
      else {
        // Mostrar un mensaje de error
        echo "No tienes suficientes vacaciones disponibles. Solo puedes solicitar hasta " . $vacaciones_disponibles . " días.<br>";
      }
    }
  }
  ?>
  
  <?php
  // Comprobar si se ha enviado el formulario de aceptar o rechazar una solicitud
  if (isset($_POST["aceptar"]) || isset($_POST["rechazar"])) {
    // Obtener el id de la solicitud
    $id_solicitud = $_POST["id_solicitud"];
    
    // Obtener el comentario introducido por el administrador o jefe
    $comentario = $_POST["comentario"];
    
    // Comprobar si se ha pulsado el botón de aceptar o el de rechazar
    if (isset($_POST["aceptar"])) {
      // Actualizar el estado de la solicitud a aprobada y guardar el comentario
      $sql = "UPDATE solicitudes SET estado = 'aprobada', comentario = '$comentario' WHERE id = '$id_solicitud'";
      if ($conn->query($sql) === TRUE) {
        // Mostrar un mensaje de confirmación
        echo "La solicitud ha sido aprobada.<br>";
      } else {
        // Mostrar un mensaje de error
        echo "Error al aprobar la solicitud: " . $conn->error . "<br>";
      }
    }
    elseif (isset($_POST["rechazar"])) {
      // Actualizar el estado de la solicitud a rechazada y guardar el comentario
      $sql = "UPDATE solicitudes SET estado = 'rechazada', comentario = '$comentario' WHERE id = '$id_solicitud'";
      if ($conn->query($sql) === TRUE) {
        // Mostrar un mensaje de confirmación
        echo "La solicitud ha sido rechazada.<br>";
      } else {
        // Mostrar un mensaje de error
        echo "Error al rechazar la solicitud: " . $conn->error . "<br>";
      }
      
      // Consultar la base de datos para obtener el id del empleado que hizo la solicitud y el número de días solicitados
      $sql = "SELECT id_empleado, fecha_inicio, fecha_fin FROM solicitudes WHERE id = '$id_solicitud'";
      $result = $conn->query($sql);
      $row = $result->fetch_assoc();
      $id_empleado = $row["id_empleado"];
      $fecha_inicio = $row["fecha_inicio"];
      $fecha_fin = $row["fecha_fin"];
      
      // Calcular el número de días solicitados
      $diferencia = date_diff(date_create($fecha_inicio), date_create($fecha_fin));
      $dias_solicitados = $diferencia->format("%a") + 1;
      
      // Consultar la base de datos para obtener las vacaciones disponibles del empleado
      $sql = "SELECT vacaciones_disponibles FROM empleados WHERE id = '$id_empleado'";
      $result = $conn->query($sql);
      $row = $result->fetch_assoc();
      $vacaciones_disponibles = $row["vacaciones_disponibles"];
      
      // Actualizar las vacaciones disponibles del empleado sumando los días solicitados
      $vacaciones_disponibles += $dias_solicitados;
      $sql = "UPDATE empleados SET vacaciones_disponibles = '$vacaciones_disponibles' WHERE id = '$id_empleado'";
      if ($conn->query($sql) === TRUE) {
        // Mostrar un mensaje con las vacaciones disponibles actualizadas
        echo "El empleado ahora tiene " . $vacaciones_disponibles . " días de vacaciones disponibles.<br>";
      } else {
        // Mostrar un mensaje de error
        echo "Error al actualizar las vacaciones disponibles: " . $conn->error . "<br>";
      }
    }
  }
  ?>
  
  <?php
  // Comprobar si se ha pulsado el enlace para solicitar vacaciones
  if (isset($_GET["accion"]) && $_GET["accion"] == "solicitar_vacaciones") {
    // Mostrar un formulario para solicitar vacaciones con dos campos para la fecha de inicio y la fecha de fin y un botón para enviar la solicitud
    echo "<form method='post'>";
    echo "<label for='fecha_inicio'>Fecha de inicio:</label>";
    echo "<input type='date' name='fecha_inicio' id='fecha_inicio'><br>";
    echo "<label for='fecha_fin'>Fecha de fin:</label>";
    echo "<input type='date' name='fecha_fin' id='fecha_fin'><br>";
    echo "<input type='submit' name='solicitar' value='solicitar_vacaciones’>"; echo “</form>”; } ?>

<?php
// Comprobar si se ha pulsado el enlace para ver las solicitudes pendientes
if (isset($_GET[“accion”]) && $_GET[“accion”] == “ver_solicitudes”) {
  // Consultar la base de datos para obtener las solicitudes de vacaciones con estado pendiente
  $sql = “SELECT s.id, e.nombre, s.fecha_inicio, s.fecha_fin FROM solicitudes s JOIN empleados e ON s.id_empleado = e.id WHERE s.estado = ‘pendiente’”; $result = $conn->query($sql);
  // Si hay resultados, mostrarlos en una tabla con un formulario para aceptar o rechazar cada solicitud
if ($result->num_rows > 0) {
  echo "<table>";
  echo "<tr><th>Nombre</th><th>Fecha de inicio</th><th>Fecha de fin</th><th>Acción</th></tr>";
  // Recorrer los resultados y mostrar el nombre del empleado, la fecha de inicio y la fecha de fin de la solicitud
  while($row = $result->fetch_assoc()) {
    $id_solicitud = $row["id"];
    $nombre = $row["nombre"];
    $fecha_inicio = $row["fecha_inicio"];
    $fecha_fin = $row["fecha_fin"];
    echo "<tr>";
    echo "<td>$nombre</td>";
    echo "<td>$fecha_inicio</td>";
    echo "<td>$fecha_fin</td>";
    // Mostrar un formulario con un campo para el comentario y dos botones para aceptar o rechazar la solicitud
    echo "<td>";
    echo "<form method='post'>";
    echo "<input type='hidden' name='id_solicitud' value='$id_solicitud'>";
    echo "<label for='comentario'>Comentario:</label>";
    echo "<input type='text' name='comentario' id='comentario'><br>";
    echo "<input type='submit' name='aceptar' value='Aceptar'>";
    echo "<input type='submit' name='rechazar' value='Rechazar'>";
    echo "</form>";
    echo "</td>";
    echo "</tr>";
  }
  echo "</table>";
}
else {
  // Mostrar un mensaje de que no hay solicitudes pendientes
  echo "No hay solicitudes pendientes de vacaciones.<br>";
}
?>
</body>
</html>
