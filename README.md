# mm2mc
MailMan to MailChimp (Stanford)

This application imports the membership of one or more Mailman list at Stanford into a MailChimp list using MailChimp's API.
Running this application more than once will import any new members found in the Mailman list.

It will not unsubscribe anyone from the MailChimp list, even if they have left the Mailman list.
This is mainly because it's not part of our use case in University IT right now.

The person or service running this code needs to be an admin of the mailman lists.

## Database

Create a simple one-table database to hold any email addresses that have already been seen.

    CREATE TABLE `email` (
      `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
      `email` varchar(255) DEFAULT NULL,
      `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`),
      UNIQUE KEY `email` (`email`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

## Installation

Download Composer

    curl -sS https://getcomposer.org/installer | php

Run Composer

    php composer.phar install

For Slack Integration, set up an incoming webhook: https://api.slack.com/incoming-webhooks

## Configuration

Copy app/config/config.yml.sample to a new /app/config/config.yml and enter your information.

## Run the application

Run the application by typing:

    php subscribers.php
