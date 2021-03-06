<!-- DO NOT EDIT THIS FILE; it is auto-generated from readme.txt -->
# Mentionable

![Banner](assets/banner-1544x500.png)
Mention WordPress content with inline autocomplete inside tinyMCE.

**Contributors:** [jonathanbardo](http://profiles.wordpress.org/jonathanbardo), [topher1kenobe](http://profiles.wordpress.org/topher1kenobe), [shadyvb](http://profiles.wordpress.org/shadyvb), [westonruter](http://profiles.wordpress.org/westonruter), [x-team](http://profiles.wordpress.org/x-team)  
**Tags:** [tinyMCE](http://wordpress.org/plugins/tags/tinyMCE), [admin](http://wordpress.org/plugins/tags/admin), [mention](http://wordpress.org/plugins/tags/mention)  
**Requires at least:** 4.1  
**Tested up to:** 4.1  
**Stable tag:** trunk (master)  
**License:** [GPLv2 or later](http://www.gnu.org/licenses/gpl-2.0.html)  

## Description ##

This plugin brings the power of @mention inside tinyMCE. You can choose where the autocompletion gets his information from and on which custom post type this plugin is activated on. You can also create custom template replacement on the front-end based on your needs.

A review of the plugin is available on [WP Tavern](http://wptavern.com/mentionable-plugin-adds-mentions-for-wordpress-content-with-inline-autocomplete).

**Development of this plugin is done [on GitHub](https://github.com/x-team/wp-mentionable). Pull requests welcome. Please see [issues](https://github.com/x-team/wp-mentionable/issues) reported there before going to the plugin forum.**

[![Build Status](https://travis-ci.org/jonathanbardo/WP-Mentionable.png?branch=master)](https://travis-ci.org/jonathanbardo/WP-Mentionable)

## Installation ##

1. Upload `mentionable` to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Optional : create a template called mentionable.php in your theme directory and replace the @mention content with custom template.

## Screenshots ##

### Start typing "@" for the autocomplete to trigger

![Start typing "@" for the autocomplete to trigger](assets/screenshot-1.png)

### After pressing enter, the plugin replace your input with the right link and content

![After pressing enter, the plugin replace your input with the right link and content](assets/screenshot-2.png)

## Changelog ##

### 0.4.3 ###
* Add WordPress 4.3 compatibility

### 0.4.2 ###
* Add new filter for WP_Query autocomplete

### 0.4.1 ###
* Restore PHP 5.3 compatibility

### 0.4.0 ###
* Add option to open link in a new tab
* Change Styling to be closer with WP UI

### 0.3.0 ###
* Update for 4.1 Compatibility

### 0.2.1 ###
* Update for 3.9 Compatibility

### 0.2.0 ###
* Store reference to mentionable content inside post metas
* Add the ability to replace the custom content with a template name mentionable.php
* Add plugin banner
* Add french localization

### 0.1.0 ###
First Release


