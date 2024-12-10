<?php
/*
Plugin Name: Vertex Wordpress Importer
Description: Import data as posts from a CSV file. <em>You can reach the author at <a href="mailto:bwitlawalyusuf@gmail.com">bwitlawalyusuf@gmail.com</a></em>.
Version: 0.0.1
Author: Lawal Yusuf
*/

class SitepressImporterPlugin
{

    var $defaults = array(
        'SiteID'      => null,
        'Content'       => null,
        'DateCreated'       => null,
        'CreatedBy'    => null,
        'ModifiedBy'       => null,
        'EditableAreaCount'       => null,
        'isDefault' => null,
        // 'ID'     => null,
        'ID'     => 0,
    );

    var $log = array();

    /**
     * Determine value of option $name from database, $default value or $params,
     * save it to the db if needed and return it.
     *
     * @param string $name
     * @param mixed  $default
     * @param array  $params
     * @return string
     */
    function process_option($name, $default, $params)
    {
        if (array_key_exists($name, $params)) {
            $value = stripslashes($params[$name]);
        } elseif (array_key_exists('_' . $name, $params)) {
            // unchecked checkbox value
            $value = stripslashes($params['_' . $name]);
        } else {
            $value = null;
        }
        $stored_value = get_option($name);
        if ($value == null) {
            if ($stored_value === false) {
                if (
                    is_callable($default) &&
                    method_exists($default[0], $default[1])
                ) {
                    $value = call_user_func($default);
                } else {
                    $value = $default;
                }
                add_option($name, $value);
            } else {
                $value = $stored_value;
            }
        } else {
            if ($stored_value === false) {
                add_option($name, $value);
            } elseif ($stored_value != $value) {
                update_option($name, $value);
            }
        }
        return $value;
    }

    /**
     * Plugin's interface
     *
     * @return void
     */
    function form()
    {
        $opt_draft = $this->process_option(
            'sitepress_importer_import_as_draft',
            'publish',
            $_POST
        );
        $opt_cat = $this->process_option('sitepress_importer_cat', 0, $_POST);

        if ('POST' == $_SERVER['REQUEST_METHOD']) {
            $this->post(compact('opt_draft', 'opt_cat'));
        }

        // form HTML {{{
?>

        <div class="wrap">
            <h2>Vertex Sitepress Content & Page Import</h2>
            <form method="post" enctype="multipart/form-data">
                <?php wp_nonce_field('vertex-wordpress-importer', '_sitepress_importer_nonce'); ?>

                <table class="form-table widefat wp-list-table">
                    <tbody>

                        <tr valign="top">
                            <td scope="row"><b>Select specific pages/templates</b></td>
                            <td>
                                <select name="sitepress_importer_page">
                                    <option value="">Select a page...</option>
                                    <?php
                                    // Fetch all pages, including unpublished
                                    $pages = get_pages(array(
                                        'sort_column' => 'post_title',
                                        'sort_order' => 'asc',
                                        'post_status' => array('publish', 'draft', 'pending', 'private', 'future') // Include all statuses
                                    ));

                                    if (!empty($pages)) {
                                        foreach ($pages as $page) {
                                            echo '<option value="' . esc_attr($page->ID) . '">';
                                            echo esc_html($page->post_title) . ' (' . esc_html(ucfirst($page->post_status)) . ')'; // Show status for clarity
                                            echo '</option>';
                                        }
                                    } else {
                                        echo '<option value="">No pages found</option>';
                                    }
                                    ?>
                                </select>
                                <br />
                                <small>This will associate new content with the selected page/template.</small>
                            </td>
                        </tr>



                        <!-- File input -->
                        <tr valign="top">
                            <td scope="row"><b>Upload Files</b></td>
                            <td>
                                <label for="csv_import"></label>
                                <input name="csv_import" id="csv_import" type="file" value="" aria-required="true" />
                                <br />
                                <small>
                                    <?php
                                    echo sprintf(
                                        __('You may want to see <a href="#" id="viewExamplesLink">example CSV import files</a>.', 'vertex-wordpress-importer')
                                    );
                                    ?>
                                </small>
                            </td>
                        </tr>

                        <!-- Submit button -->
                        <tr valign="top">
                            <td><input type="submit" class="button-primary" name="submit" value="Start Import" /></td>
                        </tr>
                    </tbody>
                </table>
            </form>
        </div><!-- end wrap -->


        <div id="exampleFilesModal" style="min-width:300px; display:none; position: fixed; top: 20%; left: 50%; transform: translate(-50%, -50%); background:#fff; padding:5px 20px 20px 20px; border-radius:5px; box-shadow: 0 0 15px rgba(0,0,0,.2); z-index: 1000;">
            <h2><?php _e('Vertex Sitepress Importer Sample Template ', 'vertex-wordpress-importer'); ?></h2>
            <ul>
                <?php
                $examples_dir = plugin_dir_path(__FILE__) . 'examples/';
                $examples_url = plugin_dir_url(__FILE__) . 'examples/';
                if ($handle = opendir($examples_dir)) {
                    while (false !== ($entry = readdir($handle))) {
                        if ($entry != "." && $entry != "..") {
                            echo '<li><a href="' . esc_url($examples_url . $entry) . '" download>' . esc_html($entry) . '</a></li>';
                        }
                    }
                    closedir($handle);
                }
                ?>
            </ul>
            <button id="closeModal" class="button" style="margin-top: 10px" ;>Close</button>
        </div>

        <script type="text/javascript">
            jQuery(document).ready(function($) {
                $('#viewExamplesLink').click(function(e) {
                    e.preventDefault(); // Prevent the default link behavior
                    $('#exampleFilesModal').fadeIn();
                });

                $('#closeModal').click(function() {
                    $('#exampleFilesModal').fadeOut();
                });
            });
        </script>

        <!-- </?php include 'includes/notice.php'; ?> -->

        <?php
        // end form HTML }}}

    }

