<?php $this->load->view("partial/header"); ?>

<script type="text/javascript">
    $(document).ready(function () {
        <?php
        $has_cost_price_permission = $this->Employee->has_module_action_permission('items', 'see_cost_price', $this->Employee->get_logged_in_employee_info()->person_id);
        if ($has_cost_price_permission)
        {
            ?>
            var table_columns = ["", "item_id", "item_number", 'name', 'category', 'size', 'cost_price', 'unit_price', 'quantity', '', '', '', '', ''];
            <?php
        }
        else
        {
            ?>
            var table_columns = ["", "item_id", "item_number", 'name', 'category', 'size', 'unit_price', 'quantity', '', '', '', '', ''];
            <?php
        }
        ?>
        enable_sorting("<?php echo site_url("$controller_name/sorting"); ?>", table_columns, <?php echo $per_page; ?>, <?php echo json_encode($order_col);?>, <?php echo json_encode($order_dir);?>);
        enable_select_all();
        enable_checkboxes();
        enable_row_selection();
        enable_search('<?php echo site_url("$controller_name");?>',<?php echo json_encode(lang("common_confirm_search"));?>);
        enable_delete(<?php echo json_encode(lang($controller_name . "_confirm_delete"));?>,<?php echo json_encode(lang($controller_name . "_none_selected"));?>);
        enable_cleanup(<?php echo json_encode(lang("items_confirm_cleanup"));?>);

        $('#generate_barcodes').click(function () {
            var selected = get_selected_values();

            if (selected.length == 0) {
                bootbox.alert(<?php echo json_encode(lang('common_must_select_item_for_barcode')); ?>);
                return false;
            }

            $("#skip-labels").modal('show');
            return false;
        });

        $("#generate_barcodes_form").submit(function () {
            var selected = get_selected_values();
            var num_labels_skip = $("#num_labels_skip").val() ? $("#num_labels_skip").val() : 0;
            var url = '<?php echo site_url("items/generate_barcodes");?>' + '/' + selected.join('~') + '/' + num_labels_skip;
            window.location = url;
            return false;
        });

        $('#generate_qrcode_labels').click(function () {
            var selected = get_selected_values();
            if (selected.length == 0) {
                bootbox.alert(<?php echo json_encode(lang('common_must_select_item_for_qrcode')); ?>);
                return false;
            }

            $(this).attr('href', '<?php echo site_url("items/generate_qrcode_labels");?>/' + selected.join('~'));
        });

        $('#generate_barcode_labels').click(function () {
            var selected = get_selected_values();
            if (selected.length == 0) {
                bootbox.alert(<?php echo json_encode(lang('common_must_select_item_for_barcode')); ?>);
                return false;
            }

            $(this).attr('href', '<?php echo site_url("items/generate_barcode_labels");?>/' + selected.join('~'));
        });


        <?php if ($this->session->flashdata('manage_success_message')) { ?>
            show_feedback('success', <?php echo json_encode($this->session->flashdata('manage_success_message')); ?>, <?php echo json_encode(lang('common_success')); ?>);
        <?php } ?>
    });

    function post_bulk_form_submit(response) {
        window.location.reload();
    }

    function select_inv() {
        bootbox.confirm(<?php echo json_encode(lang('items_select_all_message')); ?>, function (result) {
            if (result) {
                $('#select_inventory').val(1);
                $('#selectall').css('display', 'none');
                $('#selectnone').css('display', 'block');
                $.post('<?php echo site_url("items/select_inventory");?>', {select_inventory: $('#select_inventory').val()});
            }
        });
    }

    function select_inv_none() {
        $('#select_inventory').val(0);
        $('#selectnone').css('display', 'none');
        $('#selectall').css('display', 'block');
        $.post('<?php echo site_url("items/clear_select_inventory");?>', {select_inventory: $('#select_inventory').val()});
    }

    $.post('<?php echo site_url("items/clear_select_inventory");?>', {select_inventory: $('#select_inventory').val()});

</script>


