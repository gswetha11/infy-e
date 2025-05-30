<div class="login-box w-auto">
    <!-- Content Header (Page header) -->
    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-info">
                        <form class="form-horizontal" method="POST" id="add_dboy_form">
                            <?php if (isset($user_data) && !empty($user_data)) { ?>
                                <input type="hidden" name="user_id" value="<?= $user_data['to_be_seller_id'] ?>">
                                <input type='hidden' name='user_name' value='<?= $user_data['to_be_seller_name'] ?>'>
                                <input type='hidden' name='user_mobile' value='<?= $user_data['to_be_seller_mobile'] ?>'>
                            <?php
                            } ?>
                            <div class="card-body">
                                <div class="login-logo">
                                    <a href="<?= base_url() . 'delivery_boy/login' ?>"><img src="<?= base_url() . $logo ?>"></a>
                                </div>
                                <h4 class="mb-4">Delivery Boy Registration</h4>
                                <h5>Personal Details</h5>
                                <hr>
                                <div class="form-group row">
                                    <label for="name" class="col-sm-2 col-form-label">Name <span class='text-danger text-sm'>*</span></label>
                                    <div class="col-sm-10">
                                        <input type="text" class="form-control" id="name" placeholder="Delivery Boy Name" name="name">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="mobile" class="col-sm-2 col-form-label">Mobile <span class='text-danger text-sm'>*</span></label>
                                    <div class="col-sm-10">
                                         <input type="text" maxlength="16" oninput="validateNumberInput(this)" class="form-control" id="delivery_boy_mobile" placeholder="Enter Mobile" name="mobile">

                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="email" class="col-sm-2 col-form-label">Email <span class='text-danger text-sm'>*</span></label>
                                    <div class="col-sm-10">
                                        <input type="email" class="form-control" id="email" placeholder="Enter Email" name="email">
                                    </div>
                                </div>

                                <div class="form-group row ">
                                    <label for="password" class="col-sm-2 col-form-label">Password <span class='text-danger text-sm'>*</span></label>

                                    <div class="input-group col-sm-10">
                                        <input type="password" class="form-control form-input passwordToggle" name="password" id="password" placeholder="Type Password here" value="" required>
                                        <span class="input-group-text togglePassword" style="cursor: pointer;">
                                            <i class="fa fa-eye"></i>
                                        </span>
                                    </div>
                                </div>
                                <div class="form-group row ">
                                    <label for="confirm_password" class="col-sm-2 col-form-label">Confirm Password <span class='text-danger text-sm'>*</span></label>

                                    <div class="input-group col-sm-10">
                                        <input type="password" class="form-control form-input passwordToggle" name="confirm_password" id="confirm_password" placeholder="Type Confirm Password here" value="" required>
                                        <span class="input-group-text togglePassword" style="cursor: pointer;">
                                            <i class="fa fa-eye"></i>
                                        </span>
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label for="address" class="col-sm-2 col-form-label">Address <span class='text-danger text-sm'>*</span></label>
                                    <div class="col-sm-10">
                                        <input type="text" class="form-control" id="address" placeholder="Enter Address" name="address">
                                    </div>
                                </div>

                                <?php
                                $pincode_wise_deliverability = (isset($shipping_method['pincode_wise_deliverability']) && $shipping_method['pincode_wise_deliverability'] == 1) ? $shipping_method['pincode_wise_deliverability'] : '0';
                                $city_wise_deliverability = (isset($shipping_method['city_wise_deliverability']) && $shipping_method['city_wise_deliverability'] == 1) ? $shipping_method['city_wise_deliverability'] : '0';
                                ?>
                                <input type="hidden" name="city_wise_deliverability" value="<?= $city_wise_deliverability ?>">
                                <input type="hidden" name="pincode_wise_deliverability" value="<?= $pincode_wise_deliverability ?>">
                                <div class="form-group row">
                                    <?php if ((isset($shipping_method['pincode_wise_deliverability']) && $shipping_method['pincode_wise_deliverability'] == 1) || (isset($shipping_method['local_shipping_method']) && isset($shipping_method['shiprocket_shipping_method']) && $shipping_method['local_shipping_method'] == 1 && $shipping_method['shiprocket_shipping_method'] == 1)) { ?>
                                        <label for="serviceable_zipcodes" class="col-form-label col-md-2">Serviceable Zipcodes <span class='text-danger text-sm'>*</span></label>
                                        <div class="col-md-10">
                                            <select name="serviceable_zipcodes[]" class="deliveryboy_search_zipcode form-control w-100" multiple onload="multiselect()" id="deliverable_zipcodes">
                                                <?php if (isset($zipcodes) && !empty($zipcodes)) {
                                                    $zipcodes_name = fetch_details('zipcodes', "", 'zipcode,id', "", "", "", "", "id", $zipcodes);
                                                    foreach ($zipcodes_name as $row) {
                                                ?>
                                                        <option value="<?= $row['id'] ?>"><?= $row['zipcode'] ?></option>
                                                <?php }
                                                } ?>
                                            </select>
                                        </div>

                                    <?php  }
                                    if (isset($shipping_method['city_wise_deliverability']) && $shipping_method['city_wise_deliverability'] == 1 && $shipping_method['shiprocket_shipping_method'] != 1) { ?>
                                        <label for="cities" class="col-form-label col-md-2">Serviceable Cities <span class='text-danger text-sm'>*</span></label>
                                        <?php
                                        $selected_city_ids = (isset($fetched_data[0]['serviceable_cities']) &&  $fetched_data[0]['serviceable_cities'] != NULL) ? explode(",", $fetched_data[0]['serviceable_cities']) : [];
                                        ?>
                                        <div class="col-md-10">

                                            <select class="form-control deliveryboy_search_cities w-100" name="serviceable_cities[]" id="deliverable_cities" multiple>
                                                <?php foreach ($cities as $row) { ?>
                                                    <option value="<?= $row['id'] ?>"><?= $row['name'] ?></option>
                                                <?php }; ?>
                                            </select>
                                        </div>
                                    <?php } ?>

                                </div>

                                <div class="form-group row">
                                    <label for="driving_license" class="col-sm-2 col-form-label">Driving License <span class='text-danger text-sm'>*</span></label>
                                    <div class="col-sm-10">
                                        <input type="file" class="form-control" name="driving_license[]" id="driving_license" accept="image/*" multiple />
                                    </div>
                                </div>

                                <div class="form-group">
                                    <button type="reset" class="btn btn-warning">Reset</button>
                                    <button type="submit" class="btn btn-success" id="submit_btn">Submit</button>
                                </div>
                            </div>

                            <!-- /.card-footer -->
                        </form>
                    </div>
                    <!--/.card-->
                </div>
                <!--/.col-md-12-->
            </div>
            <!-- /.row -->
        </div><!-- /.container-fluid -->
    </section>
    <!-- /.content -->
</div>