    function print_messages()
    {
        if (!empty($this->log)) {

            // messages HTML {{{
        ?>

            <div class="wrap">
                <?php if (!empty($this->log['error'])): ?>

                    <div class="error">

                        <?php foreach ($this->log['error'] as $error): ?>
                            <p><strong><?php echo esc_html($error); ?></strong></p>
                        <?php endforeach; ?>

                    </div>

                <?php endif; ?>

                <?php if (!empty($this->log['notice'])): ?>

                    <div class="updated fade">

                        <?php foreach ($this->log['notice'] as $notice): ?>
                            <p><strong><?php echo esc_html($notice); ?></strong></p>
                        <?php endforeach; ?>

                    </div>

                <?php endif; ?>
            </div><!-- end wrap -->

<?php
            // end messages HTML }}}

            $this->log = array();
        }
    }

    /**
     * Handle POST submission
     *
     * @param array $options
     * @return void
     */

    function post($options)
    {
        if (!current_user_can('manage_options')) {
            die(esc_html_e('Security check', 'sitepress_importer'));
        }

        if (!isset($_POST['_sitepress_importer_nonce']) || !wp_verify_nonce($_POST['_sitepress_importer_nonce'], 'vertex-wordpress-importer')) {
            die(esc_html_e('Security check', 'sitepress_importer'));
        }

        if (empty($_FILES['csv_import']['tmp_name'])) {
            $this->log['error'][] = 'No file uploaded, aborting.';
            $this->print_messages();
            return;
        }

        if (!current_user_can('publish_pages') || !current_user_can('publish_posts')) {
            $this->log['error'][] = 'You don\'t have the permissions to publish posts and pages. Please contact the blog\'s administrator.';
            $this->print_messages();
            return;
        }

        require_once 'File_CSV_DataSource/DataSource.php';

        $time_start = microtime(true);
        $csv = new File_CSV_DataSource;
        $file = $_FILES['csv_import']['tmp_name'];
        $this->stripBOM($file);

        if (!$csv->load($file)) {
            $this->log['error'][] = 'Failed to load file, aborting.';
            $this->print_messages();
            return;
        }

        // Pad shorter rows with empty values
        $csv->symmetrize();

        // Set correct timezone
        $tz = get_option('timezone_string');
        if ($tz && function_exists('date_default_timezone_set')) {
            date_default_timezone_set($tz);
        }

        $skipped = 0;
        $imported = 0;
        $comments = 0;
        $imported_titles = []; // Store imported titles

        foreach ($csv->connect() as $csv_data) {
            if ($post_id = $this->create_post($csv_data, $options)) {
                $imported++;
                $comments += $this->add_comments($post_id, $csv_data);
                $this->create_custom_fields($post_id, $csv_data);

                // Get and store the title of the imported post/page
                $post_title = get_the_title($post_id);
                $imported_titles[] = $post_title ?: '(No Title)';
            } else {
                $skipped++;
            }
        }

        if (file_exists($file)) {
            @unlink($file);
        }

        $exec_time = microtime(true) - $time_start;

        // Notices for skipped and imported items
        if ($skipped) {
            $this->log['notice'][] = "Skipped {$skipped} pages (most likely due to empty Title, Content, and ModifiedBy Author/Admin).";
        }

        // $this->log['notice'][] = sprintf("Imported {$imported} pages and {$comments} comments in %.2f seconds.", $exec_time);

        // Add details about imported templates
        if (!empty($imported_titles)) {
            foreach ($imported_titles as $title) {
                $this->log['notice'][] = "✓ Imported Template: {$title}";
            }
        }

        // Total imported pages
        $this->log['notice'][] = sprintf("Progress: {$imported} Items processed in %.2f seconds.", $exec_time);

        $this->print_messages();
    }


