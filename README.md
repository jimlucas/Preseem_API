Preseem API via PHP
===================

## Announcement Preseem API (June 2020)

I am happy to announce the release of the initial version of my Preseem API powered by PHP.

## Features

Via the Preseem API interface
- Setup Account
- Setup Service and link with Account

## Installation

For this repo to work correctly, you will need a few PHP packages installed.  At the time of this writing, the following packages are needed.

  apt install php7.0-cli php7.0-common php7.0-curl php7.0-json php7.0-opcache php7.0-readline php7.0-xmlrpc

## Getting Started

This repo requires a webserver with PHP that has access to the internet.

To setup the configuration for the scripts to work correctly

  cp ./config/config.example.php ./config/config.local.php

The most important item that needs set is your Preseem API key.  Edit the new ./config/config.local.php and input your Preseem API key

You will also need to create the logs folder.

  mkdir ./logs/

Make sure that the logs folder is read and writable by the user you are issuing the export script as.

## Contributing

I would love to get input from all developers out there.

