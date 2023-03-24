<?php

include 'config.php';

session_start();

if(isset($_SESSION['user_id'])){
   $user_id = $_SESSION['user_id'];
}else{
   $user_id = '';
};

if(isset($_POST['register'])){

   $name = $_POST['name'];
   $name = filter_var($name, FILTER_SANITIZE_STRING);
   $email = $_POST['email'];
   $email = filter_var($email, FILTER_SANITIZE_STRING);
   $pass = sha1($_POST['pass']);
   $pass = filter_var($pass, FILTER_SANITIZE_STRING);
   $cpass = sha1($_POST['cpass'] );
   $cpass = filter_var($cpass, FILTER_SANITIZE_STRING);

   $select_user = $conn->prepare("SELECT * FROM `user` WHERE name = ? AND email = ?");
   $select_user->execute([$name, $email]);

   if($select_user->rowCount() > 0){
      $message[] = 'username or email already exists!';
   }else{
      if($pass != $cpass){
         $message[] = 'confirm password not matched!';
      }else{
         $insert_user = $conn->prepare("INSERT INTO `user`(name, email, password) VALUES(?,?,?)");
         $insert_user->execute([$name, $email, $cpass]);
         $message[] = 'registered successfully, login now please!';
      }
   }

}

if(isset($_POST['update_qty'])){
   $cart_id = $_POST['cart_id'];
   $qty = $_POST['qty'];
   $qty = filter_var($qty, FILTER_SANITIZE_STRING);
   $update_qty = $conn->prepare("UPDATE `cart` SET quantity = ? WHERE id = ?");
   $update_qty->execute([$qty, $cart_id]);
   $message[] = 'cart quantity updated!';
}

if(isset($_GET['delete_cart_item'])){
   $delete_cart_id = $_GET['delete_cart_item'];
   $delete_cart_item = $conn->prepare("DELETE FROM `cart` WHERE id = ?");
   $delete_cart_item->execute([$delete_cart_id]);
   header('location:index.php');
}

if(isset($_GET['logout'])){
   session_unset();
   session_destroy();
   header('location:index.php');
}

if(isset($_POST['add_to_cart'])){

   if($user_id == ''){
      $message[] = 'ju lutem kuquni pastaj bëni porosinë!';
   }else{

      $pid = $_POST['pid'];
      $name = $_POST['name'];
      $price = $_POST['price'];
      $image = $_POST['image'];
      $qty = $_POST['qty'];
      $qty = filter_var($qty, FILTER_SANITIZE_STRING);

      $select_cart = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ? AND name = ?");
      $select_cart->execute([$user_id, $name]);

      if($select_cart->rowCount() > 0){
         $message[] = 'tashmë është vendosur në shportë';
      }else{
         $insert_cart = $conn->prepare("INSERT INTO `cart`(user_id, pid, name, price, quantity, image) VALUES(?,?,?,?,?,?)");
         $insert_cart->execute([$user_id, $pid, $name, $price, $qty, $image]);
         $message[] = 'u vendos në shportë';
      }

   }

}

if(isset($_POST['order'])){

   if($user_id == ''){
      $message[] = 'ju lutem kuquni pastaj bëni porosinë!';
   }else{
      $name = $_POST['name'];
      $name = filter_var($name, FILTER_SANITIZE_STRING);
      $number = $_POST['number'];
      $number = filter_var($number, FILTER_SANITIZE_STRING);
      $address = 'flat no.'.$_POST['flat'].', '.$_POST['street'].' - '.$_POST['pin_code'];
      $address = filter_var($address, FILTER_SANITIZE_STRING);
      $method = $_POST['method'];
      $method = filter_var($method, FILTER_SANITIZE_STRING);
      $total_price = $_POST['total_price'];
      $total_products = $_POST['total_products'];

      $select_cart = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
      $select_cart->execute([$user_id]);

      if($select_cart->rowCount() > 0){
         $insert_order = $conn->prepare("INSERT INTO `orders`(user_id, name, number, method, address, total_products, total_price) VALUES(?,?,?,?,?,?,?)");
         $insert_order->execute([$user_id, $name, $number, $method, $address, $total_products, $total_price]);
         $delete_cart = $conn->prepare("DELETE FROM `cart` WHERE user_id = ?");
         $delete_cart->execute([$user_id]);
         $message[] = 'porosia është kryer më sukses!';
      }else{
         $message[] = 'shporta juaj është bosh!';
      }
   }

}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Supermarketi Im</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">

