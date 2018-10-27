<?php

/*
 * This interface(abstract class) is implemented by any model intergating online store with PHPPOS
 */
abstract class Ecom extends CI_Model
{

    public $ecommerce_store_location;

    public function __construct()
    {
        $this->ecommerce_store_location = $this->config->item('ecom_store_location') ? $this->config->item('ecom_store_location') : 1;
    }

    public static function get_ecom_model()
    {
        $CI = & get_instance();
        if ($CI->config->item("ecommerce_platform") == 'woocommerce') {
            $CI->load->model('Woo');
            return $CI->Woo;
        }
    }

    /*
     * Gets id of item with given ecommerce_product_id
     */
    function get_item_id_for_ecommerce_product($ecommerce_product_id)
    {
        $item_id = NULL;
        
        $this->db->from('items');
        $this->db->where(array(
            'ecommerce_product_id' => $ecommerce_product_id
        ));
        $result = $this->db->get();
        if ($result->num_rows() > 0) {
            $item = $result->row_array();
            $item_id = $item['item_id'];
        }
        
        return $item_id;
    }

    /*
     * Get PHPPOS item quantity for e-commerce location
     */
    function get_item_quantity($item_id)
    {
        $this->load->model('Item_location');
        return $this->Item_location->get_location_quantity($item_id, $this->ecommerce_store_location);
    }

    /*
     * Update Item to link it with ecommerce product
     */
    function link_item($item_id, $ecommerce_product_id)
    {
        $this->db->where('item_id', $item_id);
        $this->db->update('items', array(
            'ecommerce_product_id' => $ecommerce_product_id
        ));
    }

    function unlink_all()
    {
        $this->db->update('items', array(
            'ecommerce_product_id' => NULL
        ));
    }

    function unlink_item($item_id)
    {
        $this->db->where('item_id', $item_id);
        $this->db->update('items', array(
            'ecommerce_product_id' => NULL
        ));
    }

    function unlink_items($item_ids)
    {
        $this->db->where_in('item_id', $item_ids);
        $this->db->update('items', array(
            'ecommerce_product_id' => NULL
        ));
    }

    function get_products_not_in_ecommerce()
    {
        $this->db->select('GROUP_CONCAT(' . $this->db->dbprefix('tags') . '.name) as tags,items.*');
        $this->db->from('items');
        $this->db->join('items_tags', 'items_tags.item_id = items.item_id', 'left');
        $this->db->join('tags', 'tags.id = items_tags.tag_id', 'left');
        $this->db->group_by('items.item_id');
        $this->db->where('ecommerce_product_id', NULL);
        $this->db->where('is_ecommerce', 1);
        $this->db->where('items.deleted', 0);
        return $this->db->get()->result_array();
    }

    function get_products_modified_since_last_sync()
    {
        $this->db->select('GROUP_CONCAT(' . $this->db->dbprefix('tags') . '.name) as tags,items.*');
        $this->db->from('items');
        $this->db->join('items_tags', 'items_tags.item_id = items.item_id', 'left');
        $this->db->join('tags', 'tags.id = items_tags.tag_id', 'left');
        $this->db->group_by('items.item_id');
        $this->db->where('ecommerce_product_id is NOT NULL', NULL, FALSE);
        $this->db->where('is_ecommerce', 1);
        $this->db->where('last_modified >', $this->config->item('last_ecommerce_sync_date') ? $this->config->item('last_ecommerce_sync_date') : date('Y-m-d H:i:s'));
        $this->db->where('items.deleted', 0);
        return $this->db->get()->result_array();
    }

    function get_sync_progress()
    {
        return array(
            'percent_complete' => $this->config->item('ecommerce_sync_percent_complete'),
            'message' => $this->config->item('ecommerce_sync_message')
        );
    }

    function update_sync_progress($progress, $message)
    {
        $this->Appconfig->save('ecommerce_sync_percent_complete', $progress);
        $this->Appconfig->save('ecommerce_sync_message', $message ? $message : '');
    }

    /*
     * Get categoreis and sub categories for implementation
     */
    abstract protected function get_all_categories_and_sub_categories();

    /*
     * Get tags
     */
    abstract protected function get_all_tags();

    /*
     * export products from php pos to ecommerce.
     */
    abstract function export_phppos_items_to_ecommerce();

    /*
     * export products from php pos to ecommerce.
     */
    abstract function export_phppos_tags_to_ecommerce();

    /*
     * export products from php pos to ecommerce.
     */
    abstract function export_phppos_categories_to_ecommerce();

    /*
     * Import products from online store.
     * It will import only those products which are not present in the phppos items list.
     */
    abstract protected function import_ecommerce_items_into_phppos();

    /*
     * Adds ecommerce product to the items table
     */
    abstract protected function add_item_from_ecommerce_to_phppos($product);

    /*
     * Push new POS product to online store
     */
    abstract protected function save_item_from_phppos_to_ecommerce($item_data, $item_id);

    /*
     * Sync inventory counts
     */
    abstract protected function sync_inventory_changes();

    /*
     * Sync changes to item data
     */
    abstract protected function sync_phppos_item_changes();

    /*
     * Sets if configured
     */
    abstract protected function is_configured();
}
?>