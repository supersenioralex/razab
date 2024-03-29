<?php
/**
 * Created by PhpStorm.
 * User: sunil
 * Date: 01-02-2018
 * Time: 01:59 PM
 */

?>
<div class="login-box-body">
    <p class="login-box-msg">Reset Password</p>
    <?php if($error){?>
    <div class="alert alert-danger alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
        <h4><i class="icon fa fa-times"></i> Alert!</h4>
        <?php echo $error;?>
    </div>
    <?php }?>
    <form action="<?php echo current_url();?>" method="post" id="login-form">
        <div class="form-group has-feedback">
            <input type="email" name="email" class="form-control" placeholder="Email">
            <span class="glyphicon glyphicon-user form-control-feedback"></span>
        </div>
        <?php if (isset($referrer)){?>
            <input type="hidden" name="referrer" value="<?php echo $referrer;?>">
        <?php }?>
        <div class="row">
            <div class="col-xs-12">
                <button type="button" id="sign-btn" class="btn btn-primary btn-block btn-flat">Send Password Reset Mail</button>
            </div>
            <!-- /.col -->
        </div>
    </form>

    <a href="<?php echo base_url();?>users/auth">Login</a><br>
    <a href="<?php echo base_url();?>users/auth/register" class="text-center">Register a new membership</a>

</div>

<script type="text/javascript">
    $(document).ready(function () {
        $('#sign-btn').click(function () {
            $('#login-form').submit();
        });
    });
</script>