    function create_post($data, $options)
    {
        $opt_draft = isset($options['opt_draft']) ? $options['opt_draft'] : null;
        $opt_cat = isset($options['opt_cat']) ? $options['opt_cat'] : null;

        $data = array_merge($this->defaults, $data);

        // Interpret `isDefault` as a boolean: 1 => 'page', 0 => 'post'
        $type = isset($data['isDefault']) && $data['isDefault'] == 1 ? 'page' : 'post';
        // $type = $data['isDefault'] ? $data['isDefault'] : 'post';
        $valid_type = (function_exists('post_type_exists') &&
            post_type_exists($type)) || in_array($type, array('post', 'page'));

        if (!$valid_type) {
            $this->log['error']["type-{$type}"] = sprintf(
                'Unknown post type "%s".',
                $type
            );
        }

        $new_post = array(
            'post_title'   => isset($data['Title']) ? convert_chars($data['Title']) : '(No Title)', // Default to '(No Title)' if missing
            'post_content' => wpautop(isset($data['Content']) ? convert_chars($data['Content']) : ''), // Default empty if missing
            'post_status'  => $opt_draft,
            'post_type'    => $type,
            'post_date'    => $this->parse_date(isset($data['DateCreated']) ? $data['DateCreated'] : ''),
            'post_excerpt' => isset($data['ModifiedBy']) ? convert_chars($data['ModifiedBy']) : '',
            'post_name'    => isset($data['SiteID']) ? $data['SiteID'] : '',
            'post_author'  => $this->get_auth_id(isset($data['CreatedBy']) ? $data['CreatedBy'] : ''),
            'tax_input'    => $this->get_taxonomies($data),
            'post_parent'  => isset($data['ID']) ? $data['ID'] : 0, // Default to 0 if missing
        );

        // pages don't have tags or categories
        if ('page' !== $type) {
            $new_post['tags_input'] = $data['SiteID'];

            $cats = $this->create_or_get_categories($data, $opt_cat);
            $new_post['post_category'] = $cats['post'];
        }

        // create!
        $id = wp_insert_post($new_post);

        if ('page' !== $type && !$id) {
            // cleanup new categories on failure
            foreach ($cats['cleanup'] as $c) {
                wp_delete_term($c, 'category');
            }
        }
        return $id;
    }

    /**
     * Return an array of category ids for a post.
     *
     * @param string  $data 
     * @param integer $common_parent_id common parent id for all categories
     * @return array category ids
     */
    function create_or_get_categories($data, $common_parent_id)
    {
        $ids = array(
            'post' => array(),
            'cleanup' => array(),
        );
        $items = array_map('trim', explode(',', $data['EditableAreaCount']));
        foreach ($items as $item) {
            if (is_numeric($item)) {
                if (get_category($item) !== null) {
                    $ids['post'][] = $item;
                } else {
                    $this->log['error'][] = "Category ID {$item} does not exist, skipping.";
                }
            } else {
                $parent_id = $common_parent_id;
                // item can be a single category name or a string such as
                // Parent > Child > Grandchild
                $categories = array_map('trim', explode('>', $item));
                if (count($categories) > 1 && is_numeric($categories[0])) {
                    $parent_id = $categories[0];
                    if (get_category($parent_id) !== null) {
                        // valid id, everything's ok
                        $categories = array_slice($categories, 1);
                    } else {
                        $this->log['error'][] = "Category ID {$parent_id} does not exist, skipping.";
                        continue;
                    }
                }
                foreach ($categories as $category) {
                    if ($category) {
                        $term = $this->term_exists($category, 'category', $parent_id);
                        if ($term) {
                            $term_id = $term['term_id'];
                        } else {
                            $term_id = wp_insert_category(array(
                                'cat_name' => $category,
                                'category_parent' => $parent_id,
                            ));
                            $ids['cleanup'][] = $term_id;
                        }
                        $parent_id = $term_id;
                    }
                }
                if (isset($term_id)) {
                    $ids['post'][] = $term_id;
                }
            }
        }
        return $ids;
    }

