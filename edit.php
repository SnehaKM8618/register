<?php
// edit.php - show a pre-filled form for the given id and handle update on POST
require 'config.php';

function clean($v){ return htmlspecialchars(trim($v)); }

// If no id, redirect back
$id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
if (!$id) {
    header('Location: show_all.php');
    exit;
}

// Handle POST -> update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // collect and sanitize
    $full_name = $_POST['full_name'] ?? '';
    $dob = $_POST['dob'] ?: null;
    $gender = $_POST['gender'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $father_name = $_POST['father_name'] ?? '';
    $mother_name = $_POST['mother_name'] ?? '';
    $enrollment_no = $_POST['enrollment_no'] ?? '';
    $course = $_POST['course'] ?? '';
    $semester = $_POST['semester'] ?? '';
    $address = $_POST['address'] ?? '';
    $city = $_POST['city'] ?? '';
    $state = $_POST['state'] ?? '';
    $country = $_POST['country'] ?? '';
    $pincode = $_POST['pincode'] ?? '';
    $blood_group = $_POST['blood_group'] ?? '';
    $skills = $_POST['skills'] ?? '';
    $hobbies = $_POST['hobbies'] ?? '';

    // server-side minimal validation
    $errors = [];
    if (trim($full_name) === '') $errors[] = "Full name required.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid email required.";

    // fetch current profile_pic path
    $stmt = $mysqli->prepare("SELECT profile_pic FROM registrations WHERE id = ?");
    $stmt->bind_param("i",$id);
    $stmt->execute();
    $res = $stmt->get_result();
    $existing = $res->fetch_assoc();
    $stmt->close();

    $profile_pic_db = $existing['profile_pic'];

    // handle optional uploaded new picture
    if (!empty($_FILES['profile_pic']['name']) && $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
        $f = $_FILES['profile_pic'];
        $allowed = ['image/jpeg','image/png','image/jpg','image/gif'];
        if (!in_array($f['type'], $allowed)) {
            $errors[] = "Only JPG/PNG/GIF images allowed.";
        } elseif ($f['size'] > 2*1024*1024) {
            $errors[] = "Profile picture must be under 2MB.";
        } else {
            $ext = pathinfo($f['name'], PATHINFO_EXTENSION);
            $newName = uniqid('prof_', true) . '.' . $ext;
            $upload_dir = __DIR__ . '/uploads/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
            $dest = $upload_dir . $newName;
            if (move_uploaded_file($f['tmp_name'], $dest)) {
                // remove old file if exists
                if ($profile_pic_db && file_exists(__DIR__ . '/' . $profile_pic_db)) {
                    @unlink(__DIR__ . '/' . $profile_pic_db);
                }
                $profile_pic_db = 'uploads/' . $newName;
            } else {
                $errors[] = "Failed to save uploaded file.";
            }
        }
    }

    if (!empty($errors)) {
        // show errors below the form (handled later)
    } else {
        // perform update
        $sql = "UPDATE registrations SET full_name=?, dob=?, gender=?, email=?, phone=?, father_name=?, mother_name=?, enrollment_no=?, course=?, semester=?, address=?, city=?, state=?, country=?, pincode=?, blood_group=?, skills=?, hobbies=?, profile_pic=? WHERE id=?";
        $stmt = $mysqli->prepare($sql);
        if (!$stmt) { die("Prepare failed: " . $mysqli->error); }
        $stmt->bind_param("sssssssssssssssssssi",
          $full_name, $dob, $gender, $email, $phone, $father_name, $mother_name, $enrollment_no, $course, $semester, $address, $city, $state, $country, $pincode, $blood_group, $skills, $hobbies, $profile_pic_db, $id
        );
        $ok = $stmt->execute();
        $stmt->close();
        if ($ok) {
            header('Location: show_all.php?msg=updated');
            exit;
        } else {
            $errors[] = "Update failed: " . $mysqli->error;
        }
    }
}