<div class="modal fade skip-labels" id="skip-labels" role="dialog" aria-labelledby="skipLabels" aria-hidden="true">
    <div class="modal-dialog customer-recent-sales">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"
                aria-label=<?php echo json_encode(lang('common_close')); ?>><span
                aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="skipLabels"><?php echo lang('common_skip_labels') ?></h4>
            </div>
            <div class="modal-body">
                <?php echo form_open("items/generate_barcodes", array('id' => 'generate_barcodes_form', 'autocomplete' => 'off')); ?>
                <input type="text" class="form-control text-center" name="num_labels_skip" id="num_labels_skip"
                placeholder="<?php echo lang('common_skip_labels') ?>">
                <?php echo form_submit('generate_barcodes_form', lang("common_submit"), 'class="btn btn-block btn-primary"'); ?>
                <?php echo form_close(); ?>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->


<div class="manage_buttons">

    <!-- Css Loader  -->
    <div class="spinner" id="ajax-loader" style="display:none">
        <div class="rect1"></div>
        <div class="rect2"></div>
        <div class="rect3"></div>
    </div>

     <div class="manage-row-options hidden">

        <div class="email_buttons items">
            <?php if ($this->Employee->has_module_action_permission($controller_name, 'add_update', $this->Employee->get_logged_in_employee_info()->person_id)) { ?>
                <?php echo
                anchor("$controller_name/bulk_edit/",
                    '<span class="">' . lang("items_bulk_edit") . '</span>',
                    array('id' => 'bulk_edit', 'data-toggle' => 'modal', 'data-target' => '#myModal',
                        'class' => 'btn btn-primary btn-lg  disabled',
                        'title' => lang('items_edit_multiple_items')));
                ?>
            <?php } ?>
           
            <?php echo
            anchor("$controller_name/generate_qrcode_labels",
                '<span class="">' . lang("Nhãn QR code") . '</span>',
                array('id' => 'generate_qrcode_labels',
                    'class' => 'btn btn-primary btn-lg ',
                    'title' => lang('Nhãn QR code')));
            ?>

            <?php echo
            anchor("$controller_name/generate_barcode_labels",
                '<span class="">' . lang("common_barcode_labels") . '</span>',
                array('id' => 'generate_barcode_labels',
                    'class' => 'btn btn-primary btn-lg  disabled',
                    'title' => lang('common_barcode_labels')));
            ?>

            <?php echo
            anchor("$controller_name/generate_barcodes",
                '<span class="">' . lang("common_barcode_sheet") . '</span>',
                array('id' => 'generate_barcodes',
                    'class' => 'btn btn-primary btn-lg  disabled',
                    'target' => '_blank',
                    'title' => lang('common_barcode_sheet')));
            ?>

            <?php if ($this->Employee->has_module_action_permission($controller_name, 'delete', $this->Employee->get_logged_in_employee_info()->person_id)) { ?>
                <?php echo
                anchor("$controller_name/delete",
                    '<span class="">' . lang("common_delete") . '</span>',
                    array('id' => 'delete',
                        'class' => 'btn btn-red btn-lg disabled', 'title' => lang("common_delete")));
                ?>
            <?php } ?>
            <a href="#"
               class="btn btn-lg btn-clear-selection btn-warning"><?php echo lang('common_clear_selection'); ?></a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-9">
            <?php echo form_open("$controller_name/search", array('id' => 'search_form', 'autocomplete' => 'off', 'class' => '')); ?>
                <div class="search search-items no-left-border">
                    <ul class="list-inline">
                        <li>
                            <input type="text" class="form-control" name='search' id='search'
                            value="<?php echo H($search); ?>"
                            placeholder="<?php echo lang('common_search'); ?> <?php echo lang('module_' . $controller_name); ?>"/>
                        </li>
                        <li>
                            <?php echo lang('common_fields'); ?>:
                            <?php echo form_dropdown('fields',
                                array(
                                    'all' => lang('common_all'),
                                        //$this->db->dbprefix('items') . '.item_id' => lang('common_item_id'),
                                    $this->db->dbprefix('items') . '.item_number' => lang('common_item_number_expanded'),
                                    $this->db->dbprefix('items') . '.product_id' => lang('common_product_id'),
                                    $this->db->dbprefix('items') . '.name' => lang('common_item_name'),
                                    $this->db->dbprefix('items') . '.description' => lang('common_description'),
                                    $this->db->dbprefix('items') . '.size' => lang('common_size'),
                                    $this->db->dbprefix('items') . '.cost_price' => lang('common_cost_price'),
                                    $this->db->dbprefix('items') . '.unit_price' => lang('common_unit_price'),
                                    $this->db->dbprefix('items') . '.promo_price' => lang('items_promo_price'),
                                    $this->db->dbprefix('location_items') . '.quantity' => lang('items_quantity'),
                                    $this->db->dbprefix('items') . '.reorder_level' => lang('items_reorder_level'),
                                    $this->db->dbprefix('suppliers') . '.company_name' => lang('common_supplier'),
                                    $this->db->dbprefix('manufacturers') . '.name' => lang('common_manufacturer'),
                                    $this->db->dbprefix('tags') . '.name' => lang('common_tag'),
                                ), $fields, 'class="form-control" id="fields"');
                            ?>
                        </li>
                        <li>
                            <?php echo lang('common_category'); ?>:
                            <?php echo form_dropdown('category_id', $categories, $category_id, 'class="form-control" id="category_id"'); ?>
                        </li>
                        <li><?php echo form_submit('submitf', lang('common_search'), 'class="btn btn-primary btn-lg"'); ?></li>
                        <li>
                            <div class="clear-block items-clear-block <?php echo ($search == '') ? 'hidden' : '' ?>">
                                <a class="clear" href="<?php echo site_url($controller_name . '/clear_state'); ?>">
                                    <i class="ion ion-close-circled"></i>
                                </a>
                            </div>
                        </li>
                    </ul>
                </div>
            <?php echo form_close() ?>
        </div>
        <div class="col-md-3">
            <div class="buttons-list items-buttons">
                <div class="pull-right-btn">
                    <div class="spinner hidden" id="ajax-loader">
                        <div class="rect1"></div>
                        <div class="rect2"></div>
                        <div class="rect3"></div>
                    </div>
                    <?php if ($this->Employee->has_module_action_permission($controller_name, 'add_update', $this->Employee->get_logged_in_employee_info()->person_id)) { ?>
                        <?php echo
                        anchor("$controller_name/view/-1/",
                            '<span class="">' . lang($controller_name . '_new') . '</span>',
                            array('class' => 'btn btn-primary btn-lg',
                                'title' => lang($controller_name . '_new')));
                        ?>
                        <?php echo
                        anchor("stock_in/index",
                            '<span class="">' . lang('items_stock_in') . '</span>',
                            array('class' => 'btn btn-primary btn-lg', 'title' => lang('items_stock_in')));
                        ?>
                        <?php echo
                        anchor("stock_out/index",
                            '<span class="">' . lang('items_stock_out') . '</span>',
                            array('class' => 'btn btn-primary btn-lg', 'title' => lang('items_stock_out')));
                        ?>
                    <?php } ?>
                    <div class="piluku-dropdown">
                        <button type="button" class="btn btn-more dropdown-toggle" data-toggle="dropdown"
                        aria-expanded="false">
                        <i class="ion-android-more-horizontal"></i>
                        </button>
                        <ul class="dropdown-menu" role="menu">
                            <?php if ($this->Employee->has_module_action_permission($controller_name, 'manage_categories', $this->Employee->get_logged_in_employee_info()->person_id)) { ?>
                                <li>
                                    <?php echo anchor("$controller_name/categories",
                                        '<span class="">' . lang("items_manage_categories") . '</span>',
                                        array('class' => '',
                                            'title' => lang('items_manage_categories')));
                                    ?>
                                </li>
                            <?php } ?>
                            <?php if ($this->Employee->has_module_action_permission($controller_name, 'manage_tags', $this->Employee->get_logged_in_employee_info()->person_id)) { ?>
                                <li>
                                    <?php echo anchor("$controller_name/manage_tags",
                                        '<span class="">' . lang("items_manage_tags") . '</span>',
                                        array('class' => '',
                                            'title' => lang('items_manage_tags')));
                                    ?>
                                </li>
                            <?php } ?>

                            <?php if ($this->Employee->has_module_action_permission($controller_name, 'manage_manufacturers', $this->Employee->get_logged_in_employee_info()->person_id)) { ?>
                                <li>
                                    <?php echo anchor("$controller_name/manage_manufacturers",
                                        '<span class="">' . lang("items_manage_manufacturers") . '</span>',
                                        array('class' => '',
                                            'title' => lang('items_manage_manufacturers')));
                                    ?>
                                </li>
                            <?php } ?>

                            <li>
                                <?php echo anchor("stock_report/view_by_month", '<span class="">' . lang("items_report_stock") . '</span>', array('class' => '', 'title' => lang('items_report_stock'))); ?>
                            </li>

                            <?php if ($this->Employee->has_module_action_permission($controller_name, 'count_inventory', $this->Employee->get_logged_in_employee_info()->person_id)) { ?>
                                <li>
                                    <?php echo anchor("$controller_name/count",
                                        '<span class="">' . lang("items_count_inventory") . '</span>',
                                        array('class' => '',
                                            'title' => lang('items_count_inventory')));
                                    ?>
                                </li>
                            <?php } ?>

                            <?php if ($this->Employee->has_module_action_permission($controller_name, 'add_update', $this->Employee->get_logged_in_employee_info()->person_id)) { ?>
                                <li>
                                    <?php echo anchor("$controller_name/excel_import/",
                                        '<span class="">' . lang("common_excel_import") . '</span>',
                                        array('class' => ' ',
                                            'title' => lang('common_excel_import')));
                                            ?>
                                        </li>
                                        <li>
                                            <?php echo anchor("$controller_name/excel_export/",
                                                '<span class="">' . lang("common_excel_export") . '</span>',
                                                array('class' => ' ',
                                                    'title' => lang('common_excel_export')));
                                                    ?>
                                        </li>
                                    <?php } ?>
                            <?php if ($this->Employee->has_module_action_permission($controller_name, 'delete', $this->Employee->get_logged_in_employee_info()->person_id)) { ?>
                                <li>
                                    <?php echo
                                    anchor("$controller_name/cleanup",
                                        '<span class="">' . lang("items_cleanup_old_items") . '</span>',
                                        array('id' => 'cleanup',
                                            'class' => '', 'title' => lang("items_cleanup_old_items")));
                                    ?>
                                </li>
                            <?php } ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="row alert-select-all">
    <div class="col-md-12">

        <div id="selectall" class="selectall text-center" onclick="select_inv()">
            <div class="alert alert-danger">
                <?php echo lang('items_all') . ' ' . lang('items_select_inventory') . ' <strong>' . lang('items_for_current_search') . '</strong>'; ?>
            </div>
        </div>

        <div id="selectnone" class="selectnone text-center" onclick="select_inv_none()">
            <div class="alert alert-danger">
                <?php echo '<strong>' . lang('items_selected_inventory_total') . ' ' . lang('items_select_inventory_none') . '</strong>'; ?>
            </div>
        </div>
        <?php echo form_input(array(
            'name' => 'select_inventory',
            'id' => 'select_inventory',
            'style' => 'display:none',
        ));
        ?>
    </div>