</head>
<body>

<?php
   if(isset($message)){
      foreach($message as $message){
         echo '
         <div class="message">
            <span>'.$message.'</span>
            <i class="fas fa-times" onclick="this.parentElement.remove();"></i>
         </div>
         ';
      }
   }
?>

<!-- header section starts  -->

<header class="header">

   <section class="flex">

   <a href="#home" class="logo"><span>Super</span>Marketi Im</a>

      <nav class="navbar">
         <a href="#home">Baza</a>
         <a href="#about">Rreth ne</a>
         <a href="#menu">Produktet</a>
         <a href="#order">Porosia</a>
         <a href="#faq">Pytje?</a>
      </nav>

      <div class="icons">
         <div id="menu-btn" class="fas fa-bars"></div>
         <div id="user-btn" class="fas fa-user"></div>
         <div id="order-btn" class="fas fa-box"></div>
         <?php
            $count_cart_items = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
            $count_cart_items->execute([$user_id]);
            $total_cart_items = $count_cart_items->rowCount();
         ?>
         <div id="cart-btn" class="fas fa-shopping-cart"><span>(<?= $total_cart_items; ?>)</span></div>
      </div>

   </section>

</header>

<!-- header section ends -->

<div class="user-account">

   <section>

      <div id="close-account"><span>mbylle</span></div>

      <div class="user">
         <?php
            $select_user = $conn->prepare("SELECT * FROM `user` WHERE id = ?");
            $select_user->execute([$user_id]);
            if($select_user->rowCount() > 0){
               while($fetch_user = $select_user->fetch(PDO::FETCH_ASSOC)){
                  echo '<p>mirë se vini ! <span>'.$fetch_user['name'].'</span></p>';
                  echo '<a href="index.php?logout" class="btn">Dilni nga llogaria</a>';
               }
            }else{
               echo '<p><span>ju nuk jeni identifikuar ende!</span></p>';
            }
         ?>
      </div>

      <div class="display-orders">
         <?php
            $select_cart = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
            $select_cart->execute([$user_id]);
            if($select_cart->rowCount() > 0){
               while($fetch_cart = $select_cart->fetch(PDO::FETCH_ASSOC)){
                  echo '<p>'.$fetch_cart['name'].' <span>('.$fetch_cart['price'].' x '.$fetch_cart['quantity'].')</span></p>';
               }
            }else{
               echo '<p><span>karroca juaj është bosh!</span></p>';
            }
         ?>
      </div>

      <div class="flex">

         <form action="user_login.php" method="post">
            <h3>Kyquni </h3>
            <input type="email" name="email" required class="box" placeholder="shkruani emailin tuaj" maxlength="50">
            <input type="password" name="pass" required class="box" placeholder="shkruani fjalëkalimin tuaj" maxlength="20">
            <input type="submit" value="Kyquni Tani" name="login" class="btn">
         </form>

         <form action="" method="post">
            <h3>Regjistrohuni </h3>
            <input type="text" name="name" oninput="this.value = this.value.replace(/\s/g, '')" required class="box" placeholder="shkruani emrin e përdorusit" maxlength="20">
            <input type="email" name="email" required class="box" placeholder="shkruani emailin tuaj" maxlength="50">
            <input type="password" name="pass" required class="box" placeholder="shkruani fjalëkalimin tuaj" maxlength="20" oninput="this.value = this.value.replace(/\s/g, '')">
            <input type="password" name="cpass" required class="box" placeholder="konfirmoni fjalkalimin tuaj" maxlength="20" oninput="this.value = this.value.replace(/\s/g, '')">
            <input type="submit" value="Regjistrohuni Tani" name="register" class="btn">
         </form>
         <form action="admin_login.php" method="post">
            <h3>Jeni Administrator </h3> 
         <a href="http://localhost/em/admin_login.php">
          <input type="submit" value="Kyquni Tani"  name="Kyquni" class="btn"/>
          </a>
         </form>
      </div>

   </section>