    /**
     * Parse taxonomy data from the file
     *
     * array(
     *      // hierarchical taxonomy name => ID array
     *      'my taxonomy 1' => array(1, 2, 3, ...),
     *      // non-hierarchical taxonomy name => term names string
     *      'my taxonomy 2' => array('term1', 'term2', ...),
     * )
     *
     * @param array $data
     * @return array
     */
    function get_taxonomies($data)
    {
        $taxonomies = array();
        foreach ($data as $k => $v) {
            if (preg_match('/^csv_ctax_(.*)$/', $k, $matches)) {
                $t_name = $matches[1];
                if ($this->taxonomy_exists($t_name)) {
                    $taxonomies[$t_name] = $this->create_terms(
                        $t_name,
                        $data[$k]
                    );
                } else {
                    $this->log['error'][] = "Unknown taxonomy $t_name";
                }
            }
        }
        return $taxonomies;
    }

    /**
     * Return an array of term IDs for hierarchical taxonomies or the original
     * string from CSV for non-hierarchical taxonomies. The original string
     * should have the same format as csv_post_tags.
     *
     * @param string $taxonomy
     * @param string $field
     * @return mixed
     */
    function create_terms($taxonomy, $field)
    {
        if (is_taxonomy_hierarchical($taxonomy)) {
            $term_ids = array();
            foreach ($this->_parse_tax($field) as $row) {
                list($parent, $child) = $row;
                $parent_ok = true;
                if ($parent) {
                    $parent_info = $this->term_exists($parent, $taxonomy);
                    if (!$parent_info) {
                        // create parent
                        $parent_info = wp_insert_term($parent, $taxonomy);
                    }
                    if (!is_wp_error($parent_info)) {
                        $parent_id = $parent_info['term_id'];
                    } else {
                        // could not find or create parent
                        $parent_ok = false;
                    }
                } else {
                    $parent_id = 0;
                }

                if ($parent_ok) {
                    $child_info = $this->term_exists($child, $taxonomy, $parent_id);
                    if (!$child_info) {
                        // create child
                        $child_info = wp_insert_term(
                            $child,
                            $taxonomy,
                            array('parent' => $parent_id)
                        );
                    }
                    if (!is_wp_error($child_info)) {
                        $term_ids[] = $child_info['term_id'];
                    }
                }
            }
            return $term_ids;
        } else {
            return $field;
        }
    }

    /**
     * Compatibility wrapper for WordPress term lookup.
     */
    function term_exists($term, $taxonomy = '', $parent = 0)
    {
        if (function_exists('term_exists')) { // 3.0 or later
            return term_exists($term, $taxonomy, $parent);
        } else {
            return is_term($term, $taxonomy, $parent);
        }
    }

    /**
     * Compatibility wrapper for WordPress taxonomy lookup.
     */
    function taxonomy_exists($taxonomy)
    {
        if (function_exists('taxonomy_exists')) { // 3.0 or later
            return taxonomy_exists($taxonomy);
        } else {
            return is_taxonomy($taxonomy);
        }
    }

    /**
     * Hierarchical taxonomy fields are tiny CSV files in their own right.
     *
     * @param string $field
     * @return array
     */
    function _parse_tax($field)
    {
        $data = array();
        if (function_exists('str_getcsv')) { // PHP 5 >= 5.3.0
            $lines = $this->split_lines($field);

            foreach ($lines as $line) {
                $data[] = str_getcsv($line, ',', '"');
            }
        } else {
            // Use temp files for older PHP versions. Reusing the tmp file for
            // the duration of the script might be faster, but not necessarily
            // significant.
            $handle = tmpfile();
            fwrite($handle, $field);
            fseek($handle, 0);

            while (($r = fgetcsv($handle, 999999, ',', '"')) !== false) {
                $data[] = $r;
            }
            fclose($handle);
        }
        return $data;
    }

    /**
     * Try to split lines of text correctly regardless of the platform the text
     * is coming from.
     */
    function split_lines($text)
    {
        $lines = preg_split("/(\r\n|\n|\r)/", $text);
        return $lines;
    }

