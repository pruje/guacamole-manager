<h1>Welcome to Guacamole manager!</h1>

<?php
    $templates = ConnectionTemplate::getAll();
    if (!$templates || count($templates) == 0) {
?>
  <div class="alert alert-warning" role="alert">
    You have to <a href="help#add-template">create a template</a> before using connection import.
  </div>
<?php
    }
?>

<p>You can:</p>
<ul>
  <li><a href="import">Import or update connections</a> from a CSV file</li>
  <li><a href="wakeonlan">Wake up connections</a> using Wake on LAN</li>
  <li><a href="help">Read help</a> for more informations</li>
</ul>
