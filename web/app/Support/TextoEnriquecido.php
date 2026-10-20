<?php

namespace App\Support;

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMText;
use Illuminate\Support\HtmlString;

/**
 * Texto con formato de los campos de la propuesta (editor tipo Word).
 *
 * Se guarda como HTML saneado con una lista blanca: párrafos, saltos,
 * negrita, cursiva, subrayado, tachado, listas y <span> con fuente, tamaño,
 * color, fondo y alineación como estilos en línea. Cualquier otra etiqueta,
 * atributo o propiedad CSS se descarta (scripts, enlaces, imágenes, eventos…).
 * Los textos anteriores, sin formato, se convierten a párrafos.
 */
class TextoEnriquecido
{
    /** Etiqueta permitida => la que se escribe (b/i se normalizan). */
    private const ETIQUETAS = [
        'p' => 'p', 'br' => 'br', 'strong' => 'strong', 'b' => 'strong',
        'em' => 'em', 'i' => 'em', 'u' => 'u', 's' => 's', 'strike' => 's',
        'span' => 'span', 'ol' => 'ol', 'ul' => 'ul', 'li' => 'li',
        'sub' => 'sub', 'sup' => 'sup',
    ];

    /** Etiquetas que se quitan junto con todo su contenido. */
    private const DESCARTAR = ['script', 'style', 'iframe', 'object', 'embed', 'template', 'svg', 'math', 'noscript', 'head', 'title'];

    public const FUENTES = [
        'Arial' => 'Arial, Helvetica, sans-serif',
        'Calibri' => 'Calibri, Carlito, sans-serif',
        'Georgia' => 'Georgia, serif',
        'Times New Roman' => '"Times New Roman", Times, serif',
        'Verdana' => 'Verdana, Geneva, sans-serif',
        'Courier New' => '"Courier New", Courier, monospace',
    ];

    public const TAMANOS = ['10px', '12px', '14px', '16px', '18px', '24px', '32px'];

    private const ALINEACIONES = ['left', 'center', 'right', 'justify'];

    public static function sanear(?string $valor): string
    {
        $valor = trim((string) $valor);

        if ($valor === '') {
            return '';
        }

        if (! self::esHtml($valor)) {
            return self::desdeTextoPlano($valor);
        }

        $documento = new DOMDocument('1.0', 'UTF-8');
        $anterior = libxml_use_internal_errors(true);
        $documento->loadHTML(
            '<?xml encoding="UTF-8"><div id="raiz">' . $valor . '</div>',
            LIBXML_NONET | LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );
        libxml_clear_errors();
        libxml_use_internal_errors($anterior);

        $raiz = $documento->getElementById('raiz');

        if (! $raiz) {
            return self::desdeTextoPlano(strip_tags($valor));
        }

        $salida = '';

        foreach (iterator_to_array($raiz->childNodes) as $hijo) {
            $salida .= self::nodo($hijo);
        }

        // Quill deja un párrafo vacío cuando el editor no tiene nada.
        return self::texto($salida) === '' ? '' : $salida;
    }

    /** HTML listo para mostrar (se vuelve a sanear por si viene de datos viejos). */
    public static function html(?string $valor): HtmlString
    {
        return new HtmlString(self::sanear($valor));
    }

    /** HTML para mostrar o, si el campo está vacío, el texto alterno (escapado). */
    public static function mostrar(?string $valor, string $siVacio): HtmlString
    {
        $html = self::sanear($valor);

        return new HtmlString($html !== '' ? $html : '<p>' . e($siVacio) . '</p>');
    }

