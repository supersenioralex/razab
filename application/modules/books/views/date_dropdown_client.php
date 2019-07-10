<select class="form-control select_dt" id="appo_date">
    <option value="" >Select Date</option>
    <?php foreach($dtas as $dta) {?>
        <?php if($dta>=date("m/d/Y")){ ?> 

            <option value="<?php echo $dta; ?>">
                <?php echo $dta; ?>
            </option>

        <?php  } ?>


    <?php } ?>
</select>


<script type="text/javascript">
    $("#appo_date").change(function () {
        var appo_date = $('#appo_date').find(":selected").text();
        var client_id =$('#client_id').val();
        //var ad_id = $(this).parent().find("input").val();
        var ad_id =$('#modal_ad_id').text();
        // console.log(appo_date);
        // console.log(client_id);
        // console.log(ad_id);
        show_time(appo_date,client_id,ad_id);
    });



    function show_time(appo_date,client_id,ad_id){
        // var client_id = $('#client_id').val();
        // var ad_id = $('#ad_id').val();

            

        $.post("<?php echo base_url('books/get_time');?>", {
                client_id: client_id,
                ad_id: ad_id,
                appo_date:appo_date
            })
            .done(function (data) {
                //console.log(data);
                $('#show_time').html('');
                $('#appointment_label').show();
                $('#show_time').append(data);
                

            });
    }


</script>