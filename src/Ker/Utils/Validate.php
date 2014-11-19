<?php

namespace Ker\Utils;

/**
 * Klasa ze statycznymi metodami użytkowymi służącymi do walidacji.
 *
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 * @copyright Copyright (c) Dariusz Rumiński <dariusz.ruminski@gmail.com>
 * @date 2013-08-19 22:35:01
 */
class Validate
{

    /**
     * Funkcja sprawdzająca czy zadane hasło jest wystarczająco silne - ma minimum 8 znaków, wielką i małą literę, cyfrę oraz znak specjalny.
     *
     * @param  string     $_value sprawdzane haslo
     * @return null|array NULL w przypadku wystarczająco silnego hasla, tablica błędów w przeciwnym przypadku
     * @see Res::call("validate_password_error_length")
     * @see Res::call("validate_password_error_noNumber")
     * @see Res::call("validate_password_error_noUppercase")
     * @see Res::call("validate_password_error_noLowercase")
     * @see Res::call("validate_password_error_noSpecial")
     */
    public static function password($_value)
    {
        $min_length = 8;
        $uppercase = '/[A-Z]/';  //Uppercase
        $lowercase = '/[a-z]/';  //lowercase
        $special_chars = '!@#$%^&*()_=+{};:,<.>';
        $special = "/[$special_chars]/";  // whatever you mean by 'special char'
        $number = '/[0-9]/';  //numbers

        $return = array();
        if (strlen($_value) < $min_length) {
            $return[] = \Res::call("validate_password_error_length", $min_length);
        }

        if (preg_match($number, $_value) < 1) {
            $return[] = \Res::call("validate_password_error_noNumber");
        }

        if (preg_match($uppercase, $_value) < 1) {
            $return[] = \Res::call("validate_password_error_noUppercase");
        }

        if (preg_match($lowercase, $_value) < 1) {
            $return[] = \Res::call("validate_password_error_noLowercase");
        }

        if (preg_match($special, $_value) < 1) {
            $return[] = \Res::call("validate_password_error_noSpecial", $special_chars);
        }

        return ($return ? $return : null);
    }

}
