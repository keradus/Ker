<?php

namespace Ker\Ex;

/**
 * Klasa uszczególniająca wyjątek logiczny (PDOException) - występuje w sytuacji niepowodzenia zatwierdzenia transakcji.
 *
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 * @copyright Copyright (c) Dariusz Rumiński <dariusz.ruminski@gmail.com>
 * @date 2013-08-19 22:35:01
 */
class PDOTransactionCommit extends \PDOException
{

}