    /** Solo el texto, sin formato: para contar caracteres, validar o exportar a Excel. */
    public static function texto(?string $valor): string
    {
        $valor = (string) $valor;

        if (self::esHtml($valor)) {
            $valor = preg_replace(['#<br\s*/?>#i', '#</(p|li)>#i'], ["\n", "\n"], $valor);
            $valor = html_entity_decode(strip_tags((string) $valor), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }

        $valor = str_replace("\u{00A0}", ' ', $valor);
        $valor = preg_replace("/[ \t]+/u", ' ', $valor);
        $valor = preg_replace("/\n{3,}/u", "\n\n", (string) $valor);

        return trim((string) $valor);
    }

    public static function longitud(?string $valor): int
    {
        return mb_strlen(self::texto($valor));
    }

    private static function esHtml(string $valor): bool
    {
        return (bool) preg_match('#<\s*/?\s*(p|br|span|strong|b|em|i|u|s|ol|ul|li|div|font|h[1-6])\b#i', $valor);
    }

    private static function desdeTextoPlano(string $valor): string
    {
        $parrafos = preg_split("/\r\n|\r|\n/", $valor);

        return collect($parrafos)
            ->map(fn (string $linea): string => '<p>' . ($linea === '' ? '<br>' : e($linea)) . '</p>')
            ->implode('');
    }

    private static function nodo(DOMNode $nodo): string
    {
        if ($nodo instanceof DOMText) {
            return e(str_replace("\u{00A0}", ' ', $nodo->wholeText));
        }

        if (! $nodo instanceof DOMElement) {
            return '';
        }

        $nombre = mb_strtolower($nodo->nodeName);

        if (in_array($nombre, self::DESCARTAR, true)) {
            return '';
        }

        $interior = '';

        foreach (iterator_to_array($nodo->childNodes) as $hijo) {
            $interior .= self::nodo($hijo);
        }

        // Etiqueta no permitida: se conserva su contenido. Los bloques
        // (div, h1…) se vuelven párrafos para no perder los saltos.
        if (! isset(self::ETIQUETAS[$nombre])) {
            return preg_match('/^(div|h[1-6]|blockquote|pre|section|article)$/', $nombre)
                ? '<p>' . $interior . '</p>'
                : $interior;
        }

        $etiqueta = self::ETIQUETAS[$nombre];

        if ($etiqueta === 'br') {
            return '<br>';
        }

        // Quill 2 marca las viñetas con <li data-list="bullet"> dentro de <ol>.
        if ($etiqueta === 'ol' && self::esListaDeVinetas($nodo)) {
            $etiqueta = 'ul';
        }

        $estilo = self::estilo($nodo, $etiqueta);

        if ($etiqueta === 'span' && $estilo === '') {
            return $interior;
        }

        return '<' . $etiqueta . ($estilo !== '' ? ' style="' . e($estilo) . '"' : '') . '>' . $interior . '</' . $etiqueta . '>';
    }

    private static function esListaDeVinetas(DOMElement $lista): bool
    {
        foreach ($lista->childNodes as $hijo) {
            if ($hijo instanceof DOMElement && $hijo->getAttribute('data-list') === 'bullet') {
                return true;
            }
        }

        return false;
    }

    /** Solo propiedades y valores conocidos; lo demás se descarta. */
    private static function estilo(DOMElement $nodo, string $etiqueta): string
    {
        $permitidas = [];
        $clases = ' ' . $nodo->getAttribute('class') . ' ';

        // Alineación de Quill como clase (ql-align-center) o como estilo.
        if (in_array($etiqueta, ['p', 'li'], true) && preg_match('/ ql-align-(center|right|justify) /', $clases, $m)) {
            $permitidas['text-align'] = $m[1];
        }

        foreach (explode(';', $nodo->getAttribute('style')) as $declaracion) {
            if (! str_contains($declaracion, ':')) {
                continue;
            }

            [$propiedad, $valor] = array_map('trim', explode(':', $declaracion, 2));
            $propiedad = mb_strtolower($propiedad);
            $valor = trim(str_ireplace('!important', '', $valor));

            $limpio = match ($propiedad) {
                'color', 'background-color' => self::color($valor),
                'font-size' => in_array($valor, self::TAMANOS, true) ? $valor : null,
                'font-family' => self::fuente($valor),
                'text-align' => in_array($etiqueta, ['p', 'li'], true) && in_array($valor, self::ALINEACIONES, true) ? $valor : null,
                default => null,
            };

            if ($limpio !== null) {
                $permitidas[$propiedad] = $limpio;
            }
        }

        return collect($permitidas)
            ->map(fn (string $valor, string $propiedad): string => "{$propiedad}: {$valor}")
            ->implode('; ');
    }

    private static function color(string $valor): ?string
    {
        $valor = mb_strtolower($valor);

        if (preg_match('/^#([0-9a-f]{3}|[0-9a-f]{6})$/', $valor)) {
            return $valor;
        }

        if (preg_match('/^rgba?\(\s*(\d{1,3})\s*,\s*(\d{1,3})\s*,\s*(\d{1,3})\s*(,\s*(0|1|0?\.\d+)\s*)?\)$/', $valor, $m)
            && max((int) $m[1], (int) $m[2], (int) $m[3]) <= 255) {
            return sprintf('#%02x%02x%02x', $m[1], $m[2], $m[3]);
        }

        return null;
    }

    private static function fuente(string $valor): ?string
    {
        $primera = mb_strtolower(trim(explode(',', $valor)[0], " \"'"));

        foreach (self::FUENTES as $nombre => $pila) {
            if (mb_strtolower($nombre) === $primera) {
                return $pila;
            }
        }

        return null;
    }
}
