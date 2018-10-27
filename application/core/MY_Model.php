<?php defined('BASEPATH') OR exit('No direct script access allowed');
/*
* Copyright (C) 2014 @avenirer [avenir.ro@gmail.com]
* Everyone is permitted to copy and distribute verbatim or modified copies of this license document,
* and changing it is allowed as long as the name is changed.
* DON'T BE A DICK PUBLIC LICENSE TERMS AND CONDITIONS FOR COPYING, DISTRIBUTION AND MODIFICATION
*
***** Do whatever you like with the original work, just don't be a dick.
***** Being a dick includes - but is not limited to - the following instances:
********* 1a. Outright copyright infringement - Don't just copy this and change the name.
********* 1b. Selling the unmodified original with no work done what-so-ever, that's REALLY being a dick.
********* 1c. Modifying the original work to contain hidden harmful content. That would make you a PROPER dick.
***** If you become rich through modifications, related works/services, or supporting the original work, share the love. Only a dick would make loads off this work and not buy the original works creator(s) a pint.
***** Code is provided with no warranty.
*********** Using somebody else's code and bitching when it goes wrong makes you a DONKEY dick.
*********** Fix the problem yourself. A non-dick would submit the fix back.
 *
 */

/** how to extend MY_Model:
 *	class User_model extends MY_Model
 *	{
 *      public $table = 'users'; // Set the name of the table for this model.
 *      public $primary_key = 'id'; // Set the primary key
 *      public $fillable = array(); // You can set an array with the fields that can be filled by insert/update
 *      public $protected = array(); // ...Or you can set an array with the fields that cannot be filled by insert/update
 * 		public function __construct()
 * 		{
 *          $this->_database_connection  = group_name or array() | OPTIONAL
 *              Sets the connection preferences (group name) set up in the database.php. If not trset, it will use the
 *              'default' (the $active_group) database connection.
 *          $this->timestamps = TRUE | array('made_at','modified_at','removed_at')
 *              If set to TRUE tells MY_Model that the table has 'created_at','updated_at' (and 'deleted_at' if $this->soft_delete is set to TRUE)
 *              If given an array as parameter, it tells MY_Model, that the first element is a created_at field type, the second element is a updated_at field type (and the third element is a deleted_at field type)
 *          $this->soft_deletes = FALSE;
 *              Enables (TRUE) or disables (FALSE) the "soft delete" on records. Default is FALSE
 *          $this->timestamps_format = 'Y-m-d H:i:s'
 *              You can at any time change the way the timestamp is created (the default is the MySQL standard datetime format) by modifying this variable. You can choose between whatever format is acceptable by the php function date() (default is 'Y-m-d H:i:s'), or 'timestamp' (UNIX timestamp)
 *          $this->return_as = 'object' | 'array'
 *              Allows the model to return the results as object or as array
 *          $this->has_one['phone'] = 'Phone_model' or $this->has_one['phone'] = array('Phone_model','foreign_key','local_key');
 *          $this->has_one['address'] = 'Address_model' or $this->has_one['address'] = array('Address_model','foreign_key','another_local_key');
 *              Allows establishing ONE TO ONE or more ONE TO ONE relationship(s) between models/tables
 *          $this->has_many['posts'] = 'Post_model' or $this->has_many['posts'] = array('Posts_model','foreign_key','another_local_key');
 *              Allows establishing ONE TO MANY or more ONE TO MANY relationship(s) between models/tables
 *          $this->has_many_pivot['posts'] = 'Post_model' or $this->has_many_pivot['posts'] = array('Posts_model','foreign_primary_key','local_primary_key');
 *              Allows establishing MANY TO MANY or more MANY TO MANY relationship(s) between models/tables with the use of a PIVOT TABLE
 *              !ATTENTION: The pivot table name must be composed of the two table names separated by "_" the table names having to to be alphabetically ordered (NOT users_posts, but posts_users).
 *                  Also the pivot table must contain as identifying columns the columns named by convention as follows: table_name_singular + _ + foreign_table_primary_key.
 *                  For example: considering that a post can have multiple authors, a pivot table that connects two tables (users and posts) must be named posts_users and must have post_id and user_id as identifying columns for the posts.id and users.id tables.
 *          $this->cache_driver = 'file'
 *          $this->cache_prefix = 'mm'
 *              If you know you will do some caching of results without the native caching solution, you can at any time use the MY_Model's caching.
 *              By default, MY_Model uses the files to cache result.
 *              If you want to change the way it stores the cache, you can change the $cache_driver property to whatever CodeIgniter cache driver you want to use.
 *              Also, with $cache_prefix, you can prefix the name of the caches. by default any cache made by MY_Model starts with 'mm' + _ + "name chosen for cache"
 *          $this->delete_cache_on_save = FALSE
 *              If you use caching often and you don't want to be forced to delete cache manually, you can enable $this->delete_cache_on_save by setting it to TRUE. If set to TRUE the model will auto-delete all cache related to the model's table whenever you write/update/delete data from that table.
 *          $this->pagination_delimiters = array('<span>','</span>');
 *              If you know you will use the paginate() method, you can change the delimiters between the pages links
 *          $this->pagination_arrows = array('&lt;','&gt;');
 *              You can also change the way the previous and next arrows look like.
 *
 *
 * 			parent::__construct();
 * 		}
 * 	}
 *
 **/

