# Vertex WordPress Importer

**Tags**: template, page-content, import, content, excel  
**Requires at least**: 3.1  
**Requires PHP**: 8.2  
**Tested up to**: 6.7.1  

Import templates, posts, and pages from CSV files into WordPress.

---

## Description

This plugin imports content from Sitepress `template.csv` files into WordPress posts and pages.

---

## Features

- WordPress admin page under Tools menu.
- File upload fields for `templates.csv`.
- Import all or select specific pages/templates.
- Show progress during import.
- Display errors if they occur.
- Convert template markup to WordPress content.
- Basic handling of template variables.
- Pages created with hierarchical structure.
- Basic content preserved.
- Metadata (titles, descriptions) imported.
- Creation/modification dates maintained.
- Columns in the CSV file can be in any order, provided they have correct headings.
- Multi-language support.

---

## TODO

- Establish relationships and connect with `page-content.csv`.
- Store content in XML format with content areas.
- Fields include: `ID`, `SiteID`, `ParentPageID`, `PageName`, `PageURL`, etc.
- A basic hierarchical page structure that preserves the content relationships from the source data and imports directly into the template content area.

---

## Screenshots

1. Plugin interface:

    https://raw.githubusercontent.com/lawalyusuf/vertex-sitepress-importer-plugin/main/screenshot/vertex-img1.png

    https://raw.githubusercontent.com/lawalyusuf/vertex-sitepress-importer-plugin/main/screenshot/vertex-img2.png

    https://raw.githubusercontent.com/lawalyusuf/vertex-sitepress-importer-plugin/main/screenshot/vertex-img3.png

    https://raw.githubusercontent.com/lawalyusuf/vertex-sitepress-importer-plugin/main/screenshot/vertex-img4.png

    https://raw.githubusercontent.com/lawalyusuf/vertex-sitepress-importer-plugin/main/screenshot/vertex-img5.png

    https://raw.githubusercontent.com/lawalyusuf/vertex-sitepress-importer-plugin/main/screenshot/vertex-img6.png
        
    
---

## Installation

### Installing the Plugin:
1. Unzip the plugin's directory into `wp-content/plugins`.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. The plugin will be available under **Tools → Vertex WordPress Importer** on the WordPress administration page.

---

## Usage

1. Click on the **Vertex WordPress Importer** link on your WordPress admin page.
2. Choose the file you would like to import (`template.csv`) and click **Import**.
3. Check the results.

The `examples` directory inside the plugin's folder contains sample files to demonstrate how to use the plugin. The best way to get started is to import the sample files and observe the output.

### CSV Structure

CSV is a tabular format consisting of rows and columns. Each row represents a post, and each column contains specific information about that post.

---

## Post/Page Information

**TODO**
