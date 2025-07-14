<?php
/**
 * Genera uno slug a partire da una stringa:
 * – porta tutto in minuscolo
 * – sostituisce tutto ciò che non è lettera o numero con trattini
 * – rimuove eventuali trattini multipli o iniziali/finali
 *
 * @param string $text
 * @return string
 */
function slugify(string $text): string {
    // 1. Trasforma in minuscolo
    $text = mb_strtolower($text, 'UTF-8');

    // 2. Sostituisci ogni carattere non alfanumerico con un trattino
    $text = preg_replace('/[^\p{L}\p{Nd}]+/u', '-', $text);

    // 3. Rimuovi trattini multipli
    $text = preg_replace('/-+/', '-', $text);

    // 4. Rimuovi eventuali trattini iniziali o finali
    $text = trim($text, '-');

    return $text;
}