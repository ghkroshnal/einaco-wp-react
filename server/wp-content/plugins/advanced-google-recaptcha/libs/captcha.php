<?php

/**
 * WP Captcha
 * https://getwpcaptcha.com/
 * (c) WebFactory Ltd, 2022 - 2023, www.webfactoryltd.com
 */

class WPCaptcha_Captcha
{
    static function wp_rand($min = null, $max = null)
    {
        global $rnd_value;

        /*
         * Some misconfigured 32-bit environments (Entropy PHP, for example)
         * truncate integers larger than PHP_INT_MAX to PHP_INT_MAX rather than overflowing them to floats.
         */
        $max_random_number = 3000000000 === 2147483647 ? (float) '4294967295' : 4294967295; // 4294967295 = 0xffffffff

        if (null === $min) {
            $min = 0;
        }

        if (null === $max) {
            $max = $max_random_number;
        }

        // We only handle ints, floats are truncated to their integer value.
        $min = (int) $min;
        $max = (int) $max;

        // Use PHP's CSPRNG, or a compatible method.
        static $use_random_int_functionality = true;
        if ($use_random_int_functionality) {
            try {
                // wp_rand() can accept arguments in either order, PHP cannot.
                $_max = max($min, $max);
                $_min = min($min, $max);
                $val  = random_int($_min, $_max);
                if (false !== $val) {
                    return absint($val);
                } else {
                    $use_random_int_functionality = false;
                }
            } catch (Error $e) {
                $use_random_int_functionality = false;
            } catch (Exception $e) {
                $use_random_int_functionality = false;
            }
        }

        /*
         * Reset $rnd_value after 14 uses.
         * 32 (md5) + 40 (sha1) + 40 (sha1) / 8 = 14 random numbers from $rnd_value.
         */
        if (strlen($rnd_value) < 8) {
            $seed = '';
            $rnd_value  = md5(uniqid(microtime() . mt_rand(), true) . $seed); // phpcs:ignore
            $rnd_value .= sha1($rnd_value);
            $rnd_value .= sha1($rnd_value . $seed);
            $seed       = md5($seed . $rnd_value);
        }

        // Take the first 8 digits for our value.
        $value = substr($rnd_value, 0, 8);

        // Strip the first eight, leaving the remainder for the next call to wp_rand().
        $rnd_value = substr($rnd_value, 8);

        $value = abs(hexdec($value));

        // Reduce the value to be within the min - max range.
        $value = $min + ($max - $min + 1) * $value / ($max_random_number + 1);

        return abs((int) $value);
    }

    // convert HEX(HTML) color notation to RGB
    static function hex2rgb($color)
    {
        if ($color[0] == '#') {
            $color = substr($color, 1);
        }

        if (strlen($color) == 6) {
            list($r, $g, $b) = array(
                $color[0] . $color[1],
                $color[2] . $color[3],
                $color[4] . $color[5]
            );
        } elseif (strlen($color) == 3) {
            list($r, $g, $b) = array($color[0] . $color[0], $color[1] . $color[1], $color[2] . $color[2]);
        } else {
            return array(255, 255, 255);
        }

        $r = hexdec($r);
        $g = hexdec($g);
        $b = hexdec($b);

        return array($r, $g, $b);
    } // html2rgb


    // output captcha image
    static function generate()
    {
        $a = self::wp_rand(0, (int) 10);
        $b = self::wp_rand(0, (int) 10);
        if(isset($_GET['color'])){ // phpcs:ignore
            $color = substr($_GET['color'],0,7); // phpcs:ignore
            $color = urldecode($color);
        } else{
            $color = '#FFFFFF';
        }
        if (isset($_GET['id'])) { // phpcs:ignore
            $captcha_cookie_name = 'wpcaptcha_captcha_' . intval($_GET['id']); // phpcs:ignore
        } else {
            $captcha_cookie_name = 'wpcaptcha_captcha';
        }

        if ($a > $b) {
            $out = "$a - $b";
            $captcha_value = $a - $b;
        } else {
            $out = "$a + $b";
            $captcha_value = $a + $b;
        }

        setcookie($captcha_cookie_name, $captcha_value, time() + 60 * 5, '/');

        $font   = 5;
        $width  = ImageFontWidth($font) * strlen($out);
        $height = ImageFontHeight($font);
        $im     = ImageCreate($width, $height);

        $x = imagesx($im) - $width;
        $y = imagesy($im) - $height;

        $white = imagecolorallocate($im, 255, 255, 255);
        $gray = imagecolorallocate($im, 66, 66, 66);
        $black = imagecolorallocate($im, 0, 0, 0);
        $trans_color = $white; //transparent color

        if ($color) {
            $color = self::hex2rgb($color);
            $new_color = imagecolorallocate($im, $color[0], $color[1], $color[2]);
            imagefill($im, 1, 1, $new_color);
        } else {
            imagecolortransparent($im, $trans_color);
        }

        imagestring($im, $font, $x, $y, $out, $black);

        // always add noise
        if (1 == 1) {
            $color_min = 100;
            $color_max = 200;
            $rand1 = imagecolorallocate($im, self::wp_rand($color_min, $color_max), self::wp_rand($color_min, $color_max), self::wp_rand($color_min, $color_max));
            $rand2 = imagecolorallocate($im, self::wp_rand($color_min, $color_max), self::wp_rand($color_min, $color_max), self::wp_rand($color_min, $color_max));
            $rand3 = imagecolorallocate($im, self::wp_rand($color_min, $color_max), self::wp_rand($color_min, $color_max), self::wp_rand($color_min, $color_max));
            $rand4 = imagecolorallocate($im, self::wp_rand($color_min, $color_max), self::wp_rand($color_min, $color_max), self::wp_rand($color_min, $color_max));
            $rand5 = imagecolorallocate($im, self::wp_rand($color_min, $color_max), self::wp_rand($color_min, $color_max), self::wp_rand($color_min, $color_max));

            $style = array($rand1, $rand2, $rand3, $rand4, $rand5);
            imagesetstyle($im, $style);
            imageline($im, self::wp_rand(0, $width), 0, self::wp_rand(0, $width), $height, IMG_COLOR_STYLED);
            imageline($im, self::wp_rand(0, $width), 0, self::wp_rand(0, $width), $height, IMG_COLOR_STYLED);
            imageline($im, self::wp_rand(0, $width), 0, self::wp_rand(0, $width), $height, IMG_COLOR_STYLED);
            imageline($im, self::wp_rand(0, $width), 0, self::wp_rand(0, $width), $height, IMG_COLOR_STYLED);
            imageline($im, self::wp_rand(0, $width), 0, self::wp_rand(0, $width), $height, IMG_COLOR_STYLED);
        }

        header('Cache-Control: no-cache, must-revalidate');
        header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
        header('Content-type: image/gif');
        imagegif($im);
        die();
    } // create
} // WPCaptcha_Captcha


if (isset($_GET['wpcaptcha-generate-image'])) { // phpcs:ignore
    WPCaptcha_Captcha::generate();
}
