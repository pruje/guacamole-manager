<h1>Help</h1>

<h2>Connection import</h2>
<p>Connection import allows you to add or update connections from a CSV file.</p>

<h3 id="add-template">Create a new template</h3>
<p>To add a new template:</p>
<ol>
  <li>Connect to your guacamole instance and go to parameters</li>
  <li>Add a new Connection Group named <strong>Templates</strong></li>
  <li>Create a connection inside it and configure all fields as you need</li>
</ol>

<h3 id="csv-import">CSV file for import</h3>
<p>The CSV file must have headers that contains parameters to be overwritten.</p>
<p>e.g. a CSV with headers <strong>hostname,wol-mac-addr</strong> will import a bunch of machines with their MAC addresses for Wake on LAN</p>
<div class="alert alert-info" role="alert">
  Note: if <strong>connection_name</strong> is not specified, it will be replaced by
  the host part of the <strong>hostname</strong> field.
</div>

<h2>Wake on LAN</h2>
<p>You can wake up a group of connections or a single connection.</p>

<h2>Credits</h2>
<p>Guacamole manager is licensed under the MIT License.</p>
<p>Source code is <a href="https://github.com/pruje/guacamole-manager">available on Github</a>.</p>
