<h1>Wake on LAN</h1>
<?php
    // process form
    if (isset($_GET['group']) && $_GET['group'] != '') {
        $alert = 'success';
        $message = '';

        $group = new ConnectionGroup(htmlspecialchars($_GET['group']));
        if (!$group) {
            echo 'Connection group not found';
            die();
        }

        foreach ($group->getConnections() as $connection) {
            if ($connection->wakeUp(7) && $connection->wakeUp()) {
                $message .= 'Waked up: '.$connection->getParameter('connection_name').'<br/>';
            } else {
                $alert = 'danger';
                $message .= 'Failed to wake up: '.$connection->getParameter('connection_name').'<br/>';
            }
        }

        echo '<div class="alert alert-'.$alert.'" role="alert">'.$message.'</div>';
        die();
    }

    if (isset($_GET['connection']) && $_GET['connection'] != '') {
        $connection = Connection::getByName(htmlspecialchars($_GET['connection']));
        if (!$connection) {
            echo 'Connection not found';
            die();
        }

        $alert = 'danger';
        $message = 'Failed to wake up';

        if ($connection->wakeUp(7) && $connection->wakeUp()) {
            $alert = 'success';
            $message = 'Wake on Lan sent';
        }

        echo '<div class="alert alert-'.$alert.'" role="alert">'.$message.'</div>';
        die();
    }
?>

<form method="GET" enctype="multipart/form-data">
  <?php
      $connectionGroups = ConnectionGroup::getAll();

      if ($connectionGroups) {
  ?>

  <div class="mb-3">
    <label class="form-label">Wake up a group:</label>
    <select name="group" class="form-control">
      <option value=""></option>
      <?php
          foreach ($connectionGroups as $connectionGroup) {
              echo '<option value="'.$connectionGroup['id'].'">'.$connectionGroup['name'].'</option>';
          }
      ?>
    </select>
  </div>
  <?php
      }
  ?>
  <div class="mb-3">
    <label class="form-label">Wake up a single connection:</label>
    <input class="form-control" type="text" name="connection">
  </div>
  <button type="submit" class="btn btn-primary mb-3">Wake up</button>
</form>