class MY_Model extends CI_Model
{
    protected $_instance = array(
        'prefix_key' => '',
        'table' => '',
        'primary_key' => 'id',
        'collection' => array(
            'fields' => '*'
        )
    );

    protected $_meta_indexers = array(
        'index,follow' => 'index,follow',
        'index,nofollow' => 'index,nofollow',
        'noindex,follow' => 'noindex,follow',
        'noindex,nofollow' => 'noindex,nofollow',
    );

    protected $_deny_fields = array();
    protected $_after_fields = array();

    public $_data = null;

    const VALUE_YES = 1;
    const VALUE_NO = 2;

    const STATUS_ACTIVE = 1;
    const STATUS_DEACTIVE = 2;

    /* Get Prefix Cached Key */
    public function get_prefix_key() {
        if (isset($this->_instance['prefix_key'])) {
            return $this->_instance['prefix_key'];
        }
        return 'cached_key_' . time();
    }

    /*
     * Get Index URL
     * @return string
     * */
    public function get_index_url()
    {
        return site_url('admin/'.$this->get_prefix_key().'/index');
    }

    /*
     * Get Save URL
     * @param int $id
     * @return string
     * */
    public function get_save_url($id = null)
    {
        if (empty($id)) {
            $url = site_url('admin/'.$this->get_prefix_key().'/save');
        } else {
            $url = site_url('admin/'.$this->get_prefix_key().'/save/' . $id);
        }
        return $url;
    }

    /*
     * Get Ajax Save URL
     * @param int $id
     * @return string
     * */
    public function get_ajax_save_url($id = null)
    {
        if (empty($id)) {
            $url = site_url('admin/'.$this->get_prefix_key().'/ajax_save');
        } else {
            $url = site_url('admin/'.$this->get_prefix_key().'/ajax_save/id/' . $id);
        }
        return $url;
    }

    /*
     * Get Edit URL
     * @param int $id
     * @return string
     * */
    public function get_edit_url($id = null)
    {
        if (empty($id)) {
            return $this->get_create_url();
        }
        return site_url($this->get_prefix_key().'/view/' . $id);
    }

    /*
     * Get Ajax Edit URL
     * @param int $id
     * @return string
     * */
    public function get_ajax_edit_url($id = null)
    {
        if (empty($id)) {
            return site_url('admin/'.$this->get_prefix_key().'/ajax_edit');
        }
        return site_url('admin/'.$this->get_prefix_key().'/ajax_edit/id/' . $id);
    }

    /*
     * Get Delete URL
     * @param int $id
     * @return string
     * */
    public function get_delete_url($id)
    {
        return site_url('admin/'.$this->get_prefix_key().'/delete/id/' . $id);
    }

    /*
     * Get Create New URL
     * @param int $id
     * @return string
     * */
    public function get_create_url()
    {
        $url = site_url('admin/'.$this->get_prefix_key().'/create');
        return $url;
    }

    /*
     * Get Ajax Create URL
     * @param int $id
     * @return string
     * */
    public function get_ajax_create_url()
    {
        $url = site_url('admin/'.$this->get_prefix_key().'/ajax_create');
        return $url;
    }

    /* Load Entity By ID */
    public function load_by_id($id, $reload = false) {
        $CI =& get_instance();
        $cached_key = $this->get_prefix_key() . '/id/' . $id;
        if ($reload) {
            $this->_before_load();
            $query = $this->db->get_where($this->_instance['table'], array('id' => $id), 1, 0);
            $this->_data = $query->row();
            $this->_data = $this->_after_load($this->_data);
            $CI->st_registry->set_key($cached_key, $this->_data, GLOBAL_CACHE_DURATION);
            $query->free_result();
        } else {
            $this->_data = $CI->st_registry->get_key($cached_key);
            if (!$this->_data) {
                return $this->load_by_id($id, true);
            }
        }
        return $this->_data;
    }

