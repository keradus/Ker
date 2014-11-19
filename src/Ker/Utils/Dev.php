<?php

namespace Ker\Utils;

/**
 * Klasa ze statycznymi metodami użytkowymi służącymi celom developerskim.
 *
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 * @copyright Copyright (c) Dariusz Rumiński <dariusz.ruminski@gmail.com>
 * @date 2013-08-19 22:35:01
 */
class Dev
{

    /**
     * Funkcja tworząca preloader js dla obrazków. Obrazki w preloaderze będą posortowane wg rozmiaru.\n
     * Funkcja wykorzystywana przede wszystkim do stworzenia preloadera layoutu.
     *
     * @param  string $_path     ścieżka do katalogu, z którego pobierane są pliki
     * @param  array  $_excluded [opt = array ( ) ] tablica plików, które będą pominięte w wynikowym skrypcie
     * @return string kod Java Script odpowiedzialny za preloading obrazków
     */
    public static function imgPreloaderFileContentGenerator($_path, $_excluded = array())
    {
        $files = array();
        foreach (new \DirectoryIterator($_path) as $item) {
            if ($item->isFile()) {
                if (in_array($item->getFilename(), $_excluded)) {
                    continue;
                }
                $files[] = array($item->GetFilename(), $item->getSize());
            }
        }

        usort($files, function ($a, $b) {
                    $x = $a[1];
                    $y = $b[1];
                    if ($x < $y)
                        return 1;
                    elseif ($x === $y)
                        return 0;
                    else
                        return -1;
                });

        $files = array_map(function ($_) {
                    return "\"{$_[0]}\"";
                }, $files);

        return "(function () {
    \"use strict\";
    var i, img, images;
    img = new Image();
    images = [" . implode(", ", $files) . "];
    for (i=0; i<images.length; ++i) {
        img.src = \"/public $_path\" + images[i];
    }
    }());";
    }

    public static function imgPreloaderFileContentGenerator2($_path, $_excluded = array())
    {
        $files = array();
        foreach (new \DirectoryIterator($_path) as $item) {
            if ($item->isFile()) {
                if (in_array($item->getFilename(), $_excluded)) {
                    continue;
                }
                $files[] = array($item->GetFilename(), $item->getSize());
            }
        }

        usort($files, function ($a, $b) {
                    $x = $a[1];
                    $y = $b[1];
                    if ($x < $y)
                        return 1;
                    elseif ($x === $y)
                        return 0;
                    else
                        return -1;
                });

        $jsCode = "";
        $counter = 0;
        foreach ($files as $file) {
            $jsCode .="
    image_url[$counter] = '{$file[0]}';";
            ++$counter;
        }

        return "{
    preload_image_object = new Image();
    image_url = new Array();
    $jsCode
    var i = 0;
    for(i=0; i<$counter; i++)
    preload_image_object.src = '/$_path' + image_url[i];
    }";
    }

}
