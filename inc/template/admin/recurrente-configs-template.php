<?php
 
if(isset($_GET['status'])){
    if($_GET['status'] == 'success'){
?>
         <div class="alert alert-success" role="alert">
             Settings saved successfully
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

?>

<div class="row">
    <div class="col">
        <div class="card " style="border-radius:10px;max-width:100% !important">
            <div class="card-header">
                <h3>Llaves del API</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <form id="recurrente-settings-form" method="post">

                        <?php wp_nonce_field( ); ?> 
                        <div class="form-group">
                            <label for="public_key">Clave Publica</label>
                            <input type="text" name="public_key" id="public_key" class="form-control" value="<?= get_option("recurrente_public_key", '') ?>">
                        </div>
                        
                        <div class="form-group" style="margin-top:10px">
                            <label for="secret_key">Clave Secreta</label>
                            <input type="text" name="secret_key" id="secret_key" class="form-control" value="<?= get_option("recurrente_secret_key", '') ?>">
                        </div>
                        <input type="hidden" name="save_recurrente_credentials" value="submit">
                        <button class="btn btn-primary" type="submit">Guardar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card " style="border-radius:10px;max-width:100% !important">
            <div class="card-header">
                <div class="card-title">
                    <h3>¿Cómo conseguir las llaves? </h3>
                </div>
            </div>
            <hr>
            <div class="card-body">
                <p style="color:grey">Puedes conseguir las llaves desde tu cuenta de recurrente, en la opción de configuración (Icono de los usuarios) y luego en la opción de “Desarrolladores y API”.</p>
            </div>
        </div>
    </div>
</div>