    function add_comments($post_id, $data)
    {
        // First get a list of the comments for this post
        $comments = array();
        foreach ($data as $k => $v) {
            // comments start with cvs_comment_
            if (
                preg_match('/^csv_comment_([^_]+)_(.*)/', $k, $matches) &&
                $v != ''
            ) {
                $comments[$matches[1]] = 1;
            }
        }
        // Sort this list which specifies the order they are inserted, in case
        // that matters somewhere
        ksort($comments);

        // Now go through each comment and insert it. More fields are possible
        // in principle (see docu of wp_insert_comment), but I didn't have data
        // for them so I didn't test them, so I didn't include them.
        $count = 0;
        foreach ($comments as $cid => $v) {
            $new_comment = array(
                'comment_post_ID' => $post_id,
                'comment_approved' => 1,
            );

            if (isset($data["csv_comment_{$cid}_author"])) {
                $new_comment['comment_author'] = convert_chars(
                    $data["csv_comment_{$cid}_author"]
                );
            }
            if (isset($data["csv_comment_{$cid}_author_email"])) {
                $new_comment['comment_author_email'] = convert_chars(
                    $data["csv_comment_{$cid}_author_email"]
                );
            }
            if (isset($data["csv_comment_{$cid}_url"])) {
                $new_comment['comment_author_url'] = convert_chars(
                    $data["csv_comment_{$cid}_url"]
                );
            }
            if (isset($data["csv_comment_{$cid}_content"])) {
                $new_comment['comment_content'] = convert_chars(
                    $data["csv_comment_{$cid}_content"]
                );
            }
            if (isset($data["csv_comment_{$cid}_date"])) {
                $new_comment['comment_date'] = $this->parse_date(
                    $data["csv_comment_{$cid}_date"]
                );
            }

            $id = wp_insert_comment($new_comment);
            if ($id) {
                $count++;
            } else {
                $this->log['error'][] = "Could not add comment $cid";
            }
        }
        return $count;
    }

    function create_custom_fields($post_id, $data)
    {
        foreach ($data as $k => $v) {
            // anything that doesn't start with csv_ is a custom field
            if (!preg_match('/^csv_/', $k) && $v != '') {
                add_post_meta($post_id, $k, $v);
            }
        }
    }

    function get_auth_id($author)
    {
        if (is_numeric($author)) {
            return $author;
        }

        // get_userdatabylogin is deprecated as of 3.3.0
        if (function_exists('get_user_by')) {
            $author_data = get_user_by('login', $author);
        } else {
            $author_data = get_userdatabylogin($author);
        }

        return ($author_data) ? $author_data->ID : 0;
    }

    /**
     * Convert date in CSV file to 1999-12-31 23:52:00 format
     *
     * @param string $data
     * @return string
     */
    function parse_date($data)
    {
        $timestamp = strtotime($data);
        if (false === $timestamp) {
            return '';
        } else {
            return date('Y-m-d H:i:s', $timestamp);
        }
    }

    /**
     * Delete BOM from UTF-8 file.
     *
     * @param string $fname
     * @return void
     */
    function stripBOM($fname)
    {
        $res = fopen($fname, 'rb');
        if (false !== $res) {
            $bytes = fread($res, 3);
            if ($bytes == pack('CCC', 0xef, 0xbb, 0xbf)) {
                $this->log['notice'][] = 'Getting rid of byte order mark...';
                fclose($res);

                $contents = file_get_contents($fname);
                if (false === $contents) {
                    trigger_error('Failed to get file contents.', E_USER_WARNING);
                }
                $contents = substr($contents, 3);
                $success = file_put_contents($fname, $contents);
                if (false === $success) {
                    trigger_error('Failed to put file contents.', E_USER_WARNING);
                }
            } else {
                fclose($res);
            }
        } else {
            $this->log['error'][] = 'Failed to open file, aborting.';
        }
    }
}

if (!defined('ABSPATH')) {
    die;
}

function csv_admin_menu()
{
    require_once ABSPATH . '/wp-admin/admin.php';
    $plugin = new SitepressImporterPlugin;
    add_management_page(
        'edit.php',
        'Vertex Wordpress Importer',
        'manage_options',
        __FILE__,
        array($plugin, 'form')
    );
}

add_action('admin_menu', 'csv_admin_menu');
add_action('admin_enqueue_scripts', 'enqueue_assets');

function enqueue_assets($hook)
{
    if ('tools_page_vertex-wordpress-importer/sitepress_importer' !== $hook) {
        return;
    }
    wp_enqueue_style('import-users-from-csv-css', plugin_dir_url(__FILE__) . 'includes/assets/notice.css');
}
