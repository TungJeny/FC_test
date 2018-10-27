<?php if (empty($stock_temp_items)): ?>
<tr>
    <th colspan="9"><p class="font-14 mt-10" align="center">Không có thông tin</p></th>
</tr>
<?php else: ?>
<?php $index = 1; foreach ($stock_temp_items as $item): ?>
<tr>
    <td><?php echo $index; ?></td>
    <td><?php echo $item['company_name']; ?></a></td>
    <td>
        <?php echo $item['po_code']; ?>
    </td>
    <td><?php echo $item['package_code']; ?></td>
    <td><?php echo $item['name']; ?></td>
    <td><?php echo $item['overflow_quantity']; ?></td>
    <td></td>
    <td><?php echo $item['note']; ?></td>
    <td class ="text-center">
       <input type ="checkbox" disabled <?php echo $item['status']? 'checked':''; ?> >
       <label><span></span></label>
    </td>
</tr>
<?php $index++; endforeach; ?>
<?php endif; ?>
                    