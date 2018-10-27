<?php $this->load->view("partial/header");?>

    <?php if (isset($msg) || isset($msg_error) ):?>
        <script type="text/javascript">
        toastr.warning(' <?php echo $msg; ?> ',100000); 
        </script>
        <script type="text/javascript">
        toastr.error("<?php echo $msg_error; ?>"); 
        </script>
    <?php endif; ?>
    

    

<div class="panel panel-piluku" style="margin-top: 30px;">
    <div class="panel-heading">
        <ul class="nav nav-tabs" role="tablist">
            <li role="presentation" class="active"><a href="#detail" aria-controls="home" role="tab" data-toggle="tab" @click="showDetail()">KẾ HOẠCH SẢN XUẤT THÁNG <?php echo date('m');?> NĂM <?php echo date('Y');?></a></li>
        </ul>
        
        <form action=" <?php echo site_url('production/upload_file_import') ?>" method="post" enctype="multipart/form-data">
            <div class="bootstrap-filestyle input-group">

                <input type="file" name="file_upload_khsx" id="fileToUpload">
                <input type="submit" value="Upload EXCEL" class="submit_form" name="submit">
                <!-- <input type="text" class="form-control " disabled=""> --> 
                
            </div>
        </form>

        <div class="text-right combo_right">
            <select name="list_month_khsx" class="option_select">
               <option value=""><?php echo date('Y').'-'.'01'; ?></option>
               <option value=""><?php echo date('Y').'-'.'02'; ?></option>
               <option value=""><?php echo date('Y').'-'.'03'; ?></option>
               <option value=""><?php echo date('Y').'-'.'04'; ?></option>
               <option value=""><?php echo date('Y').'-'.'05'; ?></option>
               <option value=""><?php echo date('Y').'-'.'06'; ?></option>
               <option value=""><?php echo date('Y').'-'.'07'; ?></option>
               <option value=""><?php echo date('Y').'-'.'08'; ?></option>
               <option value=""><?php echo date('Y').'-'.'09'; ?></option>
               <option value=""><?php echo date('Y').'-'.'10'; ?></option>
               <option value=""><?php echo date('Y').'-'.'11'; ?></option>
               <option value=""><?php echo date('Y').'-'.'12'; ?></option>
            </select>
        </div>
    </div>
          <div class="col-md-9 materials-content-summary" style="padding-left: 0px;">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th class="center" rowspan="2" width="30px">STT</th>
                            <th class="center" rowspan="2">Tên mặt hàng</th>
                            <th class="center" rowspan="2">ĐVT</th>
                            <!-- <th class="center" colspan="2">Kế hoạch tháng hiện tại</th> -->
                            <th class="center" rowspan="2">Số Lượng Phôi</th>
                        </tr>
                        <!-- <tr>
                            <th width="180px">Số lượng Trong tháng</th>
                            <th>Gía KH</th>
                        </tr> -->
                    </thead>
                    <tbody>

                        <?php if ( ! empty($data)): ?>
                            <?php $i=1; foreach ($data as $item ): ?>
                                <?php if (! empty($item['item_name'])): ?>
                                    <tr>
                                        <td><span><?php echo $i; ?></span></td>
                                        <td><span><?php echo $item['item_name']; ?></span></td>
                                        <td><span><?php echo $item['unit']; ?></span></td>
                                        <!-- <td><span><?php echo $item['quantity']; ?></span></td> -->
                                        <!-- <td><span><?php echo $item['price_kh']; ?></span></td> -->
                                        <td><span><?php echo $item['quantity_phoi']; ?></span></td>
                                    </tr>
                                <?php endif; ?>
                            <?php $i++; endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
 <!-- @click="fnSave" -->
    <div class="form-actions pull-right">
        <input type="submit" value="Lưu Dữ Liệu" class="submit_button floating-button btn btn-primary save_khsx_import" >
    </div>
</div>
<?php 
    if (! empty($data)) {
        $json_data = json_encode($data);
    }
?>


<script>
    $(document).ready(function() {
        $('.save_khsx_import').click(function(){
            var url_link = "<?php echo site_url('production/save_excel_khsx'); ?>";
            var data_post = <?php echo $json_data; ?>;
            //alert(url_link);        
            $.ajax({
                type: "POST",
                url: url_link,
                data: data_post,
                success: function(response) {
                    console.log(response);
                    toastr.success('Cập nhật Thành Công');
                },
                error: function(response) {
                    console.log(response);
                    toastr.error('Cập Nhật Thất Bại');
                }
            });
        }); 
    });
    
</script>

<style type="text/css">

td.kehoachhientai{
    border:1px solid;
}
.table-responsive {
    background: #fdf8f8;
}
.table-responsive.table.table-bordered {
    background: #fffdfd;
}
.td_height{
    height:35px;
    border: 1px solid;
}
#detail table > tbody > tr > td{
    border: 1px solid;
}

input.submit_form {
    position: relative;
    top: -21px;
    left: 307px;
}
.text-right.combo_right {
    position: relative;
    top: -44px;
}
select.option_select {
    width: 92px;
    height: 30px;
}

</style>
<?php $this->load->view("partial/footer"); ?>