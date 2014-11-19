<?php

namespace Ker;

//TASK: zrobic aktywator integracji z Messages ! NIE ! zrobic by na output'ie generowana byla tablica [pole]=>blad, i ew. na niej odpalany callback, tu ew. zapisany statyczny callback domyslny operujacy na Messages

/**
 * Description of Form
 *
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 * @copyright Copyright (c) Dariusz Rumiński <dariusz.ruminski@gmail.com>
 * @date 2013-08-19 22:56:44
 * @todo TASK: #15, #26, #43, #44, #45, #56
 */
class Form
{

    private static $checks = null;
    private static $hooksPredefined = null;
    private $items;
    private $formId;
    private $innerIdPrefix;
    private $innerIdCounter;
    private $formAction;
    private $formClass;
    private $formMethod;
    private $formStyle;
    private $formTrigger;
    private $formWithFiles;
    private $hooks;
    private $ulClass;
    private $ulId;
    private $errorClass;
    private $isXhtml;
    private $xHtml;
    private $output;
    private $useMessages;

    public static function __constructStatic()
    {
        static $isInitialized = false;

        if ($isInitialized) {
            return;
        }

        $isInitialized = true;

        static::$checks = array(
            "isEmail" => array(
                "check" => function (& $_value, $_rule, FormType $_type) {
                    // jesli check jest ustawiony ale nieaktywny - wartosc zawsze jest poprawna wzgledem tego checka
                    if (!$_rule) {
                        return true;
                    }

                    return preg_match("/^[\\w-+]+(?:\\.[\\w-+]+)*@(?:[\\w-]+\\.)+[a-zA-Z]{2,7}$/", $_value);
                },
                "error" => function ($_rule, FormType $_type) {
                    return Res::call("validate_isEmail_error", $_rule);
                },
            ),
            "isInt" => array(
                "check" => function (& $_value, $_rule, FormType $_type) {
                    // jesli check jest ustawiony ale nieaktywny - wartosc zawsze jest poprawna wzgledem tego checka
                    if (!$_rule) {
                        return true;
                    }

                    return preg_match("/^[-+]?\\d+$/", $_value);
                },
                "error" => function ($_rule, FormType $_type) {
                    return Res::call("validate_isInt_error", $_rule);
                },
            ),
            "isFloat" => array(
                "check" => function (& $_value, $_rule, FormType $_type) {
                    // jesli check jest ustawiony ale nieaktywny - wartosc zawsze jest poprawna wzgledem tego checka
                    if (!$_rule) {
                        return true;
                    }

                    return preg_match("/^[-+]?\\d+(?:[.,]\d+)?$/", $_value);
                },
                "error" => function ($_rule, FormType $_type) {
                    return Res::call("validate_isFloat_error", $_rule);
                },
            ),
            "isHttpUrl" => array(
                "check" => function (& $_value, $_rule, FormType $_type) {
                    // jesli check jest ustawiony ale nieaktywny - wartosc zawsze jest poprawna wzgledem tego checka
                    if (!$_rule) {
                        return true;
                    }

                    return preg_match('/^((?:http|https)(?::\\/{2}[\\w]+)(?:[\\/|\\.]?)(?:[^\\s"]*))$/', $_value);
                },
                "error" => function ($_rule, FormType $_type) {
                    return Res::call("validate_isHttpUrl_error", $_rule);
                },
            ),
            "max" => array(
                "check" => function (& $_value, $_rule, FormType $_type) {
                    if ($_type->isStringType()) {
                        return ( strlen($_value) <= $_rule );
                    }

                    if ($_type->equals(FormType::checkbox)) {
                        return ( count($_value) <= $_rule );
                    }

                    if ($_type->equals(FormType::radio)) {
                        throw new \LogicException("Radio form could has only one value, max check is illogical.");
                    }

                    if ($_type->equals(FormType::select)) {
                        throw new \LogicException("Select form must has only one value, max check is illogical.");
                    }
                },
                "error" => function ($_rule, FormType $_type) {
                    if ($_type->isStringType()) {
                        return Res::call("validate_max_string_error", $_rule);
                    }

                    return Res::call("validate_max_selection_error", $_rule);
                }
            ),
            "maxNumber" => array(
                "check" => function (& $_value, $_rule, FormType $_type) {
                    return ($_value <= $_rule);
                },
                "error" => function ($_rule, FormType $_type) {
                    return Res::call("validate_maxNumber_error", $_rule);
                },
            ),
            "min" => array(
                "check" => function (& $_value, $_rule, FormType $_type) {
                    if ($_type->isStringType()) {
                        return ( strlen($_value) >= $_rule );
                    }

                    if ($_type->equals(FormType::checkbox)) {
                        return ( count($_value) >= $_rule );
                    }

                    if ($_type->equals(FormType::radio)) {
                        return isset($_value);
                    }

                    if ($_type->equals(FormType::select)) {
                        throw new \LogicException("Select form must has only one value, min check is illogical.");
                    }

                    if ($_type->equals(FormType::file)) {
                        return \Ker\Utils\File::fileWasSent($_value);
                    }
                },
                "error" => function ($_rule, FormType $_type) {
                    if ($_type->isStringType()) {
                        if ($_rule === 1) {
                            return Res::call("validate_empty_string_error");
                        }

                        return Res::call("validate_min_string_error", $_rule);
                    }

                    if ($_type->equals(FormType::radio)) {
                        return Res::call("validate_empty_radio_error");
                    }

                    // TASK: #149 - wyniesc do zasobow
                    if ($_type->equals(FormType::file)) {
                        return "Brak pliku!";
                    }

                    return Res::call("validate_min_selection_error", $_rule);
                },
            ),
            "minNumber" => array(
                "check" => function (& $_value, $_rule, FormType $_type) {
                    return ($_value >= $_rule);
                },
                "error" => function ($_rule, FormType $_type) {
                    return Res::call("validate_minNumber_error", $_rule);
                },
            ),
            "re" => array(
                "check" => function (& $_value, $_rule, FormType $_type) {
                    return preg_match($_rule, $_value);
                },
                "error" => function ($_rule, FormType $_type) {
                    return Res::call("validate_re_error");
                },
            ),
            "fileReceived" => array(
                "check" => function (& $_value, $_rule, FormType $_type) {
                    // jesli nie przeslano pliku - nie sprawdzamy
                    if (!\Ker\Utils\File::fileWasSent($_value)) {
                        return true;
                    }

                    return \Ker\Utils\File::fileWasReceived($_value);
                },
                // TASK: #149 - do zasobow
                "error" => function ($_rule, FormType $_type) {
                    return "Błąd przesyłania pliku!";
                },
            ),
            "fileTypes" => array(
                "check" => function (& $_value, $_rule, FormType $_type) {
                    // jesli nie przeslano pliku - nie sprawdzamy
                    if (!\Ker\Utils\File::fileWasSent($_value)) {
                        return true;
                    }

                    $types = explode(" ", $_rule);

                    return \Ker\Utils\File::typeIsAcceptable($_value, $types);
                },
                // TASK: #149 - do zasobow
                "error" => function ($_rule, FormType $_type) {
                    $types = explode(" ", $_rule);

                    return "Niewłaściwy format pliku! Możliwe formaty: " . implode(", ", $types);
                },
            ),
        );

        static::$hooksPredefined = array(
            "htmlCleaner" => function (& $_items) {
                foreach ($_items as & $item) {
                    if ($item["type"]->isTextType() && (!isset($item["allowHtml"]) || !$item["allowHtml"] )) {
                        $item ["value"] = strip_tags($item["value"]);
                    }
                }
            },
            "textTrim" => function (& $_items) {
                foreach ($_items as & $item) {
                    if ($item["type"]->isTextType()) {
                        $item["value"] = trim($item["value"]);
                    }
                }
            },
            "normalizeEmail" => function (& $_items) {
                foreach ($_items as & $item) {
                    if (isset($item["check"]) && isset($item["check"]["isEmail"]) && $item["check"]["isEmail"]) {
                        $item ["value"] = strtolower($item["value"]);
                    }
                }
            },
            "normalizeFloat" => function (& $_items) {
                foreach ($_items as & $item) {
                    if (isset($item["check"]) && isset($item["check"]["isFloat"]) && $item["check"]["isFloat"]) {
                        $item ["value"] = str_replace(",", ".", $item["value"]);
                    }
                }
            },
        );
    }

