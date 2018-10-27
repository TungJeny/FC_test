

<table class="table table-bordered table-hover">
    <thead>
    <tr>
        <th>STT</th>
        <th>Tên hàng</th>
        <th>ĐVT</th>
        <th>Số lượng cần đặt</th>
        <th>Đơn giá</th>
        <th>Thành tiền</th>
        <th>Thời gian cần về cho SX</th>
        <th>Lưu ý</th>
        <th>Bỏ chọn</th>
        <th>Thêm Giai Đoạn</th>
    </tr>
    </thead>
    <tbody>
    <?php if (empty($items)): ?>
    <tr>
        <th colspan="9"><p class="font-14 mt-10" align="center">Bạn chưa chọn nguyên liệu</p></th>
    </tr>
    <?php else: ?>
    <?php $index = 1; foreach ($items as $item):?>
    <input type="hidden" name="data[items][<?php echo get_data($item, 'item_id'); ?>][item_id]" value="<?php echo $item['item_id']; ?>" />
    <input type="hidden" name="data[items][<?php echo get_data($item, 'item_id'); ?>][unit_id]" value="<?php echo $item['unit_id']; ?>" />
    <tr class="po-item" id="po-item-id-<?php echo $item['item_id']; ?>">
        <td width ="5%"><?php echo $index; ?></td>
        <td width ="20%"><a target="_blank" class="name_item_<?php echo $item['item_id']; ?>" href="<?php echo site_url('items/view/' . $item['item_id'] . '/2'); ?>"><?php echo $item['item_name']; ?></a></td>
        <td width ="5%">
            <?php echo $item['unit_name']; ?>
        </td>
        <td width ="15%"><input name="data[items][<?php echo get_data($item, 'item_id'); ?>][quantity]" type="number" class="input-quantity form-control" onchange="admin_po.update_item(<?php echo $item['item_id']; ?>, 'quantity', this.value)" value="<?php echo $item['quantity']; ?>" /></td>
        <td width ="10%" class="cost_price cost_price_<?php echo $item['item_id']; ?>"><?php echo to_currency(!empty($item['cost_price'])? $item['cost_price']:0); ?></td>
        <td width ="10%"><?php echo to_currency(!empty($item['cost_price'])? $item['cost_price']*$item['quantity']:0*$item['quantity']); ?></td>
        <td width ="15%"><input name="data[items][<?php echo get_data($item, 'item_id'); ?>][month]" type="text" class="form-control" id="month_<?php echo $item['item_id']; ?>" onchange="admin_po.update_item(<?php echo $item['item_id']; ?>, 'month', this.value)" value="<?php echo $item['month']; ?>" /></td>
        <td width ="15%"><textarea name="data[items][<?php echo get_data($item, 'item_id'); ?>][comment]" placeholder="Lưu ý cho nguyên vật liệu" class="form-control" onchange="admin_po.update_item(<?php echo $item['item_id']; ?>, 'comment', this.value)"><?php echo $item['comment']; ?></textarea></td>
        <td  width ="5%">
            <button onclick="admin_po.unselect_item(<?php echo $item['item_id']; ?>)" type="button" class="btn btn-primary">
                <i class="glyphicon glyphicon-remove"></i>
            </button>
        </td>
        <td>
            <button type="button" class="btn btn-primary add_staged">
                <i class="glyphicon glyphicon-plus-sign"></i>
                <input type="hidden" id="id_item" value="<?php echo $item['item_id']; ?>">
            </button>
        </td>
  
    </tr> 
    <?php if (!empty($item['staged'])): ?>

    <tr>
        <!-- <td colspan="10"><?php var_dump($item['staged']); ?></td> -->
        <?php foreach ($item['staged'] as $row): ?>
            <tr>
                <td>Giai Đoạn</td>
                <td colspan="2"> <?php echo $item['item_name'] ?> </td>
                <td> <input type="number" name="data[items][<?php echo $item['item_id']; ?>][json_staged][quantity][]" value="<?php echo $row->quantity;?>"> </td>
                <td></td>
                <td></td>
                <td><input name="data[items][<?php echo $item['item_id']; ?>][json_staged][month][]" type="text" class="form-control" value="<?php echo $row->month;?>"/></td>
                <td></td>
                <td></td>
                <td><button type="button" class="btn btn-primary" id="remove_staged"><i class="glyphicon glyphicon-remove"></i></button></td>
            </tr>
        <?php endforeach ?>
                              
    </tr>    
    <?php endif; ?>

    <?php $index++; endforeach; ?>
    <?php endif; ?>

    </tbody>
    
</table>
               <!--  <?php 
                    $current_month = date('Y-m');
                    $month_date_next = strtotime('+1 month',date('Y-m')); 
                ?> -->
<script type="text/javascript">
    $(document).ready(function() {
        var count = 0;
        $('.add_staged').click(function(){
                count += 1;
                id_item = $(this).find('#id_item').val();
               
                month = $('#month_'+id_item).val();
                //console.log(id_item); '+count+'
                //alert(month);
                name_item = $('.name_item_'+id_item).text();
                //alert(name_item);
                cost_price = $('.cost_price_'+id_item).text();
                var html = '';
                html += '<tr class="block_staged">';
                html += '<td>Giai Đoạn</td>';
                html += '<td colspan="2">'+name_item+'</td>';
                html += '<td width ="15%">';
                html += '<input type="number" name="data[items]['+id_item+'][json_staged][quantity][]">';
                html += '</td>';
                html += '<td></td>';
                html += '<td></td>';
                html += '<td><input name="data[items]['+id_item+'][json_staged][month][]" type="text" class="form-control" value="'+month+'" /></td>';
                html += '<td></td>';
                html += '<td></td>';
                html += '<td>';
                html += '<button type="button" class="btn btn-primary" id="remove_staged"><i class="glyphicon glyphicon-remove"></i></button>';
                html += '</td>';
                html += '</tr';
                $(this).parent().parent().parent().append(html);
        });

        $(document).on('click','#remove_staged',function(){
            $(this).parent().parent().remove();
        });
    });
</script>

<style type="text/css">
    textarea.text_ghichu{
        width: 300px;
    }
</style>