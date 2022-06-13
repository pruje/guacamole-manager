<h1>Wake on LAN</h1>
<?php
    // process form
    if (isset($_POST['group']) && $_POST['group'] != '') {
        $alert = 'success';
        $message = '';

        $group = new ConnectionGroup(htmlspecialchars($_POST['group']));
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

    if (isset($_POST['connection']) && $_POST['connection'] != '') {
        $connection = Connection::getByName(htmlspecialchars($_POST['connection']));
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

<form method="POST" enctype="multipart/form-data">
  <div class="mb-3">
    <label class="form-label">Wake up a group:</label>
    <select name="group" class="form-control">
      <option value=""></option>
      <?php
          $connectionGroups = ConnectionGroup::getAll();
          foreach ($connectionGroups as $connectionGroup) {
              echo '<option value="'.$connectionGroup['id'].'">'.$connectionGroup['name'].'</option>';
          }
      ?>
    </select>
  </div>
  <div class="mb-3">
    <label class="form-label">or a connection:</label>
    <input class="form-control" type="text" name="connection">
  </div>
  <button type="submit" class="btn btn-primary mb-3">Wake up</button>
</form>
