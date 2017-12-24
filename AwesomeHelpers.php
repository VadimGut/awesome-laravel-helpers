<?php


if (!function_exists('object2Array')) {
    /**
     * Transforms object into array including ancestors
     * by using json encode decode
     *
     * @param $obj
     *
     * @return mixed
     */
    function object2Array($obj)
    {
        return json_decode(json_encode($obj), true);
    }
}


if (!function_exists('array2Object')) {
    /**
     * Transforms array into object including ancestors
     * by using json encode decode
     *
     * @param $obj
     *
     * @return mixed
     */
    function array2Object($obj)
    {
        return json_decode(json_encode($obj), false);
    }
}


if (!function_exists('insertOnDuplicateKeyUpdate')) {
    /**
     * @param \Illuminate\Database\Query\Builder $builder
     * @param array $values
     * @param array $exclude_columns_from_updating - this columns from being updated (they will still be inserted)
     *
     * @return int
     */
    function insertOnDuplicateKeyUpdate(Illuminate\Database\Query\Builder $builder, array $values, array $exclude_columns_from_updating = [])
    {
        if (empty($values)) {
            return 0;
        }

        // Case where $data is not an array of arrays.
        if (!isset($values[0])) {
            $values = [$values];
        }

        // find the key columns
        list($first_row) = $values;
        if (empty($exclude_columns_from_updating)) {
            $update_columns =  array_keys($first_row);
        } else {
            $update_columns = array_diff(array_keys($first_row), $exclude_columns_from_updating);
        }

        // Build sql query
        $grammar = $builder->getGrammar();
        $sql  = $grammar->compileInsert($builder, $values);

        $sql .= ' ON DUPLICATE KEY UPDATE ';
        $sql .= implode(',', array_map(function ($val) {
            return sprintf('`%s` = VALUES(`%s`)', $val, $val);
        }, $update_columns));

        // Build the bindings of the values
        // later to be used in the statement 
        $bindings = [];
        foreach ($values as $record) {
            foreach ($record as $value) {
                $bindings[] = $value;
            }
        }

        return $builder->getConnection()->affectingStatement($sql, $bindings);
    }
}


if (!function_exists('arraySearchKey')) {
    /**
     * Recursive search for key inside an associative array and returns the value
     *
     * @param $needle_key
     * @param $haystack
     *
     * @return bool|string
     */
    function arraySearchKey($needle_key, $haystack)
    {
        if (empty($haystack)) {
            return false;
        }

        $result = false;
        array_walk_recursive($haystack, function ($item, $key) use ($needle_key, &$result) {
            if ($result === false && $key == $needle_key) {
                $result = $item;
            }
        });

        return $result;
    }
}


if (!function_exists('arraySetDefaults')) {
    /**
     * Combine passed pairs with our default pairs.
     *
     * The defaults should be considered to be all of the attributes which are
     * supported by the caller and given as a list. The returned attributes will
     * only contain the attributes in the $defaults list.
     *
     * If the $atts list has unsupported attributes, then they will be ignored and
     * removed from the final returned list.
     *
     *
     * @param array $defaults  Entire list of supported attributes and their defaults.
     * @param array $pairs User defined attributes in shortcode tag.
     *
     * @return array Combined and filtered attribute list.
     */
    function arraySetDefaults(array $defaults, array $pairs)
    {
        $output = [];

        foreach ($defaults as $key => $value) {
            $output[$key] = (array_key_exists($key, $pairs)) 
                ? $pairs[$key]
                : $value;
        }

        return $output;
    }
}


if (!function_exists('lastKey')) {
    /**
     * Returns the key at the end of the array
     *
     * @param array $array
     *
     * @return string
     */
    function lastKey($array)
    {
        end($array);

        return key($array);
    }
}


if (!function_exists('sqlDateFormat')) {
    /**
     * returns date in SQL format
     *
     * @param string $date_string
     * @param bool $seconds
     *
     * @return string
     */
    function sqlDateFormat($date_string = "now", $seconds = true)
    {
        $format = $seconds ? 'Y-m-d H:i:s' : 'Y-m-d H:i';

        return date($format, strtotime($date_string));
    }
}


