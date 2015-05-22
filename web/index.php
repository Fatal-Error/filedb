<?php
require_once __DIR__ . '/../filedb.inc';
require_once __DIR__ . '/../lib/dds/FileDBA.php';

try {
  $tables = FileDBA::showTables();
}
catch (Exception $e) {
  $error = $e->getMessage();
}

$base_path = 'http://' . $_SERVER['HTTP_HOST'];
$admin_path = $base_path . '/admin/service.php';
?>
<html>
<head>
  <title>FileDB</title>
  <link href='http://fonts.googleapis.com/css?family=Signika+Negative' rel='stylesheet' type='text/css'>
  <link rel="stylesheet" href="//code.jquery.com/ui/1.11.2/themes/smoothness/jquery-ui.css">
  <link href="css/filedbadmin.css" type="text/css" rel="stylesheet"/>
  <script src="js/jquery-1.10.2.min.js" type="text/javascript"></script>
  <script src="js/jquery-ui.js"></script>
  <script src="js/filedbadmin.js" type="text/javascript"></script>
</head>
<body>
  <h1><a href="<?php print $base_path ?>" title="FileDB">File<span class="red">DB</span></a></h1>

  <?php if (!empty($error)): ?>
    <div class="error">
      <p><?php print $error; ?></p>
    </div>
  <?php endif; ?>

  <div id="sidebar-left">
    <h2>Tables</h2>
    <ul id="tables-list" class="list">
      <?php foreach ($tables as $tableName): ?>
        <li>
          <a class="service-call tablename" href="<?php print $admin_path . '?op=show&table=' . $tableName ?>" title="<?php print $tableName ?>"><?php print $tableName ?></a>
        </li>
      <?php endforeach; ?>
    </ul>
  </div>

  <div id="content-wrapper">
    <table class="tablesorter">
      <thead>
      <tr>
        <th>Table</th>
        <th>Action</th>
        <th>Rows</th>
      </tr>
      </thead>
      <tbody>
      <?php foreach ($tables as $tableName): ?>
        <?php $table = FileDB::getTable($tableName); ?>
        <tr>
          <td class="tablename"><?php print $tableName; ?></td>
          <td>
            <ul class="actions-list">
              <li><a class="service-call" href="<?php print $admin_path . '?op=show&table=' . $tableName ?>" title="Show">Show</a></li>
              <li><a class="service-call" href="<?php print $admin_path . '?op=structure&table=' . $tableName ?>" title="Structure">Structure</a></li>
            </ul>
          </td>
          <td><?php print count($table->getRows()) ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</body>
</html>