    /**
     * Load By Field
     * @param $field
     * @param $value
     * @return null
     */
    public function load_by_field($field, $value) {
        $this->_before_load();
        $query = $this->db->get_where($this->_instance['table'], array($field => $value), 1, 0);
        $this->_data = $query->row();
        $this->_data = $this->_after_load($this->_data);
        $query->free_result();
        return $this->_data;
    }

    /* Load By Field */
    public function get_data_by_id($id, $field) {
        $query = $this->db->query('SELECT ' . $field . ' FROM ' . $this->_instance['table'] . ' WHERE ' . $this->_instance['primary_key'] . ' = ' . $id . ' LIMIT 0, 1');
        $row = $query->row();
        $query->free_result();
        if (empty($row) || !is_object($row)) {
            return null;
        }
        return $row->$field;
    }

    /* Get CI Core */
    public function get_core() {
        $CI =& get_instance();
        return $CI;
    }

    /* Get Model */
    public function get_model($name) {
        if (!class_exists($name)) {
            $this->get_core()->load->model($name);
        }
        return $this->get_core()->$name;
    }

    /* Save Session Data */
    public function save_session_data($key, $value) {
        $CI = $this->get_core();
        $CI->st_registry->register($key, serialize($value));
        return $this;
    }

    /* Get Session Data */
    public function get_session_data($key) {
        $CI = $this->get_core();
        $data = $CI->st_registry->registry($key);
        if (!empty($data)) {
            @$data = unserialize($data);
        }
        return $data;
    }

    /* Paginate Collection */
    function paginate($current_page, $record_per_page, $count_results, $page_range) {
        $pag = array();
        if ($current_page <= 1) {
            $current_page = 1;
        }
        $pag['start'] = ($current_page - 1) * $record_per_page;
        if ($pag['start'] < 0) {
            $pag['start'] = 0;
        }
        $count_pages = ceil($count_results / $record_per_page);
        $pag['total'] = $count_pages;
        $delta = ceil($page_range / 2);
        if ($current_page - $delta > $count_pages - $page_range) {
            $pag['lower'] = $count_pages - $page_range;
            $pag['upper'] = $count_pages;
        } else {
            if ($current_page - $delta < 0) {
                $delta = $current_page;
            }
            $offset = $current_page - $delta;
            $pag['lower'] = $offset + 1;
            $pag['upper'] = $offset + $page_range;
        }
        if ($pag['lower'] <= 1) {
            $pag['lower'] = 1;
        }
        if ($pag['upper'] >= $count_pages) {
            $pag['upper'] = $count_pages;
        }
        if ($pag['upper'] <= 1) {
            $pag['upper'] = 1;
        }
        return $pag;
    }

    /* Get Table */
    public function get_table() {
        return $this->_instance['table'];
    }

    /* Make Query */
    public function query($sql) {
        return $this->db->query($sql);
    }

    /*
     * Add Entity
     * @param array $data
     * @return object
     * */
    public function add($data) {
        $data = $this->_before_create($data);
        $data = $this->_before_save($data);
        $origin_data = $data;
        if (!empty($this->_deny_fields)) {
            /* Filter Deny Fields Before Insert */
            foreach ($data as $key => $value) {
                if (in_array($key, $this->_deny_fields)) {
                    unset($data[$key]);
                }
            }
        }
        $this->db->insert($this->_instance['table'], $data);
        $id = $this->db->insert_id();
        $data = $origin_data;
        $data['id'] = $id;
        $this->_after_save($data);
        $this->_after_create($data);
        return $id;
    }

    /*
     * Update Entity
     * @param int $id
     * @param array $data
     * @return object
     * */
    public function update($id, $data) {
        $data = $this->_before_update($data);
        $data = $this->_before_save($data);
        $origin_data = $data;
        if (!empty($this->_deny_fields)) {
            /* Filter Deny Fields Before Insert */
            foreach ($data as $key => $value) {
                if (in_array($key, $this->_deny_fields)) {
                    unset($data[$key]);
                }
            }
        }
        $this->db->where('id', $id);
        $this->db->update($this->_instance['table'], $data);
        $data = $origin_data;
        $data['id'] = $id;
        $this->_after_save($data);
        $this->_after_update($data);
        $this->update_cache($id);
        return $this;
    }

