
<?php
/*
 * Plugin Name:       filtering docs
 * Description:       Handles the filtering of docs.
 * Version:           1.0
 * Requires PHP:      8.0
 * Author:            Prakhar Satyam
 * Author URI:        https://blog-livid-alpha.vercel.app/
 */


add_action('admin_menu', 'csv_filter_table_menu');

function csv_filter_table_menu() {
    add_menu_page('CSV Filter Table', 'CSV Filter Table', 'manage_options', 'csv-filter-table', 'csv_filter_table_page');
}

function csv_filter_table_page() {
    ?>
    <div class="wrap">
        <h2>Upload CSV File</h2>
        <form method="post" enctype="multipart/form-data">
            <input type="file" name="csv_file" />
            <input type="submit" name="upload_csv" value="Upload" class="button button-primary" />
        </form>
        <?php handle_csv_upload(); ?>
    </div>
    <?php
}

function handle_csv_upload() {
    if (isset($_POST['upload_csv'])) {
        if (isset($_FILES['csv_file']) && $_FILES['csv_file']['error'] == 0) {
            $file = $_FILES['csv_file'];
            $upload_dir = wp_upload_dir();
            $upload_path = $upload_dir['path'] . '/' . basename($file['name']);
            if (move_uploaded_file($file['tmp_name'], $upload_path)) {
                update_option('csv_filter_table_file', $upload_path);
                echo "<div class='updated'><p>File uploaded successfully!</p></div>";
            } else {
                echo "<div class='error'><p>File upload failed!</p></div>";
            }
        } else {
            echo "<div class='error'><p>No file uploaded or there was an upload error!</p></div>";
        }
    }
}

function csv_filter_table_shortcode() {
    $file = get_option('csv_filter_table_file');

    if (!$file || !file_exists($file)) {
        return "<p>CSV file not found! Please upload a CSV file.</p>";
    }

    ob_start();
    include plugin_dir_path(__FILE__) . 'template.php';
    return ob_get_clean();
}
add_shortcode('csv_filter_table', 'csv_filter_table_shortcode');
