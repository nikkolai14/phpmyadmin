<?php
/**
 * Fake database driver for testing purposes
 *
 * @package    PhpMyAdmin-DBI
 * @subpackage Dummy
 */
if (! defined('PHPMYADMIN')) {
    exit;
}

/**
 * Array of queries this "driver" supports
 */
$GLOBALS['dummy_queries'] = array(
    array('query' => 'SELECT 1', 'result' => array(array('1'))),
    array(
        'query' => 'SHOW STORAGE ENGINES',
        'result' => array(
            array('Engine' => 'dummy', 'Support' => 'YES', 'Comment' => 'dummy comment'),
            array('Engine' => 'dummy2', 'Support' => 'NO', 'Comment' => 'dummy2 comment'),
        )
    ),
    array(
        'query' => 'SHOW STATUS WHERE Variable_name LIKE \'Innodb\\_buffer\\_pool\\_%\' OR Variable_name = \'Innodb_page_size\';',
        'result' => array(
            array('Innodb_buffer_pool_pages_data', 0),
            array('Innodb_buffer_pool_pages_dirty', 0),
            array('Innodb_buffer_pool_pages_flushed', 0),
            array('Innodb_buffer_pool_pages_free', 0),
            array('Innodb_buffer_pool_pages_misc', 0),
            array('Innodb_buffer_pool_pages_total', 4096),
            array('Innodb_buffer_pool_read_ahead_rnd', 0),
            array('Innodb_buffer_pool_read_ahead', 0),
            array('Innodb_buffer_pool_read_ahead_evicted', 0),
            array('Innodb_buffer_pool_read_requests', 64),
            array('Innodb_buffer_pool_reads', 32),
            array('Innodb_buffer_pool_wait_free', 0),
            array('Innodb_buffer_pool_write_requests', 64),
            array('Innodb_page_size', 16384),
        )
    ),
    array(
        'query' => 'SHOW INNODB STATUS;',
        'result' => false,
    ),
    array(
        'query' => 'SELECT @@innodb_version;',
        'result' => array(
            array('1.1.8'),
        )
    ),
    array(
        'query' => 'SHOW GLOBAL VARIABLES LIKE \'innodb_file_per_table\';',
        'result' => array(
            array('innodb_file_per_table', 'OFF'),
        )
    ),
    array(
        'query' => 'SHOW GLOBAL VARIABLES LIKE \'innodb_file_format\';',
        'result' => array(
            array('innodb_file_format', 'Antelope'),
        )
    ),
);

/**
 * Current database.
 */
$GLOBALS['dummy_db'] = '';

/* Some basic setup for dummy driver */
$GLOBALS['userlink'] = 1;
$GLOBALS['cfg']['DBG']['sql'] = False;
if (! defined('PMA_DRIZZLE')) {
    define('PMA_DRIZZLE', 0);
}


/**
 * Run the multi query and output the results
 *
 * @param mysqli $link  mysqli object
 * @param string $query multi query statement to execute
 *
 * @return boolean false always false since mysql extention not support
 *                       for multi query executions
 */
function PMA_DBI_real_multi_query($link, $query)
{
    return false;
}

/**
 * connects to the database server
 *
 * @param string $user                 mysql user name
 * @param string $password             mysql user password
 * @param bool   $is_controluser
 * @param array  $server               host/port/socket/persistent
 * @param bool   $auxiliary_connection (when true, don't go back to login if connection fails)
 *
 * @return mixed false on error or a mysqli object on success
 */
function PMA_DBI_connect(
    $user, $password, $is_controluser = false, $server = null,
    $auxiliary_connection = false
) {
    return true;
}

/**
 * selects given database
 *
 * @param string   $dbname name of db to select
 * @param resource $link   mysql link resource
 *
 * @return bool
 */
function PMA_DBI_select_db($dbname, $link = null)
{
    return $GLOBALS['dummy_db'];
}

/**
 * runs a query and returns the result
 *
 * @param string   $query   query to run
 * @param resource $link    mysql link resource
 * @param int      $options query options
 *
 * @return mixed
 */
function PMA_DBI_real_query($query, $link = null, $options = 0)
{
    $query = trim(preg_replace('/  */', ' ', str_replace("\n", ' ', $query)));
    for ($i = 0; $i < count($GLOBALS['dummy_queries']); $i++) {
        if ($GLOBALS['dummy_queries'][$i]['query'] == $query) {
            $GLOBALS['dummy_queries'][$i]['pos'] = 0;
            if (is_array($GLOBALS['dummy_queries'][$i]['result'])) {
                return $i;
            } else {
                return false;
            }
        }
    }
    echo "Not supported query: $query\n";
    return false;
}

/**
 * returns result data from $result
 *
 * @param resource $result result  MySQL result
 *
 * @return array
 */
function PMA_DBI_fetch_any($result)
{
    if ($GLOBALS['dummy_queries'][$result]['pos'] >= count($GLOBALS['dummy_queries'][$result]['result'])) {
        return false;
    }
    $ret = $GLOBALS['dummy_queries'][$result]['result'][$GLOBALS['dummy_queries'][$result]['pos']];
    $GLOBALS['dummy_queries'][$result]['pos'] += 1;
    return $ret;
}

/**
 * returns array of rows with associative and numeric keys from $result
 *
 * @param resource $result result  MySQL result
 *
 * @return array
 */
function PMA_DBI_fetch_array($result)
{
    $data = PMA_DBI_fetch_any($result);
    return $data;
}

/**
 * returns array of rows with associative keys from $result
 *
 * @param resource $result MySQL result
 *
 * @return array
 */
function PMA_DBI_fetch_assoc($result)
{
    $data = PMA_DBI_fetch_any($result);
    return $data;
}

/**
 * returns array of rows with numeric keys from $result
 *
 * @param resource $result MySQL result
 *
 * @return array
 */
function PMA_DBI_fetch_row($result)
{
    $data = PMA_DBI_fetch_any($result);
    return $data;
}

/**
 * Adjusts the result pointer to an arbitrary row in the result
 *
 * @param $result
 * @param $offset
 *
 * @return bool true on success, false on failure
 */
function PMA_DBI_data_seek($result, $offset)
{
    if ($offset > count($GLOBALS['dummy_queries'][$i]['result'])) {
        return false;
    }
    $GLOBALS['dummy_queries'][$i]['pos'] = $offset;
    return true;
}

/**
 * Frees memory associated with the result
 *
 * @param resource $result
 */
function PMA_DBI_free_result($result)
{
    return;
}

/**
 * Check if there are any more query results from a multi query
 *
 * @return bool false
 */
function PMA_DBI_more_results()
{
    return false;
}

/**
 * Prepare next result from multi_query
 *
 * @return boo false
 */
function PMA_DBI_next_result()
{
    return false;
}

/**
 * returns the number of rows returned by last query
 *
 * @param resource $result MySQL result
 *
 * @return string|int
 */
function PMA_DBI_num_rows($result)
{
    if (!is_bool($result)) {
        return count($GLOBALS['dummy_queries'][$result]['result']);
    } else {
        return 0;
    }
}
