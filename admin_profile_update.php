<?php

include 'config.php';

session_start();

$admin_id = $_SESSION['admin_id'];

if(!isset($admin_id)){
   header('location:admin_login.php');
};

if(isset($_POST['update'])){

   $name = $_POST['name'];
   $name = filter_var($name, FILTER_SANITIZE_STRING);

   $update_profile_name = $conn->prepare("UPDATE `admin` SET name = ? WHERE id = ?");
   $update_profile_name->execute([$name, $admin_id]);

   $prev_pass = $_POST['prev_pass'];
   $old_pass = sha1($_POST['old_pass']);
   $old_pass = filter_var($old_pass, FILTER_SANITIZE_STRING);
   $new_pass = sha1($_POST['new_pass']);
   $new_pass = filter_var($new_pass, FILTER_SANITIZE_STRING);
   $confirm_pass = sha1($_POST['confirm_pass']);
   $confirm_pass = filter_var($confirm_pass, FILTER_SANITIZE_STRING);
   $empty_pass = 'da39a3ee5e6b4b0d3255bfef95601890afd80709';

   if($old_pass != $empty_pass){
      if($old_pass != $prev_pass){
         $message[] = 'fjalëkalimi i vjetër nuk përputhet!';
      }elseif($new_pass != $confirm_pass){
         $message[] = 'fjalëkalimin konfirmuar   nuk përputhet!';
      }else{
         if($new_pass != $empty_pass){
            $update_admin_pass = $conn->prepare("UPDATE `admin` SET password = ? WHERE id = ?");
            $update_admin_pass->execute([$confirm_pass, $admin_id]);
            $message[] = 'fjalëkalimi u përditësua me sukses!';
         }else{
            $message[] = 'ju lutemi shkruani një fjalëkalim të ri!';
         }
      }
   }else{
      $message[] = 'ju lutemi shkruani fjalëkalimin e vjetër';
   }

}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>përditësimi i admin</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- custom admin style link  -->
   <link rel="stylesheet" href="css/admin_style.css">

</head>
<body>

<?php include 'admin_header.php' ?>

<section class="form-container">

   <form action="" method="post">
      <h3>Përditësoje profilin</h3>
      <input type="hidden" name="prev_pass" value="<?= $fetch_profile['password']; ?>">
      <input type="text" name="name" value="<?= $fetch_profile['name']; ?>" required placeholder="shkruani emrin tuaj të përdoruesit" maxlength="20"  class="box" oninput="this.value = this.value.replace(/\s/g, '')">
      <input type="password" name="old_pass" placeholder="fut fjalëkalimin e vjetër" maxlength="20"  class="box" oninput="this.value = this.value.replace(/\s/g, '')">
      <input type="password" name="new_pass" placeholder="fut fjalëkalimin e ri" maxlength="20"  class="box" oninput="this.value = this.value.replace(/\s/g, '')">
      <input type="password" name="confirm_pass" placeholder="konfirmoni fjalëkalimin e ri" maxlength="20"  class="box" oninput="this.value = this.value.replace(/\s/g, '')">
      <input type="submit" value="Përditso Tani" class="btn" name="update">
   </form>

</section>


<script src="js/admin_script.js"></script>

</body>
</html>