if (!function_exists('percentToDecimalFraction')) {
    /**
     * Convert percent to decimal fraction
     *
     * @param $percent
     *
     * @return float
     */
    function percentToDecimalFraction($percent)
    {
        return 1 + $percent / 100;
    }
}


if (!function_exists('decimalFractionToPercent')) {
    /**
     * Convert decimal fraction to percent
     *
     * @param $decimal_fraction
     *
     * @return float
     */
    function decimalFractionToPercent($decimal_fraction)
    {
        return ($decimal_fraction - 1) * 100;
    }
}


if (!function_exists('convertTimeZoneNameToTimeZoneTime')) {
    /**
     * Convert time zone name to time zone time
     *
     * Example:
     * request: Pacific/Honolulu
     * response: -10:00
     *
     * @param $time_zone_name
     *
     * @return string
     */
    function convertTimeZoneNameToTimeZoneTime($time_zone_name) {
        $dateTime = new DateTime();
        $dateTime->setTimeZone(new DateTimeZone($time_zone_name));
        $seconds = $dateTime->getOffset();
        $flag = strpos($seconds, '-') !== false ? '-' : null;
        $seconds = intval(str_replace('-', '', $seconds));
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds / 60) % 60);
        return (strlen($hours) == 1 ? $flag . '0' . $hours : $hours) . ':' . (strlen($minutes) == 1 ? $minutes . '0' : $minutes);
    }
}


