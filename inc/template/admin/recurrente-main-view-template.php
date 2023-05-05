<?php
$access_key = $this->gateway->get_option('access_key');
$secret_key = $this->gateway->get_option('access_key');

// Get the currency code configured in WooCommerce
$currency_code = get_woocommerce_currency();

if(empty($access_key) || empty($secret_key)){?>
    <div class="row alert-row">
        <div class="alert alert-danger" style="padding:5px; border-radius: 10px;">
            <i class="dashicons dashicons-warning alert-i" style="color:red;"></i>
            <p class="alert-p">lorem ipsum lorem ipsum lorem ipsum lorem ipsum</p>
        </div>
    </div>
<?php
}

if(isset($_GET['status'])){
    if($_GET['status'] == 'success'){
?>
         <div class="alert alert-success" role="alert">
             Gateway status changed successfully
         </div>   
<?php            
    }else{
?>
         <div class="alert alert-danger" role="alert">
             Server Error, Try Again
         </div>

<?php 
    }

 }

$recurrente = new Recurrente_Gateway();
$isActive = $recurrente->enabled == 'yes' ? true : false;
?>

<div class="row">
    <div class="col">
        <div class="card " style="border-radius:10px;max-height: 250px">
           
            <div class="card-body">
                <div class="row" style="margin-top: -2rem !important;">
                    <div class="col">
                        <p>Estado</p>
                    </div>
                    <div class="col">
                        <form method="post">
                            <?php wp_nonce_field( ); ?> 
                            <input type="hidden" name="toggle_recurrete_gateway" value="<?=  ($isActive ? 'disable' : 'enable') ?>">
                            <button class="btn <?=  ($isActive ? 'btn-danger' : 'btn-primary') ?>" type="submit" style="float: right;"><?= ($isActive ? 'Desactivar' : 'Activar') ?></button>
                        </form>
                    </div>
                </div>
                <div class="row">
                    <div class="col" style="margin-top: 4%;">
                        <div class="circle" style="background-color:<?= ($isActive ? '#5a865a' : 'red');  ?>"></div>
                    </div>
                    <div class="col" style="margin-left: -86%;">
                        <h1><?= ($isActive ? 'ACTIVO' : 'NO ACTIVO') ?></h1>
                    </div>
                </div>
                <div class="row">
                    <div class="col">
                        <a href="<?= admin_url('edit.php?post_type=recurrente&page=recurrente-configurations');?>"><button class="btn btn-primary">Configurar Ilaves</button></a>
                    </div>
                    <div class="col" style="margin-left: -25%;">
                        <a href="https://app.recurrente.com/"><button class="btn btn-primary">Mi cuenta</button></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card " style="border-radius:10px;max-height: 250px">
           
            <div class="card-body">
                <div class="row">
                    <h3>Information Del comercio</h3>
                    <hr>
                </div>
                <div class="row">
                    <p>Nombre</p>
                    <h3><?= get_bloginfo('name') ?></h3>
                </div>
                <div class="row">
                    <p>Moneda</p>
                    <div style="margin-top: -0.5rem;">
                        <h1 style="float:left"><?= $currency_code ?></h1>
                        <?php  if(!($currency_code == 'USD' || $currency_code == 'GTQ')){ ?>
                            <i class="dashicons dashicons-warning card-i" style="color:red;"></i>
<?php                   } ?>
                    </div>

                </div>    
            </div>

        </div>
    </div>
</div>

<div class="row">
    <div class="col">
        <div class="card " style="border-radius:10px;max-height: 200px">
           
            <div class="card-body">
                <div class="row">
                    <p class="col">Metodo de cobro</p>
                </div>
                <div class="row">
                    <div class="col">
                        <button class="btn btn-primary">Simple</button>
                    </div>
                    <div class="col" style="margin-left: -60%;">
                        <button class="btn btn-secondary disabled" data-toggle="tooltip" title="Coming Soon">Recurrente</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card " style="border-radius:10px;max-height: 200px;">
           
            <div class="card-body">
                <div class="row">
                <div class="col">
                    <h3>Image Here</h3>
                    
                </div>
                <div class="col">
                    <div class="row">
                        <p>Text here util ?</p>
                    </div>
                    <div class="row">
                        <p style="color:darkgray">Another text here with grey color esta plugin</p>
                    </div>
                    <div class="row">
                        <a href="https://app.recurrente.com/s/aurora-u2u7iw/cafe-grande-con-leche"><button class="btn btn-primary">Compra un cafe</button></a>
                    </div>

                </div> 
            </div>
            </div>

        </div>
    </div>
</div>

<script>
    jQuery(document).ready(function($){
        $('[data-toggle="tooltip"]').tooltip(); 
    });
</script>