<?php

namespace Ker\Ex;

/**
 * Klasa uszczególniająca wyjątek logiczny (LogicException) - występuje w sytuacji, gdy wywołano istniejącą z powodów dziedziczenia metodę, która w danej klasie potomnej jest zakazana.
 *
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 * @copyright Copyright (c) Dariusz Rumiński <dariusz.ruminski@gmail.com>
 * @date 2013-08-19 22:35:01
 */
class ForbiddenAction extends \LogicException
{

}
