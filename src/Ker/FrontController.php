<?php

namespace Ker;

/**
 * Klasa implementująca wzorzec projektowy FrontController.
 *
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 * @copyright Copyright (c) Dariusz Rumiński <dariusz.ruminski@gmail.com>
 * @date 2013-08-19 22:56:44
 * @todo TASK: #55
 */
class FrontController
{

    protected static $actionsWithDataOnly = array(
        "atom" => "application/atom+xml",
        "js" => "application/javascript", //TASK: #24 - co z IE8-?
        "json" => "application/json",
        "pdf" => "application/pdf",
        "doc" => "application/msword",
        "rss" => "application/rss+xml",
        "zip" => "application/zip",
        "gzip" => "pplication/x-gzip",
        "xml" => "text/xml", //TASK: #25 - co z IE7?
    );
    public static $dispatcher;

    //TASK: #55 - dokumentacja
    // Dzieki wyniesieniu ladowania pliku widoku do osobnej funkcji w pliku nie beda widoczne zmienne uzyte w funkcji. Dodatkowo w funkcji zastosowano mechanizm ukrywania kontekstu (self)
    private static function loadViewFile($_file, $_ = array())
    {
        // Dzieki uzyciu anonimowej funkcji do wczytania pliku wczytywany plik nie zna kontekstu (self)!
        $fileLoader = function ($_file, $_) {
                    return include $_file;
                };

        return $fileLoader($_file, $_);
    }

    public static function run()
    {
        $router = new Router();
        $dispatcher = new Dispatcher($router, array(
            "requireViewFile" => !in_array($router->getAction(), array_keys(static::$actionsWithDataOnly)),
        ));

        static::$dispatcher = & $dispatcher;

        $action = $dispatcher->getAction();
        $controller = $dispatcher->getController();
        $lang = $dispatcher->getLang();
        $module = $dispatcher->getModule();
        $permission = $dispatcher->getPermission();
        $view = $dispatcher->getView();

        $info = array(
            "action" => $action,
            "controller" => $controller,
            "data" => $dispatcher->getData(),
            "extra" => $dispatcher->getExtra(),
            "lang" => $lang,
            "module" => $module,
            "view" => $view,
        );

        if ($permission::getPermission($action, Session::getPerm(), $info) === false) {
            $dispatcher->setError(403);
            $info["action"] = ($action = $dispatcher->getAction());
            $info["controller"] = ($controller = $dispatcher->getController());
            $info["module"] = ($module = $dispatcher->getModule());
            $info["view"] = ($view = $dispatcher->getView());
        }

        //TASK: przeniesc do __constructStatic, znalezc inne wywolania __init i to samo!
        $view::__init($action);

        $view::obStart();

        if (array_key_exists($action, static::$actionsWithDataOnly)) {
            header("Content-Type: " . static::$actionsWithDataOnly[$action]);

            //TODO: czy metody withDataOnly powinny otrzymywac argument $viewParams["info"] ?

            if (is_callable(array($controller, $action))) {
                echo $controller::$action();
            } elseif (is_callable(array($controller, "{$action}_"))) {
                $action2 = "{$action}_";
                echo $controller::$action2();
            }
        } else {
            if (Config::getOne("requireWww")) {
                Utils\Header::redirectIfNotWww();
            }

            $viewParams = array(
                "info" => $info,
            );

            // Troche magii:
            // Jesli mamy modul, w ktorym akcja nazywa sie tak samo jak modul - to PHP uzna akcje za konstruktor, lecz ten nie moze byc statyczny wiec bedzie blad PHP.
            // Wtedy definiujemy akcje jako z sufixem "_" (np. "AKCJA_"), a tu umozliwiamy jej wywolanie.
            // Przyklad: \App\Controller\Index::index
            if (is_callable(array($controller, $action))) {
                $viewParams["data"] = $controller::$action($viewParams["info"]);
            } elseif (is_callable(array($controller, "{$action}_"))) {
                $action2 = "{$action}_";
                $viewParams["data"] = $controller::$action2($viewParams["info"]);
            }

            $bodyContent = static::loadViewFile($view::getViewFile($lang, $module, $action), $viewParams);
            $headerContent = static::loadViewFile($view::getViewFile($lang, "common", "header"), $viewParams);

            $view::printSite([
                "bodyHeader" => $headerContent,
                "body" => $bodyContent,
                "info" => $info,
            ]);
        }

        $view::obFinish();
    }

}
