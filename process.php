<?php
require 'config.php';

function clean($v) {
    return htmlspecialchars(trim($v));
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.html');
    exit;
}

// collect fields
$fields = [
  'full_name','dob','gender','email','phone','father_name','mother_name','enrollment_no',
  'course','semester','address','city','state','country','pincode','blood_group','skills','hobbies'
];
foreach($fields as $f){ $$f = clean($_POST[$f] ?? ''); }

$errors = [];

// Validate all required
foreach($fields as $f) {
  if (empty($$f)) $errors[] = ucfirst(str_replace('_',' ',$f)) . " is required.";
}

// Extra validation
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email format.";
if (!preg_match('/^\d{10}$/', $phone)) $errors[] = "Phone number must be exactly 10 digits.";
if (!preg_match('/^\d{6}$/', $pincode)) $errors[] = "Pincode must be exactly 6 digits.";

// File upload validation
$profile_pic_db = null;
if (empty($_FILES['profile_pic']['name'])) {
  $errors[] = "Profile picture is required.";
} else {
  $f = $_FILES['profile_pic'];
  if ($f['error'] === UPLOAD_ERR_OK) {
    $allowed = ['image/jpeg','image/png','image/jpg','image/gif'];
    if (!in_array($f['type'], $allowed)) {
      $errors[] = "Only JPG/PNG/GIF allowed.";
    } elseif ($f['size'] > 2*1024*1024) {
      $errors[] = "Profile picture must be under 2MB.";
    } else {
      $ext = pathinfo($f['name'], PATHINFO_EXTENSION);
      $newName = uniqid('prof_', true) . '.' . $ext;
      $upload_dir = __DIR__ . '/uploads/';
      if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
      if (move_uploaded_file($f['tmp_name'], $upload_dir . $newName)) {
        $profile_pic_db = 'uploads/' . $newName;
      } else {
        $errors[] = "Failed to upload profile picture.";
      }
    }
  } else {
    $errors[] = "File upload error.";
  }
}

// If errors → show
if ($errors) {
  echo "<h2>Submission Errors:</h2><ul>";
  foreach ($errors as $er) echo "<li>".htmlspecialchars($er)."</li>";
  echo "</ul><p><a href='index.html'>Go Back</a></p>";
  exit;
}

// Insert into DB
$stmt = $mysqli->prepare("INSERT INTO registrations 
(full_name, dob, gender, email, phone, father_name, mother_name, enrollment_no, course, semester, address, city, state, country, pincode, blood_group, skills, hobbies, profile_pic)
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

$stmt->bind_param("sssssssssssssssssss",
  $full_name, $dob, $gender, $email, $phone, $father_name, $mother_name, $enrollment_no, $course, $semester,
  $address, $city, $state, $country, $pincode, $blood_group, $skills, $hobbies, $profile_pic_db
);

$stmt->execute();
$id = $stmt->insert_id;
$stmt->close();

// Redirect to success display
header("Location: show_all.php?msg=success&id=$id");
exit;
?>
