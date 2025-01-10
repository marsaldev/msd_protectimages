<?php
/**
 * @author Marco Salvatore (marsaldev)
 * @license MIT License
 */

namespace Marsaldev\Module\MsdProtectImages\Tool;

use PrestaShop\PrestaShop\Core\Cache\Clearer\CacheClearerInterface;
use Tools as LegacyTools;
use Hook;

class ManageHtaccess {

    /**
     * @var CacheClearerInterface
     */
    private $cacheClearer;

    /**
     * ManageHtaccess constructor.
     *
     * @param CacheClearerInterface $cacheClearer
     */
    public function __construct(CacheClearerInterface $cacheClearer)
    {
        $this->cacheClearer = $cacheClearer;
    }

    /**
     * Generates htaccess rules.
     *
     * @return bool
     */
    public function generateApacheRules()
    {
        $isGenerated = $this->generateHtaccess();

        if ($isGenerated) {
            $this->cacheClearer->clear();
        }

        return $isGenerated;
    }

    public function clearApacheRules()
    {
        $isCleaned = $this->cleanHtaccess();

        if ($isCleaned) {
            $this->cacheClearer->clear();
        }

        return $isCleaned;
    }

    public static function generateHtaccess()
    {
        if (
            defined('_PS_IN_TEST_')
            || (defined('PS_INSTALLATION_IN_PROGRESS'))
        ) {
            return true;
        }

        $htaccessPath = _PS_ROOT_DIR_ . '/.htaccess';
        $adminDir = self::getAdminDir();

        // Check current content of .htaccess, if yes, the rules are already applied
        if (file_exists($htaccessPath)) {
            $content = file_get_contents($htaccessPath);
            if (preg_match('#^(.*)\# ~~start msd_protectimages~~.*\# ~~end msd_protectimages~~[^\n]*(.*)$#s', $content, $m)) {
                return false;
            }
        }

        if(file_exists($htaccessPath)) {
            // save the htaccess content
            $content = file_get_contents($htaccessPath);
        }

        // Write .htaccess data
        if (!$write_fd = @fopen($htaccessPath, 'wb')) {
            return false;
        }

        $domains = LegacyTools::getDomains();

        // Write data in .htaccess file
        fwrite($write_fd, "# ~~start msd_protectimages~~".PHP_EOL);
        fwrite($write_fd, "# Do not remove this comment, Prestashop will keep automatically the code outside this comment when .htaccess will be generated again".PHP_EOL);

        // RewriteEngine
        fwrite($write_fd, "<IfModule mod_rewrite.c>".PHP_EOL);
        fwrite($write_fd, "RewriteEngine on");

        foreach ($domains as $domain => $list_uri) {
            // As we use regex in the htaccess, ipv6 surrounded by brackets must be escaped
            $domain = str_replace(['[', ']'], ['\[', '\]'], $domain);

            foreach ($list_uri as $uri) {
                fwrite($write_fd, PHP_EOL.PHP_EOL.'#Domain: '.$domain.PHP_EOL);
                fwrite($write_fd, '#Protect original images from direct access'.PHP_EOL);
                fwrite($write_fd, PHP_EOL);

                // Block access for the rewrite rule of the original images
                fwrite($write_fd, '# E.g. https://'.$domain.'/1023/1023-beautiful-product.jpg'.PHP_EOL);
                fwrite($write_fd, 'RewriteCond %{HTTP_REFERER} '.$adminDir.' [NC]'.PHP_EOL);
                fwrite($write_fd, 'RewriteRule ^/([0-9]*)/([_a-zA-Z0-9-]*)\.(?:jpg|png|webp|avif)$ - [L]'.PHP_EOL);
                fwrite($write_fd, 'RewriteCond %{HTTP_HOST} ^'.$domain.'$'.PHP_EOL);
                fwrite($write_fd, 'RewriteRule ^/([0-9]*)/([_a-zA-Z0-9-]*)\.(?:jpg|png|webp|avif)$ - [F]'.PHP_EOL);
                fwrite($write_fd, PHP_EOL);

                $imagePath = $image = '';
                for ($i = 1; $i < 10; $i++) {
                    $imagePath .= $i.'/';
                    $image .= $i;
                    fwrite($write_fd, '#E.g. /img/'.$imagePath.$image.'.jpg'.PHP_EOL);
                    fwrite($write_fd, 'RewriteCond %{HTTP_REFERER} '.$adminDir.' [NC]'.PHP_EOL);
                    fwrite(
                        $write_fd,
                        'RewriteRule ^img/p/'.str_repeat('([0-9])/', $i).'([0-9]+)\.(?:jpg|png|webp|avif)$ - [L]'.PHP_EOL
                    );

                    fwrite($write_fd, 'RewriteCond %{HTTP_HOST} ^'.$domain.'$'.PHP_EOL);
                    fwrite($write_fd, 'RewriteRule ^img/p/'.str_repeat('([0-9])/', $i).'([0-9]+)\.jpg$ - [F]'.PHP_EOL);
                    if($i!=9)fwrite($write_fd, PHP_EOL);
                }
            }
        }

        fwrite($write_fd, "</IfModule>".PHP_EOL);
        fwrite($write_fd, '# Do not remove this comment, PrestaShop will keep automatically the code outside this comment when .htaccess will be generated again'.PHP_EOL);
        fwrite($write_fd, '# ~~end msd_protectimages~~'.PHP_EOL.PHP_EOL);
        fclose($write_fd);

        if (!defined('PS_INSTALLATION_IN_PROGRESS')) {
            Hook::exec('actionHtaccessCreate');
        }

        if($content)
            file_put_contents($htaccessPath, $content, FILE_APPEND);

        return true;
    }

    public static function cleanHtaccess()
    {
        $key1 = "# ~~start msd_protectimages~~".PHP_EOL;
        $key2 = "# ~~end msd_protectimages~~".PHP_EOL;
        $path = _PS_ROOT_DIR_ . '/.htaccess';
        dump(file_exists($path), is_writable($path));
        if (file_exists($path) && is_writable($path)) {
            $s = LegacyTools::file_get_contents($path);
            $p1 = strpos($s, $key1);
            $p2 = strpos($s, $key2, $p1);
            dump($p1, $p2);
            if ($p1 === false || $p2 === false) {
                return false;
            }
            $s = LegacyTools::substr($s, 0, $p1) . LegacyTools::substr($s, $p2 + LegacyTools::strlen($key2));
            file_put_contents($path, $s);
        }

        return true;
    }

    /**
     * @return bool|string
     */
    public static function getAdminDir()
    {
        $adminDir = str_replace('\\', '/', _PS_ADMIN_DIR_);
        $adminDir = explode('/', $adminDir);
        $len = count($adminDir);

        return $len > 1 ? $adminDir[$len - 1] : _PS_ADMIN_DIR_;
    }
}