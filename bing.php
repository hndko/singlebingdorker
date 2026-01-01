<?php

// ANSI Color Codes
define('RED', "\033[0;31m");
define('GREEN', "\033[0;32m");
define('YELLOW', "\033[0;33m");
define('BLUE', "\033[0;34m");
define('CYAN', "\033[0;36m");
define('BOLD', "\033[1m");
define('RESET', "\033[0m");

// Banner
function banner() {
    echo CYAN . "
    ██████╗ ██╗███╗   ██╗ ██████╗
    ██╔══██╗██║████╗  ██║██╔════╝
    ██████╔╝██║██╔██╗ ██║██║  ███╗
    ██╔══██╗██║██║╚██╗██║██║   ██║
    ██████╔╝██║██║ ╚████║╚██████╔╝
    ╚═════╝ ╚═╝╚═╝  ╚═══╝ ╚═════╝
    " . RESET . "\n";
    echo YELLOW . "    🔥 Auto Dorker Bing Single v2.0 🔥" . RESET . "\n";
    echo GREEN . "    👨‍💻 Created By : Kyuoko" . RESET . "\n";
    echo BLUE . "    🚀 Thx To : UKL-TEAM, GFS-TEAM, AND YOU" . RESET . "\n";
    echo "    --------------------------------------------\n";
}

// Help Menu
function usage() {
    echo RED . "    [!] Usage: php bing.php \"dork\"" . RESET . "\n";
    echo YELLOW . "    [?] Ex   : php bing.php \"inurl:/buy.php\" > output.txt" . RESET . "\n";
    exit;
}

set_time_limit(0);
error_reporting(0);

if (!isset($argv[1])) {
    banner();
    usage();
}

$dork = $argv[1];
banner();

// Feedback to STDERR so it doesn't pollute the pipe
fwrite(STDERR, GREEN . "    [+] Target Dork : " . BOLD . $dork . RESET . "\n");
fwrite(STDERR, BLUE . "    [~] Starting Scanning..." . RESET . "\n");

function getRandomUserAgent() {
    $agents = [
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
        'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/92.0.4515.107 Safari/537.36',
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0',
        'Mozilla/5.0 (iPhone; CPU iPhone OS 14_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.1.1 Mobile/15E148 Safari/604.1'
    ];
    return $agents[array_rand($agents)];
}

function getSource($url) {
    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($curl, CURLOPT_USERAGENT, getRandomUserAgent());
    curl_setopt($curl, CURLOPT_TIMEOUT, 10);

    $content = curl_exec($curl);

    if(curl_errno($curl)){
        fwrite(STDERR, RED . "    [!] Error: " . curl_error($curl) . RESET . "\n");
    }

    curl_close($curl);
    return $content;
}

$do = urlencode($dork);
$npage    = 1;
$npages   = 30000;
$allLinks = [];

while ($npage <= $npages) {
    fwrite(STDERR, YELLOW . "\r    [~] Scraping Page: " . RESET . ceil($npage / 10) . "...");

    $url = "http://www.bing.com/search?q=" . $do . "&first=" . $npage;
    $x = getSource($url);

    if ($x) {
        // Improved Regex to catch more links
        preg_match_all('/<h2><a href="(.*?)" h="ID/', $x, $findlink);

        if (empty($findlink[1])) {
             // Try alternative regex if structure changed
             preg_match_all('/<li class="b_algo"><h2><a href="(.*?)"/', $x, $findlink);
        }

        if (!empty($findlink[1])) {
            foreach ($findlink[1] as $fl) {
                // Filter out microsoft or bing links if any
                if (strpos($fl, 'bing.com') === false && strpos($fl, 'microsoft.com') === false) {
                     $allLinks[] = $fl;
                }
            }
        } else {
            // No more results found on this page
            // Check if there is a "Next" button/link to decide if we really stop,
            // but usually empty results means end or block.
            // Let's assume end of results to avoid infinite loop on empty.
            if (empty($findlink[1]) && strpos($x, '<a href="" class="sb_pagN">') === false) {
                 fwrite(STDERR, RED . "\n    [!] No more meaningful results found." . RESET . "\n");
                 break;
            }
        }

        $npage = $npage + 10;

        // Check for next page indicator (this is brittle but standard for basic scrapers)
        // If we want to be safer, we rely on the loop limit + empty results.
        if (preg_match("/first=" . $npage . "&amp/", $x) == 0 && strpos($x, 'sb_pagN') === false) {
            fwrite(STDERR, "\n    [!] End of pages reached." . RESET . "\n");
            break;
        }

    } else {
        fwrite(STDERR, RED . "\n    [!] Failed to retrieve content." . RESET . "\n");
        break;
    }

    // Nice delay to be polite (optional, but good for 'powerful' stability)
    usleep(500000);
}

fwrite(STDERR, "\n" . GREEN . "    [+] Extraction Complete! Processing domains..." . RESET . "\n");

$URLs = [];
foreach ($allLinks as $url) {
    $parsed_url = parse_url($url);
    if (isset($parsed_url['host'])) {
        $URLs[] = $parsed_url['host'];
    }
}

$array = array_unique(array_filter($URLs));
$total = count($array);

fwrite(STDERR, CYAN . "    [OK] Total Unique Domains Found: " . BOLD . $total . RESET . "\n");
fwrite(STDERR, "    --------------------------------------------\n");

foreach ($array as $domain) {
    echo "http://$domain/\n";
}
?>