</div>

<div class="my-orders">

   <section>

      <div id="close-orders"><span>mbylle</span></div>

      <h3 class="title"> Porosia Ime </h3>

      <?php
         $select_orders = $conn->prepare("SELECT * FROM `orders` WHERE user_id = ?");
         $select_orders->execute([$user_id]);
         if($select_orders->rowCount() > 0){
            while($fetch_orders = $select_orders->fetch(PDO::FETCH_ASSOC)){   
      ?>
      <div class="box">
         <p> data e porosiës : <span><?= $fetch_orders['placed_on']; ?></span> </p>
         <p> emri : <span><?= $fetch_orders['name']; ?></span> </p>
         <p> numri tel : <span><?= $fetch_orders['number']; ?></span> </p>
         <p> adresa: <span><?= $fetch_orders['address']; ?></span> </p>
         <p> mënyra pagesës : <span><?= $fetch_orders['method']; ?></span> </p>
         <p> produktet për porosi : <span><?= $fetch_orders['total_products']; ?></span> </p>
         <p> shuma totale : <span>$<?= $fetch_orders['total_price']; ?>/-</span> </p>
         <p> statusi porosisë : <span style="color:<?php if($fetch_orders['payment_status'] == 'pending'){ echo 'red'; }else{ echo 'green'; }; ?>"><?= $fetch_orders['payment_status']; ?></span> </p>
      </div>
      <?php
         }
      }else{
         echo '<p class="empty">skeni porositur asgjë!</p>';
      }
      ?>

   </section>

</div>



<div class="shopping-cart">

   <section>

      <div id="close-cart"><span>mbylle</span></div>

      <?php
         $grand_total = 0;
         $select_cart = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
         $select_cart->execute([$user_id]);
         if($select_cart->rowCount() > 0){
            while($fetch_cart = $select_cart->fetch(PDO::FETCH_ASSOC)){
              $sub_total = ($fetch_cart['price'] * $fetch_cart['quantity']);
              $grand_total += $sub_total; 
      ?>
      <div class="box">
         <a href="index.php?delete_cart_item=<?= $fetch_cart['id']; ?>" class="fas fa-times" onclick="return confirm('delete this cart item?');"></a>
         <img src="uploaded_img/<?= $fetch_cart['image']; ?>" alt="">
         <div class="content">
          <p> <?= $fetch_cart['name']; ?> <span>(<?= $fetch_cart['price']; ?> x <?= $fetch_cart['quantity']; ?>)</span></p>
          <form action="" method="post">
             <input type="hidden" name="cart_id" value="<?= $fetch_cart['id']; ?>">
             <input type="number" name="qty" class="qty" min="1" max="99" value="<?= $fetch_cart['quantity']; ?>" onkeypress="if(this.value.length == 2) return false;">
               <button type="submit" class="fas fa-edit" name="update_qty"></button>
          </form>
         </div>
      </div>
      <?php
         }
      }else{
         echo '<p class="empty"><span>your cart is empty!</span></p>';
      }
      ?>

      <div class="cart-total"> Shuma totale : <span><?= $grand_total; ?>/€</span></div>

      <a href="#order" class="btn">Porosit tani</a>

   </section>

</div>

<div class="home-bg">

   <section class="home" id="home">

      <div class="slide-container">
      <div class="slide active">
     
            <div class="image">
               <img src="images/home-img-1.png" alt="">
            </div>
            <div class="content">
               <h3>PSE PO<br> NGUTESH  KADAL</br></h3>
               <div class="fas fa-angle-left" onclick="prev()"></div>
               <div class="fas fa-angle-right" onclick="next()"></div>
            </div>
         </div>
         <div class="slide">
            <div class="image">
               <img src="images/home-img-2.gif" alt="">
            </div>
            <div class="content">
               <h3><br>ME NI KLIK BLEN <br>CKA DON</br></h3>
               <div class="fas fa-angle-left" onclick="prev()"></div>
               <div class="fas fa-angle-right" onclick="next()"></div>
            </div>
         </div>

         <div class="slide">
            <div class="image">
               <img src="images/home-img-3.png" alt="">
            </div>
            <div class="content">
               <h3>NSHPI NA TA BIM</h3>
               <div class="fas fa-angle-left" onclick="prev()"></div>
               <div class="fas fa-angle-right" onclick="next()"></div>
            </div>
         </div>

      </div>

   </section>

