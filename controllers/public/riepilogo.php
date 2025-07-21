<?php


// 1) Istanzio il frame principale
$main = new Template("dtml/hator/frame");
$main->setContent("page_title", $page_title);

// 2) Istanzio il sotto‐template per la pagina checkout
$body = new Template("dtml/hator/riepilogo");

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $body->setContent('ordine', htmlspecialchars($_GET['id'], ENT_QUOTES));
    if(isset($_GET['metodo'])){
    if($_GET['metodo'] === 'bank_transfer') {
        $body->setContent('remind','<div class="section-title text-center">
                                        <h3>Ricordati:</h3>
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
                                    </div>');
    } elseif($_GET['metodo'] === 'cheque') {
        $body->setContent('remind','<div class="section-title text-center">
                                        <h3>Ricordati:</h3>
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
                                    </div>');
    }
    } else { //paypal
        $body->setContent('remind', '<div class="section-title text-center">Grazie per il tuo ordine!</div>');
    }
}

$main->setContent("body", $body->get());
$main->close();