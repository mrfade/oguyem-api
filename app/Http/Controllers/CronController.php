<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\MenuComment;
use App\Models\MenuCommentImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PHPHtmlParser\Dom;

class CronController extends Controller {

    public function fetchMenus() {

        ini_set('mbstring.language', 'Turkish');

        $response = $this->get_response('https://yemekhane.ogu.edu.tr/');

        $result = [];
        $dom = new Dom;
        $dom->loadStr($response);

        $contents = $dom->find('.menu-hafta .row');

        foreach ($contents as $content) {
            $date_range = explode('-', $content->getAttribute('id'));
            $date_start = new \DateTime($date_range[0]);
            $date_end = new \DateTime($date_range[1]);

            $date = clone $date_start;

            $days = $content->find('.yemek-menu-col');

            foreach ($days as $day) {

                $food_list = [];
                $weekend = false;
                $holiday = false;
                $holiday_title = null;

                $dayHtml = html_entity_decode(preg_replace("/U\+([0-9A-F]{4})/", "&#x\\1;", $day->innerHtml), ENT_NOQUOTES, 'UTF-8');

                try {
                    $weekend = (boolean) preg_match('#yemek-menu-haftasonu#ms', $dayHtml);
                    preg_match('#yemek-menu-tatil">\s*<strong>[^<]+</strong>\s*([^<]+)#ms', $dayHtml, $holiday_match);
                    if (isset($holiday_match[1])) {
                        $holiday = true;
                        $holiday_title = $holiday_match[1];
                    }

                    preg_match_all('#data-icerik="(?P<contents>[^>]*)">(?P<name>[^<]+)</a>\s*</span>\s*<span class="yemek-menu-kalori(?:\syemek-menu-kalori-bos)?">\((?P<calorie>[^<]+)\)#ms', $dayHtml, $foods);

                    foreach ($this->preg_array($foods) as $food) {
                        array_push($food_list, [
                            'name' => mb_convert_case($this->tr_to_tr($food['name']), MB_CASE_TITLE, 'UTF-8'),
                            // 'name' => $food['name'],
                            'calorie' => $food['calorie'],
                            'contents' => $food['contents'],
                        ]);
                    }
                } catch (\Throwable $th) {
                    // throw $th;
                }

                array_push($result, [
                    'date' => $date->format('Y-m-d'),
                    'food_list' => $food_list,
                    'weekend' => $weekend,
                    'holiday' => $holiday,
                    'holiday_title' => $holiday_title,
                ]);
                $date->add(new \DateInterval('P1D'));
            }
        }

        // print_r($result);

        // save to db
        foreach ($result as $day) {
            $menu = Menu::where('date', $day['date'])->first();
            if (!$menu) {
                Menu::create([
                    'date' => $day['date'],
                    'food_list' => json_encode($day['food_list'], JSON_UNESCAPED_UNICODE),
                    'weekend' => $day['weekend'],
                    'holiday' => $day['holiday'],
                    'holiday_title' => $day['holiday_title'],
                ]);
            } else {
                $food_list = json_decode($menu->food_list, true);
                if (count($food_list) <= 0 && count($day['food_list']) > 0) {
                    $menu->food_list = json_encode($day['food_list'], JSON_UNESCAPED_UNICODE);
                    $menu->weekend = $day['weekend'];
                    $menu->holiday = $day['holiday'];
                    $menu->holiday_title = $day['holiday_title'];
                    $menu->save();
                }
            }
        }

        return response()->json(['success' => true, 'result' => $result], 200);
    }

    private function get_response($url) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }

    private function preg_array($array) {
        $response = [];

        foreach ($array as $key => $val) {
            for ($i = 0; $i < count($val); $i++) {
                $response[$i][$key] = $val[$i];
            }
        }

        return $response;
    }

    private function tr_to_tr($str) {
        return str_replace(['Ğ', 'Ü', 'Ş', 'İ', 'I', 'Ö', 'Ç'], ['ğ', 'ü', 'ş', 'i', 'ı', 'ö', 'ç'], $str);
    }
}
