<?php
require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

function wup_run_migration(){
  global $wpdb;

  $migrations = array(
    '0.0.0' => array(
      'migration' => 'CREATE TABLE ' . $wpdb->prefix . 'wup_packages (
        `id` INT NOT NULL AUTO_INCREMENT ,
        `name` VARCHAR(255) NOT NULL ,
        `slug` VARCHAR(255) NOT NULL ,
        PRIMARY KEY (`id`),
        UNIQUE (`slug`)
      ) ENGINE = InnoDB;',
      'post' => '0.0.1'
    ),
    '0.0.1' => array(
      'migration' => 'CREATE TABLE ' . $wpdb->prefix . 'wup_packages (
        `id` INT NOT NULL AUTO_INCREMENT ,
        `name` VARCHAR(255) NOT NULL ,
        `slug` VARCHAR(255) NOT NULL ,
        `deployKey` VARCHAR(64) NOT NULL ,
        PRIMARY KEY (`id`),
        UNIQUE (`slug`),
        UNIQUE (`deployKey`)
      ) ENGINE = InnoDB;',
      'post' => '0.0.2'
    ),
    '0.0.2' => array(
      'migration' => 'CREATE TABLE ' . $wpdb->prefix . 'wup_versions (
        `id` INT NOT NULL AUTO_INCREMENT ,
        `packageId` INT NOT NULL ,
        `version` VARCHAR(11) NOT NULL ,
        `releaseDate` TIMESTAMP NOT NULL ,
        `pluginData` TEXT NOT NULL ,
        PRIMARY KEY (`id`)
        ) ENGINE = InnoDB;',
      'post' => '0.0.3'
    ),
    '0.0.3' => array(
      'migration' => 'CREATE TABLE ' . $wpdb->prefix . 'wup_domains (
        `id` INT NOT NULL AUTO_INCREMENT ,
        `packageId` INT NOT NULL ,
        `domain` VARCHAR(255) NOT NULL ,
        `version` VARCHAR(10) NOT NULL ,
        PRIMARY KEY (`id`)
      ) ENGINE = InnoDB;',
      'post' => '0.0.4'
    ),
    '0.0.4' => false
  );

  $currentVersion = get_site_option('wup_db_version');
  
  if(strlen($currentVersion) < 4){
    $currentVersion = "0.0.0";
  }

  if($migrations[$currentVersion] === false){
    return;
  }

  dbDelta($migrations[$currentVersion]['migration']);

  update_site_option('wup_db_version', $migrations[$currentVersion]['post']);
  wup_run_migration();
}