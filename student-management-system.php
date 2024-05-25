<?php
/*
Plugin Name: Student Management System
Description: A plugin for managing student records.
Version: 1.0
Author: Akila
*/

// Create the database table on plugin activation
function create_student_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'students';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        name varchar(100) NOT NULL,
        phone varchar(20) NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
register_activation_hook(__FILE__, 'create_student_table');

// Add "Students" menu item in WordPress admin dashboard
function sm_student_menu() {
    add_menu_page('Student Management', 'Students', 'manage_options', 'sm-students', 'sm_student_page');
}
add_action('admin_menu', 'sm_student_menu');

// Display student management page
function sm_student_page() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'students';

    $message = '';

    // Check if the form is submitted
    if (isset($_POST['submit_student'])) {
        $name = sanitize_text_field($_POST['name']);
        $phone = sanitize_text_field($_POST['phone']);

        if (isset($_POST['student_id'])) {
            $student_id = intval($_POST['student_id']);
            $wpdb->update(
                $table_name,
                array(
                    'name' => $name,
                    'phone' => $phone
                ),
                array('id' => $student_id)
            );
            $message = 'Student updated successfully!';
        } else {
            // Insert the data into the database
            $wpdb->insert(
                $table_name,
                array(
                    'name' => $name,
                    'phone' => $phone
                )
            );

            // Display success message
            $message = 'Student added successfully!';
        }
    }

    // Check if the delete action is triggered
    if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['student_id'])) {
        $student_id = intval($_GET['student_id']);
        $wpdb->delete($table_name, array('id' => $student_id));
        $message = 'Student deleted successfully!';
    }

    // Fetch all students from the database
    $students = $wpdb->get_results("SELECT * FROM $table_name");

    // Display form for adding/editing students
    ?>
    <div class="wrap">
        <!-- <h1 class="wp-heading-inline">Student Management</h1> -->
        <hr class="wp-header-end">

        <?php if ($message) : ?>
            <div class="updated"><p><?php echo esc_html($message); ?></p></div>
        <?php endif; ?>

        <!-- <h2>Add/Edit Student</h2> -->
        <!-- <form method="post" action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>"> -->
        <form method="post" action="">
            <?php if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['student_id'])) : ?>
                <?php $student_id = intval($_GET['student_id']); ?>
                <input type="hidden" name="student_id" value="<?php echo $student_id; ?>">
                <h3>Edit Student</h3>
            <?php else : ?>
                <h3>Add New Student</h3>
            <?php endif; ?>
            <table class="form-table">
                <tr>
                    <th scope="row">Name:</th>
                    <td><input type="text" name="name" value="<?php echo isset($student_id) ? $wpdb->get_var("SELECT name FROM $table_name WHERE id = $student_id") : ''; ?>" required></td>
                </tr>
                <tr>
                    <th scope="row">Phone Number:</th>
                    <td><input type="text" name="phone" value="<?php echo isset($student_id) ? $wpdb->get_var("SELECT phone FROM $table_name WHERE id = $student_id") : ''; ?>" required></td>
                </tr>
            </table>
            <p class="submit">
                <input type="submit" name="submit_student" class="button-primary" value="<?php echo isset($student_id) ? 'Save Changes' : 'Add Student'; ?>">
            </p>
        </form>

        <hr>

        <h2>Students List</h2>
        <table class="wp-list-table widefat striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Phone</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($students as $student) : ?>
                    <tr>
                        <td><?php echo $student->id; ?></td>
                        <td><?php echo $student->name; ?></td>
                        <td><?php echo $student->phone; ?></td>
                        <td>
                            <a href="<?php echo admin_url('admin.php?page=sm-students&action=edit&student_id=' . $student->id); ?>">Edit</a> |
                            <a href="<?php echo admin_url('admin.php?page=sm-students&action=delete&student_id=' . $student->id); ?>" onclick="return confirm('Are you sure you want to delete this student?')">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
}

// Add shortcode for displaying students management system
function display_students_management() {
    ob_start();
    sm_student_page();
    return ob_get_clean();
}
add_shortcode('students_management', 'display_students_management');