    public function __construct($_args)
    {
        static $prototype = array(
    "formAction" => "",
    "formClass" => NULL,
    //formMethod - trzeba recznie nadac!
    "formStyle" => NULL,
    //formTrigger - trzeba recznie nadac!
    "formWithFiles" => false,
    "formId" => NULL,
    //formSubmit - trzeba recznie nadac!
    "ulClass" => NULL,
    "ulId" => NULL,
    "errorClass" => NULL,
    "isXhtml" => true,
    //output - trzeba recznie nadac!
    "useMessages" => false,
        );

        if (!in_array($_args["formMethod"], array("post", "get"))) {
            throw new \InvalidArgumentException("Method must be 'post' or 'get'!");
        }

        $_args = array_merge($prototype, $_args);

        $this->formAction = $_args["formAction"];
        $this->formClass = $_args["formClass"];
        $this->formMethod = $_args["formMethod"];
        $this->formStyle = $_args["formStyle"];
        $this->formTrigger = $_args["formTrigger"];
        $this->formWithFiles = $_args["formWithFiles"];
        $this->formId = $_args["formId"];
        $this->innerIdPrefix = $this->formId . "_inner";
        $this->innerIdCounter = 0;
        $this->formSubmit = $_args["formSubmit"];
        $this->ulClass = $_args["ulClass"];
        $this->ulId = $_args["ulId"];
        $this->errorClass = $_args["errorClass"];
        $this->isXhtml = $_args["isXhtml"];
        $this->xHtml = ($this->isXhtml ? " /" : "");
        $this->output = $_args["output"];
        $this->useMessages = $_args["useMessages"];

        $this->hooks = array(
            "afterValidation" => array(),
            "beforeOutput" => array(),
            "beforeValidation" => array(),
        );
        $this->registerDefaultHooks();
    }

