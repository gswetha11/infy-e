<section class="page_404">
    <div class="container">
        <div class="row">
            <div class="col-sm-12 ">
                <div class="col-sm-12 col-sm-offset-1  text-center">
                    <div class="four_zero_four_bg">
                        <h1 class="text-center ">404</h1>


                    </div>

                    <div class="contant_box_404">
                        <h3 class="h2">
                        <?= !empty($this->lang->line('look_like_you_are_lost')) ? str_replace('\\', '', $this->lang->line('look_like_you_are_lost')) : ' Look like you\'re lost' ?>
                           
                        </h3>

                        <p><?= !empty($this->lang->line('the_page_you_are_looking_for_is_not_available_so_please_check_your_url')) ? str_replace('\\', '', $this->lang->line('the_page_you_are_looking_for_is_not_available_so_please_check_your_url')) : 'the page you are looking for is not available, so please check your URL!' ?></p>

                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style type="text/css">
    .page_404 {
        padding: 40px 0;
        background: #fff;
        font-family: 'Arvo', serif;
    }

    .page_404 img {
        width: 100%;
    }

    .four_zero_four_bg {

        background-image: url('<?= base_url("assets/front_end/classic/images/404_image.gif") ?>');
        height: 400px;
        background-position: center;
    }

    .four_zero_four_bg h1 {
        font-size: 80px;
    }

    .four_zero_four_bg h3 {
        font-size: 80px;
    }

    .link_404 {
        color: #fff !important;
        padding: 10px 20px;
        background: #39ac31;
        margin: 20px 0;
        display: inline-block;
    }

    .contant_box_404 {
        margin-top: -50px;
    }
</style>