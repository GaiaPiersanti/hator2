<?php
// definisce una tag library (form) che genera 
// automaticamente blocchi HTML di form basati sui tag che scrivi nei template.
    Class form extends tagLibrary {
        public function dummy() {}
        
//ogni metodo corrisponde a un tipo di campo del form
// Genera un campo testo <input type="text">, più label e help:

        public function text($name, $data, $pars){

            if (isset($pars['disabled'])) {
                preg_match("~({$pars['disabled']})~", basename($_SERVER['SCRIPT_NAME']), $matches);

    //	•	~…~ sono i delimitatori (alternativa a /…/), comodi quando la regex contiene /.
	//•	Cerca se il nome dello script (basename($_SERVER['SCRIPT_NAME'])) contiene la parola specificata in $pars['disabled'].
	//•	Se trova, in $matches[1] ci sarà proprio quel testo, e puoi usarlo per decidere se generare l’<input disabled> o no.
                
    
    // 	1.	Controllo disabled
                // 	•	Se hai passato nel tag disabled="edit", verifica con preg_match se lo script corrente (basename($_SERVER['SCRIPT_NAME'])) contiene la parola "edit"
                // 	•	Se sì, nell’<input> mette l’attributo disabled

                if ($matches[1] == $pars['disabled']) {
                    return "<div class=\"form-group\">
                                <label class=\"form-label\">{$pars['label']}</label>
                                <span class=\"help\">{$pars['placeholder']}</span>
                                <div class=\"controls\">
                                <input name=\"{$name}\" type=\"text\" value=\"{$data}\" disabled class=\"form-control\">
                                </div>
                            </div>";
                    
                }   else {
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

    //     Genera un campo nascosto <input type="hidden">:
	// •	Se $data (quindi il valore passato dal controller) è non vuoto, lo usa
	// •	Altrimenti prende value da $pars['value']
        public function hidden($name, $data, $pars){

            if ($data != "") {
                $value = $data;
            } else {
                $value = $pars['value'];
            } 

            return "<input name=\"{$name}\" type=\"hidden\" value=\"{$value}\">";

            
        }

        public function password($name, $data, $pars){

            return "<div class=\"form-group\">
                        <label class=\"form-label\">{$pars['label']}</label>
                        <span class=\"help\">{$pars['placeholder']}</span>
                        <div class=\"controls\">
                        <input name=\"{$name}\" value=\"{$data}\" type=\"password\" class=\"form-control\">
                        </div>
                    </div>";

            
        }

        public function email($name, $data, $pars){

            return "<div class=\"form-group\">
                        <label class=\"form-label\">{$pars['label']}</label>
                        <span class=\"help\">{$pars['placeholder']}</span>
                        <div class=\"controls\">
                        <input name=\"{$name}\" value=\"{$data}\" type=\"email\" class=\"form-control\">
                        </div>
                    </div>";

            
        }

        public function date($name, $data, $pars){

            return "<div class=\"form-group\">
                        <label class=\"form-label\">{$pars['label']}</label>
                        <span class=\"help\">{$pars['placeholder']}</span>
                        <div class=\"controls\">
                        <input name=\"{$name}\" value=\"{$data}\" type=\"date\" class=\"form-control\">
                    </div>
                </div>";

            
        }
        //Genera un blocco con <textarea> (per un editor WYSIWYG):
        public function editor($name, $data, $pars){

            return "<div class=\"form-group\">
                    <label class=\"form-label\">{$pars['label']}</label>
                    <div class=\"controls\">
                    <textarea id=\"text-editor\" placeholder=\"Enter text ...\" class=\"form-control\" rows=\"10\"></textarea>
                    </div>
              </div>";
        }

//         Restituisce una stringa testuale basata sul contesto:
// 	1.	Se $data (valore passato) non è vuoto, la ritorna direttamente
// 	2.	Altrimenti estrae dalla pagina corrente (SCRIPT_NAME) la parola add, edit o delete e restituisce “Add”, “Edit” o “Delete”
// 	3.	Se non trova nessuna corrispondenza, torna “Default”

// È utile per mostrare di default il nome dell’operazione nel form (es. il pulsante o l’intestazione).

        function operation($name, $data, $pars) {
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

    //int preg_match ( string $pattern , string $subject [, array &$matches [, int $flags = 0 [, int $offset = 0 ]]] )
    //a funzione preg_match() in PHP
    // serve a cercare una corrispondenza fra una stringa e una espressione regolare (PCRE).
//     	•	$pattern
//          L’espressione regolare, racchiusa fra delimitatori (tipicamente /…/), eventualmente corredata da modificatori (i, u, m, ecc.).
//              Esempio: /^abc/i cerca la sequenza “abc” all’inizio della stringa, senza distinzione fra maiuscole/minuscole.
// 	•	$subject
//          La stringa in cui si vuole cercare.
// 	•	&$matches (opzionale)
//          Se passato, alla fine della chiamata conterrà un array con:
// 	•	$matches[0] → la porzione di stringa che ha fatto match complessivamente
// 	•	$matches[1], $matches[2], … → eventuali sottogruppi catturati (parentesi tonde nella regex)
// 	•	Valore di ritorno
// 	    •	1 se la regex ha trovato almeno una corrispondenza
// 	    •	0 se non ne ha trovata
// 	    •	false in caso di errore nella regex

?>