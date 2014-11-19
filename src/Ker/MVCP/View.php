<?php

namespace Ker\MVCP;

/**
 * Description of View
 *
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 * @copyright Copyright (c) Dariusz Rumiński <dariusz.ruminski@gmail.com>
 * @date 2013-08-19 22:35:01
 * @todo TASK: #50
 */
abstract class View
{

    protected static $charset;
    protected static $cssFile;
    protected static $description;
    protected static $favicon;
    protected static $IeForce9;
    protected static $imgPreloader;
    protected static $jsCode;
    protected static $jsFile;
    protected static $jsReadyCode;
    protected static $meta;
    protected static $minJsCode;
    protected static $obState;
    protected static $obWanted;
    protected static $staticContentPath;
    protected static $subtitle;
    protected static $title;
    protected static $titlePattern;
    protected static $xHtml;
    protected static $clearWhitespace;
    protected static $replaceEndingSpaceWithNbsp;
    protected static $removeNewLines;
    protected static $bodyHeader;

    /**
     * @see \Ker\Config::get (view_charset, view_cssFile, view_description, view_favicon, view_IeForce9, view_imgPreloader, view_jsCode, view_jsFile, view_jsReadyCode, view_meta, view_obWanted,
     * view_staticContentPath, view_subtitle, view_title, view_titlePattern, view_xHtml, view_clearWhitespace, view_replaceEndingSpaceWithNbsp)
     */
    public static function __init($_action = null)
    {
        static::$charset = \Ker\Config::getOne("view_charset", null);
        static::$cssFile = \Ker\Config::getOne("view_cssFile", array());
        static::$description = \Ker\Config::getOne("view_description", null);
        static::$favicon = \Ker\Config::getOne("view_favicon", null);
        static::$IeForce9 = \Ker\Config::getOne("view_IeForce9", null);
        static::$imgPreloader = \Ker\Config::getOne("view_imgPreloader", null);
        static::$jsCode = \Ker\Config::getOne("view_jsCode", null);
        static::$jsFile = \Ker\Config::getOne("view_jsFile", array());
        static::$jsReadyCode = \Ker\Config::getOne("view_jsReadyCode", null);
        static::$meta = \Ker\Config::getOne("view_meta", array());
        static::$obWanted = \Ker\Config::getOne("view_obWanted", false);
        static::$staticContentPath = \Ker\Config::getOne("view_staticContentPath", "/static/");
        static::$subtitle = \Ker\Config::getOne("view_subtitle", null);
        static::$title = \Ker\Config::getOne("view_title", "");
        static::$titlePattern = \Ker\Config::getOne("view_titlePattern", null);
        static::$xHtml = \Ker\Config::getOne("view_xHtml", null);
        static::$clearWhitespace = \Ker\Config::getOne("view_clearWhitespace", null);
        static::$replaceEndingSpaceWithNbsp = \Ker\Config::getOne("view_replaceEndingSpaceWithNbsp", null);
        static::$removeNewLines = \Ker\Config::getOne("view_removeNewLines", null);
        static::$minJsCode = \Ker\Config::getOne("view_minJsCode", null);
        static::$bodyHeader = null;

        //wymuszenie zawartosci, jesli byl BOOL to mamy STRING, jesli bylo co innego - wymuszamy stringa
        static::$xHtml = static::$xHtml ? " /" : "";
    }

    protected static function addMeta(/* argument list */)
    {
        if (!func_num_args()) {
            throw new \BadMethodCallException();
        }

        foreach (func_get_args() as $arg) {
            static::$meta[] = $arg;
        }
    }

    protected static function getStaticContent($_file)
    {
        if (in_array(substr($_file, 0, 7), array("http://", "https:/"))) {
            return $_file;
        }

        $path = static::$staticContentPath . $_file;
        // uzywamy Config::path gdyz Ker moze byc w innej lokalizacji niz aplikacja!
        return $path . "?" . \Ker\Utils\File::getModifyTimestamp(\Ker\Config::getOne("path") . $path);
    }

    protected static function getTitle()
    {
        if (static::$titlePattern === NULL or static::$subtitle === NULL or static::$subtitle === "") {
            return static::$title;
        }

        return str_replace(array("%title%", "%subtitle%"), array(static::$title, static::$subtitle), static::$titlePattern);
    }

    public static function getViewFile($_lang, $_module, $_action)
    {
        // jesli istnieje plik przewidziany dla danego jezyka
        $file = \Ker\Config::getOne("view", "./view") . "/$_module/$_action.$_lang.php";
        if (file_exists($file)) {
            return $file;
        }

        // w przeciwnym wypadku zwroc plik ogolny (bez uszczegolowionego jezyka)
        // funckja zaklada, ze jesli nie ma pliku dla jezyka to _musi_ byc plik ogolny
        return \Ker\Config::getOne("view", "./view") . "/$_module/$_action.php";
    }

