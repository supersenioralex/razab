<?php  

  $date = new DateTime('now', new DateTimeZone('Asia/Kolkata'));  
  $today_timestamp=$date->format('YmdHis');

  $order_id= $today_timestamp.$client_id.$ad_id;
  $merchant_id=$merchant_id;
  if($ads->price){
  	$value=$ads->price;
  }else{
  	$value=0;
  }
  
  $value=$value*100;
  $currency=$currency;
  $secret=$secret;

  $sha1hash = $today_timestamp.".".$merchant_id.".".$order_id.".".$value.".".$currency;
  $sha1hashed_string = sha1($sha1hash);
  $string_with_shared_secret = $sha1hashed_string.".".$secret;
  $sha1hash_string = sha1($string_with_shared_secret);

?>

<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
    </section>

    <!-- Main content -->
    <section class="content">

        <!-- Default box -->
        <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title">Book Appointment</h3>
            </div>
            <div id="suc_msg"></div>
            <?php $attributes = array('id' => 'book_form');
            echo form_open($url, $attributes); ?>
            <div class="box-body">
                <div class="row clearfix">

                    <div class="col-md-6">
                        <label for="name" class="control-label">Name</label>
                        <div class="form-group">
                            <input type="text" name="name" value="<?php echo $this->input->post('name'); ?>" class="form-control"
                                id="name" />
                            <div id="verify_name"></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label for="email" class="control-label">Email</label>
                        <div class="form-group">
                            <input type="text" name="email" value="<?php echo $this->input->post('email'); ?>" class="form-control"
                                id="email" />
                            <div id="verify_email"></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label for="number" class="control-label">Phone Number</label>
                        <div class="form-group">
                            <input type="text" name="number" value="<?php echo $this->input->post('number'); ?>" class="form-control"
                                id="number" />
                            <div id="verify_number"></div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label for="number" class="control-label">Appointment Date</label>
                        <div class="form-group">
                            
                                <div id="show_date"></div>
                                <div id="varify_date"> </div>
                        </div>
                    </div>
                    <div class="col-md-3">   
                        <label for="number" class="control-label" id="appointment_label">Appointment Time</label>
                        <div class="form-group">                         
                            <div id="show_time"></div>   
                        </div>                         
                    </div>
                    <input type="hidden" name="client_id" id="client_id" value="<?php echo $client_id; ?>">
                    <input type="hidden" name="ad_id" id="ad_id" value="<?php echo $ad_id; ?>">
                </div>

                <!-- payment Through Global Payment -->
                  <input type="hidden" name="TIMESTAMP" value="<?php echo $date->format('YmdHis'); ?>">
                  <input type="hidden" name="MERCHANT_ID" value="<?php echo $merchant_id; ?>">
                  <input type="hidden" name="ACCOUNT" value="internet">
                  <input type="hidden" name="ORDER_ID" value="<?php echo $order_id; ?>">
                  <input type="hidden" name="AMOUNT" value="<?php echo $value;?>">
                  <input type="hidden" name="CURRENCY" value="<?php echo $currency ?>">
                  <input type="hidden" name="crncy" value="<?php echo $currency; ?>">
                  <input type="hidden" name="AUTO_SETTLE_FLAG" value="1">
                  <input type="hidden" name="COMMENT1" value="Mobile Channel">
                  <input type="hidden" name="SHIPPING_CODE" value="774|654">
                  <input type="hidden" name="SHIPPING_CO" value="GB">
                  <input type="hidden" name="BILLING_CODE" value="50001|Flat 1234">
                  <input type="hidden" name="BILLING_CO" value="US">
                  <input type="hidden" name="CUST_NUM" value="332a85b">
                  <input type="hidden" name="VAR_REF" value="High Value Product">
                  <input type="hidden" name="PROD_ID" value="SKU1000054">
                  <input type="hidden" name="HPP_LANG" value="GB">
                  <input type="hidden" name="HPP_VERSION" value="2">
                  <!-- <input type="hidden" name="MERCHANT_RESPONSE_URL" value="https://www.example.com/responseUrl"> -->
                  <input type="hidden" name="MERCHANT_RESPONSE_URL" value="<?php echo base_url('books/payment_response')?>">
                  <input type="hidden" name="CARD_PAYMENT_BUTTON" value="Pay Invoice">
                  <input type="hidden" name="CUSTOM_FIELD_NAME" value="Custom Field Data">
                  <input type="hidden" name="SHA1HASH" value="<?php echo $sha1hash_string; ?>">
            </div>
            <div class="box-footer">
                <?php if($ads->price==0){?>
                    <button type="button" class="btn btn-success queue"> Book Appointment </button>
                <?php }else{ ?>
                
                <!-- <input type="submit" class="btn btn-info"  value="Schedule Appointment via Payment" > -->
                <button type="button" class="btn btn-success via_payment"> Book Appointment </button>
                <?php } ?>
                <!-- <button type="button" class="btn btn-success payment_complete"> Completed </button>
                <button type="button" class="btn btn-danger payment_pending"> Pending </button> -->
                <!-- <a href="<?php echo base_url().'books/payment/'.$client_id.'/'.$ad_id;?>" class="btn btn-info">Appointment</a> -->
                <!-- <a href="<?php echo base_url()?>" class="btn btn-info">Appointment</a> -->
            </div>
            <div class="last_id" style="display:none;"></div>
            <?php echo form_close(); ?>
        </div>
        <!-- /.box -->

    </section>
    <!-- /.content -->
