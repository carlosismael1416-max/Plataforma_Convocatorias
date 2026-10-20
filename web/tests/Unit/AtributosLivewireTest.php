<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * Livewire no recorta los espacios del valor de wire:model: con
 * wire:model="⏎ titulo ⏎" el campo no muestra lo guardado ni guarda lo que
 * se escribe (pasó en el editor de propuestas). Este test impide que vuelvan
 * a entrar atributos de Livewire partidos en varias líneas.
 */
class AtributosLivewireTest extends TestCase
{
    public function test_ningun_atributo_de_livewire_tiene_saltos_de_linea(): void
    {
        $vistas = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(__DIR__ . '/../../resources/views'));
        $fallos = [];

        foreach ($vistas as $archivo) {
            if (! str_ends_with($archivo->getFilename(), '.blade.php')) {
                continue;
            }

            $contenido = file_get_contents($archivo->getPathname());

            if (preg_match_all('/((?:wire:[\w.:-]+|x-on:[\w.:-]+|@click[\w.]*)\s*=\s*")([^"]*\n[^"]*)"/', $contenido, $m)) {
                foreach ($m[1] as $atributo) {
                    $fallos[] = basename($archivo->getPathname()) . ': ' . $atributo;
                }
            }
        }

        $this->assertSame([], $fallos, 'Escribe el valor en una sola línea.');
    }
}