    /*
     * Delete Entity
     * @param int $id
     * @return string
     * */
    public function delete($id) {
        $this->_before_delete($id);
        $this->db->delete($this->_instance['table'], array('id' => $id));
        $this->_after_delete($id);
        return $this;
    }

    /**
     * @param $conditions
     * @return $this
     */
    public function reset($conditions) {
        if (!empty($conditions)) {
            $this->db->where($conditions);
            $this->db->delete($this->get_table());
        }
        return $this;
    }

    /*
     * Before Create
     * @param int $data
     * @return array
     * */
    protected function _before_create($data)
    {
        return $data;
    }

    /*
     * After Create
     * @param array $data
     * @return array
     * */
    protected function _after_create($data)
    {
        return $data;
    }

    /*
     * Before Update
     * @param array $data
     * @return array
     * */
    protected function _before_update($data)
    {
        return $data;
    }

    /*
     * After Update
     * @param array $data
     * @return array
     * */
    protected function _after_update($data)
    {
        return $data;
    }

    /*
     * Before Delete
     * @param int $id
     * @return array
     * */
    protected function _before_delete($id)
    {
        return $this;
    }

    /*
     * After Delete
     * @param int $id
     * @return array
     * */
    protected function _after_delete($id)
    {
        $this->update_cache($id);
        return $this;
    }

    /*
     * Event Trigger Before Save
     * @param int $data
     * @return array
     * */
    protected function _before_save($data)
    {
        return $data;
    }

    /*
     * Event Trigger After Save
     * @param int $data
     * @return array
     * */
    protected function _after_save($data)
    {
        return $data;
    }

    /*
     * Event Trigger After Load
     * @param int $data
     * @return array
     * */
    protected  function _after_load($data) {
        return $data;
    }

    /*
     * Event Trigger Before Load
     * @param int $data
     * @return array
     * */
    protected  function _before_load() {
        return $this;
    }

    /*
     * Get Collection
     * @param int $start
     * @param int $limit
     * @param array $conditions
     * @param array $orders
     * @return array
     * */
    public function get_collection($start = null, $limit = null, $conditions = null, $orders = null) {
        $query = 'SELECT '. $this->get_select_fields() . ' FROM ' . $this->_instance['table'];
        if (is_array($conditions)) {
            $conditions = implode(' AND ', $conditions);
        }
        if (!empty($conditions)) {
            $query .= ' WHERE ' . $conditions;
        }
        if (!empty($orders)) {
            $query .= ' ORDER BY ' . $orders;
        }
        if (!empty($limit)) {
            $query .= ' LIMIT ' . $start . ', ' . $limit;
        }
        echo $query;
        $query = $this->db->query($query);
        if ($query) {
            $list = $query->result();
            $query->free_result();
            return $list;
        }
        return false;
    }

    /*
     * Get Collection
     * @param int $start
     * @param int $limit
     * @param array $conditions
     * @param array $orders
     * @return array
     * */
    public function get_active_collection($start = null, $limit = null, $conditions = null, $orders = null) {
        $query = 'SELECT '. $this->get_select_fields() . ' FROM ' . $this->_instance['table'];
        if (is_string($conditions)) {
            $conditions = explode('AND', $conditions);
        }
        if (empty($conditions)) {
            $conditions = array();
        }
        $conditions['status'] = '`status` = ' . self::STATUS_ACTIVE;
        if (is_array($conditions)) {
            $conditions = implode(' AND ', $conditions);
        }
        if (!empty($conditions)) {
            $query .= ' WHERE ' . $conditions;
        }
        if (!empty($orders)) {
            $query .= ' ORDER BY ' . $orders;
        }
        if (!empty($limit)) {
            $query .= ' LIMIT ' . $start . ', ' . $limit;
        }
        $query = $this->db->query($query);
        $list = $query->result();
        $query->free_result();
        return $list;
    }

    /*
     * Get Collection By Ids
     * @param string $ids
     * */
    public function get_by_ids($ids, $key = false) {
        if (is_array($ids)) {
            $ids = array_filter($ids);
            $ids = implode(',', $ids);
        }
        if (empty($ids)) {
            return null;
        }
        $collection = $this->get_collection(null, null, $this->_instance['primary_key'] . ' IN ('.$ids.')', $this->_instance['primary_key'] . ' DESC');
        if ($key) {
            $temp_collection = $collection;
            $collection = array();
            foreach ($temp_collection as $row) {
                $collection[get_data($row, $this->_instance['primary_key'])] = $row;
            }
        }
        return $collection;
    }