</div>


<div class="container-fluid">
    <div class="row manage-table">
        <div class="panel panel-piluku">
            <div class="panel-heading">
                <h3 class="panel-title">
                    <?php echo lang('common_list_of') . ' ' . lang('module_' . $controller_name); ?>
                    <span title="<?php echo $total_rows; ?> total <?php echo $controller_name ?>"
                          class="badge bg-primary tip-left" id="manage_total_items"><?php echo $total_rows; ?></span>
                    <div class="panel-options custom">
                        <?php if ($pagination) { ?>
                            <div class="pagination pagination-top hidden-print  text-center" id="pagination_top">
                                <?php echo $pagination; ?>
                            </div>
                        <?php } ?>
                    </div>
                </h3>
            </div>
            <div class="panel-body nopadding table_holder table-responsive">
                <?php echo $manage_table; ?>
            </div>

        </div>
    </div>
</div>
<?php if ($pagination) { ?>
    <div class="text-center">
        <div class="row pagination hidden-print alternate text-center" id="pagination_bottom">
            <?php echo $pagination; ?>
        </div>
    </div>
<?php } ?>
</div>
<?php $this->load->view("partial/footer"); ?>
<style type="text/css">
    .buttons-list .pull-right-btn > a {
        width: 83px;
    }

</style>
