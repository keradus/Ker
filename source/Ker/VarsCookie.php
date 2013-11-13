<?php

namespace Ker;

/**
 * Klasa implementująca wzorzec projektowy `Property`. Służy do zarządzania zmiennymi otrzymanymi z ciasteczek.
 *
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 * @license MIT
 * @link https://github.com/keradus/Ker
 * @date 2013-11-13 12:03:00
 */
class VarsCookie extends AVars
{

    protected static $container;

}

VarsCookie::setContainer($_COOKIE);
