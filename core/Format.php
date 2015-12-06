<?php
/**
 * Some useful format helpers
 *
 * @author Tommyknocker <tommyknocker@theapp.pro>
 * @license http://www.gnu.org/licenses/lgpl.txt LGPLv3
 */
namespace App\Core;

use App;

class Format
{

    /**
     * Associative array format
     * @param array $array 
     * @param string|array $content Optional
     * @param string|array $index Optional
     * @param bool|int $exact Optional
     * @param bool $combine Optional
     * @return array|bool
     * */
    public function arrays($array, $content = '', $index = '', $exact = false, $combine = false)
    {

        if (!is_array($array) || !$array || (!is_string($content) && !is_array($content)) || (!is_string($index) && !is_array($index))) {
            return false;
        }

        if ($exact === 0) {
            $exact = true;
        }

        $result = [];
        $index = is_string($index) && $index ? [$index] : $index;
        $combine = $index ? $combine : true;
        $indexCount = count($index);

        if ($exact) {
            $amount = count($array);
            $arrayIndex = 0;

            if (is_int($exact)) {
                if ($exact > 0) {
                    $arrayIndex = $exact >= $amount ? $amount - 1 : $exact;
                } else {
                    $arrayIndex = -$exact >= $amount ? 0 : $amount + $exact;
                }
            }

            return $content ? $this->arrayResult($array[$arrayIndex], $content) : $array[$arrayIndex];
        }

        foreach ($array as $current) {
            $place = & $result;

            if (is_array($index)) {
                for ($i = 0; $i < $indexCount; $i++) {
                    $place[$current[$index[$i]]] = isset($place[$current[$index[$i]]]) ? $place[$current[$index[$i]]] : [];
                    $place = & $place[$current[$index[$i]]];
                }
            }

            if ($combine) {
                $place[] = $this->arrayResult($current, $content);
            } else {
                $place = $this->arrayResult($current, $content);
            }
        }

        return $result;
    }

    /**
     * Fromat arrays helper
     * @param array $array
     * @param integer $index
     * @return mixed
     */
    private function arrayResult($array, $index)
    {
        if (is_array($index)) {
            $result = [];
            foreach ($index as $current) {
                $result[$current] = $array[$current];
            }
        } elseif (is_string($index) && array_key_exists($index, $array)) {
            $result = $array[$index];
        } else {
            $result = $array;
        }

        return $result;
    }

    /**
     * Convert camel case to underscore
     * @param string $string
     * @return string
     */
    public function camelCaseToUnderScore($string)
    {
        preg_match_all('/([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)/', $string, $matches);
        $result = $matches[0];
        foreach ($result as &$match) {
            $match = $match == strtoupper($match) ? strtolower($match) : lcfirst($match);
        }
        return implode('_', $result);
    }

    /**
     * Сut text if its length more than allowed characters
     * @param type $text
     */
    public function preview($text, $stripTags = false)
    {
        if ($stripTags) {
            $text = strip_tags($text);
        }

        if (mb_strlen($text, "UTF-8") > App::Config()->text->preview->length) {
            $text = mb_substr($text, 0, App::Config()->text->preview->length, "UTF-8") . '...';
        }
        
        return $text;
    }
}
