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


            if ($matches[1] == $pars['disabled']) {
                return "<div class=\"form-group\">
                                <label class=\"form-label\">{$pars['label']}</label>
                                <span class=\"help\">{$pars['placeholder']}</span>
                                <div class=\"controls\">
                                <input name=\"{$name}\" type=\"text\" value=\"{$data}\" disabled class=\"form-control\">
                                </div>
                            </div>";
            } else {
                return "<div class=\"form-group\">
                                <label class=\"form-label\">{$pars['label']}</label>
                                <span class=\"help\">{$pars['placeholder']}</span>
                                <div class=\"controls\">
                                <input name=\"{$name}\" type=\"text\" value=\"{$data}\" class=\"form-control\">
                                </div>
                            </div>";
            }
        } else {
            return "<div class=\"form-group\">
                            <label class=\"form-label\">{$pars['label']}</label>
                            <span class=\"help\">{$pars['placeholder']}</span>
                            <div class=\"controls\">
                            <input name=\"{$name}\" type=\"text\" value=\"{$data}\" class=\"form-control\">
                            </div>
                        </div>";
        }
    }


    public function hidden($name, $data, $pars)
    {

        if ($data != "") {
            $value = $data;
        } else {
            $value = $pars['value'];
        }

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
                        placeholder=\"{$pars['placeholder']}\"
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
}