    private function getPassedValues()
    {
        return ($this->formMethod === "post" ? $_POST : $_GET);
    }

    private function normalizeChecks()
    {
        if (!$this->items) {
            return;
        }

        foreach ($this->items as & $item) {
            if (!isset($item["check"]) || empty($item["check"])) {
                continue;
            }

            foreach ($item["check"] as $checkName => & $check) {
                if ($checkName !== "fun" && !is_array($check)) {
                    $check = array($check, static::$checks[$checkName]["error"]);
                }
            }
        }
    }

    public function xHtml()
    {
        return $this->xHtml;
    }

    public function registerHook($_hook, $_time)
    {
        $this->hooks [$_time] [] = $_hook;
    }

    private function registerDefaultHooks()
    {
        $this->registerHook(static::$hooksPredefined["htmlCleaner"], "beforeValidation");
        $this->registerHook(static::$hooksPredefined["textTrim"], "beforeValidation");
        $this->registerHook(static::$hooksPredefined["normalizeEmail"], "beforeValidation");
        $this->registerHook(static::$hooksPredefined["normalizeFloat"], "beforeValidation");
    }

    //noDisplay
    public function run($_ = array())
    {
        $values = $this->getPassedValues();

        if (isset($values[$this->formTrigger])) {
            if ($this->validate($values)) {
                // hooki przed outputem
                foreach ($this->hooks["beforeOutput"] as & $hook) {
                    $hook($this->items);
                }

                //TASK: #22 - sprawdzic wydajnosc:
                //1. $this->output->__invoke ()
                //2. $callback = $this->output; $callback ()
                //3. call_user_func_array($this->output, )
                $retOutput = $this->output->__invoke($this);

                if (!empty($_["onSuccessReturnOutput"])) {
                    return $retOutput;
                }

                //TASK: #23 - przydala by sie mozliwosc by tutaj wyswietlac forma, inaczej czesto w funkcji output'a form jest wyswietlany
                return $this->displayForm(true);
            }

            if ($this->useMessages) {
                $communicat = "";
                foreach ($this->items as $key => & $item) {
                    if (empty($item["errors"])) {
                        continue;
                    }

                    $communicat .= "<div><label>{$item["label"]}</label><div>" .
                            implode("", array_map(function (& $_) {
                                                return "<p>{$_}</p>";
                                            }, $item["errors"]))
                            . "</div></div>";
                }

                Messages::add(MessageType::error(), $communicat, "form");
            }

            return $this->displayForm();
        } else {
            return $this->displayForm();
        }

        return NULL;
    }

