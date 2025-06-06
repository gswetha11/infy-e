<section class="main-content mb-15 deeplink_wrapper">
    <div class="row">
        <div class="col-md-12 col-12 mt-4 pt-2">
            <div class="tab-content" id="pills-tabContent">
                <div class="tab-pane fade bg-white show active shadow rounded p-4 text-center" id="dash" role="tabpanel" aria-labelledby="dashboard">
                    <i class="uil uil-times-circle fs-100 text-warning"></i>
                    <h4 class="h4 text-danger"><?= !empty($this->lang->line('payment_cancelled')) ? str_replace('\\', '', $this->lang->line('payment_cancelled')) : 'Payment Cancelled / Failed' ?></h4>
                    <p><?= !empty($this->lang->line('payment_cancelled_description')) ? str_replace('\\', '', $this->lang->line('payment_cancelled_description')) : 'It seems like payment process is failed or cancelled. Please Try again.' ?></p>
                    <a class="btn btn-primary" href="<?=base_url('cart/checkout')?>"><?= !empty($this->lang->line('try_again')) ? str_replace('\\', '', $this->lang->line('try_again')) : 'Try Again' ?></a>
                </div>
            </div>
        </div>
    </div>
</section>