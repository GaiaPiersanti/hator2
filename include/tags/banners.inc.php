<?php
/**
 * Tag library per gestire i banner dinamici di home
 *
 * Tabelle utilizzate:
 *  - banners (id, nome, tipo, ordine, img_url, link, testo)
 *
 * Campi:
 *  - tipo: 'slider' | 'hero'
 *  - ordine: intero per il sorting
 *  - testo: descrizione testuale per lo slider
 */

// Includi init.inc.php per la connessione al DB e le classi base
require_once __DIR__ . '/../../include/init.inc.php';

class banners extends TagLibrary {
    /**
     * Renderizza lo slider principale usando $conn (mysqli)
     */
    public function slider($name, $data, $pars) {
        global $conn;
        // Prepara ed esegue la query
        $stmt = $conn->prepare(
            "SELECT * FROM banners WHERE tipo = 'slider' ORDER BY ordine"
        );
        $stmt->execute();
        $result = $stmt->get_result();
        $slides = $result->fetch_all(MYSQLI_ASSOC);

        // Costruisci l’HTML dello slider
        $html  = '<div class="slider-area slider-style-three">'
               . '<div class="slider-activation owl-carousel">';

        foreach ($slides as $b) {
            $id      = htmlspecialchars($b['id'],    ENT_QUOTES);
            $bgUrl   = htmlspecialchars($b['img_url'],ENT_QUOTES);
            $link    = htmlspecialchars($b['link'],   ENT_QUOTES);
            $caption = htmlspecialchars($b['testo'],  ENT_QUOTES);
            $label   = htmlspecialchars($b['nome'],   ENT_QUOTES);

            $html .= "<div class=\"slide align-center-left fullscreen animation-style-{$id}\" style=\"background-image:url('{$bgUrl}')\">"
                   . '<div class="slider-progress"></div>'
                   . '<div class="container"><div class="row"><div class="col-lg-12"><div class="slider-content">';

            if ($caption !== '') {
                $html .= "<p>{$caption}</p>";
            }

            $html .= "<div class=\"slide-btn white-color\"><a href=\"{$link}\">{$label}</a></div>"
                   . '</div></div></div></div>';
        }

        $html .= '</div></div>';
        return $html;
    }

    /**
     * Renderizza i banner “hero” usando $conn (mysqli)
     */
    public function hero($name, $data, $pars) {
        global $conn;
        // Prepara ed esegue la query
        $stmt = $conn->prepare(
            "SELECT * FROM banners WHERE tipo = 'hero' ORDER BY ordine"
        );
        $stmt->execute();
        $result = $stmt->get_result();
        $items  = $result->fetch_all(MYSQLI_ASSOC);

        // Costruisci l’HTML dell’hero-banner
        $html  = '<div class="hero-banner-area">'
               . '<div class="container-fluid"><div class="row">';

        foreach ($items as $b) {
            $link = htmlspecialchars($b['link'],   ENT_QUOTES);
            $img  = htmlspecialchars($b['img_url'],ENT_QUOTES);
            $alt  = htmlspecialchars($b['nome'],   ENT_QUOTES);

            $html .= '<div class="col-md-6 col-sm-6 mb-xsm-30">'
                   . '<div class="single-banner zoom">'
                   . "<a href=\"{$link}\"><img src=\"{$img}\" alt=\"{$alt}\"></a>"
                   . '</div></div>';
        }

        $html .= '</div></div></div>';
        return $html;
    }
}
