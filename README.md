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

# Install dependencies
apt install php curl git -y

# Clone the repository
git clone https://github.com/kyo1337/singlebingdorker
cd singlebingdorker
```

## 💻 Usage

Run the script using PHP CLI.

```bash
php bing.php "your_dork_here"
```

### Example

Save the output to a file:

```bash
php bing.php "inurl:/buy.php" > output.txt
```

## 📝 Notes

- Ensure you have a stable internet connection.
- Use responsibly.
- [Contact Developer](https://linktr.ee/doko1554)

## 📜 License

This project is for educational purposes only.

---

_Created with ❤️ by Kyuoko_