    /*
     * Count Collection By Conditions
     * @param int $count
     * */
    public function count_by($cond = null) {
        if (!empty($cond)) {
            $cond = ' WHERE ' . implode(' AND ', $cond);
        }
        $query = 'SELECT COUNT(id) AS count_result FROM ' . $this->_instance['table'] . $cond;
        $result = $this->db->query($query);
        if ($result) {
            $row = $result->row();
            $result->free_result();
            return $row->count_result;
        }
        return $result;
    }

    /* Get Entire Data Of Model */
    public function get_data_model() {
        return $this->_data;
    }

    /* Singleton Pattern */
    public function get_instance($key) {
        if (isset($this->_instance[$key])) {
            return $this->_instance[$key];
        }
        return null;
    }

    /* Get Database Object */
    public function get_db() {
        return $this->db;
    }

    /* Select Fields For Collection */
    public function select_fields($fields) {
        if (is_array($fields)) {
            $fields = implode(',', $fields);
        }
        $this->_instance['collection']['fields'] = $fields;
        return $this;
    }

    /* Get Fields Selected For Collection */
    public function get_select_fields() {
        return $this->_instance['collection']['fields'];
    }

    /* Get Fields Selected For Collection */
    public function get_selected_fields() {
        return $this->_instance['collection']['fields'];
    }

    /* Compare Update Fields Then Change */
    protected function _compare_fields($data_before, $data_update) {
        /* Check Setting Compare Fields */
        if (!empty($this->_instance['update']['compare_fields'])) {
            $compare_fields = explode(',', $this->_instance['update']['compare_fields']);
            if (!empty($compare_fields) && is_array($compare_fields)) {
                /* Validate Changed Of One By One */
                foreach ($compare_fields as $field) {
                    $field = trim($field);
                    if (isset($data_before->$field) && isset($data_update[$field])) {
                        if (is_array($data_before->$field) || is_object($data_before->$field)) {
                            $data_before->$field = serialize($data_before->$field);
                        }
                        if (is_array($data_update[$field]) || is_object($data_update[$field])) {
                            $data_update[$field] = serialize($data_update[$field]);
                        }
                        if (empty($data_update[$field])) {
                            $data_update[$field] = '';
                        } else {
                            /* Remove Update Fields If They Not Changed */
                            if (strcmp($data_before->$field, $data_update[$field]) == 0) {
                                unset($data_update[$field]);
                            }
                        }
                    }
                }
            }
        }
        return $data_update;
    }

    /* Update Cache */
    public function update_cache($id, $alias = null)
    {
        $prefix_key = $this->get_prefix_key();
        $CI =& get_instance();
        $CI->st_registry->delete_key($prefix_key . '/id/' . $id);
        if (!empty($alias)) {
            $CI->st_registry->delete_key($prefix_key . '/alias/' . $alias);
        }
        return $this;
    }

    /* Get Meta Indexers */
    public function get_meta_indexers() {
        return $this->_meta_indexers;
    }

    /* Load Model If Not Exists */
    public function load_model($model) {
        $CI = $this->get_core();
        if (!class_exists($model)) {
            $CI->load->model($model);
        }
        return $CI->$model;
    }

    /**
     * @param $tbl_link
     * @param $data
     * @return mixed
     */
    public function detach($tbl_link, $data) {
        return $this->db->delete($tbl_link, $data);
    }

    /**
     * @param $tbl_link
     * @param $data
     * @return mixed
     */
    public function attach($tbl_link, $data) {
        return $this->db->insert($tbl_link, $data);
    }

    /**
     * @param $key
     * @return null
     */
    public function get_link($key) {
        return get_data($this->_instance['links'], $key);
    }

    /**
     * @param $tbl_link
     * @param $data
     * @return mixed
     */
    public function get_attached_ids($tbl_link, $data) {
        return $this->db->get_where($tbl_link, $data)->result();
    }

    /**
     * @return mixed
     */
    public function get_tbl() {
        return $this->_instance['table'];
    }

    /**
     * @return mixed
     */
    public function get_pk_name() {
        return $this->_instance['primary_key'];
    }

    /**
     * @param $collection
     * @return string
     */
    public function get_json_rendered_collection($collection) {
        $json_collection = '[]';
        if (!empty($collection)) {
            $json_collection = json_encode($collection);
        }
        return $json_collection;
    }
}
