<form action="{{route("guardarNuevaContrasena")}}" method="post">
    {{ csrf_field() }}
    <input type="hidden" value="{{ $token }}" name="token" id="token">
    <input type="password" id="password" name="password">
    <input type="password" id="password_confirmation" name="password_confirmation">
    <input type="submit" value="Guardar">
</form>
