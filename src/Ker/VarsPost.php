<?php

namespace Ker;

/**
 * Klasa implementująca wzorzec projektowy `Property`. Służy do zarządzania zmiennymi otrzymanymi metodą POST.
 *
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 * @license MIT
 * @link https://github.com/keradus/Ker
 * @date 2013-11-13 12:03:00
 */
class VarsPost extends AVars
{

    protected static $container;

}

VarsPost::setContainer($_POST);
