<?php

namespace Ker\MVCP;

/**
 * Description of Permission
 *
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 * @copyright Copyright (c) Dariusz Rumiński <dariusz.ruminski@gmail.com>
 * @date 2013-08-19 22:35:01
 * @todo TASK: #48
 */
abstract class Permission
{

    protected static $default = true;

    public static function getPermission($_action, $_perm = null, $_info = array())
    {
        if ($_action === __FUNCTION__) {
            throw new \LogicException("Action name " . __FUNCTION__ . " is prohibited!");
        }
        $class = get_called_class();
        $perm = (method_exists($class, $_action) ? $class::$_action($_perm, $_info) : $class::$default);

        if ($perm === true || $perm === false) {
            return $perm;
        }

        return ($perm <= $_perm);
    }

}