// Fetch row to populate form (for GET and if update had errors)
$stmt = $mysqli->prepare("SELECT * FROM registrations WHERE id = ?");
$stmt->bind_param("i",$id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$stmt->close();
if (!$row) { header('Location: show_all.php'); exit; }

?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Edit Registration #<?=intval($id)?></title>
  <link rel="stylesheet" href="assets/css/style.css">
  <style>
    .profile-small{width:120px;height:120px;object-fit:cover;border-radius:8px;margin-bottom:8px}
    .errors{background:#ffe6e6;padding:10px;border:1px solid #f5c2c2;border-radius:8px;margin-bottom:12px}
  </style>
</head>
<body>
<div class="container">
  <h1>Edit Registration #<?=intval($id)?></h1>
  <p><a href="show_all.php">← Back to list</a></p>

  <?php if (!empty($errors)): ?>
    <div class="errors"><strong>Please fix these errors:</strong><ul><?php foreach($errors as $er) echo "<li>".htmlspecialchars($er)."</li>"; ?></ul></div>
  <?php endif; ?>

  <form action="edit.php?id=<?=intval($id)?>" method="post" enctype="multipart/form-data">
    <div class="row"><label>Full Name *</label><input type="text" name="full_name" value="<?=htmlspecialchars($_POST['full_name'] ?? $row['full_name'])?>" required></div>
    <div class="row"><label>Date of Birth</label><input type="date" name="dob" value="<?=htmlspecialchars($_POST['dob'] ?? $row['dob'])?>"></div>
    <div class="row"><label>Gender</label>
      <select name="gender">
        <option value="">Select</option>
        <?php foreach(['Female','Male','Other'] as $g): $sel = (($_POST['gender'] ?? $row['gender'])==$g) ? 'selected' : ''; ?>
          <option <?=$sel?>><?=$g?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="row"><label>Email *</label><input type="email" name="email" value="<?=htmlspecialchars($_POST['email'] ?? $row['email'])?>" required></div>
    <div class="row"><label>Phone</label><input type="tel" name="phone" value="<?=htmlspecialchars($_POST['phone'] ?? $row['phone'])?>"></div>

    <div class="row"><label>Father's Name</label><input type="text" name="father_name" value="<?=htmlspecialchars($_POST['father_name'] ?? $row['father_name'])?>"></div>
    <div class="row"><label>Mother's Name</label><input type="text" name="mother_name" value="<?=htmlspecialchars($_POST['mother_name'] ?? $row['mother_name'])?>"></div>
    <div class="row"><label>Enrollment No.</label><input type="text" name="enrollment_no" value="<?=htmlspecialchars($_POST['enrollment_no'] ?? $row['enrollment_no'])?>"></div>

    <div class="row"><label>Course</label>
      <select name="course">
        <?php
        $courses = ['','B.Tech','MCA','BCA','M.Tech','Other'];
        foreach($courses as $c){ $sel = (($_POST['course'] ?? $row['course'])==$c) ? 'selected' : ''; echo "<option $sel>".htmlspecialchars($c)."</option>"; }
        ?>
      </select>
    </div>

    <div class="row"><label>Semester</label><input type="text" name="semester" value="<?=htmlspecialchars($_POST['semester'] ?? $row['semester'])?>"></div>

    <div class="row"><label>Address</label><textarea name="address" rows="2"><?=htmlspecialchars($_POST['address'] ?? $row['address'])?></textarea></div>
    <div class="row"><label>City</label><input type="text" name="city" value="<?=htmlspecialchars($_POST['city'] ?? $row['city'])?>"></div>
    <div class="row"><label>State</label><input type="text" name="state" value="<?=htmlspecialchars($_POST['state'] ?? $row['state'])?>"></div>
    <div class="row"><label>Country</label><input type="text" name="country" value="<?=htmlspecialchars($_POST['country'] ?? $row['country'])?>"></div>
    <div class="row"><label>Pincode</label><input type="text" name="pincode" value="<?=htmlspecialchars($_POST['pincode'] ?? $row['pincode'])?>"></div>

    <div class="row"><label>Blood Group</label>
      <select name="blood_group">
        <?php foreach(['','A+','A-','B+','B-','O+','O-','AB+','AB-'] as $bg){ $sel = (($_POST['blood_group'] ?? $row['blood_group'])==$bg) ? 'selected' : ''; echo "<option $sel>".htmlspecialchars($bg)."</option>"; } ?>
      </select>
    </div>

    <div class="row"><label>Skills (comma separated)</label><input type="text" name="skills" value="<?=htmlspecialchars($_POST['skills'] ?? $row['skills'])?>"></div>
    <div class="row"><label>Hobbies</label><input type="text" name="hobbies" value="<?=htmlspecialchars($_POST['hobbies'] ?? $row['hobbies'])?>"></div>

    <div class="row">
      <label>Profile Picture (leave blank to keep existing)</label>
      <?php if ($row['profile_pic'] && file_exists(__DIR__ . '/' . $row['profile_pic'])): ?>
        <img src="<?=htmlspecialchars($row['profile_pic'])?>" class="profile-small" alt="Current">
      <?php endif; ?>
      <input type="file" name="profile_pic" accept="image/*">
    </div>

    <div class="row">
      <button type="submit">Save Changes</button>
    </div>
  </form>
</div>
</body>
</html>