if (!function_exists('generateUUID')) {
    /**
     * Generate a globally unique identifier (GUID)
     *
     * @return array
     */
    function generateUUID()
    {
        if (function_exists('com_create_guid') === true) {
            return trim(com_create_guid(), '{}');
        }

        $data    = openssl_random_pseudo_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0100
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

}


if (!function_exists('createBase64UUID')) {
    /**
     * Create Base 64 UUID v4
     * Generated as short as possible unique ID
     * with seconds 22 chars
     * without seconds 18 chars
     *
     * @param bool $add_seconds
     *
     * @return array
     */
    function createBase64UUID($add_seconds = true)
    {
        $uuid = generateUUID();

        // For extra uniqueness add the seconds since 2014
        if ($add_seconds) {
            $uuid .= strtotime(2014) - time(); //seconds since 2014
        }
        $byteString = "";

        // Remove the dashes from the string
        $uuid = str_replace("-", "", $uuid);

        // Remove the opening and closing brackets
        $uuid = substr($uuid, 1, strlen($uuid) - 2);

        // Read the UUID string byte by byte
        for ($i = 0; $i < strlen($uuid); $i += 2) {
            // Get two hexadecimal characters
            $s = substr($uuid, $i, 2);
            // Convert them to a byte
            $d = hexdec($s);
            // Convert it to a single character
            $c = chr($d);
            // Append it to the byte string
            $byteString = $byteString . $c;
        }

        // Convert the byte string to a base64 string
        $b64uuid = base64_encode($byteString);
        // Replace the "/" and "+" since they are reserved characters
        $b64uuid = str_replace("/", "_", $b64uuid);
        $b64uuid = str_replace("+", "-", $b64uuid);
        // Remove the trailing "=="
        $b64uuid = substr($b64uuid, 0, strlen($b64uuid) - 2);

        return $b64uuid;
    }

}


if (!function_exists('arrayIntersectingFields')) {
    /**
     * Array filters all according to their intersection
     * This means that it will only leave fields inside the passed array which exists
     * in the fields array
     *
     * @param array $array
     * @param array $fields
     *
     * @return array
     */
    function arrayIntersectingFields(array &$array, array $fields = [])
    {
        return array_filter($array, function($key) use ($fields) {
            return in_array($key, $fields);
        }, ARRAY_FILTER_USE_KEY);
    }
}


if (!function_exists('isJson')) {
    /**
     * Is Json
     *
     * @param $string
     * @param bool $assoc
     *
     * @return bool
     */
    function isJson($string, $assoc = true)
    {
        // For best performance, check first char to be [ or {
        if (!in_array(substr($string, 0, 1), ['[', '{'])) {
            return false;
        }

        try {
            $decoded = json_decode($string, $assoc);
        } catch (\Exception $e) {
            return false;
        }

        // Check for json errors
        if (json_last_error() != JSON_ERROR_NONE) {
            return false;
        }

        // Validate the decoded result
        if (!is_object($decoded) && !is_array($decoded)) {
            return false;
        }

        return true;
    }
}


if (!function_exists('isArrayAssoc')) {
    /**
     * Is Array Associative
     *
     * @param $array
     *
     * @return bool
     */
    function isArrayAssoc($array)
    {
        return !!(count(array_filter($array, 'is_scalar')) > 0);
    }
}


if (!function_exists('transposeArray')) {
    /**
     * Transpose Array
     *
     * @param $array
     * @param $out
     * @param array $indices
     */
    function transposeArray($array, &$out, $indices = [])
    {
        if (is_array($array)) {
            foreach ($array as $key => $val) {
                //push onto the stack of indices
                $temp   = $indices;
                $temp[] = $key;
                transposeArray($val, $out, $temp);
            }
        } else {
            //go through the stack in reverse - make the new array
            $ref = &$out;
            foreach ((array)array_reverse($indices) as $idx) {
                $ref = &$ref[$idx];
            }
            $ref = $array;
        }
    }
}


if (!function_exists('snakeToWords')) {
    /**
     * Turns snake case to regular word with first char as capital case
     *
     * @param $string
     * @param bool $first_char_capital
     *
     * @return int
     */
    function snakeToWords($string, $first_char_capital = true)
    {
        $words = str_replace('_', ' ', snake_case($string));

        //remove dashes if they exist
        $words = str_replace('-', '', $words);

        if ($first_char_capital) {
            return ucwords($words);
        } else {
            return ucfirst($words);
        }
    }
}


if (!function_exists('snakeCase')) {
    /**
     * Returns snake case, takes into account a "-" sign
     *
     * @param $string
     * @return int
     * @internal param bool $first_char_capital
     *
     */
    function snakeCase($string)
    {
        $words = str_replace('-', '', snake_case($string));
        //remove dashes if they exist
        return $words;
    }
}


if (!function_exists('countArrayDimensions')) {
    /**
     * Count Array Dimensions
     *
     * @param $array
     *
     * @return int
     */
    function countArrayDimensions($array)
    {
        $max_depth = 1;

        foreach ($array as $value) {
            if (is_array($value)) {
                $depth = countArrayDimensions($value) + 1;

                if ($depth > $max_depth) {
                    $max_depth = $depth;
                }
            }
        }

        return $max_depth;
    }
}


if (!function_exists('isClosure')) {
    /**
     * Checks if the variable is a closure or not
     *
     * @param $suspected_closure
     *
     * @return bool
     */
    function isClosure($suspected_closure) {
        return $suspected_closure instanceof \Closure;
    }
}


if (!function_exists('sanitizeFileName')) {
    /**
     * cleans filename to become url compatible, inspired by wordpress
     *
     * @param $filename
     *
     * @return string
     */
    function sanitizeFileName($filename)
    {
        $special_chars = array("?", "[", "]", "/", "\\", "=", "<", ">", ":", ";", ",", "'", "\"", "&", "$", "#", "*", "(", ")", "|", "~", "`", "!", "{", "}", "%", "+", chr(0));

        // Filters the list of characters to remove from a filename.
        $filename = preg_replace("#\x{00a0}#siu", ' ', $filename);
        $filename = str_replace($special_chars, '', $filename);
        $filename = str_replace(array('%20', '+'), '-', $filename);
        $filename = preg_replace('/[\r\n\t -]+/', '-', $filename);
        $filename = trim($filename, '.-_');
        return $filename;
    }
}


