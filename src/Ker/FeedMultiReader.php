<?php

//TASK: #53 - dokumentacja

namespace Ker;

class FeedMultiReader
{

    protected $feed;

    public function addFeed($_)
    {
        $this->feed[$_["id"]] = array(
            "feed" => $_["feed"],
            "mode" => $_["mode"],
            "limit" => (isset($_["limit"]) ? $_["limit"] : null),
        );

        if (isset($_["authUser"]) and isset($_["authPass"])) {
            $this->feed[$_["id"]]["authUser"] = $_["authUser"];
            $this->feed[$_["id"]]["authPass"] = $_["authPass"];
        }
    }

    public function readSingleFeed($_id, $_feed)
    {
        $container = array();
        $i = 0; //licznik jednorazowy dla kanalu

        if ($_feed["mode"] === "RSS2.0") {
            $xml = simplexml_load_file($_feed["feed"]);
            foreach ($xml->channel->item as $item) {
                $container[] = array(
                    "date" => (string) (new DateFull((string) $item->pubDate)),
                    "title" => (string) $item->title,
                    "href" => (string) $item->link,
                    "id" => $_id,
                );

                if ($_feed["limit"]) {
                    ++$i;
                    if ($i >= $_feed["limit"]) {
                        break;
                    }
                }
            }
        } elseif ($_feed["mode"] === "Atom1.0") {
            $xml = (isset($_feed["authUser"])
                ? file_get_contents(
                    $_feed["feed"],
                    false,
                    stream_context_create(
                        array(
                            "http" => array(
                                "header" => "Authorization: Basic " . base64_encode("{$_feed["authUser"]}:{$_feed["authPass"]}")
                            )
                        )
                    )
                )
                : file_get_contents($_feed["feed"])
            );
            $items = null;
            preg_match_all('/(<entry[^>]*>.*<\/entry>)/sUi', $xml, $items);

            foreach ($items[1] as & $itemXML) {
//TASK: #54 - wsparcie Atom'u
                $item = array();
            }
        }

        return $container;
    }

    public function read($_limit = null, $callback = null)
    {
        if (!$this->feed) {
            return NULL;
        }

        $container = array();

        foreach ($this->feed as $id => $feed) {
            if ($_limit and !isset($feed["limit"])) {
                $feed["limit"] = 5;
            }

            $container = array_merge($container, $this->readSingleFeed($id, $feed));
        }

        if ($callback) {
            foreach ($container as $key => & $item) {
                $item = $callback($item);
                if ($item === NULL) {
                    unset($container[$key]);
                }
            }
        }

        usort($container, function ($_a, $_b) {
                    return ( strtotime($_a["date"]) < strtotime($_b["date"]));
                });

        if ($_limit) {
            return array_slice($container, 0, $_limit);
        }

        return $container;
    }

}
