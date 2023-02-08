<?php
/*
 * @ https://kino-xa.ru
 Магазин раскодированных модулей. Скоро будет работать
 */

if (!defined("DATALIFEENGINE")) {
    header("HTTP/1.1 403 Forbidden");
    header("Location: ../../");
    exit("Hacking attempt!");
}
ini_set("memory_limit", "256M");
ini_set("max_execution_time", 200);
ignore_user_abort(true);
set_time_limit(200);
session_write_close();
require_once DLEPlugins::Check(ENGINE_DIR . "/modules/collaps.func.php");
if (!$config["allow_cache"]) {
    $config["allow_cache"] = 1;
}
$apikey = "4f457e870e91b76e02292d52a46fc445";
$config_mod = unserialize(file_get_contents(ENGINE_DIR . "/data/collaps.config"));
if (!$config_mod) {
    $config_mod = array();
}

$hostName = strtolower(substr(getenv("HTTP_HOST"), 0, 4)) == "www." ? substr(getenv("HTTP_HOST"), 4) : getenv("HTTP_HOST");

if (isset($_GET["type"])) {
    $url = "http://api.themoviedb.org/3/movie/".intval($_POST["kp_id"]."?api_key=" . $apikey);
    $ex_time = time() - 2 * 24 * 3600;
    $db->query("UPDATE " . PREFIX . "_collaps SET status=1, err_time='0' WHERE (err_time>'0' AND err_time<'" . $ex_time . "')");
    if ($config_mod["add_film"] && $_GET["type"] == "film") {
        $url = request("http://api.themoviedb.org/3/movie/".intval($_POST["kp_id"]."?api_key={$apikey}"));
    } else {
        if ($config_mod["add_serial"] && $_GET["type"] == "serial") {
            $url = "http://api.themoviedb.org/3/tv/".intval($_POST["kp_id"]."?api_key=" . $apikey);
           } else {
            if ($config_mod["add_anime"] && $_GET["type"] == "anime") {
                $url = "http://api.themoviedb.org/3/movie/".intval($_POST["kp_id"]."?api_key=" . $apikey);
            } else {
                if ($config_mod["add_mult"] && $_GET["type"] == "mult") {
                    $url = "http://api.themoviedb.org/3/movie/".intval($_POST["kp_id"]."?api_key=" . $apikey);
                 } else {
                    if ($config_mod["add_multserial"] && $_GET["type"] == "multserial") {
                        $url = "http://api.themoviedb.org/3/movie/".intval($_POST["kp_id"]."?api_key=" . $apikey);
                       } else {
                        if ($config_mod["add_animeserial"] && $_GET["type"] == "animeserial") {
                            $url = "http://api.themoviedb.org/3/movie/".intval($_POST["kp_id"]."?api_key=" . $apikey);
                        }
                    }
                }
            }
        }
    }
  
    if (!$url) {
        exit("choise video type. film serial mult or anime");
    }
    $res = request($url);
    $res = json_decode($res, true);
    if (!empty($res["message"])) {
        exit($res["message"]);
    }
    foreach ($res["results"] as $k => $value) {
        $kp_id = intval($value["kinopoisk_id"]);
        $year = intval($value["year"]);
        $type = $value["type"];
        $quality = addslashes($value["quality"]);
        $serial_status = addslashes($value["serial_status"]);
        $season = 0;
        $episode = 0;
        if ($kp_id < 10) {
            continue;
        }
        if ($type == "series" || $type == "anime-series" || $type == "cartoon-series") {
            $seasons = array();
            foreach ($value["seasons"] as $s) {
                $seasons[] = $s["season"];
            }
            rsort($seasons);
            $season = $seasons[0];
            $episodes = array();
            $arr = 1 < count($seasons) ? $seasons[0] - 1 : 0;
            foreach ($value["seasons"][$arr]["episodes"] as $e) {
                if ($e["iframe_url"] == "") {
                    continue;
                }
                $episodes[] = $e["episode"];
            }
            rsort($episodes);
            $episode = explode("-", $episodes[0]);
            rsort($episode);
            $episode = $episode[0];
        }
        $row = $db->super_query("SELECT * FROM " . PREFIX . "_collaps WHERE kp_id='" . $kp_id . "'");
        if ($row) {
            if ($row["quality"] != $quality || $row["season"] < $season || $row["episode"] < $episode || $row["serial_status"] != $serial_status) {
                $db->query("UPDATE " . PREFIX . "_collaps SET status='1', quality='" . $quality . "', episode='" . $episode . "', serial_status='" . $serial_status . "', season='" . $season . "' WHERE kp_id='" . $kp_id . "'");
            }
        } else {
            $db->query("INSERT INTO " . PREFIX . "_collaps (kp_id, year, quality, type, episode, serial_status, season) VALUES('" . $kp_id . "', '" . $year . "', '" . $quality . "', '" . $type . "', '" . $episode . "', '" . $serial_status . "', '" . $season . "')");
        }
    }
    exit("Insert and update data");
} else {
    $and = array();
    if ($config_mod["add_film"]) {
        $and[] = "'film'";
    }
    if ($config_mod["add_serial"]) {
        $and[] = "'series'";
    }
    if ($config_mod["add_anime"]) {
        $and[] = "'anime-film'";
    }
    if ($config_mod["add_mult"]) {
        $and[] = "'cartoon'";
    }
    if ($config_mod["add_multserial"]) {
        $and[] = "'cartoon-series'";
    }
    if ($config_mod["add_animeserial"]) {
        $and[] = "'anime-series'";
    }
    if (count($and)) {
        $and = "AND type IN(" . implode(", ", $and) . ")";
    }
    if ($config_mod["first_new"]) {
        $collaps = $db->super_query("SELECT * FROM " . PREFIX . "_collaps WHERE status>0 " . $and . " ORDER BY `status` ASC, `year` DESC");
    } else {
        $collaps = $db->super_query("SELECT * FROM " . PREFIX . "_collaps WHERE status>0 " . $and . " ORDER BY `status` ASC");
    }
    $post_row = $db->super_query("SELECT id, title, xfields FROM " . PREFIX . "_post WHERE xfields LIKE '%kinopoisk_id|" . $collaps["kp_id"] . "%'");
    if ($post_row) {
        if ($post_row && $collaps["status"] == 1) {
            $collaps["news_id"] = $post_row["id"];
            $xfdata = xfieldsdataload($post_row["xfields"]);
        } else {
            $db->query("UPDATE " . PREFIX . "_collaps SET status=0, news_id='" . $post_row["id"] . "' WHERE kp_id='" . $collaps["kp_id"] . "'");
            exit("news exists --- " . $post_row["id"] . " --- " . $post_row["title"]);
        }
    } else {
        $collaps["news_id"] = 0;
    }
    if ($config_mod["blacklist"]) {
        $blacklist = explode("\n", $config_mod["blacklist"]);
        if (in_array($collaps["kp_id"], $blacklist)) {
            $db->query("UPDATE " . PREFIX . "_collaps SET status=0, err_time='" . $_TIME . "' WHERE kp_id='" . $collaps["kp_id"] . "'");
            exit("in blacklist --- kinopoisk_id --- " . $collaps["kp_id"]);
        }
    }
    $res = request("http://api.themoviedb.org/3/movie/".intval($_POST["kp_id"]."?api_key=" . $apikey);
    $res = json_decode($res, true);
    print_r($res);
    if (!empty($res["message"])) {
        $db->query("UPDATE " . PREFIX . "_collaps SET status=0, err_time='" . $_TIME . "' WHERE kp_id='" . $collaps["kp_id"] . "'");
    }
    if (!empty($res["message"])) {
        exit($res["message"] . " --- kinopoisk_id --- " . $collaps["kp_id"]);
    }
    if (!allow_country((array) $res["country"], (array) $config_mod["allow_country"])) {
        $db->query("UPDATE " . PREFIX . "_collaps SET status=0, news_id='" . $post_row["id"] . "' WHERE kp_id='" . $collaps["kp_id"] . "'");
        exit("not allowed countryes");
    }
    if ($config_mod["allow_year"] && !in_array(intval($res["year"]), $config_mod["allow_year"])) {
        $db->query("UPDATE " . PREFIX . "_collaps SET status=0, news_id='" . $post_row["id"] . "' WHERE kp_id='" . $collaps["kp_id"] . "'");
        exit("not allowed year");
    }
    if (!$config_mod["enable_ads"] && $res["ads"] == "1") {
        $db->query("UPDATE " . PREFIX . "_collaps SET status=0, news_id='" . $post_row["id"] . "' WHERE kp_id='" . $collaps["kp_id"] . "'");
        exit("not allowed. ads");
    }
    if (($premier = strtotime($res["premier"])) !== false && $res["premier"]) {
        $res["premier"] = date("j", $premier) . " " . $langdate[date("F", $premier)] . " " . date("Y", $premier);
    }
    if (($premier_rus = strtotime($res["premier_rus"])) !== false && $res["premier_rus"]) {
        $res["premier_rus"] = date("j", $premier_rus) . " " . $langdate[date("F", $premier_rus)] . " " . date("Y", $premier_rus);
    }
    $request = array();
    $genres = $res["genres"];
    $ex_genres = array();
    if ($res["type"] == "series") {
        $ex_genres[] = "сериал";
        $request["video_type"] = "сериал";
    } else {
        if ($res["type"] == "film") {
            $ex_genres[] = "фильм";
            $request["video_type"] = "фильм";
        } else {
            if ($res["type"] == "anime-film") {
                $ex_genres[] = "аниме";
                $request["video_type"] = "аниме";
            } else {
                if ($res["type"] == "cartoon") {
                    $ex_genres[] = "мультфильм";
                    $request["video_type"] = "мультфильм";
                } else {
                    if ($res["type"] == "cartoon-series") {
                        $ex_genres[] = "мультсериал";
                        $request["video_type"] = "мультсериал";
                    } else {
                        if ($res["type"] == "anime-series") {
                            $ex_genres[] = "аниме сериал";
                            $request["video_type"] = "аниме сериал";
                        }
                    }
                }
            }
        }
    }
    $cats = array();
    $genres = fixGenres((array) $res["genre"]);
    $inter = array_merge($ex_genres, $genres, (array) $res["production_countries"]["name"], array($res["release_date"]));
    foreach ($config_mod["category"] as $cat_id => $values) {
        $f = true;
        foreach ($values as $value) {
            if (!in_array($value, $inter)) {
                $f = false;
                break;
            }
        }
        if ($f) {
            $cats[] = $cat_id;
        }
    }
    $config_mod["category"] = implode(",", $cats);
    if (!$config_mod["category"]) {
        $db->query("UPDATE " . PREFIX . "_collaps SET status=0, news_id='" . $post_row["id"] . "' WHERE kp_id='" . $collaps["kp_id"] . "'");
        exit("not allowed category");
    }
    $request["title_ru"] = $res["title"];
    $request["title_en"] = !empty($res["original_title"]) ? $res["original_title"] : "";
    $request["year"] = $res["year"];
    $request["description"] = html_entity_decode($res["description"]);
    $request["countries"] = implode(", ", $res["country"]);
    $request["genres"] = implode(", ", $res["genre"]);
    $request["actors"] = implode(", ", $res["actors"]);
    $request["actors_dubl"] = implode(", ", $res["actors_dubl"]);
    $request["directors"] = implode(", ", $res["director"]);
    $request["collection"] = implode(", ", $res["collection"]);
    $request["parts"] = implode(", ", $res["parts"]);
    $request["iframe_url"] = $res["iframe_url"];
    $request["quality"] = $res["quality"];
    $request["budget"] = $res["budget"];
    $request["slogan"] = $res["slogan"];
    $request["trivia"] = $res["Знаете ли вы…"];
    $request["fees_rus"] = $res["fees_rus"];
    $request["fees_use"] = $res["fees_use"];
    $request["fees_world"] = $res["fees_world"];
    $request["design"] = $res["design"];
    $request["editor"] = $res["editor"];
    $request["operator"] = $res["operator"];
    $request["producer"] = $res["producer"];
    $request["id"] = $res["id"];
    $request["screenwriter"] = $res["screenwriter"];
    $request["translator"] = !empty($res["voiceActing"]) ? implode(", ", $res["voiceActing"]) : "";
    $request["premiere_ru"] = $res["premier_rus"];
    $request["premiere_world"] = $res["premier"];
    $request["rating_kp"] = $res["kinopoisk"];
    $request["rating_imdb"] = $res["imdb"];
    $request["rating_world_art"] = $res["world_art"];
    $request["rate_mpaa"] = $res["rate_mpaa"];
    $request["kinopoisk_id"] = $res["kinopoisk_id"];
    $request["imdb_id"] = $res["imdb_id"];
    $request["world_art_id"] = $res["world_art_id"];
    $request["age"] = $res["age"];
    $request["time"] = $res["time"];
    $request["trailer"] = count($res["trailers"]) ? $res["trailers"][0]["iframe_url"] : "";
    $request["instream_ads"] = $res["ads"] == "" ? "" : 1;
    if ($request["title_en"] == $request["title_ru"]) {
        $request["title_en"] = "";
    }
    $is_serial = false;
    if (in_array($res["type"], array("series", "anime-series", "cartoon-series"))) {
        $seasons = array();
        foreach ($res["seasons"] as $s) {
            $seasons[] = $s["season"];
        }
        rsort($seasons);
        $season = $seasons[0];
        $episodes = array();
        $arr = 1 < count($seasons) ? $seasons[0] - 1 : 0;
        foreach ($res["seasons"][$arr]["episodes"] as $e) {
            if ($e["iframe_url"] == "") {
                continue;
            }
            $episodes[] = $e["episode"];
        }
        rsort($episodes);
        $episode = explode("-", $episodes[0]);
        rsort($episode);
        $episode = $episode[0];
        $request["last_season"] = 0 < intval($season) ? intval($season) : "";
        $request["last_episode"] = 0 < intval($episode) ? intval($episode) : "";
        $is_serial = true;
    }
    if ($is_serial) {
        $request["serial_status"] = $serial_statuses[$collaps["serial_status"]];
    }
    if (!$collaps["news_id"]) {
        if ($config_mod["upload_poster"]) {
            $poster_file = $collaps["kp_id"] . "_" . time();
            $poster = request($res["poster"], ROOT_DIR . "/uploads/posts/" . FOLDER_PREFIX . "/" . $poster_file);
            if ($poster) {
                $request["poster"] = str_replace(ROOT_DIR . "/", $config["http_home_url"], $poster);
                $poster = str_replace(ROOT_DIR . "/uploads/posts/", "", $poster);
            }
        } else {
            $request["poster"] = $res["poster"];
        }
    } else {
        unset($xfdata["iframe_url"]);
        unset($xfdata["quality"]);
        unset($xfdata["last_episode"]);
        unset($xfdata["last_season"]);
        unset($xfdata["translator"]);
        $xfdata["translator"] = $request["translator"];
        $xfdata["quality"] = $request["quality"];
        $xfdata["iframe_url"] = $request["iframe_url"];
        if ($is_serial) {
            $xfdata["last_season"] = $request["last_season"];
            $xfdata["last_episode"] = $request["last_episode"];
        }
    }
    $compile = template($config_mod, $request);
    if ($compile["category"] == "") {
        $compile["category"] = 0;
    }
    $compile["xfields"] = !$collaps["news_id"] ? xfcompile($compile["xfields"]) : xfcompile($xfdata);
    $compile["alt_name"] = totranslit(stripslashes($compile["alt_name"]), true, false);
    $compile["date"] = date("Y-m-d H:i:s");
    if (empty($compile["short_story"])) {
        $compile["short_story"] = $compile["title"];
        $compile["full_story"] = $compile["title"];
        $compile["descr"] = $compile["title"];
    }
    $approve = !empty($config_mod["go_moder"]) && (bool) $config_mod["go_moder"] === true ? 0 : 1;
    if (!empty($config_mod["go_moder_empty_descr"]) && (bool) $config_mod["go_moder_empty_descr"] === true && !trim($request["description"])) {
        $approve = 0;
    }
    if (!empty($config_mod["go_moder_empty_poster"]) && (bool) $config_mod["go_moder_empty_poster"] === true && !$poster) {
        $approve = 0;
    }
    $disable_index = 0;
    if (!empty($config_mod["disable_index"]) && (bool) $config_mod["disable_index"] === true) {
        $disable_index = 1;
    }
    $updateNewsDate = false;
    if (!empty($config_mod["update_news_date"]) && (bool) $config_mod["update_news_date"] === true) {
        $updateNewsDate = true;
    }
    if (!$collaps["news_id"]) {
        $db->query(db_query("INSERT INTO `" . PREFIX . "_post` (`autor`, `date`, `short_story`, `full_story`, `xfields`, `title`, `descr`, `keywords`, `category`, `alt_name`, `comm_num`, `allow_comm`, `allow_main`, `approve`, `fixed`, `allow_br`, `symbol`, `tags`, `metatitle`) VALUES ('collaps', :date, :short_story, :full_story, :xfields, :title, :descr, :keywords, :category, :alt_name, 0, 1, 1, " . $approve . ", 0, 0, '', :tags, :metatitle);", $compile));
        $news_id = $db->insert_id();
        $db->query("INSERT INTO " . PREFIX . "_post_extras (news_id,user_id,disable_index) VALUES('" . $news_id . "','1', '" . $disable_index . "')");
        if ($poster) {
            $db->query("INSERT INTO " . PREFIX . "_images (images,news_id,author,date) VALUES('" . $poster . "','" . $news_id . "','collaps','" . $_TIME . "')");
        }
        $quality = $db->safesql($res["quality"]);
        $db->query("UPDATE " . PREFIX . "_collaps SET status = '0', news_id = '" . $news_id . "', quality='" . $quality . "' WHERE kp_id = '" . $collaps["kp_id"] . "'");
        echo "insert news --- " . $news_id . " --- " . $res["name"];
    } else {
        $news_id = $collaps["news_id"];
        $db->query("DELETE FROM " . PREFIX . "_xfsearch WHERE news_id='" . $collaps["news_id"] . "' ");
        if (preg_match("#([^a-z0-9-_]|^)poster\\|([^\\|]*)#i", $post_row["xfields"], $find)) {
            $poster = "||poster|" . $find[2];
        } else {
            $poster = "";
        }
        $xfields = $db->safesql($compile["xfields"] . $poster);
        $title = $db->safesql($compile["title"]);
        $upd_title = $is_serial ? "title = '" . $title . "'," : "";
        if ($updateNewsDate) {
            $date = date("Y-m-d H:i:s", time());
            $db->query("UPDATE " . PREFIX . "_post SET " . $upd_title . " xfields = '" . $xfields . "', date='" . $date . "' WHERE id = '" . $collaps["news_id"] . "'");
            $db->query("UPDATE " . PREFIX . "_post_extras SET disable_index = '" . $disable_index . "', editdate = '" . $_TIME . "', editor='collaps', reason='Парсер обновил данный материал' WHERE news_id = '" . $collaps["news_id"] . "'");
        } else {
            $db->query("UPDATE " . PREFIX . "_post SET xfields = '" . $xfields . "' WHERE id = '" . $collaps["news_id"] . "'");
        }
        $quality = $db->safesql($res["quality"]);
        $last_episode = intval($request["last_episode"]);
        $db->query("UPDATE " . PREFIX . "_collaps SET status = '0', news_id='" . $collaps["news_id"] . "', quality = '" . $quality . "', episode='" . $last_episode . "' WHERE kp_id = '" . $collaps["kp_id"] . "'");
        echo "update news --- " . $post_row["id"] . " --- " . $post_row["title"];
    }
    if (version_compare("13.2", $config["version_id"], "<=") && !$collaps["news_id"]) {
        $ex_cats = explode(",", $compile["category"]);
        foreach ($ex_cats as $ex_cat) {
            $ex_cat = intval($ex_cat);
            $db->query("INSERT INTO " . PREFIX . "_post_extras_cats (news_id, cat_id) VALUES ('" . $news_id . "', '" . $ex_cat . "')");
        }
    }
    $xfields = xfieldsload();
    $news_xf = xfieldsdataload($compile["xfields"]);
    $xf_words = $xf_search_words = $newnews_xf = array();
    foreach ($xfields as $name => $value) {
        if ($value[6] == 1 && !empty($news_xf[$value[0]])) {
            $news_xf[$value[0]] = html_entity_decode($news_xf[$value[0]], ENT_QUOTES, $config["charset"]);
            $newnews_xf[$value[0]] = trim(htmlspecialchars(strip_tags(stripslashes($news_xf[$value[0]])), ENT_QUOTES, $config["charset"]));
            $temp_array = explode(",", $newnews_xf[$value[0]]);
            foreach ($temp_array as $value2) {
                $value2 = trim($value2);
                if ($value2) {
                    $xf_search_words[] = array($db->safesql($value[0]), $db->safesql($value2));
                }
            }
        }
    }
    if (count($xf_search_words)) {
        $temp_array = array();
        foreach ($xf_search_words as $value) {
            $temp_array[] = "('" . $news_id . "', '" . $value[0] . "', '" . $value[1] . "')";
        }
        $xf_search_words = implode(", ", $temp_array);
        $db->query("INSERT INTO " . PREFIX . "_xfsearch (news_id, tagname, tagvalue) VALUES " . $xf_search_words);
    }
    if (!$collaps["news_id"] && $approve) {
        updateSocialPosting($news_id);
    }
}

?>