    public static function obStart()
    {
        if (static::$obWanted) {
            static::$obState = \Ker\Utils\Misc::obStart();
        }
    }

    public static function obFlush()
    {
        \Ker\Utils\Misc::obFlush(static::$obState);
    }

    public static function obFinish()
    {
        \Ker\Utils\Misc::obFinish(static::$obState);
    }

    public static function preprocessHtml($_)
    {
        if (static::$removeNewLines) {
            $_ = \Ker\Utils\Text::removeNewLines($_, array("safeJsInline" => true));
        }

        if (static::$clearWhitespace) {
            // eksperymentalne, na rosyjskim '/\s+/' => ' ' sie wykladal, zamienial niektore litery na krzaki (np 'Русский')
            // zmieniono \s na \h - by nie laczyl wersow gdy koniec pierwszego i poczatek drugiego to spacje - jest to niewlasciwe np w textarea, gdzie stracilibysmy przejscie do nowego wersu
            $_ = preg_replace('/(\h)\h+/', '\\1', $_);
        }

        if (static::$replaceEndingSpaceWithNbsp) {
            $_ = \Ker\Utils\Text::replaceEndingSpaceWithNbsp($_);
        }

        return $_;
    }

    public static function printBody($_headerContent, $_bodyContent)
    {
        $bodyHeader = (static::$bodyHeader ? \Ker\Res::call("tpl_bodyHeader", static::$bodyHeader) : "");
        $messages = \Ker\Res::call("tpl_messages", \Ker\Messages::getAll());

        echo static::preprocessHtml(\Ker\Res::call("tpl_body", $_headerContent, $bodyHeader . $messages . $_bodyContent));
    }

    public static function printHead($_extraContent = "")
    {
        if (static::$imgPreloader) {
            echo "<script src='" . static::getStaticContent(static::$imgPreloader) . "' type='text/javascript'></script>";
            static::obFlush();
        }

        $ret = "<title>" . static::getTitle() . "</title>
<meta http-equiv='content-type' content='text/html; charset=" . static::$charset . "'" . static::$xHtml . ">";

        if (static::$IeForce9) {
            $ret .= "<meta http-equiv='X-UA-Compatible' content='IE=9'" . static::$xHtml . ">";
        }

        if (static::$favicon) {
            $ret .= "<link rel='shortcut icon' type='image/x-icon' href='" . static::$favicon . "'" . static::$xHtml . ">";
        }

        if (static::$cssFile) {
            foreach (static::$cssFile as & $item) {
                $tmp = ( is_array($item) ? "href='" . static::getStaticContent($item[0]) . "' media='{$item[1]}'" : "href='" . static::getStaticContent($item) . "'" );
                $ret .= "<link rel='stylesheet' type='text/css' $tmp" . static::$xHtml . ">";
            }
        }

        if (static::$jsFile) {
            foreach (static::$jsFile as & $item) {
                $ret .= "<script src='" . static::getStaticContent($item) . "' type='text/javascript'></script>";
            }
        }

        if (static::$jsCode or static::$jsReadyCode) {
            $jsCode = "";

            if (static::$jsCode) {
                foreach (static::$jsCode as & $item) {
                    $jsCode .= $item;
                }
            }

            if (static::$jsReadyCode) {
                $jsCode .= "$(document).ready(function () {";
                foreach (static::$jsReadyCode as & $item)
                    $jsCode .= $item;
                $jsCode .= "});";
            }

            if (static::$minJsCode) {
                $jsCode = \Ker\External\JSMin::minify($jsCode);
            }

            $jsCode = "<script type='text/javascript'>
//<![CDATA[
$jsCode
//]]>
</script>";

            $ret .= $jsCode;
        }

        if (static::$meta) {
            foreach (static::$meta as & $item) {
                $ret .= "<meta name='{$item[0]}' content='{$item[1]}'" . static::$xHtml . ">";
            }
        }

        if (static::$description) {
            $ret .= "<meta name='description' content='" . static::$description . "'" . static::$xHtml . ">";
        }

        $ret .= $_extraContent;

        echo static::preprocessHtml("<head>$ret</head>");
        static::obFlush();
    }

    public static function printSite(array $_ = [])
    {
        $info = (isset($_["info"]) ? $_["info"] : []);

        $htmlParams = [];
        foreach (["lang", "action", "module", ] as $v) {
            if (isset($info[$v])) {
                $htmlParams[$v] = $info[$v];
            }
        }

        echo \Ker\Res::call("tpl_doctype");
        echo \Ker\Res::call("tpl_html", $htmlParams);

        static::printHead((isset($_["head"]) ? $_["head"] : ""));
        static::printBody($_["bodyHeader"], $_["body"]);

        echo "</html>";
    }

}
