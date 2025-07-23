<?php
$body = new Template("dtml/hator/pagamento");

   

 if ($_SERVER['REQUEST_METHOD'] === 'GET'&&isset($_GET['id'],$_GET['total'])) {
    $_SESSION['back']['id'] = $_GET['id'];
    $_SESSION['back']['totale'] = $_GET['total'];
    $_SESSION['back']['step'] = 0;

}
if ($_SERVER['REQUEST_METHOD'] === 'POST'){

    $id = $_SESSION['back']['id'];
    $conn->query("UPDATE shipments SET processed = '1' WHERE id = '$id'") ;
    //$pattern1="\d{16}";
    //$pattern2="(0[1-9]|1[0-2])\/\d{2}";
    //$pattern3="\d{3,4}";
    //if(!preg_match($pattern1,$_POST['card-number'])||!preg_match($pattern2,$_POST['expiry-date'])||!preg_match($pattern3,$_POST['cvv'])){$body->setContent('err', "one or more of the data inserted is wrong.");}
    //else{
    unset($_SESSION['back']);
    header("Location: index.php?page=riepilogo&id=$id");
}
if(isset( $_SESSION['back'])){
    $body->setContent('tot', "€ ".$_SESSION['back']['totale']);
    if($_SESSION['back']['step'] === 0){
         $_SESSION['back']['step'] = 1;
        header("Location: index.php?page=pagamento");
    }else{
        ;
    }
}



$body->close();
