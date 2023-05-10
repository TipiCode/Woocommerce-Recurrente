<?php
 
if(isset($_GET['status'])){
    if($_GET['status'] == 'success'){
?>
         <div class="alert alert-success" role="alert">
            Las llaves se guardaron correctamente
         </div>   
<?php            
    }else{
?>
         <div class="alert alert-danger" role="alert">
            Ocurrio un error guardando las llaves
         </div>

<?php 
    }

 }

?>

<div class="row-llaves">
    <div class="col-llaves">
        <div class="card-llaves">
            <div class="card-header" >
                <h3 style="    margin-left: 2vh;padding-bottom: 1vh;border-bottom: 1px solid #4e4d4d36 !important;margin-right: 2vh;">Llaves del API</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <form id="recurrente-settings-form" method="post">

                        <?php wp_nonce_field( ); ?> 
                        <div class="form-group">
                            <label for="public_key" style="font-weight: 500;">Clave Publica</label>
                            <input type="text" style="border: 1px solid #d3d3d3b3 !important;" name="public_key" id="public_key" class="form-control" value="<?= $this->gateway->get_option('access_key') ?>">
                        </div>
                        
                        <div class="form-group" style="margin-top:10px">
                            <label for="secret_key" style="font-weight: 500;">Clave Secreta</label>
                            <input type="text" style="border: 1px solid #d3d3d3b3 !important;" name="secret_key" id="secret_key" class="form-control" value="<?= $this->gateway->get_option('secret_key') ?>">
                        </div>
                        <input type="hidden" name="save_recurrente_credentials" value="submit">
                        <button class="btn btn-primary" type="submit">Guardar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card-conseguir-llaves" style="border-radius:20px;max-width:100% !important">
            <div class="card-header">
                <div class="card-title">
                    <h3 style="margin:0; border-bottom: 1px solid #4e4d4d36 !important;padding-bottom: 8px;">¿Cómo conseguir las llaves? </h3>
                </div>
            </div>
            <div class="card-body-conseguir-llaves">
                <p style="color:grey; margin:0 0; font-size:14px;">Puedes conseguir las llaves desde tu cuenta de recurrente, en la opción de configuración (Icono de los usuarios) y luego en la opción de “Desarrolladores y API”.</p>
            </div>
        </div>
    </div>
</div>