<?php

namespace Ker;

/**
 * Klasa implementująca wzorzec projektowy Router. Służy do odbioru parametrów żądania. Dodatkowo potrafi wygenerować link do ustalonego przez nas żądania.
 *
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 * @copyright Copyright (c) Dariusz Rumiński <dariusz.ruminski@gmail.com>
 * @date 2013-08-19 22:56:44
 * @todo TASK: #61
 */
class Router
{

    //TASK: #61 - dokumentacja
    public static $defaultAction = "index";
    public static $defaultModule = "Index";

    /**
     * Zmienna przechowująca wartość parametru wywołania strony `action`, mówiącego jaką akcję chcemy wykonać.
     *
     * @access protected
     * @var string
     */
    protected $action;

    /**
     * Zmienna przechowująca wartość parametru wywołania strony `data`, zawierającego dodatkowe dane dla żądania.
     *
     * @access protected
     * @var string
     */
    protected $data;

    /**
     * Zmienna przechowująca wartość parametru wywołania strony `extra`, zawierającego dodatkowe dane dla żądania.
     *
     * @access protected
     * @var string
     */
    protected $extra;

    /**
     * Zmienna przechowująca skrot obecnego jezyku, odpowiedni dla rozszerzen plikow widoku.
     *
     * @access protected
     * @var string
     */
    protected $lang;

    /**
     * Zmienna przechowująca wartość parametru wywołania strony `module`, mówiącego w jakim chcemy wywołać akcję.
     *
     * @access protected
     * @var string
     */
    protected $module;

    /**
     * Konstruktor klasy, wyznacza wartości zmiennych action, module oraz data na podstawie zmiennych get żądania.
     *
     * @access public
     * @return Router instancja klasy
     * @see Config::get ("defaultLanguage")
     */
    public function __construct()
    {
        $site = explode("/", VarsGet::getHtmlEncoded("site"));
        if ($site[0] === "") {
            $site = array();
        }

        $this->lang = array_shift($site) ? : Config::get("defaultLanguage");
        $this->module = array_shift($site) ? : static::$defaultModule;
        $this->action = array_shift($site) ? : static::$defaultAction;
        $this->extra = $site;
        $this->data = VarsGet::getHtmlEncoded("data");
    }

    /**
     * Metoda pobierająca wartość pola `action`.
     *
     * @access public
     * @return string wartość pola `action`
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * Metoda pobierająca wartość pola `data`.
     *
     * @access public
     * @return string wartość pola `data`
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Metoda pobierająca wartość pola `extra`.
     *
     * @access public
     * @return string wartość pola `extra`
     */
    public function getExtra()
    {
        return $this->extra;
    }

    /**
     * Metoda pobierająca wartość pola `lang`.
     *
     * @access public
     * @return string wartość pola `lang`
     */
    public function getLang()
    {
        return $this->lang;
    }

    /**
     * Metoda pobierająca wartość pola `module`.
     *
     * @access public
     * @return string wartość pola `module`
     */
    public function getModule()
    {
        return $this->module;
    }

    //TASK: #148 - poprawic dokumentacje - byly trzy parametry, teraz jest tablica parametrow, doszedl parametr lang, data
    //           - dodac obsluge modRewrite !
    //           - dodac opis HASH
    /**
     * Metoda generująca link do strony.
     *
     * @access public
     * @param  string? $_module[=NULL] nazwa kontrolera
     * @param  string? $_action[=NULL] nazwa akcji
     * @param  string? $_extra[=NULL]  wartość dodatkowych danych dla żądania
     * @return string  Link do zadanej strony
     * @static
     * @see Config::get ("index")
     * @see Config::get ("usingModRewrite")
     */
    public static function generateLink($_)
    {
        $url = "";

        $url = Config::getOne("url", "") . "/" . Config::getOne("index", "index.php");
        $url .= "?site={$_["lang"]}";
        if (isset($_["module"]) && $_["module"]) {
            $url .= "/{$_["module"]}";
            if (isset($_["action"]) && $_["action"] && $_["action"] !== static::$defaultAction) {
                $url .= "/{$_["action"]}";
                if (isset($_["extra"]) && $_["extra"]) {
                    $url .= "/" . implode("/", $_["extra"]);
                }
            }
        }

        if (isset($_["data"]) && $_["data"]) {
            $url .= "&data=" . implode("/", $_["data"]);
        }

        if (isset($_["hash"]) && $_["hash"]) {
            $url .= "#" . $_["hash"];
        }

        return $url;
    }

    // TASK: #61 - dokumentacja
    public static function generateLinkWithPerms($_)
    {
        $permissionClass = \Ker\Dispatcher::getPermissionByModule($_);
        if (!$permissionClass::getPermission(@$_["action"] ? : static::$defaultAction, \Ker\Session::getPerm(), $_)) {
            return "";
        }

        return str_replace("%URL%", static::generateLink($_), $_["tpl"]);
    }

}
