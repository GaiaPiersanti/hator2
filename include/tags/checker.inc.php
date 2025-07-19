<?php
class checker extends TagLibrary
{
    public function checklist($name, $data, $pars)
    {   //creo una connessione
        $conection = $this->connection();

        $userId = $_SESSION['user']['user_id'];
        $res = $conection->query("
            SELECT p.name, pv.price, pv.size_ml, c.quantity
            FROM carts c
            JOIN product_variants pv ON c.product_id = pv.id
            JOIN products p ON pv.product_id = p.id
            WHERE c.user_id = $userId
            ");
        if (!$res) {
            die("DB error: " . $conn->error);
        }

        $html =    '<table>
                        <thead>
                            <tr>
                                <th class="product-name">Product</th>
                                <th class="product-size">Size</th>
                                <th class="product-total">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                    ';
        $subtotale = 0;
        while ($row = $res->fetch_assoc()) {
            // calcolo il totale
            $total = $row['quantity'] * $row['price'];

            // formatto il totale
            $totalFormatted = number_format($total, 2, '.', ',');

            // Html escaping del titolo
            $title = htmlspecialchars($row['name'], ENT_QUOTES);

            $html.=        '<tr class="cart_item">
                                <td class="product-name">
                                    ' . $title . ' <span class="product-quantity"> × ' . $row['quantity'] . '</span>
                                </td>
                                <td class="product-total">
                                    <span class="size">' . $row['size_ml'] . 'ML</span>
                                </td>
                                <td class="product-total">
                                    <span class="amount">€' . $totalFormatted . '</span>
                                </td>
                            </tr>';
             $subtotale += $total;
        }
         if ($subtotale <= 0) {
            $totale = 0;
        }else{
            $totale = $subtotale + 10; // Aggiungo 10 come spese di spedizione fisse
        }
        //formatto i totali
        $subtotale = number_format($subtotale, 2, '.', ',');
        $totale = number_format($totale, 2, '.', ',');
        
        $html.=    ' 
                        </tbody>
                    </table>';

        $html.= '<table>
                    <tfoot>
                        <tr class="cart-subtotal">
                            <th>Cart Subtotal</th>
                            <td><span class="amount">€' . $subtotale . '</span></td>
                        </tr>
                        <tr class="order-total">
                            <th>Order Total</th>
                            <td><span class=" total amount">€' . $totale . '</span>
                            </td>
                        </tr>
                    </tfoot>
                </table>';
        $conection->close();
        return $html;
    }

    public function tendine(){
        return '<div id="accordion">
                                        <div class="card">
                                            <div class="card-header" id="headingone">
                                                <h5 class="mb-0">
                                                    <button class="btn btn-link" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                                        Direct Bank Transfer
                                                    </button>
                                                </h5>
                                            </div>

                                            <div id="collapseOne" class="collapse show" aria-labelledby="headingone" data-bs-parent="#accordion">
                                                <div class="card-body">
                                                    <p>Make your payment directly into our bank account. Please use your
                                                        Order ID as the payment reference. Your order won’t be shipped until
                                                        the funds have cleared in our account.</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card">
                                            <div class="card-header" id="headingtwo">
                                                <h5 class="mb-0">
                                                    <button class="btn btn-link collapsed" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                                        Cheque Payment
                                                    </button>
                                                </h5>
                                            </div>
                                            <div id="collapseTwo" class="collapse" aria-labelledby="headingtwo" data-bs-parent="#accordion">
                                                <div class="card-body">
                                                    <p>Please send your cheque to Univaq, Via Vetoio, 1, 67100 Coppito AQ, Italy. Please remember to
                                                        include your Order ID in the notes section of the cheque.Your order won’t be shipped until the cheque has been confirmed</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card">
                                            <div class="card-header" id="headingthree">
                                                <h5 class="mb-0">
                                                    <button class="btn btn-link collapsed" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                                                        PayPal
                                                    </button>
                                                </h5>
                                            </div>
                                            <div id="collapseThree" class="collapse" aria-labelledby="headingthree" data-bs-parent="#accordion">
                                                <div class="card-body">
                                                    <p>Pay via PayPal; you can pay with your credit card if you don’t have a
                                                        PayPal account.</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div> ';
    }

    //metodo per creare una connessine ricordati di chiuderla prima di restituire nelle funzioni pubbliche in cui viene chimata
    private function connection()
    {
        $host = "localhost";
        $user = "root";
        $password = ""; // Modifica questa riga con la tua password
        $database = "hator_db";
        $conection = new mysqli($host, $user, $password, $database);
        return $conection;
    }

}