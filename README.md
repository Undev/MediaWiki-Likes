MediaWiki-Likes
===============

Native Like-button on every page

# Installation

## Cloning
git clone into your extensions folder:

__git clone https://github.com/Undev/MediaWiki-Likes.git__

## LocalSettings
Add the following line to LocalSettings.php

__require_once "$IP/extensions/MediaWiki-Likes/Likes.php";__

## Database Upgrade
From within the wiki install folder on the server run the following:

__php maintenance/update.php__
