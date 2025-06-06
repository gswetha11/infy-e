<!-- breadcrumb -->
<section class="breadcrumb-title-bar colored-breadcrumb deeplink_wrapper">
    <div class="main-content responsive-breadcrumb">
        <h2><?= !empty($this->lang->line('checkout')) ? str_replace('\\', '', $this->lang->line('checkout')) : 'Checkout' ?></h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= base_url() ?>"><?= !empty($this->lang->line('home')) ? str_replace('\\', '', $this->lang->line('home')) : 'Home' ?></a></li>
                <li class="breadcrumb-item"><?= !empty($this->lang->line('checkout')) ? str_replace('\\', '', $this->lang->line('checkout')) : 'Checkout' ?></li>
            </ol>
        </nav>
    </div>

</section>
<!-- end breadcrumb -->
<!-- checkout -->
<section>
    <div class="main-content">
        <form class="needs-validation" id="checkout_form" method="POST" action="<?= base_url('cart/place-order') ?>">
            <div class="row">
                <div class="col-xl-8 bg-white mt-5">
                    <h2 class="checkout-form-title"><?= !empty($this->lang->line('billing_details')) ? str_replace('\\', '', $this->lang->line('billing_details')) : 'Billing Details' ?></h2>
                    <div class="ship-details-wrapper">
                        <input type="hidden" name="product_type" value="<?= $cart[0]['type']; ?>">
                        <input type="hidden" id="download_allowed" name="download_allowed" value="<?= in_array(0, $cart['download_allowed']) ? 0 : 1 ?>">
                        <?php if ($cart[0]['type'] != 'digital_product') { ?>
                            <div class="align-item-center ship-title-details justify-content-between user-add d-flex">
                                <h5 class="pb-3"><?= !empty($this->lang->line('shipping_address')) ? str_replace('\\', '', $this->lang->line('shipping_address')) : 'Shipping Address' ?></h5>
                                <a href="#" data-izimodal-open=".address-modal"><i class="fas fa-edit edit-icon"></i></a>
                            </div>

                            <div class="shipped-details mt-3">
                                <p class="text-muted" id="address-name-type"><?= isset($default_address) && !empty($default_address) ? $default_address[0]['name'] . ' - ' . ucfirst($default_address[0]['type']) : '' ?></p>
                                <p class="text-muted" id="address-full"><?= isset($default_address) && !empty($default_address) ? $default_address[0]['address'] . ' , ' . $default_address[0]['area'] . ' , ' . $default_address[0]['city'] : '' ?></p>
                                <p class="text-muted" id="address-country"><?= isset($default_address) && !empty($default_address) ? $default_address[0]['state'] . ' , ' . $default_address[0]['country'] . ' - ' . $default_address[0]['pincode'] : '' ?></p>
                                <p class="text-muted" id="address-mobile"><?= isset($default_address) && !empty($default_address) ? $default_address[0]['mobile'] : '' ?></p>
                            </div>

                            </br>
                            <!-- checking product deliverable or not  -->
                            <div id="deliverable_status">
                                <?php
                                $product_not_delivarable = array();
                                if (isset($default_address) && !empty($default_address)) {
                                    $zipcode_id = fetch_details('zipcodes', ['zipcode' => $default_address[0]['pincode']], 'id')[0];

                                    $product_delivarable = check_cart_products_delivarable($cart[0]['user_id'], $default_address[0]['area_id'], $default_address[0]['pincode'], $zipcode_id);
                                    if (!empty($product_delivarable)) {
                                        $product_not_delivarable = array_filter($product_delivarable, function ($var) {
                                            return ($var['is_deliverable'] == false);
                                        });
                                        $product_not_delivarable = array_values($product_not_delivarable);
                                        $deliverable_error_msg = "";
                                        foreach ($product_not_delivarable as $p_id) {
                                            if (!empty($p_id['product_id'])) {
                                                $deliverable_error_msg = (!empty($this->lang->line('product_not_delivarable_msg'))) ? str_replace('\\', '', $this->lang->line('product_not_delivarable_msg')) : "Some of the item(s) are not delivarable on selected address. Try changing address or modify your cart items.";
                                                continue;
                                            }
                                        }
                                ?>
                                        <b class="text-danger"><?= $deliverable_error_msg ?></b>
                                    <?php }
                                } else { ?>
                                    <b class="text-danger"><?= !empty($this->lang->line('please_select_address')) ? str_replace('\\', '', $this->lang->line('please_select_address')) : 'Please select address.'; ?></b>
                                <?php } ?>
                            </div>
                        <?php }
                        $settings = get_settings('system_settings', true);
                        ?>

                        <?php
                        if (is_array($cart) && isset($cart[0]) && is_array($cart[0])) {
                            if ($cart[0]['type'] != 'digital_product') {
                                foreach ($cart as $cart_item) {
                                    if (!is_array($cart_item)) {
                                        continue; // Skip if it's not an array
                                    }

                                    if (!empty($cart_item['is_attachment_required']) && $cart_item['is_attachment_required'] == 1) {
                        ?>
                                        <h4 class="mt-3">
                                            <?= !empty($this->lang->line('allow_order_attachments'))
                                                ? str_replace('\\', '', $this->lang->line('allow_order_attachments'))
                                                : 'Upload Order Attachments if you have any (Only images and docs are allowed)' ?>
                                            For <?= $cart_item['name'] ?>
                                        </h4>
                                        <div class="input-group">
                                            <input type="file" class="form-control" name="documents[]" multiple id="documents">
                                        </div>
                        <?php
                                    }
                                }
                            }
                        } ?>

                        <?php if (in_array(0, $cart['download_allowed']) && $cart[0]['type'] == 'digital_product') { ?>
                            <div class="input-group mt-3">
                                <input name="email" type="text" id="digital_product_email" class="form-control" placeholder="<?= !empty($this->lang->line('please_enter_your_email_id')) ? str_replace('\\', '', $this->lang->line('please_enter_your_email_id')) : 'Please enter your email ID'; ?> ">
                            </div>
                        <?php } ?>

                        <input type="hidden" name="address_id" id="address_id" value="<?= isset($default_address) && !empty($default_address) && $cart[0]['type'] != 'digital_product' ? $default_address[0]['id']  : '' ?>" />
                        <input type="hidden" name="mobile" id="mobile" value="<?= isset($default_address) && !empty($default_address) ? $default_address[0]['mobile'] : $wallet_balance[0]['mobile'] ?>" />
                    </div>
                    <hr>
                    <input type="hidden" name="total" value="<?= format_price($cart['sub_total']) ?>">
                    <input type="hidden" id="temp_total" name="temp_total" value="<?= $cart['total_arr'] ?>">
                    <input type="hidden" name="product_variant_id" value="<?= implode(',', array_column($cart, 'id')) ?>">
                    <input type="hidden" name="quantity" value="<?= implode(',', array_column($cart, 'qty')) ?>">
                    <input type="hidden" id="current_wallet_balance" value="<?= format_price($wallet_balance[0]['balance']) ?>">
                    <input type="hidden" id="wallet_used" name="wallet_used">

                    <input type="hidden" name="is_time_slots_enabled" id="is_time_slots_enabled" value="<?= ($time_slot_config['is_time_slots_enabled'] == 1) ? 1 : 0 ?>">
                    <input type="hidden" name="product_type" id="product_type" value="<?= $cart[0]['type'] ?>">
                    <div class="input-group">
                        <input type="text" class="form-control" placeholder="<?= !empty($this->lang->line('special_note_for_order')) ? str_replace('\\', '', $this->lang->line('special_note_for_order')) : 'Special Note for Order' ?>" name="order_note" id="order_note">
                    </div>
                    <?php if (isset($time_slot_config['is_time_slots_enabled']) && $time_slot_config['is_time_slots_enabled'] == 1 && $cart[0]['type'] != 'digital_product') {
                        //If Time Slot is Enabled
                    ?>
                        <hr>
                        <h4 class="mt-3"><?= !empty($this->lang->line('preferred_delivery_date_time')) ? str_replace('\\', '', $this->lang->line('preferred_delivery_date_time')) : 'Preferred Delivery Date / Time' ?></h4>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="far fa-clock"></i></span>
                            </div>
                            <input type="text" class="form-control float-right" id="datepicker">
                            <input type="hidden" id="start_date" class="form-control float-right">
                        </div>
                        <div class="mt-3" id="time_slots">
                            <?php foreach ($time_slots as $row) { ?>
                                <div class="custom-control custom-radio">
                                    <input id="<?= $row['id'] ?>" name="delivery_time" type="radio" class="custom-control-input time-slot-inputs" data-last_order_time="<?= $row['last_order_time'] ?>" value="<?= $row['title'] ?>" required>
                                    <label class="custom-control-label" for="<?= $row['id'] ?>"><?= $row['title'] ?></label>
                                </div>
                            <?php } ?>
                        </div>
                    <?php } ?>
                    <hr>
                    <input type="hidden" name="delivery_date" id="delivery_date" class="form-control float-right">
                    <div class="align-item-center ship-title-details justify-content-between d-flex">
                        <h5><?= !empty($this->lang->line('wallet_balance')) ? str_replace('\\', '', $this->lang->line('wallet_balance')) : 'Use wallet balance' ?></h5>
                    </div>
                    <?php $disabled = $wallet_balance[0]['balance'] == 0 ? 'disabled' : '' ?>
                    <div class="form-check d-flex">
                        <input class="form-check-input" type="checkbox" value="" id="wallet_balance" <?= $disabled ?>>
                        <label class="form-check-label d-flex" for="wallet_balance">
                            <?= !empty($this->lang->line('available_balance')) ? str_replace('\\', '', $this->lang->line('available_balance')) : 'Available balance' ?> : <?= $currency . '<span id="available_balance">' . format_price($wallet_balance[0]['balance']) . '</span>' ?>
                        </label>
                    </div>

                    <div class="ship-details-wrapper mt-3 payment-methods">
                        <div class="align-item-center ship-title-details justify-content-between d-flex">
                            <h5><?= !empty($this->lang->line('payment_method')) ? str_replace('\\', '', $this->lang->line('payment_method')) : 'Payment Method' ?></h5>
                        </div>
                        <div class="shipped-details mt-3 col-md-6">
                            <table class="table table-step-shipping">
                                <tbody>
                                    <?php if (isset($payment_methods['cod_method']) && $payment_methods['cod_method'] == 1) { ?>
                                        <tr>
                                            <label for="cod">
                                                <td>
                                                    <label for="cod">
                                                        <input id="cod" title="<?= isset($cart[0]['is_cod_allowed']) && $cart[0]['is_cod_allowed'] == 0 ? 'Cash on delivery is not allowed for one of the item in your cart' : 'Please select one of this options.' ?>" name="payment_method" type="radio" value="COD" <?= isset($cart[0]['is_cod_allowed']) && $cart[0]['is_cod_allowed'] == 0 ? 'disabled' : '' ?>>
                                                    </label>
                                                </td>
                                                <td>
                                                    <label for="cod">
                                                        <img src="<?= THEME_ASSETS_URL . 'images/cod.png' ?>" class="payment-gateway-images" alt="COD">
                                                    </label>
                                                </td>
                                                <td>
                                                    <label for="cod">
                                                        <?= !empty($this->lang->line('cash_on_delivery')) ? str_replace('\\', '', $this->lang->line('cash_on_delivery')) : 'Cash On Delivery' ?>
                                                    </label>
                                                </td>
                                        </tr>
                                    <?php } ?>
                                    <?php if (isset($payment_methods['paypal_payment_method']) && $payment_methods['paypal_payment_method'] == 1) { ?>
                                        <tr>
                                            <td>
                                                <label for="paypal">
                                                    <input id="paypal" name="payment_method" type="radio" value="Paypal">
                                                </label>
                                            </td>
                                            <td>
                                                <label for="paypal">
                                                    <img src="<?= THEME_ASSETS_URL . 'images/paypal.png' ?>" class="payment-gateway-images" alt="Paypal">
                                                </label>
                                            </td>
                                            <td>
                                                <label for="paypal">
                                                    Paypal
                                                </label>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                    <?php if (isset($payment_methods['razorpay_payment_method']) && $payment_methods['razorpay_payment_method'] == 1) { ?>
                                        <tr>
                                            <td>
                                                <label for="razorpay">
                                                    <input id="razorpay" name="payment_method" type="radio" value="Razorpay">
                                                </label>
                                            </td>
                                            <td>
                                                <label for="razorpay">
                                                    <img src="<?= THEME_ASSETS_URL . 'images/razorpay.png' ?>" class="payment-gateway-images" alt="Razorpay">
                                                </label>
                                            </td>
                                            <td>
                                                <label for="razorpay">
                                                    RazorPay
                                                </label>
                                            </td>
                                        </tr>
                                    <?php } ?>





                                    <?php if (isset($payment_methods['paystack_payment_method']) && $payment_methods['paystack_payment_method'] == 1) { ?>
                                        <tr>
                                            <td>
                                                <label for="paystack">
                                                    <input id="paystack" name="payment_method" type="radio" value="Paystack">
                                                </label>
                                            </td>
                                            <td>
                                                <label for="paystack">
                                                    <img src="<?= THEME_ASSETS_URL . 'images/paystack.png' ?>" class="payment-gateway-images" alt="Paystack">
                                                </label>
                                            </td>
                                            <td>
                                                <label for="paystack">
                                                    Paystack
                                                </label>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                    <?php if (isset($payment_methods['payumoney_payment_method']) && $payment_methods['payumoney_payment_method'] == 1) { ?>
                                        <tr>
                                            <td>
                                                <label for="payumoney">
                                                    <input id="payumoney" name="payment_method" type="radio" value="Payumoney">
                                                </label>
                                            </td>
                                            <td>
                                                <label for="payumoney">
                                                    <img src="<?= THEME_ASSETS_URL . 'images/payumoney.png' ?>" class="payment-gateway-images" alt="Payumoney">
                                                </label>
                                            </td>
                                            <td>
                                                <label for="payumoney">
                                                    Payumoney
                                                </label>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                    <?php if (isset($payment_methods['flutterwave_payment_method']) && $payment_methods['flutterwave_payment_method'] == 1) { ?>
                                        <tr>
                                            <td>
                                                <label for="flutterwave">
                                                    <input id="flutterwave" name="payment_method" type="radio" value="Flutterwave">
                                                </label>
                                            </td>
                                            <td>
                                                <label for="flutterwave">
                                                    <img src="<?= THEME_ASSETS_URL . 'images/flutterwave.png' ?>" class="payment-gateway-images" alt="Flutterwave">
                                                </label>
                                            </td>
                                            <td>
                                                <label for="flutterwave">
                                                    Flutterwave
                                                </label>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                    <?php if (isset($payment_methods['paytm_payment_method']) && $payment_methods['paytm_payment_method'] == 1) { ?>
                                        <tr>
                                            <td>
                                                <label for="paytm">
                                                    <input id="paytm" name="payment_method" type="radio" value="Paytm">
                                                </label>
                                            </td>
                                            <td>
                                                <label for="paytm">
                                                    <img src="<?= THEME_ASSETS_URL . 'images/paytm.png' ?>" class="payment-gateway-images" alt="Paytm">
                                                </label>
                                            </td>
                                            <td>
                                                <label for="paytm">
                                                    Paytm
                                                </label>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                    <?php if (isset($payment_methods['stripe_payment_method']) && $payment_methods['stripe_payment_method'] == 1) { ?>
                                        <tr>
                                            <td>
                                                <label for="stripe">
                                                    <input id="stripe" name="payment_method" type="radio" value="Stripe">
                                                </label>
                                            </td>
                                            <td>
                                                <label for="stripe">
                                                    <img src="<?= THEME_ASSETS_URL . 'images/stripe.png' ?>" class="payment-gateway-images" alt="Stripe">
                                                </label>
                                            </td>
                                            <td>
                                                <label for="stripe">
                                                    Stripe
                                                </label>
                                            </td>
                                        </tr>
                                    <?php } ?>


                                    <?php if (isset($payment_methods['direct_bank_transfer']) && $payment_methods['direct_bank_transfer'] == 1) { ?>
                                        <tr>
                                            <td>
                                                <label for="bank_transfer">
                                                    <input id="bank_transfer" name="payment_method" type="radio" value="<?= BANK_TRANSFER ?>">
                                                </label>
                                            </td>
                                            <td>
                                                <label for="bank_transfer">
                                                    <img src="<?= THEME_ASSETS_URL . 'images/bank_transfer.png' ?>" class="payment-gateway-images" alt="Direct Bank Transfers">
                                                </label>
                                            </td>
                                            <td>
                                                <label for="bank_transfer">
                                                    <?= !empty($this->lang->line('direct_bank_transfers')) ? str_replace('\\', '', $this->lang->line('direct_bank_transfers')) : 'Direct Bank Transfers' ?>
                                                </label>
                                            </td>
                                        </tr>
                                    <?php } ?>

                                    <!-- Midtrans -->
                                    <?php if (isset($payment_methods['midtrans_payment_method']) && $payment_methods['midtrans_payment_method'] == 1) { ?>
                                        <tr>
                                            <td>
                                                <label for="midtrans">
                                                    <input id="midtrans" name="payment_method" type="radio" value="Midtrans">
                                                </label>
                                            </td>
                                            <td>
                                                <label for="midtrans">
                                                    <img src="<?= THEME_ASSETS_URL . 'images/midtrans.jpg' ?>" class="payment-gateway-images" alt="Midtrans">
                                                </label>
                                            </td>
                                            <td>
                                                <label for="midtrans">
                                                    Midtrans
                                                </label>
                                            </td>
                                        </tr>
                                    <?php } ?>

                                    <?php if (isset($payment_methods['myfaoorah_payment_method']) && $payment_methods['myfaoorah_payment_method'] == 1) { ?>
                                        <tr>
                                            <td>
                                                <label for="my_fatoorah">
                                                    <input id="my_fatoorah" name="payment_method" type="radio" value="my_fatoorah">
                                                </label>
                                            </td>
                                            <td>
                                                <label for="my_fatoorah">
                                                    <img src="<?= THEME_ASSETS_URL . 'images/myfatoorah.jpg' ?>" class="payment-gateway-images" alt="myfatoorah">
                                                </label>
                                            </td>
                                            <td>
                                                <label for="my_fatoorah">
                                                    My Fatoorah
                                                </label>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                    <?php if (isset($payment_methods['instamojo_payment_method']) && $payment_methods['instamojo_payment_method'] == 1) { ?>
                                        <tr>
                                            <td>
                                                <label for="instamojo">
                                                    <input id="instamojo" name="payment_method" type="radio" value="instamojo">
                                                </label>
                                            </td>
                                            <td>
                                                <label for="instamojo">
                                                    <img src="<?= THEME_ASSETS_URL . 'images/instamojo.png' ?>" class="payment-gateway-images" alt="instamojo">
                                                </label>
                                            </td>
                                            <td>
                                                <label for="instamojo">
                                                    Instamojo
                                                </label>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                    <?php if (isset($payment_methods['phonepe_payment_method']) && $payment_methods['phonepe_payment_method'] == 1) { ?>
                                        <tr>
                                            <td>
                                                <label for="phonepe">
                                                    <input id="phonepe" name="payment_method" type="radio" value="phonepe">
                                                </label>
                                            </td>
                                            <td>
                                                <label for="phonepe">
                                                    <img src="<?= THEME_ASSETS_URL . 'images/phonepay-logo.png' ?>" class="payment-gateway-images" alt="phonepe">
                                                </label>
                                            </td>
                                            <td>
                                                <label for="phonepe">
                                                    PhonePe
                                                </label>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div id="stripe_div">
                        <div id="stripe-card-element">
                            <!--Stripe.js injects the Card Element-->
                        </div>
                        <p id="card-error" role="alert"></p>
                        <p class="result-message hidden"></p>
                    </div>

                    <div id="my_fatoorah_div">
                        <div id="card-element">
                            <!--Stripe.js injects the Card Element-->
                        </div>
                        <p id="card-error" role="alert"></p>
                        <p class="result-message hidden"></p>
                    </div>


                    <div id="bank_transfer_slide">
                        <?php if (isset($payment_methods['direct_bank_transfer']) && $payment_methods['direct_bank_transfer'] == 1) { ?>
                            <div class="row">
                                <div class="alert alert-warning">
                                    <strong><?= !empty($this->lang->line('edit_address')) ? str_replace('\\', '', $this->lang->line('edit_address')) : 'Instructions! Make your payment directly into our account. Your order will not further proceed until the funds have cleared in our account. <br> You have to send your payment receipt from order details page then admin will verify that.' ?> </strong>
                                </div>
                                <div class="alert alert-info col-md-12">
                                    <strong><?= !empty($this->lang->line('account_details')) ? str_replace('\\', '', $this->lang->line('account_details')) : 'Account Details!' ?> </strong> <br><br>
                                    <ul>
                                        <li><?= !empty($this->lang->line('account_name')) ? str_replace('\\', '', $this->lang->line('account_name')) : 'Account Name' ?>: <?= (isset($payment_methods['account_name'])) ? $payment_methods['account_name'] : "" ?></li>
                                        <li><?= !empty($this->lang->line('account_number')) ? str_replace('\\', '', $this->lang->line('account_number')) : 'Account Number' ?>: <?= (isset($payment_methods['account_number'])) ? $payment_methods['account_number'] : "" ?></li>
                                        <li><?= !empty($this->lang->line('bank_name')) ? str_replace('\\', '', $this->lang->line('bank_name')) : 'Bank Name' ?>: <?= (isset($payment_methods['bank_name'])) ? $payment_methods['bank_name'] : "" ?></li>
                                        <li><?= !empty($this->lang->line('bank_code')) ? str_replace('\\', '', $this->lang->line('bank_code')) : 'Bank Code' ?>: <?= (isset($payment_methods['bank_code'])) ? $payment_methods['bank_code'] : "" ?></li>
                                    </ul>
                                </div>
                                <div class="alert alert-info col-md-12">
                                    <strong><?= !empty($this->lang->line('extra_details')) ? str_replace('\\', '', $this->lang->line('extra_details')) : 'Extra Details' ?>! </strong> <br><br>
                                    <p><?= (isset($payment_methods['notes'])) ? $payment_methods['notes'] : "" ?></p>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                    <hr class="mb-2">
                    <input type="hidden" name="app_name" id="app_name" value="<?= $settings['app_name'] ?>" />
                    <input type="hidden" name="username" id="username" value="<?= $user->username ?>" />
                    <input type="hidden" id="user_id" value="<?= $user->id ?>" />
                    <input type="hidden" name="user_email" id="user_email" value="<?= isset($user->email) && !empty($user->email) ? $user->email : $support_email ?>" />
                    <input type="hidden" name="user_contact" id="user_contact" value="<?= $user->mobile ?>" />
                    <input type="hidden" name="logo" id="logo" value="<?= base_url(get_settings('web_logo')) ?>" />
                    <input type="hidden" name="order_amount" id="amount" value="" />
                    <input type="hidden" name="razorpay_order_id" id="razorpay_order_id" value="" />
                    <input type="hidden" name="razorpay_payment_id" id="razorpay_payment_id" value="" />
                    <input type="hidden" name="razorpay_signature" id="razorpay_signature" value="" />

                    <input type="hidden" name="midtrans_order_id" id="midtrans_order_id" value="" />
                    <input type="hidden" name="midtrans_transaction_token" id="midtrans_transaction_token" value="" />


                    <input type="hidden" id="paytm_transaction_token" name="paytm_transaction_token" value="" />
                    <input type="hidden" id="paytm_order_id" name="paytm_order_id" value="" />

                    <input type="hidden" name="paystack_reference" id="paystack_reference" value="" />
                    <input type="hidden" name="phonepe_transaction_id" id="phonepe_transaction_id" value="" />
                    <input type="hidden" name="stripe_client_secret" id="stripe_client_secret" value="" />
                    <input type="hidden" name="stripe_payment_id" id="stripe_payment_id" value="" />

                    <input type="hidden" name="my_fatoorah_order_id" id="my_fatoorah_order_id" value="" />
                    <input type="hidden" name="my_fatoorah_session_id" id="my_fatoorah_session_id" value="" />
                    <input type="hidden" name="my_fatoorah_payment_id" id="my_fatoorah_payment_id" value="" />

                    <input type="hidden" name="flutterwave_public_key" id="flutterwave_public_key" value="<?= $payment_methods['flutterwave_public_key'] ?>" />
                    <input type="hidden" id="flutterwave_currency" value="<?= $payment_methods['flutterwave_currency_code'] ?>" />
                    <input type="hidden" name="flutterwave_transaction_id" id="flutterwave_transaction_id" value="" />
                    <input type="hidden" name="flutterwave_transaction_ref" id="flutterwave_transaction_ref" value="" />

                    <input type="hidden" name="instamojo_order_id" id="instamojo_order_id" value="" />
                    <input type="hidden" name="instamojo_payment_id" id="instamojo_payment_id" value="" />

                    <input type="hidden" name="promo_set" id="promo_set" value="" />
                    <input type="hidden" name="promo_is_cashback" id="promo_is_cashback" value="" />
                </div>
                <div class="col-xl-4 mt-5">
                    <div class="checkout-order-wrapper">
                        <div class="checkout-title">
                            <h1><?= !empty($this->lang->line('order_summary')) ? str_replace('\\', '', $this->lang->line('order_summary')) : 'Order Summary' ?></h1>
                        </div>
                        <div class="order-details">
                            <div class="product-checkout-wrapper">
                                <div class="product-checkout-title">
                                    <h2 class="clearfix mb-0 text-muted">
                                        <a class="#"><?= isset($cart[0]['cart_count']) && !empty($cart[0]['cart_count']) ? $cart[0]['cart_count'] : 0 ?><?= !empty($this->lang->line('items_in_cart')) ? str_replace('\\', '', $this->lang->line('items_in_cart')) : ' Item(s) in Cart' ?></a>
                                    </h2>
                                </div>
                                <div>
                                    <div class="product-checkout mt-4">
                                        <?php if (isset($cart) && !empty($cart)) {
                                            if ($cart[0]['type'] != 'digital_product') {
                                                $product_not_delivarable = array_column($product_not_delivarable, "product_id");
                                            }

                                            foreach ($cart as $row) {
                                                if (isset($row['qty']) && $row['qty'] != 0) {
                                                    $price = $row['special_price'] != '' && $row['special_price'] != null && $row['special_price'] > 0 && $row['special_price'] < $row['price'] ? $row['special_price'] : $row['price'];
                                                    $amount = $row['qty'] * $price;
                                        ?>
                                                    <div class="border-line">
                                                        <span class="product-wrap">
                                                            <div class="widget-image">
                                                                <a href="<?= base_url("products/details/" . $row['slug']) ?>">
                                                                    <img src="<?= $row['image_sm'] ?>" alt="">
                                                                </a>
                                                            </div>
                                                            <!-- checking product deliverable or not  -->
                                                            <span class="product-info text-left">
                                                                <a href="<?= base_url("products/details/" . $row['slug']) ?>" class="product-title text-muted"><?= output_escaping(str_replace('\r\n', '&#13;&#10;', $row['name'])) ?></a>
                                                                <?php if ($cart[0]['type'] != 'digital_product') { ?>
                                                                    <div id="p_<?= $row['product_id'] ?>" class="text-danger deliverable_status"><?= (isset($default_address) && !empty($default_address) && in_array($row['product_id'], $product_not_delivarable)) ? "Not deliverable" : "" ?></div>
                                                                <?php } ?>
                                                                <?php if (!empty($row['product_variants'])) { ?>
                                                                    <?= str_replace(',', ' | ', $row['product_variants'][0]['variant_values']) ?>
                                                                <?php } ?>
                                                                <div class="qty">
                                                                    <span class="text-muted"><?= !empty($this->lang->line('qty')) ? str_replace('\\', '', $this->lang->line('qty')) : 'Qty' ?> :</span>
                                                                    <span class="text-muted"><?= $row['qty'] ?></span>
                                                                </div>
                                                                <?php if (isset($row['item_tax_percentage']) && !empty($row['item_tax_percentage'])) { ?>
                                                                    <div>
                                                                        <span class="text-muted"><?= !empty($this->lang->line('net_amount')) ? str_replace('\\', '', $this->lang->line('net_amount')) : 'Net Amount' ?> :<?= $settings['currency'] ?><?= format_price((($amount) - (calculate_tax_inclusive(($amount), $row['item_tax_percentage'])))) ?></i></span>
                                                                    </div>
                                                                    <div>
                                                                        <?php $tax_percentage = explode(',', $row['item_tax_percentage']);
                                                                        $total_tax = array_sum($tax_percentage); ?>
                                                                        <span class="text-muted"><?= !empty($row['tax_title']) ? $row['tax_title'] : 'Tax' ?> :</span>
                                                                        <span class="text-muted"><?= $settings['currency'] ?><?= format_price(calculate_tax_inclusive(($amount), $total_tax)) ?></span>

                                                                    </div>
                                                                <?php } ?>
                                                            </span>
                                                            <span class="item-price text-muted"><?= $settings['currency'] ?></i> <?= format_price($row['qty'] * $price) ?></span>
                                                        </span>
                                                    </div>
                                        <?php }
                                            }
                                        } ?>
                                    </div>
                                </div>
                                <input type="hidden" id="sub_total" value="<?= $cart['sub_total'] ?>">
                                <div class="cart-total-price">
                                    <table class="table cart-products-table">
                                        <tbody>
                                            <tr>
                                                <td class="text-muted"><?= !empty($this->lang->line('subtotal')) ? str_replace('\\', '', $this->lang->line('subtotal')) : 'Subtotal' ?></td>
                                                <td class="text-muted"><?= $settings['currency'] . ' <span class="sub_total">' . format_price($cart['sub_total']) . '</span>' ?></td>
                                            </tr>


                                            <?php if (!empty($cart['tax_percentage'])) { ?>
                                                <tr class="cart-product-tax d-none">
                                                    <td class="text-muted"><?= !empty($this->lang->line('tax')) ? str_replace('\\', '', $this->lang->line('tax')) : 'Tax' ?> (<?= $cart['tax_percentage'] ?>%)</td>
                                                    <td class="text-muted"><?= $settings['currency'] . ' ' . format_price($cart['tax_amount']) ?></td>
                                                </tr>
                                            <?php } ?>
                                            <?php
                                            $shiprocket_settings = get_settings('shipping_method', true);
                                            if (isset($shiprocket_settings['shiprocket_shipping_method']) && $shiprocket_settings['shiprocket_shipping_method'] == 1 && $cart[0]['type'] != 'digital_product') {
                                            ?>
                                                <tr>
                                                    <td>
                                                        <div class="row ">
                                                            <?php if ($cart[0]['type'] != 'digital_product') { ?>
                                                                <div class="column delivery_charge text-success">
                                                                    <h3><?= !empty($this->lang->line('delivery_charge')) ? str_replace('\\', '', $this->lang->line('delivery_charge')) : 'Delivery Charges' ?></h3>
                                                                </div>

                                                        </div>

                                                        <div class="row mt-2  d-none">
                                                            <div class="column delivery_charge  text-muted">
                                                                <?= !empty($this->lang->line('shipping_method')) ? str_replace('\\', '', $this->lang->line('shipping_method')) : 'shipping method' ?>
                                                            </div>
                                                            <div class="column text-muted deliverycharge_currency">
                                                                <?= $settings['currency'] . ' ' ?><span class="shipping_method"></span>
                                                            </div>
                                                        </div>
                                                        <div class="row mt-2 ">
                                                            <div class="column delivery_charge  text-muted">
                                                                <h3><?= !empty($this->lang->line('delivery_charge_with_cod')) ? str_replace('\\', '', $this->lang->line('delivery_charge_with_cod')) : 'Delivery Charge with COD :' ?></h3>
                                                            </div>
                                                            <div class="column text-muted deliverycharge_currency">
                                                                <?= $settings['currency'] . ' ' ?><span class="delivery_charge_with_cod"></span>
                                                                <input type="hidden" name="delivery_charge_with_cod" class="delivery_charge_with_cod" value="" />

                                                            </div>
                                                        </div>
                                                        <div class="row mt-2">
                                                            <div class="column delivery_charge text-muted">
                                                                <h3><?= !empty($this->lang->line('delivery_charge_without_cod')) ? str_replace('\\', '', $this->lang->line('delivery_charge_without_cod')) : 'Delivery Charge without COD :' ?></h3>
                                                            </div>
                                                            <div class="text-muted deliverycharge_currency">
                                                                <?= $settings['currency'] . ' ' ?><span class="delivery_charge_without_cod"></span>
                                                                <input type="hidden" name="delivery_charge_without_cod" class="delivery_charge_without_cod" value="" />
                                                            </div>
                                                        </div>
                                                        <div class="row mt-2">
                                                            <div class="column delivery_charge  text-muted">
                                                                <h3><?= !empty($this->lang->line('estimate_date')) ? str_replace('\\', '', $this->lang->line('estimate_date')) : 'Estimated Delivery Date :' ?></h3>
                                                            </div>
                                                            <div class="text-muted">
                                                                <h3 class="estimate_date"></h3>
                                                            </div>
                                                        </div>
                                                    <?php } ?>
                                                    </td>
                                                </tr>
                                            <?php } else { ?>
                                                <?php if ($cart[0]['type'] != 'digital_product') { ?>

                                                    <!-- <tr>
                                                        <td class="text-muted"><?= !empty($this->lang->line('delivery_charge')) ? str_replace('\\', '', $this->lang->line('delivery_charge')) : 'Delivery Charge' ?></td>
                                                        <td class="text-muted"><?= $settings['currency'] . ' ' ?><span class="delivery-charge"><?= $cart['delivery_charge'] ?></span></td>
                                                    </tr> -->
                                                    <tr>
                                                        <td>
                                                            <div class="row ">
                                                                <div class="column delivery_charge text-success">
                                                                    <h3><?= !empty($this->lang->line('delivery_charge')) ? str_replace('\\', '', $this->lang->line('delivery_charge')) : 'Delivery Charges' ?></h3>
                                                                </div>

                                                            </div>
                                                            <div class="row mt-2 ">
                                                                <div class="column delivery_charge  text-muted">
                                                                    <h3><?= !empty($this->lang->line('delivery_charge_with_cod')) ? str_replace('\\', '', $this->lang->line('delivery_charge_with_cod')) : 'Delivery Charge with COD :' ?></h3>
                                                                </div>
                                                                <div class="column text-muted deliverycharge_currency">
                                                                    <?= $settings['currency'] . ' ' ?><span class="delivery_charge_with_cod"></span>
                                                                    <input type="hidden" name="delivery_charge_with_cod" class="delivery_charge_with_cod" value="" />

                                                                </div>
                                                            </div>
                                                            <div class="row mt-2">
                                                                <div class="column delivery_charge text-muted">
                                                                    <h3><?= !empty($this->lang->line('delivery_charge_without_cod')) ? str_replace('\\', '', $this->lang->line('delivery_charge_without_cod')) : 'Delivery Charge without COD :' ?></h3>
                                                                </div>
                                                                <div class="text-muted deliverycharge_currency">
                                                                    <?= $settings['currency'] . ' ' ?><span class="delivery_charge_without_cod"></span>
                                                                    <input type="hidden" name="delivery_charge_without_cod" class="delivery_charge_without_cod" value="" />
                                                                </div>
                                                            </div>


                                                        </td>
                                                    </tr>
                                                <?php } ?>
                                            <?php } ?>


                                            <tr>
                                                <td class="text-muted"><?= !empty($this->lang->line('wallet')) ? str_replace('\\', '', $this->lang->line('wallet')) : 'Wallet' ?></td>
                                                <td class="text-muted"><?= $settings['currency'] ?> <span class="wallet_used">0.00<span></td>

                                            </tr>
                                            <tr id="promocode_div" class="d-none">
                                                <td class="text-muted"><?= !empty($this->lang->line('promocode')) ? str_replace('\\', '', $this->lang->line('promocode')) : 'Promo code' ?> <span id="promocode"></span></td>
                                                <td class="text-muted"> <i><?= $settings['currency'] ?></i> <span id="promocode_amount"></span></td>
                                            </tr>
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                            <tr class="total-price">
                                                <td><?= !empty($this->lang->line('total')) ? str_replace('\\', '', $this->lang->line('total')) : 'Total' ?></td>
                                                <td><?= $settings['currency'] ?> <span id="final_total"><?= format_price($cart['sub_total']) ?></span></td>
                                            </tr>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                                <div class="input-group">
                                    <a href="#" data-izimodal-open=".promo_code_modal" class="mb-4 pl-0"><?= !empty($this->lang->line('see_all_offers')) ? str_replace('\\', '', $this->lang->line('see_all_offers')) : 'See All Offers' ?>(%)</i></a>
                                </div>
                                <div class="input-group">
                                    <input type="text" class="form-control" placeholder="<?= !empty($this->lang->line('promocode')) ? str_replace('\\', '', $this->lang->line('promocode')) : 'Promo Code' ?>" id="promocode_input">
                                    <div class="input-group-append">
                                        <button class="button button-primary" id="redeem_btn"><?= !empty($this->lang->line('redeem')) ? str_replace('\\', '', $this->lang->line('redeem')) : 'Redeem' ?></button>
                                        <button class="button button-danger d-none" id="clear_promo_btn"><?= !empty($this->lang->line('clear')) ? str_replace('\\', '', $this->lang->line('clear')) : 'Clear' ?></button>
                                    </div>
                                </div>
                                <?php $is_disabled = false;
                                foreach ($product_not_delivarable as $p_id) {
                                    if (!empty($p_id['product_id'])) {
                                        $is_disabled = true;
                                        continue;
                                    }
                                } ?>
                                <button class="block" id="place_order_btn" type="submit" <?= ($is_disabled) ? "disabled" : ""; ?>><?= !empty($this->lang->line('place_order')) ? str_replace('\\', '', $this->lang->line('place_order')) : 'Place Order' ?></button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
    </div>
    </form>
</section>

<form action="<?= base_url('payment/paypal') ?>" id="paypal_form" method="POST">
    <input type="hidden" id="csrf_token" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
    <input type="hidden" name="order_id" id="paypal_order_id" value="" />
</form>
<input type="hidden" name="stripe_key_id" id="stripe_key_id" value="<?= $payment_methods['stripe_publishable_key'] ?>" />
<input type="hidden" name="razorpay_key_id" id="razorpay_key_id" value="<?= $payment_methods['razorpay_key_id'] ?>" />
<input type="hidden" name="paystack_key_id" id="paystack_key_id" value="<?= $payment_methods['paystack_key_id'] ?>" />
<input type="hidden" id="delivery_starts_from" value="<?= (isset($time_slot_config['delivery_starts_from']) ? $time_slot_config['delivery_starts_from'] : '') ?>">
<input type="hidden" id="delivery_ends_in" value="<?= (isset($time_slot_config['allowed_days']) ? $time_slot_config['allowed_days'] : '') ?>">
<div id="modal-custom" class="address-modal" data-iziModal-group="grupo1">
    <button data-iziModal-close class="icon-close">x</button>

    <section id="address_form">
        <div class="h4"><?= !empty($this->lang->line('shipping_address')) ? str_replace('\\', '', $this->lang->line('shipping_address')) : 'Shipping Address' ?></div>
        <ul id="address-list"></ul>
        <div class="col-12 text-right mt-2">

        </div>
        <footer class="mt-4">
            <button data-iziModal-close><?= !empty($this->lang->line('cancel')) ? str_replace('\\', '', $this->lang->line('cancel')) : 'Cancel' ?></button>
            <button class="submit" id="select_address"><?= !empty($this->lang->line('save')) ? str_replace('\\', '', $this->lang->line('save')) : 'Save' ?></button>
        </footer>
    </section>
</div>
<div id="modal-custom" class="promo_code_modal" data-iziModal-group="grupo1">
    <button data-iziModal-close class="icon-close">x</button>
    <section id="promo_code_form">
        <div class="h4"><?= !empty($this->lang->line('promocodes')) ? str_replace('\\', '', $this->lang->line('promocodes')) : 'Promocodes' ?></div>
        <ul id="promocode-list"></ul>
    </section>
</div>
<!-- Button trigger modal -->


<div class="modal fade" id="add-address-modal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header pb-0">
                <h5 class="modal-title"><?= !empty($this->lang->line('add_address')) ? str_replace('\\', '', $this->lang->line('add_address')) : 'Add Address'; ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">x</button>
            </div>
            <div class="modal-body">
                <form action="<?= base_url('my-account/add-address') ?>" method="POST" id="add-address-form" class="">
                    <div class="row">
                        <div class="col-md-12 col-sm-12 col-xs-12 form-group">
                            <label for="name" class="control-label"><?= !empty($this->lang->line('name')) ? str_replace('\\', '', $this->lang->line('name')) : 'Name' ?></label>
                            <input type="text" class="form-control" id="address_name" name="name" placeholder="<?= !empty($this->lang->line('name')) ? str_replace('\\', '', $this->lang->line('name')) : 'Name' ?>" />
                        </div>
                        <div class="col-md-6 col-sm-12 col-xs-12 form-group">
                            <label for="mobile_number" class="control-label"><?= !empty($this->lang->line('mobile_number')) ? str_replace('\\', '', $this->lang->line('mobile_number')) : 'Mobile Number' ?></label>
                            <input type="text" class="form-control" id="mobile_number" name="mobile" placeholder="<?= !empty($this->lang->line('mobile_number')) ? str_replace('\\', '', $this->lang->line('mobile_number')) : 'Mobile Number' ?>" />
                        </div>
                        <div class="col-md-6 col-sm-12 col-xs-12 form-group">
                            <label for="alternate_mobile" class="control-label"><?= !empty($this->lang->line('alternate_mobile')) ? str_replace('\\', '', $this->lang->line('alternate_mobile')) : 'Alternate Mobile Number' ?></label>
                            <input type="text" class="form-control" id="alternate_mobile" name="alternate_mobile" placeholder="<?= !empty($this->lang->line('alternate_mobile')) ? str_replace('\\', '', $this->lang->line('alternate_mobile')) : 'Alternate Mobile Number' ?>" />
                        </div>
                        <div class="col-md-12 col-sm-12 col-xs-12 form-group">
                            <label for="address" class="control-label"><?= !empty($this->lang->line('address')) ? str_replace('\\', '', $this->lang->line('address')) : 'Address' ?></label>
                            <textarea name="address" class="form-control" id="address" cols="30" rows="4" placeholder="#Door no, Street Address, Locality, Area, Pincode"></textarea>
                        </div>
                        <div class="col-md-6 col-sm-12 col-xs-12 form-group city">
                            <label for="city" class="control-label"><?= !empty($this->lang->line('city')) ? str_replace('\\', '', $this->lang->line('city')) : 'City' ?></label>
                            <select class="form-control" name="city_id" id="city">
                                <option value=""><?= !empty($this->lang->line('select_city')) ? str_replace('\\', '', $this->lang->line('select_city')) : '--Select City--' ?></option>
                                <?php foreach ($cities as $row) { ?>
                                    <option value="<?= $row['id'] ?>"><?= $row['name'] ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="col-md-6 col-sm-12 col-xs-12 form-group area">
                            <label for="area" class="control-label"><?= !empty($this->lang->line('area')) ? str_replace('\\', '', $this->lang->line('area')) : 'Area' ?></label>
                            <input type="text" class="form-control" id="area" name="general_area_name" placeholder="<?= !empty($this->lang->line('area')) ? str_replace('\\', '', $this->lang->line('area')) : 'Area' ?>" />
                        </div>
                        <div class="col-md-6 col-sm-12 col-xs-12 form-group area">
                            <label for="pincode" class="control-label"><?= !empty($this->lang->line('pincode')) ? str_replace('\\', '', $this->lang->line('pincode')) : 'Zipcode' ?></label>
                            <select name="pincode" id="pincode" class="form-control">
                                <option value=""><?= !empty($this->lang->line('select_zipcode')) ? str_replace('\\', '', $this->lang->line('select_zipcode')) : '--Select Zipcode--' ?></option>
                            </select>
                        </div>

                        <div class="col-md-6 col-sm-12 col-xs-12 form-group city_name d-none">
                            <label for="city" class="control-label"><?= !empty($this->lang->line('city')) ? str_replace('\\', '', $this->lang->line('city')) : 'City Name' ?></label>
                            <input type="text" class="form-control " id="city_name" name="city_name" placeholder="<?= !empty($this->lang->line('city')) ? str_replace('\\', '', $this->lang->line('city')) : 'City Name' ?>" />
                        </div>
                        <div class="col-md-6 col-sm-12 col-xs-12 form-group area_name d-none">
                            <label for="area" class="control-label"><?= !empty($this->lang->line('area')) ? str_replace('\\', '', $this->lang->line('area')) : 'Area' ?></label>
                            <input type="text" class="form-control " id="area_name" name="area_name" placeholder="<?= !empty($this->lang->line('area')) ? str_replace('\\', '', $this->lang->line('area')) : 'Area' ?>" />
                        </div>

                        <div class="col-md-6 col-sm-12 col-xs-12 form-group pincode_name d-none">
                            <label for="area" class="control-label"><?= !empty($this->lang->line('pincode')) ? str_replace('\\', '', $this->lang->line('pincode')) : 'Zipcode' ?></label>
                            <input type="text" class="form-control " id="pincode_name" name="pincode_name" placeholder="<?= !empty($this->lang->line('pincode')) ? str_replace('\\', '', $this->lang->line('pincode')) : 'Zipcode' ?>" />
                        </div>

                        <div class="col-md-6 col-sm-12 col-xs-12 form-group">
                            <label for="state" class="control-label"><?= !empty($this->lang->line('state')) ? str_replace('\\', '', $this->lang->line('state')) : 'State' ?></label>
                            <input type="text" class="form-control" id="state" name="state" placeholder="<?= !empty($this->lang->line('state')) ? str_replace('\\', '', $this->lang->line('state')) : 'State' ?>" />
                        </div>
                        <div class="col-md-6 col-sm-12 col-xs-12 form-group">
                            <label for="country" class="control-label"><?= !empty($this->lang->line('country')) ? str_replace('\\', '', $this->lang->line('country')) : 'Country' ?></label>
                            <input type="text" class="form-control" name="country" id="country" placeholder="<?= !empty($this->lang->line('country')) ? str_replace('\\', '', $this->lang->line('country')) : 'Country' ?>" />
                        </div>
                        <div class="col-md-12 col-sm-12 col-xs-12 form-group">
                            <label for="country" class="control-label"><?= !empty($this->lang->line('type')) ? str_replace('\\', '', $this->lang->line('type')) : 'Type : ' ?></label>
                            <div class="form-check form-check-inline">
                                <input type="radio" class="form-check-input" name="type" id="home" value="home" />
                                <label for="home" class="form-check-label text-dark"><?= !empty($this->lang->line('home')) ? str_replace('\\', '', $this->lang->line('home')) : 'Home' ?></label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input type="radio" class="form-check-input" name="type" id="office" value="office" placeholder="Office" />
                                <label for="office" class="form-check-label text-dark"><?= !empty($this->lang->line('office')) ? str_replace('\\', '', $this->lang->line('office')) : 'Office' ?></label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input type="radio" class="form-check-input" name="type" id="other" value="other" placeholder="Other" />
                                <label for="other" class="form-check-label text-dark"><?= !empty($this->lang->line('other')) ? str_replace('\\', '', $this->lang->line('other')) : 'Other' ?></label>
                            </div>
                        </div>

                        <div class="col-md-12 col-sm-12 col-xs-12">
                            <input type="submit" class="btn btn-primary btn-sm" id="save-address-submit-btn" value="Save" />
                            <button type="button" class="btn btn-danger btn-sm" data-dismiss="modal" aria-label="Close"><?= !empty($this->lang->line('close')) ? str_replace('\\', '', $this->lang->line('close')) : 'Close' ?></button>
                        </div>
                        <div class="col-md-12 col-sm-12 col-xs-12 text-center">
                            <div id="save-address-result"></div>
                        </div>
                    </div>
                </form>
            </div>
            <!--/.modal-content -->
        </div>
        <!--/.modal-body -->
    </div>
    <!--/.modal-dialog -->
</div>
<!--/.modal -->

<?php if (isset($payment_methods['paytm_payment_method']) && $payment_methods['paytm_payment_method'] == 1) {
    $url = ($payment_methods['paytm_payment_mode'] == "production") ? "https://securegw.paytm.in/" : "https://securegw-stage.paytm.in/";
?>
    <script type="application/javascript" src="<?= $url ?>merchantpgpui/checkoutjs/merchants/<?= $payment_methods['paytm_merchant_id'] ?>.js"></script>
<?php } ?>

<script src="https://checkout.flutterwave.com/v3.js"></script>
<script src="https://js.stripe.com/v3/"></script>
<script src="https://demo.myfatoorah.com/cardview/v2/session.js"></script>
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script src="https://js.paystack.co/v1/inline.js"></script>
<script src="https://js.instamojo.com/v1/checkout.js"></script>

<?php
$midtrans_url = $midtrans_client_key = "";

if (isset($payment_methods['midtrans_payment_mode']) && $payment_methods['midtrans_payment_mode'] != '') {
    $midtrans_url = (isset($payment_methods['midtrans_payment_mode']) && $payment_methods['midtrans_payment_mode'] == "sandbox") ? "https://app.sandbox.midtrans.com/snap/snap.js" : "https://app.midtrans.com/snap/snap.js";
    $midtrans_client_key = ($payment_methods['midtrans_client_key'] != "") ? $payment_methods['midtrans_client_key'] : 'SB-Mid-client-x5KX3W5PHEsEDMQ4'; ?>

    <script type="text/javascript" src="<?= $midtrans_url ?>" data-client-key="<?= $midtrans_client_key ?>"></script>

<?php
}
?>
<script src="<?= THEME_ASSETS_URL . '/js/checkout.js' ?>"></script>