</div>

<!-- about section starts  -->

<section class="about" id="about">

   <h1 class="heading">RRETH NE</h1>

   <div class="box-container">

   <div class="box">
         <img src="images/about-1.svg" alt="">
         <h3>Serioziteti Kompanisë  </h3>
         <p>
         Supermarketi im eshte njohur per produktet e saj te cilesise se larte dhe kujdesin e vecante ndaj konsumatorve.<br>
          Duke ofruar nje zgjedhje te gjere te produkteve,per konsumatoret tane </br>
         </p>
         <a href="#menu" class="btn">Produktet tona</a>
      </div>

      <div class="box">
         <img src="images/about-2.svg" alt="">
         <h3>Porosia arrin në kohë</h3>
         <p>Nëse keni nevojë për dërgesa të shpejta të ushqimeve, mund të përdorni shërbimin tonë online për porosinë 
            e produkteve ushqimore në më pak se 30 minuta porosia arrin tek ju.</p>
         <a href="#menu" class="btn">Produktet tona</a>
      </div>

      <div class="box">
         <img src="images/about-3.svg" alt="">
         <h3>Konsumator të lumtur</h3>
         <p>Ne jemi te lumtur qe ju keni zgjedhur t'i besoni produkteve dhe sherbimeve tona.
             Ne punojme ne menyre te palodhshme per 
             konsumatoret tonë.
             Gjithmon kujdesemi për ju!


         </p>
         <a href="#menu" class="btn">Produktet tona</a>
      </div>

   </div>

</section>

<!-- about section ends -->

<!-- menu section starts  -->

<section id="menu" class="menu">

   <h1 class="heading">PRODUKTET </h1>

   <div class="box-container">

      <?php
         $select_products = $conn->prepare("SELECT * FROM `products`");
         $select_products->execute();
         if($select_products->rowCount() > 0){
            while($fetch_products = $select_products->fetch(PDO::FETCH_ASSOC)){    
      ?>
      <div class="box">
         <div class="price"><?= $fetch_products['price'] ?>/€</div>
         <img src="uploaded_img/<?= $fetch_products['image'] ?>" alt="">
         <div class="name"><?= $fetch_products['name'] ?></div>
         <form action="" method="post">
            <input type="hidden" name="pid" value="<?= $fetch_products['id'] ?>">
            <input type="hidden" name="name" value="<?= $fetch_products['name'] ?>">
            <input type="hidden" name="price" value="<?= $fetch_products['price'] ?>">
            <input type="hidden" name="image" value="<?= $fetch_products['image'] ?>">
            <input type="number" name="qty" class="qty" min="1" max="99" onkeypress="if(this.value.length == 2) return false;" value="1">
            <input type="submit" class="btn" name="add to cart" value="Shto në Shportë">
         </form>
      </div>
      <?php
         }
      }else{
         echo '<p class="empty">no products added yet!</p>';
      }
      ?>

   </div>

</section>

<!-- menu section ends -->

<!-- order section starts  -->

<section class="order" id="order">

   <h1 class="heading">Porosia</h1>

   <form action="" method="post">

   <div class="display-orders">

   <?php
         $grand_total = 0;
         $cart_item[] = '';
         $select_cart = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
         $select_cart->execute([$user_id]);
         if($select_cart->rowCount() > 0){
            while($fetch_cart = $select_cart->fetch(PDO::FETCH_ASSOC)){
              $sub_total = ($fetch_cart['price'] * $fetch_cart['quantity']);
              $grand_total += $sub_total; 
              $cart_item[] = $fetch_cart['name'].' ( '.$fetch_cart['price'].' x '.$fetch_cart['quantity'].' ) - ';
              $total_products = implode($cart_item);
              echo '<p>'.$fetch_cart['name'].' <span>('.$fetch_cart['price'].' x '.$fetch_cart['quantity'].')</span></p>';
            }
         }else{
            echo '<p class="empty"><span>Shporta juaj është bosh!</span></p>';
         }
      ?>

   </div>

   <div class="grand-total"> Totali në euro : <span><?= $grand_total; ?>/€</span></div>

