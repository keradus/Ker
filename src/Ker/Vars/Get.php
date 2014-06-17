<?php

namespace Ker\Vars;

/**
 * Klasa implementująca wzorzec projektowy `Property`. Służy do zarządzania zmiennymi otrzymanymi metodą GET.
 *
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 * @license MIT
 * @link https://github.com/keradus/Ker
 * @date 2013-11-13 12:03:00
 */
class Get extends \Ker\Vars
{

    protected static $container;

}

Get::setContainer($_GET);
