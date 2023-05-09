<?php
$access_key = $this->gateway->get_option('access_key');
$secret_key = $this->gateway->get_option('access_key');

// Get the currency code configured in WooCommerce
$currency_code = get_woocommerce_currency();

if(empty($access_key) || empty($secret_key)){?>
    <div class="row alert-row" style="margin-bottom: .2rem !important;">
        <div class="alert alert-danger" style="padding:5px; border-radius: 10px; width:fit-content;     width: 84vh;">
            <i class="dashicons dashicons-warning alert-i" style="color:red;"></i>
            <p class="alert-p" style="padding-left:10px;">Para poder iniciar a utilizar el plugin primero debes de configurar las llaves</p>
        </div>
    </div>
<?php
}

if(isset($_GET['status'])){
    if($_GET['status'] == 'success'){
?>
         <div class="alert alert-success" role="alert">
            Estado de la pasarela actualizado correctamente
         </div>   
<?php            
    }else{
?>
         <div class="alert alert-danger" role="alert">
         Ocurrio un error porfavor intente nuevamente
         </div>

<?php 
    }

 }

$recurrente = new Recurrente_Gateway();
$isActive = $recurrente->enabled == 'yes' ? true : false;
?>

<div class="row" style="width:fit-content; gap: 2rem; margin-bottom:2vh;">
    <div class="col">
        <div class="card-inicio " style="border-radius:20px;max-height: 250px">
           
            <div class="card-body-inicio" style="padding-bottom: 2.5vh; padding-top: 2.5vh;">
                <div class="row-general">
                    <div class="col" style="    align-items: center; display: flex;">
                        <p style="margin: 0; color: #999999; font-size: 11px; font-weight: bold;">Estado</p>
                    </div>
                    <div class="col">
                        <form method="post" style="margin-left: 25vh;">
                            <?php wp_nonce_field( ); ?> 
                            <input type="hidden" name="toggle_recurrete_gateway" value="<?=  ($isActive ? 'disable' : 'enable') ?>">
                            <button class="btn <?=  ($isActive ? 'btn-danger' : 'btn-primary-activar') ?>" type="submit" style="    font-weight: 100; padding: .3rem 1.5rem;"><?= ($isActive ? 'Desactivar' : 'Activar') ?></button>
                        </form>
                    </div>
                </div>
                <div class="row-activo">
                    <div class="col" style="margin-top: 4%;">
                        <div class="circle" style="background-color:<?= ($isActive ? '#4ac26b' : 'red');  ?>"></div>
                    </div>
                    <div class="col" style="margin-left: -86%;">
                        <h1 style="font-weight: bold;"><?= ($isActive ? 'ACTIVO' : 'NO ACTIVO') ?></h1>
                    </div>
                </div>
                <div class="row-estado">
                    <div class="col">
                        <a href="<?= admin_url('edit.php?post_type=recurrente&page=recurrente-configurations');?>"><button class="btn btn-primary-estado">Configurar Ilaves</button></a>
                    </div>
                    <div class="col">
                        <a href="https://app.recurrente.com/"><button class="btn btn-primary-estado">Mi cuenta</button></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card-inicio  " style="border-radius:20px;max-height: 250px; ">
           
            <div class="card-body-inicio">
                <div class="row" style="border-bottom: 1px solid #6c757d42;">
                    <h3 style="padding-right: 3rem;">Informacion Del comercio</h3>
                    <hr>
                </div>
                <div class="row-datos">
                    <p style="color: #999999; font-size: 11px; font-weight: bold;">Nombre</p>
                    <h3 style="margin:0; margin-top: -0.5rem;"><?= get_bloginfo('name') ?></h3>
                </div>
                <div class="row-datos">
                    <p style="color: #999999; font-size: 11px; font-weight: bold;">Moneda</p>
                    <div style="margin-top: -0.5rem;     padding-bottom: .7rem;">
                        <h1 style="float:left; margin:0;"><?= $currency_code ?></h1>
                        <?php  if(!($currency_code == 'USD' || $currency_code == 'GTQ')){ ?>
                            <i class="dashicons dashicons-warning card-i" style="color:red;"></i>
<?php                   } ?>
                    </div>

                </div>    
            </div>

        </div>
    </div>
</div>

<div class="row"  style="width:100vh; gap: 2rem;">
    <div class="col">
        <div class="card-inicio" style="border-radius:20px;max-height: 200px">
           
            <div class="card-body-inicio" style="padding-right: 12vh;     padding-bottom: 3.8rem;">
                <div class="row" >
                    <p class="col" style="color: #999999; font-size: 11px; font-weight: bold; ">Metodo de cobro</p>
                </div>
                <div class="row">
                    <div class="col">
                        <button class="btn btn-primary-estado">Simple</button>
                    </div>
                    <div class="col" style="margin-left: -30%;">
                        <button class="btn btn-secondary disabled" data-toggle="tooltip" title="Coming Soon">Recurrente</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card-inicio" style="border-radius:20px;max-height: 200px;     margin-right: 13vh; width: fit-content;">
           
            <div class="card-body-inicio" style="padding-left:1.2rem;     padding-right: 2rem;">
                <div class="row" style="7rem">
                <div class="col" style=" width:fit-content;   align-items: center; display: flex;">
                    <img  style="width:80%;" src="http://localhost:10005/wp-content/uploads/2023/05/buy-me-a-coffee.jpg" alt="imagen">
                    
                </div>
                <div class="col" >
                    <div class="row">
                        <p style="font-weight: bold; margin-bottom:0px;">¿Te está siendo util ?</p>
                    </div>
                    <div class="row">
                        <p style="color:darkgray; color: #999999; font-size: 11px; font-weight: bold; margin-top:0px;">Puedes ayudar a la comunidad que desarrolla este plugin</p>
                    </div>
                    <div class="row">
                        <a href="https://app.recurrente.com/s/aurora-u2u7iw/cafe-grande-con-leche"><button class="btn btn-primary-estado" style="padding: .4rem 2rem;     margin-bottom: 1em;">Compra un cafe</button></a>
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