<input type="hidden" name="total_products" value="<?= $total_products; ?>">
<input type="hidden" name="total_price" value="<?= $grand_total; ?>">

<div class="flex">
   <div class="inputBox">
      <span>Emri juaj :</span>
      <input type="text" name="name" class="box" required placeholder="shkruaj emrin tend" maxlength="20">
   </div>
   <div class="inputBox">
      <span>Numri juaj :</span>
      <input type="number" name="number" class="box" required placeholder="shkruani numrin tuaj" min="0" max="9999999999" onkeypress="if(this.value.length == 10) return false;">
   </div>
   <div class="inputBox">
      <span>Metoda e pagesës</span>
      <select name="method" class="box">
         <option value="paratë ne dore gjate dorezimit">paratë ne dore gjate dorezimit</option>
         <option value="Karte Krediti">Kartë Krediti</option>
     
      </select>
   </div>
   <div class="inputBox">
      <span>addresa juaj:</span>
      <input type="text" name="flat" class="box" required placeholder="p.sh Klinë" maxlength="50">
   </div>
   <div class="inputBox">
      <span>emri rruges :</span>
      <input type="text" name="street" class="box" required placeholder="p.sh rruga ,Mbretresha Teute" maxlength="50">
   </div>
   <div class="inputBox">
      <span>Kodi Zip :</span>
      <input type="number" name="pin_code" class="box" required placeholder="p.sh, 3200" min="0" max="999999" onkeypress="if(this.value.length == 6) return false;">
   </div>
</div>


      <input type="submit" value="Porosit Tani" class="btn" name="order">

   </form>

</section>

<!-- order section ends -->

<!-- faq section starts  -->

<section class="faq" id="faq">

   <h1 class="heading">Pyetje e bërë më se shpeshti</h1>

   <div class="accordion-container">


      <div class="accordion">
         <div class="accordion-heading">
            <span>Orari juaj i punës</span>
            <i class="fas fa-angle-down"></i>
         </div>
         <p class="accrodion-content">
         Supermarketi ynë është një vend i jashtëzakonshëm për t'u blerë ushqime dhe produkte të nevojshme në mënyrë të lehtë dhe të shpejtë. Ne ofrojmë një gamë të gjerë të produkteve të freskëta dhe të përpunuara si dhe një shërbim të shkëlqyer për klientët tanë.

Megjithatë, për të siguruar që stafi jonë të ketë kohën e nevojshme për të bërë mirëmbajtjen dhe pastërtinë e supermarketit tonë, ne vendosëm që të mbyllim dyert tona për një ditë në javë. Kështu, çdo të diel, supermarketi ynë do të mbetet i mbyllur për klientët, por stafi ynë do të punojë për të bërë gjithçka të jetë e nevojshme për t'u siguruar që kur hapim dyert e supermarketit të hënën, të jemi gati për të pritur me krahë hapur klientët tanë.

Ju falenderojmë për kuptimin tuaj dhe lutemi të na vizitoni në oraret tona të tjera që janë në dispozicion 24/7 për të plotësuar nevojat tuaja."
         </p>
      </div>

      <div class="accordion">
         <div class="accordion-heading">
            <span>Sa kohë duhet për dorëzim të porosisë?</span>
            <i class="fas fa-angle-down"></i>
         </div>
         <p class="accrodion-content">
         "Ne jemi krenarë që ofrojmë një shërbim të shpejtë dhe të sigurt të dorëzimit të porosive brenda vendit. Ne e dimë që distanca midis vendbanimeve mund të ndryshojë nga një zonë në tjetrën, por kemi bërë të mundur që porosia të mbërrijë në adresën tuaj në maksimum 2 orë kohë.

