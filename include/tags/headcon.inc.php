<?php
class headcon extends TagLibrary{
    // metodo per creare e popolare il carrello rapido
    public function minicart($name = '', $data = '', $pars = [])
    {
        $host = "localhost";
        $user = "root";
        $password = ""; // Modifica questa riga con la tua password
        $database = "hator_db";
        $conection = new mysqli($host, $user, $password, $database);
        // Check connection
        if ($conection->connect_error) {
            die("Connection failed: " . $conection->connect_error . "<br/>");
        }
        $cart = "";
        if (isset($_SESSION['user'])) {
            // Se l'utente è loggato, prendo il carrello dal database
            $userId = $_SESSION['user']['user_id'];
            $res = $conection->query("
                SELECT p.slug,p.img1_url,p.name,pv.price,pv.id AS variant_id ,pv.size_ml, c.quantity
                FROM carts c
                JOIN product_variants pv ON c.product_id = pv.id
                JOIN products p ON pv.product_id = p.id
                WHERE c.user_id = $userId
                ");
        
        } else {
            // Se l'utente non è loggato, prendo il carrello di sessione
            if (!isset($_SESSION['cart'])) {
                $_SESSION['cart'] = [];
            }
            // Preparo il carrello per la query    
            foreach ($_SESSION['cart'] as $variantId => $quantity) {
                // Preparo il carrello per la query
                $cart .= "$variantId,";
            }
            //inserisco un numero di defoult che non esiste nel db   
            $cart .= "0";
            // Eseguo la query per ottenere i prodotti nel carrello
            $res = $conection->query("
                SELECT p.slug,p.img1_url,p.name,pv.price,pv.id AS variant_id ,pv.size_ml
                FROM products p
                JOIN product_variants pv ON pv.product_id = p.id
                WHERE pv.id IN ($cart)
                ");
            
        }
        $products = [];
        if ($res) {
            // Se la query ha avuto successo, recupero i risultati
            while ($row = $res->fetch_assoc()) {
                $products[] = $row;
            }
        }
        //conto il numero di prodotti nel carrello
        $count = 0;
        if(isset($_SESSION['user'])){
            foreach ($products as $prod) {
                $count += $prod['quantity'] ?? 1; // Aggiungo la quantità del prodotto
            }
        }else{
            foreach ($_SESSION['cart'] as $variantId => $quantity) {
                $count += $quantity; // Aggiungo la quantità del prodotto
            }
        }


        $subtotale = 0;
        if(isset($_SESSION['user'])) {
            foreach ($products as $prod) {
                $subtotale += $prod['price'] * ($prod['quantity'] ?? 0); // Calcolo il subtotale    
            }
        } else {
            foreach ($products as $prod) {
                $subtotale += $prod['price'] * ($_SESSION['cart'][$prod['variant_id']] ?? 0); // Calcolo il subtotale    
            }
        }
        

        if ($subtotale <= 0) {
            $totale = 0;
        }else{
            $totale = $subtotale + 10; // Aggiungo 10 come spese di spedizione fisse
        }
        //formatto i totali
        $subtotale = number_format($subtotale, 2, '.', ',');
        $totale = number_format($totale, 2, '.', ',');

        $html = '<li>
                                            <a href="index.php?page=cart">
                                                <span class="pe-7s-shopbag"></span>';
        if($count > 0){
            $html .=                           '<span class="total-pro">' . $count . '</span>';
        }
        $html .=                           '</a>
                                            <ul class="ht-dropdown cart-box-width">
                                                <li>
                                                    <!-- Cart Box Start -->
                                                    ' . $this->carted($products)
                                                   . '<!-- Cart Box End -->
                                                    <!-- Cart Footer Inner Start -->
                                                    <div class="cart-footer">
                                                        <ul class="price-content">
                                                            <li>Subtotal
                                                                <span>€' .  $subtotale . '</span>
                                                            </li>
                                                            <li>Shipping
                                                                <span>€10.00</span>
                                                            </li>
                                                        <!--    <li>Taxes
                                                                <span>$0.00</span>
                                                            </li> -->
                                                            <li>Total
                                                                <span>€' . $totale . '</span>
                                                            </li>
                                                        </ul>
                                                        <div class="cart-actions text-center">
                                                            <a class="cart-checkout" href="index.php?page=checkout">Checkout</a>
                                                        </div>
                                                    </div>
                                                    <!-- Cart Footer Inner End -->
                                                </li>
                                            </ul>
                                        </li>';
    $conection->close();
    return $html;
    }

    // metodo per creare i singoli prodotti e caricarli nel carrello rapido
    private function carted($products = []){
    $html = '';
    if (!empty($products)) {
        foreach ($products as $prod){  
            // 1) Preleva con null‐coalesce i valori chiave
            $slug       = $prod['slug']        ?? '';
            $imgUrl     = $prod['img1_url']    ?? '';
            $nameVal    = $prod['name']        ?? '';
            $priceVal   = $prod['price']       ?? 0.00;
            $sizeMl     = $prod['size_ml']     ?? '';
            $variantId  = $prod['variant_id']  ?? '';

            // preparo la variabile $num rispetto a se è loggato omeno
            if(!isset($_SESSION['loggedin']) || !$_SESSION['loggedin']) {
                // Se l'utente non è loggato, prendo il carrello di sessione
                $num = $_SESSION['cart'][$variantId] ?? 1; // Default a 1 se non presente
            } else {
                // Se l'utente è loggato, prendo il numero dal database
                $num = $prod['quantity'] ?? 1; // Default a 1 se non presente
            }
            // Link alla pagina di dettaglio
            $urlDetails = "index.php?page=productdetails&slug=" . urlencode($slug);

            // Formatta il prezzo
            $priceFormatted = number_format($priceVal, 2, '.', ',');
            
            // calcolo il totale
            $total = $num * $priceVal;

            // formatto il totale
            $totalFormatted = number_format($total, 2, '.', ',');

            // Html escaping del titolo
            $title = htmlspecialchars($nameVal, ENT_QUOTES);

            // Se non c’è immagine, puoi usare un placeholder
            if ($imgUrl === "") {
                $imgUrl = "assets/img/placeholder.png";
            }
            // Ora costruisci l’HTML
            $html .=    '<div class="single-cart-box">
                            <div class="cart-img">
                                <a href="' . $urlDetails . '">
                                    <img src="' . $imgUrl . '" alt="' . $title . '">
                                </a>
                                <span class="pro-quantity">' . $num . 'X</span>
                            </div>
                            <div class="cart-content">
                                <h6>
                                    <a href="' . $urlDetails . '"> ' . $title . ' </a>
                                </h6>
                                <span class="cart-price"> € ' . $totalFormatted . ' </span>
                                <span>Size: ' . $sizeMl . ' ML </span>
                            </div>
                            <a class="del-icone" href="#">
                                <i class="ion-close"></i>
                            </a>
                        </div>';
        }
    }
    return $html;
    }



    //nel caso si vogliano sostituire cosi da non dover caricare tante cose in ogni controller
    public function buttons($name = '', $data = '', $pars = [])
    {
        $buttons_not_loged = '<li>
        <!--LOG IN LINK-->                            <a href="index.php?page=login">Log In</a>
                                                </li>
                                                <li>
        <!--REGISTER LINK-->                          <a href="index.php?page=add-user">Register</a>
                                                </li>';
        $buttons_loged =  '                                           
                                                <!--aggiungi qui tutto ciò che vuoi mostrare 
                                                SOLO agli utenti loggati e aggiungi nelle
                                                funzioni a cui puoi accedere solo se sei loggato
                                                $user= $this->loadModel("User");
                                                if(! $user->check_logged_in()) {
                                                    header("Location: " . ROOT . "login");
                                                    die;   MINUTO 230 
                                                } -->
                                                <li>
        <!--LOG OUT LINK-->                          <a href="index.php?page=logout">Log Out</a>
                                                </li>';
        return isset($_SESSION['user']) ? $buttons_loged : $buttons_not_loged;
    }


    public function settings($name = '', $data = '', $pars = [])
    {
        $settings = '<li>
                                        <a href="#">Settings
                                            <i class="fa fa-angle-down"></i>
                                        </a>
                                        <!-- Dropdown Start -->
                                        <ul class="ht-dropdown">
                                             <li>
                                                <a href="index.php?page=account">my account</a>
                                            </li>
                                            <li>
                                                <a href="index.php?page=wishlist">my wishlist</a>
                                            </li>
                                            
                                     

                                        </ul>
                                        <!-- Dropdown End -->
                                    </li>';
        return isset($_SESSION['user']) ? $settings : "";
    }
}