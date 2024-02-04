<?php
// Plik: install.php

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    downloadWordPress();
    unzipWordPress();
    downloadPlugins($_POST['plugins'] ?? []);
    redirect();
} else {
    showForm();
}

function showForm() {
    // Dodanie Bootstrapa
    echo '<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">';
    ?>
    <div class="container mt-5">
    
<p id="progressText">Welcome to WordPress Turbo Installer.</p>
<p>If You install WP many times, this should speed up the process ;-). All you need to do is select the plugins you want to install and the latest version of WP together with the plugins will land at this address. Try it, and if something doesn't work, your hosting probably doesn't support these solutions, or there's some bug I didn't think about ;p you can write to me or give a star if it helped you at <a href="https://github.com/friqo/WordPressTurboInstaller" target="_blank">https://github.com/friqo/WordPressTurboInstaller</a></p>

        <form action="install.php" method="post" class="form-group">
            <!-- Lista wtyczek -->
            <p>
            <input type="checkbox" name="plugins[]" value="contact-form-7" id="contact-form-7">
            <label for="contact-form-7">Contact Form 7</label>
        </p>
        <p>
            <input type="checkbox" name="plugins[]" value="wordpress-seo" id="wordpress-seo">
            <label for="wordpress-seo">Yoast SEO</label>
        </p>
        <p>
            <input type="checkbox" name="plugins[]" value="solid-security" id="solid-security">
            <label for="solid-security">Solid Security – Password, Two Factor Authentication, and Brute Force Protection</label>
        </p>
        <p>
            <input type="checkbox" name="plugins[]" value="woocommerce" id="woocommerce">
            <label for="woocommerce">Woocommerce</label>
        </p>
        <p>
            <input type="checkbox" name="plugins[]" value="w3-total-cache" id="w3-total-cache">
            <label for="w3-total-cache">W3 Total Cache</label>
        </p>
        <p>
            <input type="checkbox" name="plugins[]" value="wp-mail-smtp" id="wp-mail-smtp">
            <label for="wp-mail-smtp">WP mail smtp</label>
        </p>
        <p>
            <input type="checkbox" name="plugins[]" value="lscache" id="lscache">
            <label for="lscache">Lite Speed Cache</label>
        </p>
        <p>
            <input type="checkbox" name="plugins[]" value="updraft-plus" id="updraft-plus">
            <label for="updraft-plus">Updraft plus</label>
        </p>
        <p>
            <input type="checkbox" name="plugins[]" value="wordfence" id="wordfence">
            <label for="wordfence">Wordfence</label>
        </p>
            
            <input type="submit" value="Install" class="btn btn-primary mt-3">
        </form>
    </div>

    
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
    <?php
}

function downloadWordPress() {
    echo "Fetching WordPress...<br>";
    flush();
    ob_flush();

    $url = 'https://wordpress.org/latest.zip';
    $zipFile = 'wordpress.zip';
    file_put_contents($zipFile, fopen($url, 'r'));
}

function unzipWordPress() {
    echo "Unpacking WordPress...<br>";
    flush();
    ob_flush();

    $zip = new ZipArchive;
    $res = $zip->open('wordpress.zip');
    if ($res === TRUE) {
        for($i = 0; $i < $zip->numFiles; $i++) {
            $filePath = $zip->getNameIndex($i);
            if (substr($filePath, 0, 10) == 'wordpress/') {
                $relativePath = substr($filePath, 10);
                if ($relativePath) {
                    if ($zip->getStream($filePath)) {
                        if (!is_dir(dirname($relativePath))) {
                            mkdir(dirname($relativePath), 0777, true);
                        }
                        file_put_contents($relativePath, file_get_contents("zip://wordpress.zip#".$filePath));
                    }
                }
            }
        }
        $zip->close();
        unlink('wordpress.zip');
    } else {
        echo 'Error during unpack: ' . $res;
    }
}

function downloadPlugins($plugins) {
    foreach ($plugins as $plugin) {
        echo "Downloading plugin: $plugin<br>";
        flush();
        ob_flush();

        $pluginUrl = getPluginDownloadUrl($plugin);
        if ($pluginUrl) {
            $zipFile = "{$plugin}.zip";
            file_put_contents($zipFile, fopen($pluginUrl, 'r'));

            $zip = new ZipArchive;
            if ($zip->open($zipFile) === TRUE) {
                $zip->extractTo('wp-content/plugins/');
                $zip->close();
                unlink($zipFile);
            } else {
                echo "Error during unpack plugin: $plugin<br>";
            }
        } else {
            echo "I was not able to fetch the plugin: $plugin, check the URL<br>";
        }
    }
}

function getPluginDownloadUrl($pluginSlug) {
    $pluginPages = [
        'contact-form-7' => 'https://pl.wordpress.org/plugins/contact-form-7/',
        'wordpress-seo' => 'https://pl.wordpress.org/plugins/wordpress-seo/',
        'solid-security' => 'https://pl.wordpress.org/plugins/better-wp-security/',
        'woocommerce' => 'https://pl.wordpress.org/plugins/woocommerce/',
        'w3-total-cache' => 'https://wordpress.org/plugins/w3-total-cache/',
        'wp-mail-smtp' => 'https://wordpress.org/plugins/wp-mail-smtp/',
        'aioseo' => 'https://pl.wordpress.org/plugins/all-in-one-seo-pack/',
        'lscache'=>'https://pl.wordpress.org/plugins/litespeed-cache/',
        'updraft-plus' => 'https://pl.wordpress.org/plugins/updraftplus/',
        'wordfence'=>'https://pl.wordpress.org/plugins/wordfence/'
        // add more plugins if You need.
    ];

    if (isset($pluginPages[$pluginSlug])) {
        $content = file_get_contents($pluginPages[$pluginSlug]);
        if ($content) {
            $dom = new DOMDocument();
            @$dom->loadHTML($content);
            $xpath = new DOMXPath($dom);
            $downloadLinks = $xpath->query("//a[contains(@class, 'plugin-download')]");
            if ($downloadLinks->length > 0) {
                return $downloadLinks->item(0)->getAttribute('href');
            }
        }
    }
    return null;
}

function redirect() {
    updateProgress(100, "Ready? Steady? Go! in 3 sec..");
    flush();
    ob_flush();

    echo '<script type="text/javascript">
            setTimeout(function() {
                window.location.href = "index.php";
            }, 3000);
          </script>';
    unlink(__FILE__);
}

?>