    //@todo: readonly
    //@todo: disable
    //values - [opt], tylko dla in_array ($_type, array(FormType::select, FormType::radio, FormType::checkbox))
    //         [key=>value]
    //check: [opt]
    //  min / [min, error_descr] - tylko dla text, password, textarea, checkbox, radio (dla radio tylko === 1)
    //  max / [max, error_descr] - tylko dla text, password, textarea, checkbox
    //  [re, error_descr], tylko dla in_array ($_type, array(FormType::text , FormType::password , FormType::textarea))
    //  anonymous fun(...)
    //UWAGA ! dla select/radio/checkbox w kluczach values NIE podawac pustego stringa ! Bedzie on mylony z wartoscia (int) 0 !
    public function add(FormType $_type, $_options)
    {
        static $added = array();

        $item = FormType::computeItem($_type, $_options);

        if (array_key_exists("name", $item)) {
            if (isset($added[$item["name"]])) {
                throw new \InvalidArgumentException("Name '{$item["name"]}' already exists!");
            }
            $added[$item["name"]] = true;
        }

        $item ["type"] = $_type;

        $this->items[] = $item;
    }

    public function getField($_field)
    {
        foreach ($this->items as & $item) {
            if (isset($item["name"]) and $item["name"] === $_field) {
                return $item["value"];
            }
        }

        return NULL;
    }

    public function getFields()
    {
        $fields = array();

        foreach ($this->items as & $item) {
            if (isset($item["name"])) {
                $fields[$item["name"]] = $item["value"];
            }
        }

        return $fields;
    }

    public function setFields($_fields)
    {
        foreach ($this->items as & $item) {
            if (isset($item["name"]) and isset($_fields[$item["name"]])) {
                $item["value"] = $_fields[$item["name"]];
            }
        }
    }

    public function getNextInnerId()
    {
        ++$this->innerIdCounter;

        return $id = $this->innerIdPrefix . $this->innerIdCounter;
    }

    /**
     * @see Res::call(validate_empty_string_error, validate_min_selection_error, validate_min_string_error, validate_max_string_error)
     * @param  type $_values
     * @return bool informacja czy formularz zwalidowano poprawnie
     */
    public function validate(& $_values = null)
    {
        if ($_values === NULL) {
            $_values = $this->getPassedValues();
        }

        $this->normalizeChecks();

        if ($this->items) {
            // uzupelnianie $item["value"]
            foreach ($this->items as & $item) {
                if ($item["type"]->equals(FormType::html)) {
                    continue;
                }

                $item["value"] = (isset($_values[$item["name"]]) ? $_values[$item["name"]] : $item["default"]);

                // jesli $item to radio lub select to moze byc tylko jedno zaznaczenie, ktore musi byc dostepne w values
                if ($item["type"]->equals(FormType::radio) || $item["type"]->equals(FormType::select)) {
                    if (!in_array($item["value"], array_keys($item["values"]))) {
                        $item["value"] = $item["default"];
                    }
                }

                // jesli $item to checkbox moze byc jedno lub wiele zaznaczen, jednak kazde z nich musi byc dostepne w values
                elseif ($item["type"]->equals(FormType::checkbox)) {
                    if (is_array($item["value"])) {
                        $item["value"] = array_intersect($item["value"], array_keys($item["values"]));
                        if (!$item["value"]) {
                            $item["value"] = $item["default"];
                        }
                    } else {
                        if (!in_array($item["value"], array_keys($item["values"]))) {
                            $item["value"] = $item["default"];
                        }
                    }
                }

                // jesli $item to plik
                elseif ($item["type"]->equals(FormType::file)) {
                    $item["value"] = & $_FILES[$item["name"]];
                }
            }

            // hooki przed walidacja
            foreach ($this->hooks["beforeValidation"] as & $hook) {
                $hook($this->items);
            }

            //checki
            foreach ($this->items as & $item) {
                // jesli nie ma checkow - nie dokonujemy sprawdzen z check'ow
                if (!isset($item["check"])) {
                    continue;
                }

                $check = & $item["check"];
                $value = & $item["value"];

                if (!empty($check)) {
                    foreach ($check as $k => $v) {
                        // checki funkcyjne bedziemy sprawdzac pozniej
                        if ($k === "fun") {
                            continue;
                        }

                        // niezdefiniowany check!
                        if (!isset(static::$checks[$k])) {
                            continue;
                        }

                        // cache'ujemy funkcyjke bo sie wyklada PHP bez tego :(
                        $check_callback = static::$checks[$k]["check"];
                        if (!$check_callback($value, $v[0], $item["type"])) {
                            $item["errors"][] = $v[1]($v[0], $item["type"]);
                        }
                    }
                }

                if (isset($check ["fun"])) {
                    $funResult = $check["fun"]($value, $this->items);
                    if ($funResult) {
                        if (is_array($funResult)) {
                            $item["errors"] = (isset($item["errors"]) ? array_merge($item["errors"], $funResult) : $funResult);
                        } else {
                            $item["errors"][] = $funResult;
                        }
                    }
                }
            }

            // hooki po walidacji
            foreach ($this->hooks["afterValidation"] as & $hook) {
                $hook($this->items);
            }

            // jesli byly jakies bledy - zwroc false
            foreach ($this->items as & $item) {
                if (!empty($item["errors"])) {
                    return false;
                }
            }
        }

        return true;
    }

