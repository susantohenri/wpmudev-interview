<?php

class WPMUInterview
{
    protected static $wpdb;
    protected static $table = 'thing';

    function __construct()
    {
        global $wpdb;
        self::$wpdb = $wpdb;
        self::$table = self::$wpdb->prefix . self::$table;
        session_start();
    }

    public static function createTable()
    {
        $table_name = self::$table;
        self::$wpdb->query("
            CREATE TABLE `{$table_name}` (
                `id` int(11) NOT NULL,
                `name` varchar(255) NOT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ");
        self::$wpdb->query("ALTER TABLE `{$table_name}` ADD PRIMARY KEY (`id`)");
        self::$wpdb->query("ALTER TABLE `{$table_name}` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT");
    }

    public static function dropTable()
    {
        $table_name = self::$table;
        self::$wpdb->query("DROP TABLE {$table_name}");
    }

    public static function form()
    {
        if (isset($_POST['create_thing'])) self::insert();
        else if (isset($_POST['read_thing'])) return self::read();
        else if (isset($_POST['update_thing'])) self::update();
        else if (isset($_POST['delete_thing'])) self::delete();
        return self::create();
    }

    protected static function create()
    {
        $csrf_token = md5(rand(0, 10000000)) . time();
        $_SESSION['csrf_token'] = $csrf_token;
        return "
            <form method='POST'>
                <input type='hidden' name='csrf_token' value='{$csrf_token}'>
                <label for='name'>name</label>
                <input type='text' name='thing_name'>
                <br>
                <input type='submit' name='create_thing' value='create'>
                <input type='reset' value='cancel'>
            </form>
        ";
    }

    protected static function insert()
    {
        if ($_SESSION['csrf_token'] === $_POST['csrf_token']) {
            self::$wpdb->insert(self::$table, [
                'name' => sanitize_text_field($_POST['thing_name']),
            ], ['%s']);
            $_SESSION['csrf_token'] = null;
        }
    }

    protected static function read()
    {
        $table_name = self::$table;
        $things = self::$wpdb->get_results(self::$wpdb->prepare("SELECT * FROM {$table_name} WHERE id = %d", sanitize_text_field($_POST['id'])));
        $thing = $things[0];
        $csrf_token = md5(rand(0, 10000000)) . time();
        $_SESSION['csrf_token'] = $csrf_token;
        return "
            <form method='POST'>
                <input type='hidden' name='csrf_token' value='{$csrf_token}'>
                <input type='hidden' name='id' value='{$thing->id}'>
                <label for='name'>name</label>
                <input type='text' name='thing_name' value='{$thing->name}'>
                <br>
                <input type='submit' name='update_thing' value='update'>
                <a href=''>cancel</a>
            </form>
        ";
    }

    protected static function update()
    {
        if ($_SESSION['csrf_token'] === $_POST['csrf_token']) {
            self::$wpdb->update(self::$table, [
                'name' => sanitize_text_field($_POST['thing_name']),
            ], [
                'id' => sanitize_text_field($_POST['id'])
            ], ['%s'], ['%d']);
            $_SESSION['csrf_token'] = null;
        }
    }

    protected static function delete()
    {
        self::$wpdb->delete(self::$table, ['id' => $_POST['id']]);
    }

    public static function list()
    {
        return "
            <table border='1' width='100%' name='table_thing'>
                <thead>
                    <tr>
                        <th colspan='2'>
                            <form method='POST'>
                                <input type='text' name='keyword' placeholder='search thing'>
                                <button name='search_button'>search</button>
                            </form>
                        </th>
                    </tr>
                    <tr>
                        <th>
                            name
                            <select name='order'>
                                <option value='asc'>asc</option>
                                <option value='desc'>desc</option>
                            </select>
                        </th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
                <tfoot>
                <tr>
                    <th colspan='2'>
                        <label for='perpage'>per page</label>
                        <select name='perpage'>
                            <option value='3'>3</option>
                            <option value='4'>4</option>
                            <option value='5'>5</option>
                        </select>
                        <label for='page'>page</label>
                        <select name='page'>
                            <option value='1' selected>1</option>
                        </select>
                    </th>
                </tr>
                </tfoot>
            </table>
        ";
    }

    public static function tbody()
    {
        $table_name = self::$table;
        $input = [
            'keyword' => sanitize_text_field($_POST['keyword']),
            'order' => sanitize_text_field($_POST['order']),
            'perpage' => sanitize_text_field($_POST['perpage']),
            'page' => sanitize_text_field($_POST['page']),
        ];
        $input['offset'] = ($input['page'] - 1) * $input['perpage'];

        $query = "SELECT * FROM {$table_name}";
        if (!empty($input['keyword'])) $query .= " WHERE name LIKE '%{$input['keyword']}%'";
        $query .= " ORDER BY name {$input['order']}";
        $records = self::$wpdb->get_results($query);
        $total_page = ceil(count($records) / $input['perpage']);

        $query .= " LIMIT {$input['perpage']}";
        $query .= " OFFSET {$input['offset']}";
        $records = self::$wpdb->get_results($query);
        // return self::$wpdb->last_query;
        return [
            'total_page' => $total_page,
            'records' => $records
        ];
    }
}