</div>

<script type="text/javascript">
    $('document').ready(function () {
        $('.payment_complete').hide();
        $('.payment_pending').hide();
        $('#appointment_label').hide();
        
        show_date();

    });

    function show_date(){
        var client_id = $('#client_id').val();
        var ad_id = $('#ad_id').val();           

        $.post("<?php echo base_url('books/get_date');?>", {
                client_id: client_id,
                ad_id: ad_id
            })
            .done(function (data) {
                //console.log(data);
                $('#show_date').append(data);
            });
    }

    $('.queue').click(function () {
        var vali_flag=0;

        if(validation()){
            in_queue();
        }else{
            error;
        }
    });

    function validation() {

        var name = $('#name').val();
        var email = $('#email').val();
        var number = $('#number').val();
        var show_time = $('#show_date option:selected').val();

        var name_flag = 0;
        var email_flag = 0;
        var number_flag = 0;
        var date_flag = 0;

        if (name == '') {
            $('#verify_name').html('<div style="color:red;">Please Fillup The Name Field .</div>');
        } else {
            name_flag = 1;
            $('#verify_name').html('');
        }

        if (show_time == '') {
            $('#varify_date').html('<div style="color:red;">Please Select Appointment Date Field...</div>');
        } else {
            date_flag = 1;
            $('#varify_date').html('');
        }

        var re = /\S+@\S+\.\S+/;
        if (!(re.test(email))) {
            $('#verify_email').html('<div style="color:red;">Please Enter a Valid Email.</div>');
        } else {
            email_flag = 1;
            $('#verify_email').html('');
        }

        if (!(number.match(/^-?\d+$/))) {
            $('#verify_number').html('<div style="color:red;">Please Enter a Valid Number.</div>');
        } else {
            number_flag = 1;
            $('#verify_number').html('');
        }
        //return
        if ((email_flag == 1) && (number_flag == 1) && (name_flag == 1) && (date_flag == 1)) {
            return true;            
        }else{
            return false;
        }
    }
    //


    function in_queue() {

        var name = $('#name').val();
        var email = $('#email').val();
        var number = $('#number').val();
        var client_id = $('#client_id').val();
        var ad_id = $('#ad_id').val();

        var appo_date = $('#appo_date').val();
        var appo_time = $('#appo_time').val();
        // console.log(appo_date);
        // console.log(appo_time);
       // retrun
        $.post("<?php echo base_url('books/add_queue');?>", {
                name: name,
                email: email,
                number: number,
                client_id: client_id,
                ad_id: ad_id,
                appo_date:appo_date,
                appo_time:appo_time
            })
            .done(function (data) {
                //console.log(data);
                    $('#suc_msg').html(
                        '<div class="alert alert-success alert-dismissible"><a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a><strong>Success!</strong> You appointment is successfully booked. Now Check Your email for confirmation, also check your spam folder.</div>'
                    );
                    //alert('Value Inserted');
                    $('.via_payment').hide();
                    $('#verify_number').html('');

            });



    }

    $('.via_payment').click(function () {

        if(validation()){
            $('#book_form').submit();
            //via_payment();
        }else{
            error;
        }
    });

    function via_payment() {

        var name = $('#name').val();
        var email = $('#email').val();
        var number = $('#number').val();
        var client_id = $('#client_id').val();
        var ad_id = $('#ad_id').val();
        var appo_date = $('#appo_date').val();
        var appo_time = $('#appo_time').val();

        $.post("<?php echo base_url('books/add_booking_via_payment');?>", {
                name: name,
                email: email,
                number: number,
                client_id: client_id,
                ad_id: ad_id,
                appo_date:appo_date,
                appo_time:appo_time
            })
            .done(function (data) {
                console.log(data);
                if(data){                
                    // $('#suc_msg').html(
                    //     '<div class="alert alert-success alert-dismissible"><a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a><strong>Success!</strong> Your Details Has Successfully Stored.</div>'
                    // );
                    // $('.last_id').html(data);
                    // //alert('Value Inserted');
                    // $('.queue').hide();
                    // $('.via_payment').hide();
                    // $('.payment_complete').show();
                    // $('.payment_pending').show();
                    location.href = "<?php echo base_url('books/payment/')?>"+ad_id+'/'+data +'/'+client_id;
                }
            });

    }


    $('.payment_complete').click(function () {
        payment_complete();
    });

    function payment_complete() {

        var last_id = $('.last_id').text();

        $.post("<?php echo base_url('books/payment_complete');?>", {
                last_id: last_id
            })
            .done(function (data) {
                console.log(data);

                if (data) {
                    $('#suc_msg').html(
                        '<div class="alert alert-success alert-dismissible"><a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a><strong>Success!</strong> Payment Completed Successfully.</div>'
                    );
                    $('.payment_pending').hide();
                }

            });

    }

    $('.payment_pending').click(function () {
        $('.payment_complete').hide();
    });
</script>
