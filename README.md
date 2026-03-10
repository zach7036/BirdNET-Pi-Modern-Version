<h1 align="center"><a href="https://github.com/mcguirepr89/BirdNET-Pi/blob/main/LICENSE">Review the license!!</a></h1>
<h1 align="center">You may not use BirdNET-Pi to develop a commercial product!!!!</h1>
<h1 align="center">
  BirdNET-Pi: Modern Version
</h1>
<p align="center">
A realtime acoustic bird classification system for the Raspberry Pi 5, 4B, 400, 3B+, and 0W2
</p>
<p align="center">
  <img src="https://user-images.githubusercontent.com/60325264/140656397-bf76bad4-f110-467c-897d-992ff0f96476.png" />
</p>

## About This Modern Version
This repository is an enhanced, fully modernized version of BirdNET-Pi, built on top of the excellent backend foundations laid by [Nachtzuster](https://github.com/Nachtzuster/BirdNET-Pi) and the original creator [mcguirepr89](https://github.com/mcguirepr89/BirdNET-Pi). 

This version introduces a complete overhaul of the user interface to bring the system inline with modern web design standards, along with highly requested functional improvements.

### Key Modernizations & Features:
- **Beautiful, Modern UI:** A complete visual overhaul featuring responsive card-based layouts, sleek rounded corners, clean shadow effects, and a cohesive indigo/slate color palette.
- **Enhanced Recording Views:** "Best Recordings", "Today's Detections", and "Species Detail" pages are now polished, easy-to-read grids.
- **Upgraded eBird Export:** Generate perfectly formatted CSV checklists for eBird. Features include a new Date Picker, built-in interactive `ⓘ` help tooltips, automated data validation, and exact alignment with eBird's "Record Format (Extended)" requirements.
- **Improved Charting:** Refined Daily and Seasonal charts with cleaner lines, adjusted opacities for a better visual hierarchy, and smoother interactions.
- All the robust backend improvements from Nachtzuster's fork included: Bookworm support, faster TFLite 2.17.1, refined daemon scripts, and improved Backup & Restore.

## Installation
The system can be installed with a single command designed for a fresh OS installation:
```
curl -s https://raw.githubusercontent.com/zach7036/BirdNET-Pi-Modern-Version/main/newinstaller.sh | bash
```
*Note: Make sure your SD Card is imaged with a 64-bit version of RaspiOS (Bookworm or Trixie).*

## Migrating from previous forks
If you already have a working installation from an older BirdNET-Pi fork and want to upgrade to this modern UI without losing your database and audio data:
```
git remote remove origin
git remote add origin https://github.com/zach7036/BirdNET-Pi-Modern-Version.git
./scripts/update_birdnet.sh
```

## Features Deep Dive
* **24/7 recording and automatic identification** of bird songs, chirps, and peeps using BirdNET machine learning
* **Automatic extraction and cataloguing** of bird clips from full-length recordings
* **Tools to visualize your recorded bird data** and analyze trends
* **Live audio stream and spectrogram**
* **Automatic disk space management** that periodically purges old audio files
* [BirdWeather](https://app.birdweather.com) integration
* Web interface access to all data and logs provided by [Caddy](https://caddyserver.com)
* SQLite3 Database, [Adminer](https://www.adminer.org/) database maintenance, and FTP server included
* [Apprise Notifications](https://github.com/caronc/apprise) supporting 90+ notification platforms

### Internationalization:
The bird names are in English by default, but other localized versions are available thanks to the efforts of [@patlevin](https://github.com/patlevin). Use the web interface's "Tools" > "Settings" and select your "Database Language" to have detections translated natively.

---
_Original BirdNET framework by [@kahst](https://github.com/kahst). Pre-built TFLite binaries by [@PINTO0309](https://github.com/PINTO0309)._