    public function displayForm($_showOnly = false)
    {
        $disabled = " disabled='disabled'";
        $readonly = " readonly='readonly'";

        static $displayByType = null;
        if (!$displayByType) {
            $displayByType = array(
                FormType::html => function (& $item) {
                    return $item["value"];
                },
                FormType::text => function ($_showOnly, & $item, & $_obj) {
                    if ($_showOnly) {
                        return "<div"
                                . ($item["itemId"] ? " id='{$item["itemId"]}'" : "")
                                . ($item["itemClass"] ? " class='{$item["itemClass"]}'" : "")
                                . ($item["itemStyle"] ? " style='{$item["itemStyle"]}'" : "")
                                . ">" . (isset($item["value"]) ? $item["value"] : "")
                                . "</div>";
                    }

                    return "<input type='text' name='{$item["name"]}'"
                            . ($item["itemId"] ? " id='{$item["itemId"]}'" : "")
                            . ($item["itemClass"] ? " class='{$item["itemClass"]}'" : "")
                            . ($item["itemStyle"] ? " style='{$item["itemStyle"]}'" : "")
                            //TODO: size jest poza prototypem - pytanie czy chcemy go do niego wcielac...
                            //. (isset ( $item[ "size" ] ) ? " size='{$item[ "size" ]}'" : "")
                            . (isset($item["check"]["max"]) ? " maxlength='{$item["check"]["max"][0]}'" : "")
                            . (isset($item["value"]) ? " value='{$item["value"]}'" : "")
                            //TODO: jak wyzej:
                            //. (isset ( $item[ "readonly" ] ) ? " readonly='readonly'" : "")
                            . "{$_obj->xHtml()}>";
                },
                //TASK: #19 (showonly)
                FormType::password => function ($_showOnly, & $item, & $_obj) {
                    if ($_showOnly) {
                        return "<div"
                                . ($item["itemId"] ? " id='{$item["itemId"]}'" : "")
                                . ($item["itemClass"] ? " class='{$item["itemClass"]}'" : "")
                                . ($item["itemStyle"] ? " style='{$item["itemStyle"]}'" : "")
                                . "> *****"
                                . "</div>";
                    }

                    return "<input type='password' name='{$item["name"]}'"
                            . ($item["itemId"] ? " id='{$item["itemId"]}'" : "")
                            . ($item["itemClass"] ? " class='{$item["itemClass"]}'" : "")
                            . ($item["itemStyle"] ? " style='{$item["itemStyle"]}'" : "")
                            . (isset($item["size"]) ? " size='{$item["size"]}'" : "")
                            . (isset($item["check"]["max"]) ? " maxlength='{$item["check"]["max"]}'" : "")
                            . (isset($item["value"]) ? " value='{$item["value"]}'" : "")
                            . "{$_obj->xHtml()}>";
                },
                FormType::hidden => function ($_showOnly, & $item, & $_obj) {
                    if ($_showOnly) {
                        return "";
                    }

                    return "<input type='hidden' name='{$item["name"]}'"
                            . ($item["itemId"] ? " id='{$item["itemId"]}'" : "")
                            . ($item["itemClass"] ? " class='{$item["itemClass"]}'" : "")
                            . ($item["itemStyle"] ? " style='{$item["itemStyle"]}'" : "")
                            . (isset($item["check"]["max"]) ? " maxlength='{$item["check"]["max"]}'" : "")
                            . (isset($item["value"]) ? " value='{$item["value"]}'" : "")
                            . "{$_obj->xHtml()}>";
                },
                FormType::textarea => function ($_showOnly, & $item) {
                    if ($_showOnly) {
                        return "<div"
                                . ($item["itemId"] ? " id='{$item["itemId"]}'" : "")
                                . ($item["itemClass"] ? " class='{$item["itemClass"]}'" : "")
                                . ($item["itemStyle"] ? " style='{$item["itemStyle"]}'" : "")
                                . ">"
                                . (isset($item["value"]) ? nl2br($item["value"]) : "")
                                . "</div>";
                    }

                    return "<textarea name='{$item["name"]}'"
                            . ($item["itemId"] ? " id='{$item["itemId"]}'" : "")
                            . ($item["itemClass"] ? " class='{$item["itemClass"]}'" : "")
                            . ($item["itemStyle"] ? " style='{$item["itemStyle"]}'" : "")
                            . ($item["rows"] ? " rows='{$item["rows"]}'" : "")
                            . ($item["cols"] ? " cols='{$item["cols"]}'" : "")
                            . ">"
                            . (isset($item["value"]) ? $item["value"] : "")
                            . "</textarea>";
                },
                FormType::select => function ($_showOnly, & $item) {
                    if ($_showOnly) {
                        return "<div"
                                . ($item["itemId"] ? " id='{$item["itemId"]}'" : "")
                                . ($item["itemClass"] ? " class='{$item["itemClass"]}'" : "")
                                . ($item["itemStyle"] ? " style='{$item["itemStyle"]}'" : "")
                                . ">"
                                . (isset($item["value"]) ? $item["values"][$item["value"]] : "")
                                . "</div>";
                    }

                    $options = "";
                    if ($item["values"]) {
                        foreach ($item["values"] as $k => $v) {
                            $options .= "<option value='$k'"
                                    . (($item["value"] == $k) ? " selected='selected'" : "")
                                    . ">$v</option>";
                        }
                    }

                    return "<select name='{$item["name"]}'"
                            . ($item["itemId"] ? " id='{$item["itemId"]}'" : "")
                            . ($item["itemClass"] ? " class='{$item["itemClass"]}'" : "")
                            . ($item["itemStyle"] ? " style='{$item["itemStyle"]}'" : "")
                            . ($_showOnly ? $disabled : "")
                            . ">"
                            . $options
                            . "</select>";
                },
                FormType::radio => function ($_showOnly, & $item, & $_obj) use ($disabled) {
                    $ret = "";

                    if ($item["values"]) {
                        foreach ($item["values"] as $k => $v) {
                            $id = $_obj->getNextInnerId();
                            $ret .= "<div class='selectionContainer'><input type='radio' name='{$item["name"]}' value='$k'"
                                    . ((isset($item["value"]) && $item["value"] == $k) ? " checked='checked'" : "")
                                    . " id='$id'"
                                    . ($_showOnly ? $disabled : "")
                                    . "{$_obj->xHtml()}><label for='$id'>$v</label></div>";
                        }
                    }

                    return "<div"
                            . ($item["itemId"] ? " id='{$item["itemId"]}'" : "")
                            . ($item["itemClass"] ? " class='{$item["itemClass"]}'" : "")
                            . ($item["itemStyle"] ? " style='{$item["itemStyle"]}'" : "")
                            . ">$ret</div>";
                },
                FormType::checkbox => function ($_showOnly, & $item, & $_obj) use ($disabled) {
                    $ret = "";

                    if ($item["values"]) {
                        $multiple = ( count($item["values"]) > 1 ? "[]" : "" );
                        foreach ($item["values"] as $k => $v) {
                            $id = $_obj->getNextInnerId();
                            $ret .= "<div class='selectionContainer'><input type='checkbox' name='{$item["name"]}$multiple' value='$k'"
                                    . ((isset($item["value"]) && (is_array($item["value"]) ? in_array($k, $item["value"]) : $item["value"] == $k ) ) ? " checked='checked'" : "")
                                    . " id='$id'"
                                    . ($_showOnly ? $disabled : "")
                                    . "{$_obj->xHtml()}><label for='$id'>$v</label></div>";
                        }
                    }

                    return "<div"
                            . ($item["itemId"] ? " id='{$item["itemId"]}'" : "")
                            . ($item["itemClass"] ? " class='{$item["itemClass"]}'" : "")
                            . ($item["itemStyle"] ? " style='{$item["itemStyle"]}'" : "")
                            . ">$ret</div>";
                },
                FormType::file => function ($_showOnly, & $item, & $_obj) use ($disabled) {
                    if ($_showOnly) {
                        return "<div"
                                . ($item["itemId"] ? " id='{$item["itemId"]}'" : "")
                                . ($item["itemClass"] ? " class='{$item["itemClass"]}'" : "")
                                . ($item["itemStyle"] ? " style='{$item["itemStyle"]}'" : "")
                                . ">"
                                . (isset($item["value"]) ? $item["value"]["name"] : "")
                                . "</div>";
                    }

                    return "<input type='file' name='{$item["name"]}'"
                            . ($item["itemId"] ? " id='{$item["itemId"]}'" : "")
                            . ($item["itemClass"] ? " class='{$item["itemClass"]}'" : "")
                            . ($item["itemStyle"] ? " style='{$item["itemStyle"]}'" : "")
                            //TODO: size jest poza prototypem - pytanie czy chcemy go do niego wcielac...
                            //. (isset ( $item[ "size" ] ) ? " size='{$item[ "size" ]}'" : "")
                            //TODO: jak wyzej:
                            //. (isset ( $item[ "readonly" ] ) ? " readonly='readonly'" : "")
                            . ($_showOnly ? $disabled : "")
                            . "{$_obj->xHtml()}>";
                },
            );
        }

        $ret = "";

        if ($this->items) {
            foreach ($this->items as & $item) {
                if (!isset($item["value"]) and isset($item["default"])) {
                    $item["value"] = $item["default"];
                }

                $ret .= "<li>";

                if ($item["type"]->equals(FormType::html)) {
                    if (isset($item["label"])) {
                        $ret .= "<label" . ($item["itemId"] ? " for='{$item["itemId"]}'" : "") . ($item["labelId"] ? " id='{$item["labelId"]}'" : "") . ($item["labelClass"] ? " class='{$item["labelClass"]}'" : "") . ($item["labelStyle"] ? " style='{$item["labelStyle"]}'" : "") . ">{$item["label"]}</label>"
                                . $displayByType[$item["type"]->__toString()]($item)
                                . (isset($item["errors"]) ? "<label" . ($this->errorClass ? " class='{$this->errorClass}'" : "") . ">" . implode(" ", $item["errors"]) . "</label>" : "");
                    } else {
                        $ret .= $displayByType[$item["type"]->__toString()]($item);
                    }
                } elseif ($item["type"]->equals(FormType::hidden)) {
                    $ret .= $displayByType[$item["type"]->__toString()]($_showOnly, $item, $this);
                } else {
                    $ret .= "<label" . ($item["itemId"] ? " for='{$item["itemId"]}'" : "") . ($item["labelId"] ? " id='{$item["labelId"]}'" : "") . ($item["labelClass"] ? " class='{$item["labelClass"]}'" : "") . ($item["labelStyle"] ? " style='{$item["labelStyle"]}'" : "") . ">{$item["label"]}</label>"
                            . $displayByType[$item["type"]->__toString()]($_showOnly, $item, $this)
                            . ($item["errors"] ? "<label" . ($this->errorClass ? " class='{$this->errorClass}'" : "") . ">" . implode(" ", $item["errors"]) . "</label>" : "");
                }

                $ret .= "</li>";
            }
        }

        return "<form"
                . " onclick=''" //iPad label click fix
                . ($this->formId ? " id='{$this->formId}'" : "")
                . ($this->formClass ? " class='{$this->formClass}'" : "")
                . ($this->formStyle ? " style='{$this->formStyle}'" : "")
                . ($this->formWithFiles ? " enctype='multipart/form-data'" : "")
                . ($_showOnly ? " action=''>" : " action='{$this->formAction}' method='{$this->formMethod}'>")
                . "<div>"
                . "<ul" . ($this->ulId ? " id='{$this->ulId}'" : "") . ($this->ulClass ? " class='{$this->ulClass}'" : "") . ">$ret</ul>"
                . ($_showOnly ? "" : $this->formSubmit)
                . "</div>"
                . "</form>";
    }

}

Form::__constructStatic();