Mund të jetë një çift i ri që ka nevojë për ushqim dhe pijet për festën e tyre në shtëpi, ose një biznes që ka nevojë për furnizim të shpejtë të materialesh - çdo lloj porosie që ne marrim është e rëndësishme për ne. Kjo është arsyeja pse ne ofrojmë një shërbim të shpejtë dhe efektiv me një dorëzim të sigurt të porosisë brenda dy orëve pas konfirmimit.

Nëse jeni brenda zonës sonë të dorëzimit, nuk do të ketë asnjë problem për ne për të siguruar që porosia juaj të mbërrijë sa më shpejt që është e mundur. Ju mund të jeni të sigurt se stafi ynë do të punojë vazhdimisht për të siguruar që porosia juaj të mbërrijë në kohë dhe me cilësi të lartë.

Ju falenderojmë që zgjodhët shërbimet tona dhe lutemi që të na kontaktoni për herë të ardhshme kur keni nevojë për një shërbim të dorëzimit të shpejtë dhe të sigurt në të gjithë vendin."
         </p>
      </div>


      <div class="accordion">
         <div class="accordion-heading">
            <span>Qka ofron supermarketi im</span>
            <i class="fas fa-angle-down"></i>
         </div>
         <p class="accrodion-content">
         "Ne jemi supermarketi juaj i preferuar që ofron një gamë të gjerë të produkteve. Nga fruta dhe perime të freskëta, deri tek mishrat, peshku dhe prodhimet e qumështit, ne sigurojmë që të gjitha produktet tona janë cilësore dhe me çmime të arsyeshme për klientët tanë.

Përveç produkteve të freskëta, ne gjithashtu ofrojmë një gamë të gjerë të produkteve të përpunuara si dhe ushqime të gatshme, që mund të konsumohen menjëherë. Nëse jeni në kërkim të një ushqimi të shpejtë dhe të shijshëm, ndjekim trendin global të ushqimit të shëndetshëm dhe ofrojmë një gamë të gjerë të alternativave të shëndetshme dhe vegane.

Nga ushqimet e konsumit të përditshëm deri te bebeshat dhe kafshët e kushtuara (pets), ne kemi mundësi për të plotësuar nevojat tuaja dhe të sigurojmë që koha juaj e mbetur të minimizohet. Ne vlerësojmë zgjedhjet tuaja dhe sigurojmë që tregtimet tona të përballojnë standardet më të larta të cilësisë dhe të sigurisë ushqimore.

Ju ftojmë të na vizitoni për të eksploruar gamën tonë të gjerë të produkteve dhe për të përfituar nga ofertat tona speciale. Na besoni kur themi se do të jeni të kënaqur me zgjedhjen tonë të produkteve dhe shërbimit tonë të shkëlqyer!"
         </p>
      </div>

   </div>

</section>

<!-- faq section ends -->

<!-- footer section starts  -->

<section class="footer">

   <div class="box-container">

      <div class="box">
         <i class="fas fa-phone"></i>
         <h3>Numri telefonit</h3>
         <p>+383-44-456-7890</p>
         <p>+383-49-222-3333</p>
      </div>

      <div class="box">
         <i class="fas fa-map-marker-alt"></i>
         <h3>Adresa jonë</h3>
         <p>Kosovë Klinë,3200</p>
      </div>

      <div class="box">
         <i class="fas fa-clock"></i>
         <h3>Orari Punës</h3>
         <p>08.00pm to 00:00am</p>
      </div>

      <div class="box">
         <i class="fas fa-envelope"></i>
         <h3>email adresa</h3>
         <p>supermarketiim@gmail.com</p>
         <p>infosupermarketiim@gmail.com</p>
      </div>

   </div>

   <div class="credit">
   &copy; copyright @ <?= date('Y'); ?> nga <span>Taulant Veselaj</span> | all rights reserved!
   
   </div>

</section>

<!-- footer section ends -->



















<!-- custom js file link  -->
<script src="js/script.js"></script>

</body>
</html>