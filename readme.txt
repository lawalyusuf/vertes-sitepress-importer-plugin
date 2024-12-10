=== Vertex Wordpress Importer ===
Tags: template, page-content, import, content, excel
Requires at least: 3.1
Requires PHP: 8.2
Tested up to: 6.7.1

Import templtes posts/page from CSV files into WordPress.


== Description ==

This plugin imports content from Sitepress template.csv files into wordpress post and pages 

= Features =

*   WordPress admin page under Tools menu.
*   File upload fields for templates.csv.
*   Import all or select specific pages/templates.
*   Show progress during import.
*   Display errors if they occur>
*   Convert template markup to WordPress content.
*   Basic handling of template variables.
*   Pages created with hierarchical structure
*   Basic content preserved
*   Metadata (titles, descriptions) imported
*   Creation/modification dates maintained
*   Columns in the CSV file can be in any order, provided that they have correct headings
*   Multi-language support

== TODO ==

*   relationships and connect with page-content.csv 
*   Stores content in XML format with content areas
*   Has fields: ID, SiteID, ParentPageID, PageName, PageURL, etc.
*   A basic hierarchical page structure that preserves the content relationships from the source data and import directly into Template content area

== Screenshots ==

1.  Plugin interface



== Installation ==

Installing the plugin:

1.  Unzip the plugin's directory into `wp-content/plugins`.
1.  Activate the plugin through the 'Plugins' menu in WordPress.
1.  The plugin will be available under Tools -> Vertex Wordpress Importer on WordPress administration page.


== Usage ==

Click on the Vertex Wordpress Importer link on your WordPress admin page, choose the file you would like to import (template.csv) and click Import. 
The `examples` directory inside the plugin's directory contains sample files that demonstrate how to use the plugin. The best way to get started is to import the sample files and look at the results.


CSV is a tabular format that consists of rows and columns. Each row in a CSV file represents a post; each column identifies a piece of information that comprises a post.

= Basic post/page information =

    TODO
