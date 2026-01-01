# 🕵️‍♂️ SingleBingDorker

![PHP Badge](https://img.shields.io/badge/Language-PHP-blue?style=for-the-badge&logo=php)
![License Badge](https://img.shields.io/badge/License-MIT-green?style=for-the-badge)
![Version Badge](https://img.shields.io/badge/Version-2.0-orange?style=for-the-badge)

**SingleBingDorker** is a powerful automated script to scrape URLs from Bing Search using custom Google Dorks. Designed for security researchers and penetration testers.

## ✨ Features

- 🚀 **Fast Scanning**: Optimized requests with curl.
- 🛡️ **Anti-Detection**: Random User-Agent rotation.
- 🎨 **Beautiful CLI**: Colorful ANSI output and progress indicators.
- 📂 **Pipe Support**: Easily pipe output to files.

## 📥 Installation

```bash
# Update repositories
apt update && apt upgrade -y

# Install Python and Pip
apt install python3 python3-pip -y

# Clone the repository
git clone https://github.com/hndko/singlebingdorker
cd singlebingdorker

# Install Python Dependencies
pip3 install -r requirements.txt

# Install Playwright Browsers
playwright install chromium
```

## 💻 Usage

Run the powerful Python script:

```bash
python3 bing_dorker.py "your_dork_here"
```

### Example

Save the output to a file:

```bash
python3 bing_dorker.py "inurl:/buy.php" > output.txt
```

## 📝 Notes

- **Python Version**: Much more powerful, uses a real browser engine to bypass CAPTCHAs.
- **PHP Version**: `bing.php` is available as a lightweight legacy alternative.
- Ensure you have a stable internet connection.
- Use responsibly.
- [Contact Developer](https://linktr.ee/doko1554)

## 📜 License

This project is for educational purposes only.

---

_Created with ❤️ by hndko_
