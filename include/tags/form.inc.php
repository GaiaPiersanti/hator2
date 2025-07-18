<?php

class form extends TagLibrary
{
    
    public function __construct() {
        parent::__construct();
        error_log("form tag library loaded, methods: " . implode(',', get_class_methods($this)));
    }

    public function dummy($name = '', $data = '', $pars = [])
    {
        return "debug: form tag library loaded!";
    }

    public function text($name, $data, $pars)
    {

        if (isset($pars['disabled'])) {
            preg_match("~({$pars['disabled']})~", basename($_SERVER['SCRIPT_NAME']), $matches);
            $placeholder  = isset($pars['placeholder'])  ? $pars['placeholder']  : '';
            $errorClass   = isset($pars['errorClass'])   ? $pars['errorClass']   : '';
            $errorMessage = isset($pars['errorMessage']) ? $pars['errorMessage'] : '';
            $label        = isset($pars['label'])        ? $pars['label']        : '';


            if ($matches[1] == $pars['disabled']) {
                return"<div class=\"form-group row mb-20\">
                    <label for=\"{$name}\" class=\"col-sm-3 col-form-label\">
                        {$label}<span class=\"required\" style=\"color:red\">*</span>
                    </label>
                    <div class=\"col-sm-7\">
                        <input
                        type=\"text\"
                        id=\"{$name}\"
                        name=\"{$name}\"
                        class=\"form-control {$errorClass}\"
                        placeholder=\"{$placeholder}\"
                        value=\"{$data}\"
                        disabled
                        >
                        <div class=\"invalid-feedback\">
                        {$errorMessage}
                        </div>
                    </div>
                    </div>
                    ";
            } else {
                return "<div class=\"form-group row mb-20\">
                    <label for=\"{$name}\" class=\"col-sm-3 col-form-label\">
                        {$label}<span class=\"required\" style=\"color:red\">*</span>
                    </label>
                    <div class=\"col-sm-7\">
                        <input
                        type=\"text\"
                        id=\"{$name}\"
                        name=\"{$name}\"
                        class=\"form-control {$errorClass}\"
                        placeholder=\"{$placeholder}\"
                        value=\"{$data}\"
                        >
                        <div class=\"invalid-feedback\">
                        {$errorMessage}
                        </div>
                    </div>
                    </div>
                    ";
            }
        } else {
                $placeholder  = isset($pars['placeholder'])  ? $pars['placeholder']  : '';
                $errorClass   = isset($pars['errorClass'])   ? $pars['errorClass']   : '';
                $errorMessage = isset($pars['errorMessage']) ? $pars['errorMessage'] : '';
                $label        = isset($pars['label'])        ? $pars['label']        : '';
    return "
                    <div class=\"form-group row mb-20\">
                    <label for=\"{$name}\" class=\"col-sm-3 col-form-label\">
                        {$label}<span class=\"required\" style=\"color:red\">*</span>
                    </label>
                    <div class=\"col-sm-7\">
                        <input
                        type=\"text\"
                        id=\"{$name}\"
                        name=\"{$name}\"
                        class=\"form-control {$errorClass}\"
                        placeholder=\"{$placeholder}\"
                        value=\"{$data}\"
                        >
                        <div class=\"invalid-feedback\">
                        {$errorMessage}
                        </div>
                    </div>
                    </div>
                    ";
    }
}


    public function hidden($name, $data, $pars)
    {  error_log("form::hidden called with name={$name}, data={$data}, pars=" . print_r($pars, true));

        // Use provided $data if non-empty, otherwise fall back to $pars['value'] or empty string
        $value = ($data !== "" ? $data : ($pars['value'] ?? ''));

        return "<input name=\"{$name}\" type=\"hidden\" value=\"{$value}\">";
    }

    public function password($name, $data, $pars)
    {
        error_log("form::password called with name=$name, data=$data, pars=" . print_r($pars, true));

        $errorClass   = isset($pars['errorClass'])   ? $pars['errorClass']   : '';
        $errorMessage = isset($pars['errorMessage']) ? $pars['errorMessage'] : '';
        

        return "
            <div class=\"form-group row mb-20\">
                <label for=\"{$name}\" class=\"col-sm-3 col-form-label\">
                    {$pars['label']}<span class=\"required\" style=\"color:red\">*</span>
                </label>
                <div class=\"col-sm-7 position-relative\">
                    <input
                        type=\"password\"
                        id=\"{$name}\"
                        name=\"{$name}\"
                        class=\"form-control {$errorClass}\"
                        value=\"{$data}\"
                    >
                    <div class=\"invalid-feedback\">
                        {$errorMessage}
                    </div>
                    <button class=\"btn show-btn\" type=\"button\" data-target=\"{$name}\">
                    Show
                    </button>
                </div>
            </div>
            ";
    }


    public function email($name, $data, $pars)
    {
        error_log("form::email called with name=$name, data=$data, pars=" . print_r($pars, true));
        $errorClass   = $pars['errorClass']   ?? '';
        $errorMessage = $pars['errorMessage'] ?? '';
        $label        = $pars['label']        ?? '';
        $placeholder  = $pars['placeholder']  ?? '';

        return "
        <div class=\"form-group row mb-20\">
        <label for=\"{$name}\" class=\"col-sm-3 col-form-label\">
            {$label}<span class=\"required\" style=\"color:red\">*</span>
        </label>
        <div class=\"col-sm-7\">
            <input
            type=\"email\"
            id=\"{$name}\"
            name=\"{$name}\"
            class=\"form-control {$errorClass}\"
            placeholder=\"{$placeholder}\"
            value=\"{$data}\"
            >
            <div class=\"invalid-feedback\">
            {$errorMessage}
            </div>
        </div>
        </div>
        ";
    }




    public function date($name, $data, $pars)
    {

        return "<div class=\"form-group\">
                        <label class=\"form-label\">{$pars['label']}</label>
                        <span class=\"help\">{$pars['placeholder']}</span>
                        <div class=\"controls\">
                        <input name=\"{$name}\" value=\"{$data}\" type=\"date\" class=\"form-control\">
                    </div>
                </div>";
    }

    public function editor($name, $data, $pars)
    {

        return "<div class=\"form-group\">
                    <label class=\"form-label\">{$pars['label']}</label>
                    <div class=\"controls\">
                    <textarea id=\"text-editor\" placeholder=\"Enter text ...\" class=\"form-control\" rows=\"10\"></textarea>
                    </div>
              </div>";
    }


    function operation($name, $data, $pars)
    {
        if ($data != "") {
            return $data;
        } else {
            // Match 'add', 'edit', or 'delete' in the script name
            preg_match('~(add|edit|delete)~', basename($_SERVER['SCRIPT_NAME']), $matches);

            if (!empty($matches[1])) {
                switch ($matches[1]) {
                    case "add":
                        return "Add";
                    case "edit":
                        return "Edit";
                    case "delete":
                        return "Delete";
                }
            }

            // Default fallback
            return "Default";
        }
    }


    public function checklist($name, $data, $pars)
    {   //creo una connessione
        $host = "localhost";
        $user = "root";
        $password = ""; // Modifica questa riga con la tua password
        $database = "hator_db";
        $conection = new mysqli($host, $user, $password, $database);

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
}
