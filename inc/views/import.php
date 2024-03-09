<h1>Connections import</h1>
<?php
    try {
        $templates = ConnectionTemplate::getAll();
    }
    catch (Throwable $t) {
        echo "You have an error with your database. Please initialize it if it's not done.";
        die();
    }
    if (!$templates || count($templates) == 0) {
?>
  <div class="alert alert-warning" role="alert">
    You have to <a href="help#add-template">create a template</a> before using connection import.
  </div>
<?php
        die();
    }

    // process form
    if (isset($_POST['template'])) {
        $template = htmlspecialchars($_POST['template']);
        $connectionGroup = htmlspecialchars($_POST['group']);
        $csv = $_FILES['csv']['tmp_name'];
        $userGroups = [];
        if (isset($_POST['users'])) {
            foreach ($_POST['users'] as $id) {
                $userGroups[] = htmlspecialchars($id);
            }
        }

        $result = Connection::import($template, $connectionGroup, $csv, $userGroups);

        $alert = 'danger';
        $message = 'Unknown error';

        if (isset($result['status'])) {
            switch ($result['status']) {
                case 'success':
                    $alert = 'success';
                    $message = 'Import finished!<br/>';
                    $message.= $result['imported'].' connection imported';
                    break;

                default:
                    $message = 'An error occured!<br/>';
                    $message.= implode('<br/>',$result['details']);
                    break;
            }
        }

        echo '<div class="alert alert-'.$alert.'" role="alert">'.$message.'</div>';
        die();
    }
?>

<form method="POST" enctype="multipart/form-data">
  <div class="mb-3">
    <label class="form-label">Connection template</label>
    <select name="template" class="form-control" required>
      <?php
          foreach ($templates as $template) {
              echo '<option value="'.$template['id'].'">'.$template['name'].'</option>';
          }
      ?>
    </select>
  </div>
  <div class="mb-3">
    <label class="form-label">Connection group</label>
    <select name="group" class="form-control" required>
      <?php
          $connectionGroups = ConnectionGroup::getAll();
          foreach ($connectionGroups as $connectionGroup) {
              echo '<option value="'.$connectionGroup['id'].'">'.$connectionGroup['name'].'</option>';
          }
      ?>
    </select>
  </div>
  <div class="mb-3">
    <label for="formFile" class="form-label">CSV file</label>
    <input class="form-control" type="file" name="csv" required>
  </div>
  <div class="mb-3">
    <label class="form-label">Give access to these user groups (optional)</label>
    <select name="users[]" multiple class="form-control">
      <?php
          $userGroups = UserGroup::getAll();
          foreach ($userGroups as $userGroup) {
              echo '<option value="'.$userGroup['id'].'">'.$userGroup['name'].'</option>';
          }
      ?>
    </select>
  </div>
  <button type="submit" class="btn btn-primary mb-3">Import</button>
</form>
