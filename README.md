<h1 align="center">
  <img src="https://skyloft.sfo3.cdn.digitaloceanspaces.com/Repos/woo-recurrente.png" alt="Recurrente">
</h1>

[![GitHub license](https://img.shields.io/github/license/Naereen/StrapDown.js.svg)](https://github.com/Naereen/StrapDown.js/blob/master/LICENSE)
[![Latest release](https://badgen.net/github/release/Naereen/Strapdown.js)](https://github.com/Naereen/Strapdown.js/releases)
[![Github all releases](https://img.shields.io/github/downloads/Naereen/StrapDown.js/total.svg)](https://GitHub.com/Naereen/StrapDown.js/releases/)
[![Generic badge](https://img.shields.io/badge/Woocommerce-6.0.0-96588a.svg)](https://woocommerce.com/)
[![Generic badge](https://img.shields.io/badge/Wordpress-5.8.0-21759b.svg)](https://wordpress.com/)

Plugin para [Woocommerce](https://woocommerce.com/) que habilita la pasarela de pago de [Recurrente](https://recurrente.com/) como método de pago en el checkout de tú sitio web, implementar una pasarela de pago para realizar cobros en linea no tiene porque ser ciencia espacial.

## Guía de uso
A continuacion encontraras como configurar el plugin dentro de tu sitio web de [Wordpress](https://wordpress.com/).

### 💿 Instalación
Requisitos de instalacion
- Contar con [Woocommerce](https://woocommerce.com/) instalado dentro de tu sitio web.
- Contar con una cuenta habilitada de [Recurrente](https://recurrente.com/).

Simplemente clona el repositorio, genera un archivo .Zip y súbelo como plugin a tu sitio web de [Wordpress](https://wordpress.com/), recuerda que [Woocommerce](https://woocommerce.com/) debe de estar instalado en el sitio para poder habilitar el plugin.

### ⚙️ Configuración
Una vez instalado debes dirigirte al area de <strong>Woocommerce / Ajustes / Pagos</strong> , aqui podras encontrar tu forma de pago bajo el nombre de <strong>Recurrente Payment Gateway</strong> aqui podrás gestionar las opciones del plugin. 

<strong>Opciones de configuración</strong>
- <strong>Activar/Desactivar :</strong> Con esta opción puede rápidamente habilitar o deshabilitar la pasarela de pago sin desinstalar el plugin.
- <strong>Título :</strong> Nombre que se le mostrará al usuario al seleccionar la opción de pago.
- <strong>Descripción :</strong> Descripcion adicional que se le mostrara al usuario al seleccionar la opción de pago.
- <strong>Status of new order :</strong> Estado el cual [Woocommerce](https://woocommerce.com/) colocará cuando una orden es creada, este estado cambia a Completed cuando el checkout de recurrente regresa Success.
- <strong>Access Key : </strong> Clave Pública brindada por [Recurrente](https://recurrente.com/).
- <strong>Secret Key : </strong> Clave Secreta brindada por [Recurrente](https://recurrente.com/).
- <strong>Debug Log : </strong> Habilita la opcion d poder guardar un log.

### 🔑 Obtención de llaves para Test y Live
Para obtener tus llaves de prueba y producción deber ir a [Recurrente](https://recurrente.com/), allí podrás crear tu cuenta y encontrarás instrucciones dentro de su documentación. Dentro del plugin puedes colocar ambas llaves ya sea modo Test o Live en las opciones de <strong>Access Key</strong> y <strong>Secret Key</strong> respectivamente.

## ¿Como contribuir?
¡Nos encantaría que puedas formar parte de esta comunidad, si deseas contribuir eres libre de hacerlo! te dejamos a continuación documentación oficial del API de [Recurrente](https://recurrente.com/) para que puedas hecharle un vistazo.
- [Inicio Rápido](https://docs.recurrente.com/quickstart)
- [API Documentation](https://public.3.basecamp.com/p/gn3Tw4xcJxe2aNBjwM2WUn87)
