@if($response["code"] == 0)
<p>La cuenta se ha verificado correctamente. Ya puedes cerrar esta ventana y volver a la app</p>
@else
<p>Al parecer el link ya no era válido. Prueba a volver a solicitar el correo de verificación</p>
@endif
