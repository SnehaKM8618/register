<?php
// show_all.php - list with Edit / Delete actions
require 'config.php';

$res = $mysqli->query("SELECT id, full_name, email, course, submitted_at, profile_pic FROM registrations ORDER BY submitted_at DESC");
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>All Registrations</title>
  <link rel="stylesheet" href="assets/css/style.css">
  <style>
    table{width:100%;border-collapse:collapse;margin-top:12px}
    th, td{padding:10px;border-bottom:1px solid #eee;text-align:left}
    th{background:#fafafa}
    .action-btn{display:inline-block;padding:6px 10px;border-radius:8px;text-decoration:none;font-weight:600}
    .edit{background:#0b76ef;color:#fff}
    .del{background:#e53935;color:#fff;margin-left:6px}
    img.thumb{width:48px;height:48px;object-fit:cover;border-radius:6px}
  </style>
  <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
</head>
<body>
<div class="container">
  <h1>All Registrations</h1>

  <p><a href="index.html">Back to form</a></p>

  <table>
    <thead>
      <tr>
        <th>#</th>
        <th>Profile</th>
        <th>Name</th>
        <th>Email</th>
        <th>Course</th>
        <th>Submitted</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php while($r = $res->fetch_assoc()): ?>
      <tr>
        <td><?=htmlspecialchars($r['id'])?></td>
        <td><?php if($r['profile_pic']) echo "<img src='".htmlspecialchars($r['profile_pic'])."' class='thumb' alt='Profile'>"; ?></td>
        <td><?=htmlspecialchars($r['full_name'])?></td>
        <td><?=htmlspecialchars($r['email'])?></td>
        <td><?=htmlspecialchars($r['course'])?></td>
        <td><?=htmlspecialchars($r['submitted_at'])?></td>
        <td>
          <a class="action-btn edit" href="edit.php?id=<?=intval($r['id'])?>">Edit</a>
          <a class="action-btn del" href="delete.php?id=<?=intval($r['id'])?>" onclick="return confirmDelete(event, <?=intval($r['id'])?>);">Delete</a>
        </td>
      </tr>
      <?php endwhile; ?>
    </tbody>
  </table>

</div>

<script>
// simple confirm; keeps user from accidental deletes
function confirmDelete(e, id){
  e.preventDefault();
  if(confirm('Are you sure you want to delete registration #' + id + '? This cannot be undone.')) {
    // proceed to url
    window.location.href = 'delete.php?id=' + id;
  }
  return false;
}
</script>
</body>
</html>
