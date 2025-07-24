<?php


// 1) Istanzio il frame principale
$main = new Template("dtml/hator/frame");
$main->setContent("page_title", $page_title);

// 2) Istanzio il sotto‐template per la pagina checkout
$body = new Template("dtml/hator/riepilogo");

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Determine current order ID
    $orderId = intval($_GET['id'] ?? 0);
    // Set order number placeholder
    $body->setContent('ordine', htmlspecialchars($_GET['id'] ?? '', ENT_QUOTES));

    // Determine content based on payment method
    $method = $_GET['metodo'] ?? '';
    switch ($method) {
        case 'bank_transfer':
            $remindHtml = <<<HTML
<div class="card mb-4">
  <div class="card-header" id="headingone">
    <h5 class="mb-0">
      <button class="btn btn-link" data-bs-toggle="collapse"
              data-bs-target="#collapseOne" aria-expanded="true"
              aria-controls="collapseOne">
        Direct Bank Transfer
      </button>
    </h5>
  </div>
  <div id="collapseOne" class="collapse show"
       aria-labelledby="headingone" data-bs-parent="#accordion">
    <div class="card-body">
      <p>Please transfer the total amount using your <strong>Order ID</strong> as the payment reference. Funds must be received before shipping.</p>
      <ul class="list-unstyled mb-3">
        <br>
        <li><strong>Bank Name:</strong> UniCredit S.p.A.</li>
        <li><strong>Account Holder:</strong> Hator Perfumes S.r.l.</li>
        <li><strong>IBAN:</strong> IT60 X054 2811 1010 0000 0123 456</li>
        <li><strong>BIC/SWIFT:</strong> UNCRITMMXXX</li>
        <li><strong>Bank Address:</strong> Piazza Gae Aulenti 10, 20154 Milano MI, Italy</li>
      </ul>
      <p><em>Note:</em> International transfers may take 3–5 business days. Please include all bank charges on your side.</p>
    </div>
  </div>
</div>
<!-- Actions -->
<div class="order-actions text-center mb-5">
  <div class="buttons-cart d-inline-block mx-2">
    <a href="index.php?page=shop">Continue Shopping</a>
  </div>
  <div class="buttons-cart d-inline-block mx-2">
    <a href="index.php?page=order-details&id=$orderId">View Your Order</a>
  </div>
</div>
HTML;
            break;

        case 'cheque':
            $remindHtml = <<<HTML
<div class="card mb-4">
  <div class="card-header" id="headingtwo">
    <h5 class="mb-0">
      <button class="btn btn-link" data-bs-toggle="collapse"
              data-bs-target="#collapseTwo" aria-expanded="true"
              aria-controls="collapseTwo">
        Cheque Payment
      </button>
    </h5>
  </div>
  <div id="collapseTwo" class="collapse show"
       aria-labelledby="headingtwo" data-bs-parent="#accordion">
    <div class="card-body">
      <p>Please prepare a cheque payable to <strong>Hator Perfumes S.r.l.</strong> for the total order amount. Mail it to the following address, quoting your <strong>Order ID</strong> on the back of the cheque:</p>
      <ul class="list-unstyled mb-3">
        <br>
        <li><strong>Recipient:</strong> Hator Perfumes S.r.l.</li>
        <li><strong>Address:</strong> Via Vetoio 1, 67100 Coppito AQ, Italy</li>
      </ul>
      <p><em>Note:</em> Allow 5–7 business days for cheque clearing. We will dispatch your order once the payment has been fully processed.</p>
    </div>
  </div>
</div>
<!-- Actions -->
<div class="order-actions text-center mb-5">
  <div class="buttons-cart d-inline-block mx-2">
    <a href="index.php?page=shop">Continue Shopping</a>
  </div>
  <div class="buttons-cart d-inline-block mx-2">
    <a href="index.php?page=order-details&id=$orderId">View Your Order</a>
  </div>
</div>
HTML;
            break;

        default: // PayPal or no method
            $remindHtml = <<<HTML
<div class="text-center py-4">
  <p>Your payment has been received. We’re preparing your items and will notify you when they ship.</p>
</div>
<!-- Actions -->
<div class="order-actions text-center mb-5">
  <div class="buttons-cart d-inline-block mx-2">
    <a href="index.php?page=shop">Continue Shopping</a>
  </div>
  <div class="buttons-cart d-inline-block mx-2">
    <a href="index.php?page=order-details&id=$orderId">View Your Order</a>
  </div>
</div>
HTML;
            break;
    }

    // Inject the reminder HTML
    $body->setContent('remind', $remindHtml);
}

$main->setContent("body", $body->get());
$main->close();