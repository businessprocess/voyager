<?php

if (!function_exists('setting')) {
    function setting($key, $default = null)
    {
        return TCG\Voyager\Facades\Voyager::setting($key, $default);
    }
}

if (!function_exists('menu')) {
    function menu($menuName, $type = null, array $options = [])
    {
        return TCG\Voyager\Facades\Voyager::model('Menu')->display($menuName, $type, $options);
    }
}

if (!function_exists('voyager_asset')) {
    function voyager_asset($path, $secure = null)
    {
        return route('voyager.voyager_assets').'?path='.urlencode($path);
    }
}

if (!function_exists('get_file_name')) {
    function get_file_name($name)
    {
        preg_match('/(_)([0-9])+$/', $name, $matches);
        if (count($matches) == 3) {
            return Illuminate\Support\Str::replaceLast($matches[0], '', $name).'_'.(intval($matches[2]) + 1);
        } else {
            return $name.'_1';
        }
    }
}

if (!function_exists('voyager_format_date')) {
    /**
     * Format a date with a strftime-style format (e.g. "%Y-%m-%d") as stored in BREAD details.
     * Replaces Carbon's formatLocalized(), which was removed in Carbon 3.
     */
    function voyager_format_date($date, $format)
    {
        $tokens = [
            'a' => 'D', 'A' => 'l', 'd' => 'd', 'e' => 'j', 'u' => 'N', 'w' => 'w', 'V' => 'W',
            'b' => 'M', 'h' => 'M', 'B' => 'F', 'm' => 'm', 'y' => 'y', 'Y' => 'Y', 'G' => 'o',
            'H' => 'H', 'k' => 'G', 'I' => 'h', 'l' => 'g', 'M' => 'i', 'p' => 'A', 'P' => 'a',
            'r' => 'h:i:s A', 'R' => 'H:i', 'S' => 's', 'T' => 'H:i:s', 'X' => 'H:i:s',
            'z' => 'O', 'Z' => 'T', 'c' => 'D M j H:i:s Y', 'D' => 'm/d/y', 'F' => 'Y-m-d',
            'x' => 'm/d/y', 's' => 'U', 'n' => "\n", 't' => "\t", '%' => '%',
        ];

        $converted = preg_replace_callback('/%([a-zA-Z%])|([^%])/', function ($match) use ($tokens) {
            if (isset($match[2])) {
                // Escape literal characters so date() keeps them as they are
                return '\\'.$match[2];
            }

            return $tokens[$match[1]] ?? '\%\\'.$match[1];
        }, (string) $format);

        return Carbon\Carbon::parse($date)->translatedFormat($converted);
    }
}

if (!function_exists('voyager_json_decode')) {
    /**
     * json_decode() that tolerates empty and already-cast values.
     * null / '' return null (PHP 8.1+ deprecates passing null to json_decode),
     * arrays and objects (model casts) are normalized the same way a decoded string would be.
     */
    function voyager_json_decode($value, $assoc = false)
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_array($value) || is_object($value)) {
            return json_decode(json_encode($value), $assoc);
        }

        return json_decode((string) $value, $assoc);
    }
}

if (!function_exists('voyager_display_value')) {
    /**
     * Make a field value safe for {{ }} output: arrays and plain objects (e.g. array/json casts)
     * are rendered as JSON instead of failing in htmlspecialchars().
     */
    function voyager_display_value($value)
    {
        if (is_array($value) || (is_object($value)
            && !$value instanceof Stringable
            && !$value instanceof Illuminate\Contracts\Support\Htmlable
            && !$value instanceof BackedEnum)) {
            return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        return $value;
    }
}
