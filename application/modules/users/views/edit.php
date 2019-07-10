<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        Edit user
        <small>Client the form bellow</small>
      </h1> 
                <?php $uid =  $users->id ?>  
                <a href="<?php echo site_url('/configurations/singup?uid=').$uid; ?>">change Setting</a>
           
    </section>

    <!-- Main content -->
    <section class="content">

      <!-- Default box -->
        <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title">User Edit</h3>
            </div>

            <?php echo form_open_multipart(current_url()); ?>
            <div class="box-body">
            <input type="hidden" name="old_email" value="<?php echo $users->email; ?>">
           
                <div class="row clearfix">

                    <div class="col-md-6">
                        <label for="first_name" class="control-label"><span class="text-danger">*</span>First Name</label>
                        <div class="form-group">
                            <input type="text" name="first_name" value="<?php echo $users->first_name; ?>" class="form-control" id="role_name" />
                            <span class="text-danger"><?php echo form_error('first_name');?></span>
                        </div>
                    </div>   
                    <div class="col-md-6">
                        <label for="last_name" class="control-label"><span class="text-danger">*</span>last Name</label>
                        <div class="form-group">
                            <input type="text" name="last_name" value="<?php echo $users->last_name; ?>" class="form-control" id="role_name" />
                            <span class="text-danger"><?php echo form_error('last_name');?></span>
                        </div>
                    </div>  
                </div>
                <div class="row clearfix">               
                    <div class="col-md-6">
                        <label for="email" class="control-label"><span class="text-danger">*</span>Email</label>
                        <div class="form-group">
                            <input type="text" name="email" value="<?php echo $users->email; ?>" class="form-control" id="email" />
                            <span class="text-danger"><?php echo form_error('email');?></span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label for="phone" class="control-label"><span class="text-danger">*</span>Phone</label>
                        <div class="form-group">
                            <input type="number" name="phone" value="<?php echo $users->phone; ?>" class="form-control" id="phone" />
                            <span class="text-danger"><?php echo form_error('phone');?></span>
                        </div>
                    </div>

                </div>
                <div class="row clearfix">
                    <div class="col-md-6">
                        <label for="user_role" class="control-label"><span class="text-danger">*</span>User Role</label>
                        <div class="form-group">
                            <select class="form-control select2" style="width: 100%;" name="user_role">
                                <?php 
                                    $userrole=$users->user_role;
                                    foreach ($user_roles as  $role) {
                                        if($userrole==$role->id){
                                            echo '<option value="'.$role->id.'" selected>'.$role->role_name.'</option>';  
                                        }else{
                                            echo '<option value="'.$role->id.'">'.$role->role_name.'</option>';
                                        }
                                    } 
                                ?>
                            </select>
                            <span class="text-danger"><?php echo form_error('user_role');?></span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label for="address" class="control-label">Address</label>
                        <div class="form-group">
                            <input type="text" name="address" value="<?php echo $users->address; ?>" onFocus="geolocate()" class="form-control" id="address" />
                            <span class="text-danger"><?php echo form_error('address');?></span>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label for="region" class="control-label">Region</label>
                        <div class="form-group">
                            <input type="text" name="region" value="<?php echo $users->region; ?>" class="form-control" id="region" />
                            <span class="text-danger"><?php echo form_error('region');?></span>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label for="city" class="control-label">City</label>
                        <div class="form-group">
                            <input type="text" name="city" value="<?php echo $users->city; ?>" class="form-control" id="city" />
                            <span class="text-danger"><?php echo form_error('city');?></span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label for="state" class="control-label">State</label>
                        <div class="form-group">
                            <input type="text" name="state" value="<?php echo $users->state; ?>" class="form-control" id="state" />
                            <span class="text-danger"><?php echo form_error('state');?></span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label for="country" class="control-label">Country</label>
                        <div class="form-group">
                            <input type="text" name="country" value="<?php echo $users->country; ?>" class="form-control" id="country" />
                            <span class="text-danger"><?php echo form_error('country');?></span>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label for="postal_code" class="control-label">Postal Code</label>
                        <div class="form-group">
                            <input type="text" name="postal_code" value="<?php echo$users->postal_code; ?>" class="form-control" id="postal_code" />
                            <span class="text-danger"><?php echo form_error('postal_code');?></span>
                        </div>
                    </div>
                    
                     <div class="col-md-6">
                        <label for="category_id" class="control-label"><span class="text-danger">*</span>Choose Category</label>
                        <div class="form-group">
                          <select class="form-control" id="category_id" name="category_id" >
                           <?php
                                foreach ($category_names as $category_name) { ?>
                                    <option <?php if($users->category_id == $category_name->id) echo 'selected';?> value="<?php echo $category_name->id; ?>"> <?php echo $category_name->name; ?></option>;
                                <?php }
                                ?>
							</select>
                                <span class="text-danger"><?php echo form_error('category_id');?></span>                        
                        </div>
                    </div>
                    
                    
                    <input type="hidden" name="lat" id="lat" value="<?php echo $users->lat; ?>">
                    <input type="hidden" name="lng" id="lng" value="<?php echo $users->lng; ?>">

                </div>
                
            </div>
            <div class="box-footer">
                <button type="submit" class="btn btn-success">
                    <i class="fa fa-check"></i> Save
                </button>
            </div>
            <?php echo form_close(); ?>
        </div>
      <!-- /.box -->

    </section>
    <!-- /.content -->
  </div>


  <script type="text/javascript">
      
      var placeSearch, autocomplete;
      var componentForm = {
        street_number: 'short_name',
        route: 'long_name',
        locality: 'long_name',
        administrative_area_level_1: 'short_name',
        country: 'long_name',
        postal_code: 'short_name'
      };

      function initAutocomplete() {
        // Create the autocomplete object, restricting the search to geographical
        // location types.
        autocomplete = new google.maps.places.Autocomplete(
            /** @type {!HTMLInputElement} */(document.getElementById('address')),
            {types: ['geocode']});

        // When the user selects an address from the dropdown, populate the address
        // fields in the form.
        autocomplete.addListener('place_changed', fillInAddress);
      }

      function fillInAddress() {
        // Get the place details from the autocomplete object.
        var place = autocomplete.getPlace();
        console.log(place.geometry);

        $('#lat').val(place.geometry.location.lat());
        $('#lng').val(place.geometry.location.lng());

        // for (var component in componentForm) {
        //   document.getElementById(component).value = '';
        //   document.getElementById(component).disabled = false;
        // }

        // // Get each component of the address from the place details
        // // and fill the corresponding field on the form.
        for (var i = 0; i < place.address_components.length; i++) {
          var addressType = place.address_components[i].types[0];
          if(addressType == 'administrative_area_level_2'){
            document.getElementById('city').value = place.address_components[i].long_name;
          }

          if(addressType == 'locality'){
            document.getElementById('region').value = place.address_components[i].long_name;
          }

          if(addressType == 'administrative_area_level_1'){
            document.getElementById('state').value = place.address_components[i].long_name;
          }
          if(addressType == 'country'){
            document.getElementById('country').value = place.address_components[i].long_name;
          }
          if(addressType == 'postal_code'){
            document.getElementById('postal_code').value = place.address_components[i].long_name;
          }
        }
      }

      // Bias the autocomplete object to the user's geographical location,
      // as supplied by the browser's 'navigator.geolocation' object.
      function geolocate() {
        if (navigator.geolocation) {
          navigator.geolocation.getCurrentPosition(function(position) {
            var geolocation = {
              lat: position.coords.latitude,
              lng: position.coords.longitude
            };
            var circle = new google.maps.Circle({
              center: geolocation,
              radius: position.coords.accuracy
            });
            autocomplete.setBounds(circle.getBounds());
          });
        }
      }
  </script>

  <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyB6D1K61C-VBTXW_g8NxRdq7twVzdvxclk&libraries=places&callback=initAutocomplete"
        async defer></script>