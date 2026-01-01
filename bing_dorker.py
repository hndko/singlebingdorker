import asyncio
import sys
import random
import urllib.parse
from playwright.async_api import async_playwright
from colorama import Fore, Style, init

# Initialize Colorama
init(autoreset=True)

# Configuration
TOTAL_PAGES = 50   # How many pages to scrape (approx. 500 results)
MIN_DELAY = 1000   # ms
MAX_DELAY = 3000   # ms

def banner():
    logo = f"""{Fore.CYAN}
    ██████╗ ██╗███╗   ██╗ ██████╗
    ██╔══██╗██║████╗  ██║██╔════╝
    ██████╔╝██║██╔██╗ ██║██║  ███╗
    ██╔══██╗██║██║╚██╗██║██║   ██║
    ██████╔╝██║██║ ╚████║╚██████╔╝
    ╚═════╝ ╚═╝╚═╝  ╚═══╝ ╚═════╝
    {Style.RESET_ALL}
    {Fore.YELLOW}🔥 Auto Dorker Bing Single (Python Power) 🔥{Style.RESET_ALL}
    {Fore.GREEN}👨‍💻 Created By : hndko{Style.RESET_ALL}
    {Fore.BLUE}🚀 Powered By : Playwright (Anti-Detect){Style.RESET_ALL}
    --------------------------------------------
    """
    sys.stderr.write(logo)

def usage():
    sys.stderr.write(f"{Fore.RED}[!] Usage: python3 bing_dorker.py \"your dork\"\n")
    sys.stderr.write(f"{Fore.YELLOW}[?] Ex   : python3 bing_dorker.py \"site:gov.id\"\n")
    sys.exit(1)

def print_log(msg, color=Fore.WHITE):
    sys.stderr.write(f"{color}{msg}{Style.RESET_ALL}\n")

async def scrape_bing(dork):
    async with async_playwright() as p:
        # Launch Browser (Headless = True for speed, False to see it working)
        # Using a persistent context or adding args to mimic real chrome
        browser = await p.chromium.launch(headless=True, args=[
            '--disable-blink-features=AutomationControlled',
            '--no-sandbox',
            '--disable-setuid-sandbox'
        ])

        # Create a new context with a realistic user agent
        context = await browser.new_context(
            user_agent='Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            viewport={'width': 1280, 'height': 800}
        )

        # Open new page
        page = await context.new_page()

        # Stealth scripts to hide webdriver property
        await page.add_init_script("""
            Object.defineProperty(navigator, 'webdriver', {
                get: () => undefined
            });
        """)

        print_log(f"    [+] Target Dork : {dork}", Fore.GREEN)
        print_log("    [~] Initializing Browser Session...", Fore.BLUE)

        # 1. Visit Bing Homepage first to set cookies
        try:
            await page.goto("https://www.bing.com", timeout=30000)
            await page.wait_for_timeout(random.randint(2000, 4000))
        except Exception as e:
            print_log(f"    [!] Error loading homepage: {e}", Fore.RED)

        # 2. Perform Search
        encoded_dork = urllib.parse.quote(dork)
        current_page = 1
        unique_domains = set()

        # Loop through pages
        for i in range(TOTAL_PAGES):
            offset = i * 10 + 1
            search_url = f"https://www.bing.com/search?q={encoded_dork}&first={offset}&FORM=PERE"

            print_log(f"    [~] Scraping Page {i+1}...", Fore.YELLOW)

            try:
                await page.goto(search_url, wait_until='domcontentloaded')

                # Random interaction (mouse move/scroll)
                await page.mouse.move(random.randint(100, 500), random.randint(100, 500))

                # Check for captcha
                content = await page.content()
                if "challenge-form" in content or "Use a different computer" in content:
                    print_log("    [!] CAPTCHA/Block Detected! Stopping...", Fore.RED)
                    break

                # Extract Links
                # Bing results are usually in <li class="b_algo"> <h2> <a href="...">
                # Or just generally valid links in results area
                links = await page.evaluate("""
                    () => {
                        const anchors = Array.from(document.querySelectorAll('li.b_algo h2 a'));
                        return anchors.map(a => a.href);
                    }
                """)

                if not links:
                    # Fallback selector
                    links = await page.evaluate("""
                        () => {
                            const anchors = Array.from(document.querySelectorAll('h2 a'));
                            return anchors.map(a => a.href);
                        }
                    """)

                if not links:
                    print_log("    [!] No results found on this page. Might be end of results.", Fore.RED)
                    break

                # Filter and Add
                batch_count = 0
                for link in links:
                    if "microsoft.com" in link or "bing.com" in link or "javascript:" in link:
                        continue

                    try:
                        parsed = urllib.parse.urlparse(link)
                        if parsed.scheme and parsed.netloc:
                            domain = f"{parsed.scheme}://{parsed.netloc}"
                            if domain not in unique_domains:
                                unique_domains.add(domain)
                                batch_count += 1
                    except:
                        continue

                # Check if "Next" button exists (sb_pagN)
                next_btn = await page.query_selector('.sb_pagN')
                if not next_btn and len(links) < 5:
                     # Very few links and no next button usually means end
                     print_log("    [!] End of results reached.", Fore.MAGENTA)
                     break

                # Wait random time
                delay = random.randint(MIN_DELAY, MAX_DELAY)
                await page.wait_for_timeout(delay)

            except Exception as e:
                print_log(f"    [!] Error: {e}", Fore.RED)
                break

        await browser.close()

        # Output Results
        print_log("\n    [+] Extraction Complete!", Fore.GREEN)
        print_log(f"    [OK] Total Unique Domains Found: {len(unique_domains)}", Fore.CYAN)
        print_log("    --------------------------------------------")

        for dom in unique_domains:
            print(dom) # Prints to STDOUT for piping

if __name__ == "__main__":
    banner()
    if len(sys.argv) < 2:
        usage()

    dork_arg = sys.argv[1]
    asyncio.run(scrape_bing(dork_arg))
