<?php

// Configuration
define('DELAY_MIN', 2); // Seconds
define('DELAY_MAX', 5); // Seconds
define('COOKIE_FILE', 'bing_cookies.txt');
define('MANUAL_COOKIE_FILE', 'manual_cookie.txt');
define('DEBUG_FILE', 'debug_blocked.html');

// ANSI Color Codes
define('RED', "\033[0;31m");
define('GREEN', "\033[0;32m");
define('YELLOW', "\033[0;33m");
define('BLUE', "\033[0;34m");
define('CYAN', "\033[0;36m");
define('BOLD', "\033[1m");
define('RESET', "\033[0m");

// Banner
function banner()
{
    fwrite(STDERR, CYAN . "
    ██████╗ ██╗███╗   ██╗ ██████╗
    ██╔══██╗██║████╗  ██║██╔════╝
    ██████╔╝██║██╔██╗ ██║██║  ███╗
    ██╔══██╗██║██║╚██╗██║██║   ██║
    ██████╔╝██║██║ ╚████║╚██████╔╝
    ╚═════╝ ╚═╝╚═╝  ╚═══╝ ╚═════╝
    " . RESET . "\n");
    fwrite(STDERR, YELLOW . "    🔥 Auto Dorker Bing Single v4.0 (Stealth Mode) 🔥" . RESET . "\n");
    fwrite(STDERR, GREEN . "    👨‍💻 Created By : Kyuoko" . RESET . "\n");
    fwrite(STDERR, BLUE . "    🚀 Thx To : UKL-TEAM, GFS-TEAM, AND YOU" . RESET . "\n");
    fwrite(STDERR, "    --------------------------------------------\n");
}

// Help Menu
function usage()
{
    fwrite(STDERR, RED . "    [!] Usage: php bing.php \"dork\"" . RESET . "\n");
    fwrite(STDERR, YELLOW . "    [?] Ex   : php bing.php \"inurl:/buy.php\" > output.txt" . RESET . "\n");
    fwrite(STDERR, CYAN . "    [i] Tip  : If blocked, save your browser cookies to '" . MANUAL_COOKIE_FILE . "'" . RESET . "\n");
    exit;
}

if (!isset($argv[1])) {
    banner();
    usage();
}

$dork = $argv[1];
banner();

// Feedback to STDERR
fwrite(STDERR, GREEN . "    [+] Target Dork : " . BOLD . $dork . RESET . "\n");

function getRandomUserAgent()
{
    $agents = [
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
    ];
    return $agents[array_rand($agents)];
}

$GLOBAL_UA = getRandomUserAgent();

function getRequest($url)
{
    global $GLOBAL_UA;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);

    // Header Construction
    $headers = [
        'User-Agent: ' . $GLOBAL_UA,
        'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8',
        'Accept-Language: en-US,en;q=0.9',
        'Connection: keep-alive',
        'Upgrade-Insecure-Requests: 1',
        'Sec-Fetch-Dest: document',
        'Sec-Fetch-Mode: navigate',
        'Sec-Fetch-Site: none',
        'Sec-Fetch-User: ?1',
        'Referer: https://www.bing.com/'
    ];

    // Manual Cookie Override
    if (file_exists(MANUAL_COOKIE_FILE)) {
        $manualCookie = trim(file_get_contents(MANUAL_COOKIE_FILE));
        if (!empty($manualCookie)) {
            $headers[] = 'Cookie: ' . $manualCookie;
        }
    } else {
        curl_setopt($ch, CURLOPT_COOKIEJAR, COOKIE_FILE);
        curl_setopt($ch, CURLOPT_COOKIEFILE, COOKIE_FILE);
    }

    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $content = curl_exec($ch);

    if (curl_errno($ch)) {
        fwrite(STDERR, RED . "    [!] Error: " . curl_error($ch) . RESET . "\n");
    }

    curl_close($ch);
    return $content;
}

// Initial Session (only if no manual cookie)
if (!file_exists(MANUAL_COOKIE_FILE)) {
    fwrite(STDERR, BLUE . "    [~] Initializing Guest Session..." . RESET . "\n");
    getRequest("https://www.bing.com/");
    sleep(2);
} else {
    fwrite(STDERR, BLUE . "    [~] Using Manual Cookies from " . MANUAL_COOKIE_FILE . "..." . RESET . "\n");
}

$do = urlencode($dork);
$npage = 1;
$npages = 30000;
$allLinks = [];
$stopScanning = false;

fwrite(STDERR, BLUE . "    [~] Starting Stealth Scanning (Sequential)..." . RESET . "\n");

while ($npage <= $npages && !$stopScanning) {
    fwrite(STDERR, YELLOW . "\r    [~] Scraping Page " . ceil($npage / 10) . " (Offset $npage)..." . RESET);

    $url = "https://www.bing.com/search?q=" . $do . "&first=" . $npage . "&FORM=PERE";
    $x = getRequest($url);

    if ($x) {
        // Block Detection
        if (strpos($x, 'CAPTCHA') !== false || strpos($x, 'challenge') !== false || strpos($x, 'id="b_content"') === false) {
            if (strpos($x, 'CAPTCHA') !== false || strpos($x, 'challenge') !== false) {
                fwrite(STDERR, RED . "\n    [!] CAPTCHA DETECTED! Results saved to " . DEBUG_FILE . RESET . "\n");
                file_put_contents(DEBUG_FILE, $x);
                $stopScanning = true;
                break;
            }
        }

        // Regex Strategies
        preg_match_all('/<h2><a href="(.*?)" h="ID/', $x, $findlink);
        if (empty($findlink[1])) {
            preg_match_all('/<li class="b_algo"><h2><a href="(.*?)"/', $x, $findlink);
        }
        if (empty($findlink[1])) {
            preg_match_all('/<h2><a href="([^"]+)"/', $x, $findlink);
        }

        if (!empty($findlink[1])) {
            $foundInPage = 0;
            foreach ($findlink[1] as $fl) {
                if (
                    strpos($fl, 'bing.com') === false &&
                    strpos($fl, 'microsoft.com') === false &&
                    strpos($fl, 'javascript:') === false
                ) {
                    $allLinks[] = $fl;
                    $foundInPage++;
                }
            }
        } else {
            // If headers are correct, usually blank results = end.
            fwrite(STDERR, RED . "\n    [!] No results found on this page. Stopping." . RESET . "\n");
            break;
        }
    } else {
        fwrite(STDERR, RED . "\n    [!] Network Error." . RESET . "\n");
        break;
    }

    $npage += 10;

    // Human-Like Delay
    $sleep = rand(DELAY_MIN, DELAY_MAX);
    if ($npage <= $npages) {
        // Don't sleep after the last page
        sleep($sleep);
    }
}

fwrite(STDERR, "\n" . GREEN . "    [+] Extraction Complete! Processing domains..." . RESET . "\n");

$URLs = [];
foreach ($allLinks as $url) {
    if (strpos($url, 'http') === 0) {
        $parsed_url = parse_url($url);
        if (isset($parsed_url['host'])) {
            $URLs[] = $parsed_url['host'];
        }
    }
}

$array = array_unique(array_filter($URLs));
$total = count($array);

fwrite(STDERR, CYAN . "    [OK] Total Unique Domains Found: " . BOLD . $total . RESET . "\n");
fwrite(STDERR, "    --------------------------------------------\n");

foreach ($array as $domain) {
    echo "http://$domain/\n";
}

// Cleanup
if (file_exists(COOKIE_FILE)) @unlink(COOKIE_FILE);
