<?php

namespace Ker;

/**
 * Klasa implementująca wzorzec projektowy Dispatcher. Służy do zarządzania akcjami na podstawie otrzymanych żądań.
 *
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 * @copyright Copyright (c) Dariusz Rumiński <dariusz.ruminski@gmail.com>
 * @date 2013-08-19 22:56:44
 * @todo TASK: #52
 */
class Dispatcher
{

    protected $action;
    protected $data;
    protected $extra;
    protected $lang;
    protected $module;

    public function __construct(Router $_router, $_ = array())
    {
        $this->lang = $_router->getLang();
        if (!in_array($this->lang, \Ker\Config::getOne("allowedLanguages"))) {
            $this->lang = \Ker\Config::getOne("defaultLanguage");
            $this->setError(404);

            return;
        }

        $this->module = $_router->getModule();
        $this->action = $_router->getAction();
        $this->data = $_router->getData();
        $this->extra = $_router->getExtra();
        $view = $this->getView();

        if (!$this->module) {
            $this->module = "Index";
            $this->action = "index";
        } elseif (
                (isset($_["requireViewFile"]) and $_["requireViewFile"] and !file_exists($view::getViewFile($this->lang, $this->module, $this->action)))
        ) {
            $this->setError(404);
        }
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
     * Metoda pobierająca nazwe klasy kontrolera.
     *
     * @access public
     * @return string nazwa klasy kontrolera
     */
    public function getController()
    {
        $class = "\\" . Config::getOne("app") . "\\Controller\\" . $this->module;
        if (!class_exists($class)) {
            $class = "\\Ker\\MVCP\\AController";
        }

        return $class;
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
     * Metoda pobierająca nazwe klasy modelu.
     *
     * @access public
     * @return string nazwa klasy modelu
     */
    public function getModel()
    {
        static $class = null;

        if (!$class) {
            $class = "\\" . Config::getOne("app") . "\\Model\\" . $this->module;
            if (!class_exists($class)) {
                $class = "\\Ker\\MVCP\\AModel";
            }
        }

        return $class;
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

    /**
     * Metoda pobierająca nazwe klasy uprawnień.
     *
     * @access public
     * @return string nazwa klasy uprawnień
     */
    public function getPermission()
    {
        static $class = null;

        if (!$class) {
            $class = static::getPermissionByModule(array("module" => $this->module,));
        }

        return $class;
    }

    // TASK: #52 - dokumentacja
    public static function getPermissionByModule($_)
    {
        $class = "\\" . Config::getOne("app") . "\\Permission\\" . $_["module"];

        if (!class_exists($class)) {
            $class = "\\Ker\\MVCP\\APermission";
        }

        return $class;
    }

    /**
     * Metoda pobierająca nazwe klasy widoku.
     *
     * @access public
     * @return string nazwa widoku
     */
    public function getView()
    {
        $class = "\\" . Config::getOne("app") . "\\View\\" . $this->module;
        if (!class_exists($class)) {
            $class = "\\Ker\\MVCP\\AView";
        }

        return $class;
    }

    /**
     * Metoda ustawiająca moduł i akcję na określony błąd.
     *
     * @access public
     * @param int $_errorCode kod błędu
     */
    public function setError($_errorCode)
    {
        static $availableErrorCodes = array(403, 404);

        if (!in_array($_errorCode, $availableErrorCodes)) {
            throw new \InvalidArgumentException();
        }

        $this->module = "SiteError";
        // e na start jest potrzebne, gdyby w np kontrolerze chciec robic akcje dla errorow - funkcja nie moze zaczynac sie cyfra !
        $this->action = "e" . $_errorCode;
    }

    //TASK: #147 - przeniesc do Routera, uzupelnic dokumentacje
    public static function generateEquivalentLink($_)
    {
        if (file_exists(\Ker\MVCP\AView::getViewFile($_["lang"], $_["module"], $_["action"]))) {
            return \Ker\Router::generateLink(array(
                        "lang" => $_["lang"],
                        "module" => $_["module"],
                        "action" => $_["action"],
                        "extra" => $_["extra"],
            ));
        }

        if (file_exists(\Ker\MVCP\AView::getViewFile($_["lang"], $_["module"], "index"))) {
            //zmienilismy akcje - parametr "extra" bylby nadmiarowy
            return \Ker\Router::generateLink(array(
                        "lang" => $_["lang"],
                        "module" => $_["module"],
                        "action" => "index",
            ));
        }

        return \Ker\Router::generateLink(array(
                    "lang" => $_["lang"],
        ));
    }

}
