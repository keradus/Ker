<?php

namespace Ker;

/**
 * Klasa implementująca wzorzec projektowy `Property`. Służy do zarządzania zmiennymi otrzymanymi metodą GET.
 *
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 * @license MIT
 * @link https://github.com/keradus/Ker
 * @date 2013-11-13 12:03:00
 */
class VarsGet extends AVars
{

    protected static $container;

}

VarsGet::setContainer($_GET);
