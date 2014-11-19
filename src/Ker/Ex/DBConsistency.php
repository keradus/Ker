<?php

namespace Ker\Ex;

/**
 * Klasa uszczególniająca wyjątek logiczny (LogicException) - rzucany w sytuacji braku oczekiwanych danych
 * naruszając tym spójność bazy (klucz obcy wskazuje na nieistniejący rekord).
 *
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 * @copyright Copyright (c) Dariusz Rumiński <dariusz.ruminski@gmail.com>
 * @date 2013-08-19 22:35:01
 */
class DBConsistency extends \LogicException
{

}
