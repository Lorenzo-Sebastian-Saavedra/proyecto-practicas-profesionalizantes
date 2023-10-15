<?php
// Conectar a la base de datos usando PDO
try {
  $conn = new PDO("mysql:host=localhost;dbname=vacaciones", "usuario", "clave");
  $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
  die("Error al conectar: " . $e->getMessage());
}

// Procesar la solicitud de vacaciones si se envio el formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  // Obtener los datos del formulario
  $id = $_POST["id"];
  $dias = $_POST["dias"];
  // Validar que el id sea un numero entero positivo
  if (filter_var($id, FILTER_VALIDATE_INT) && $id > 0) {
    // Validar que los días sean un numero entero positivo
    if (filter_var($dias, FILTER_VALIDATE_INT) && $dias > 0) {
      // Buscar el empleado por el id en la base de datos
      $sql = "SELECT * FROM empleados WHERE id = :id";
      $stmt = $conn->prepare($sql);
      $stmt->bindParam(":id", $id);
      $stmt->execute();
      // Si se encontro el empleado
      if ($stmt->rowCount() == 1) {
        // Obtener el registro del empleado como un arreglo asociativo
        $empleado = $stmt->fetch(PDO::FETCH_ASSOC);
        // Verificar que los dias solicitados no superen los dias disponibles
        if ($dias <= $empleado["dias_disponibles"]) {
          // Calcular los nuevos valores de dias tomados y disponibles
          $nuevos_dias_tomados = $empleado["dias_tomados"] + $dias;
          $nuevos_dias_disponibles = $empleado["dias_disponibles"] - $dias;
          // Actualizar la tabla empleados con los nuevos valores
          $sql = "UPDATE empleados SET dias_tomados = :dias_tomados, dias_disponibles = :dias_disponibles WHERE id = :id";
          $stmt = $conn->prepare($sql);
          $stmt->bindParam(":dias_tomados", $nuevos_dias_tomados);
          $stmt->bindParam(":dias_disponibles", $nuevos_dias_disponibles);
          $stmt->bindParam(":id", $id);
          $stmt->execute();
          // Mostrar un mensaje de exito
          echo "<p>Se ha registrado su solicitud de vacaciones. Disfrute de su descanso.</p>";
        } else {
          // Mostrar un mensaje de error
          echo "<p>No puede solicitar más días de los que tiene disponibles.</p>";
        }
      } else {
        // Mostrar un mensaje de error
        echo "<p>No se encontro el empleado con el id indicado.</p>";
      }
    } else {
      // Mostrar un mensaje de error
      echo "<p>Debe ingresar un numero valido de dias a solicitar.</p>";
    }
  } else {
    // Mostrar un mensaje de error
    echo "<p>Debe ingresar un id valido de empleado.</p>";
  }
}
// Obtener todos los empleados de la base de datos
$sql = "SELECT * FROM empleados";
$stmt = $conn->query($sql);
$empleados = $stmt->fetchAll(PDO::FETCH_ASSOC);
// Cerrar la conexión a la base de datos
$conn = null;
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Plataforma para la gestion de vacaciones</title>
</head>
<body>
  <h1>Plataforma para la gestion de vacaciones</h1>
  <h2>Informacion de los empleados y sus vacaciones</h2>
  <table border="1">
    <tr>
      <th>Id</th>
      <th>Nombre</th>
      <th>Apellido</th>
      <th>Email</th>
      <th>Dias disponibles</th>
      <th>Dias tomados</th>
    </tr>
    <?php foreach ($empleados as $empleado): ?>
    <tr>
      <td><?php echo $empleado["id"]; ?></td>
      <td><?php echo $empleado["nombre"]; ?></td>
      <td><?php echo $empleado["apellido"]; ?></td>
      <td><?php echo $empleado["email"]; ?></td>
      <td><?php echo $empleado["dias_disponibles"]; ?></td>
      <td><?php echo $empleado["dias_tomados"]; ?></td>
    </tr>
    <?php endforeach; ?>
  </table>
  <h2>Solicitar vacaciones</h2>
  <form method="post" action="index.php">
    <p>Ingrese su id de empleado: <input type="text" name="id"></p>
    <p>Ingrese el número de dias que desea solicitar: <input type="text" name="dias"></p>
    <p><input type="submit" value="Enviar"></p>
  </form>
</body>
</html>