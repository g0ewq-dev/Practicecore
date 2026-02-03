# PracticeCore

Modern PocketMine-MP practice core built for public Bedrock servers. PracticeCore provides duels, parties, bot fights, leaderboards, multiple game modes, and a custom form API — using the latest PocketMine API.

## Features

* Player duels with multiple modes
* Party system with party invites
* Bot duels with difficulty selection
* Shared arena system for all duels
* BedFight and Sumo game modes
* Elo, kills, and deaths leaderboards
* Custom reusable Form API
* Hub system (`/sethub`, `/hub`)
* Clean and scalable source layout

## Duel Modes

* Boxing
* Sumo
* NoDebuff
* Fist Fight
* BedFight

These modes are supported in:

* Player duels
* Bot duels

## Commands

### General

* `/duel` – Open duel UI
* `/duel <player>` – Duel a specific player
* `/party` – Open party menu
* `/botduel` – Open bot duel menu
* `/leaderboard` – View top 10 stats
* `/sethub` – Set hub location
* `/hub` – Teleport to hub

### Arena Setup

* `/setarena <mode> <1|2>` – Set duel arena spawns
* `/setarena bedfight 1`
* `/setarena sumo 2`

## Party System

* Create parties
* Invite players to parties
* Join via invite

## Leaderboards

Tracks and displays:

* Elo
* Kills
* Deaths

Top 10 players are shown per category.

## Custom Form API

PracticeCore includes an internal form API used across:

* Duels
* Parties
* Bot duels

Designed to be reusable and easy to extend.

## Requirements

* PocketMine-MP (latest version)
* PHP 8.1+

## Installation

1. Download or clone this repository
2. Place the plugin folder into your server's `plugins` directory
3. Start or restart your server

## Author

Created by **zqxhyt**

## License

This project is open-source and intended for public server use.
