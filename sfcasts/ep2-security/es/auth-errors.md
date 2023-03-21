# Manejo de errores de autenticación

Cuando iniciamos sesión con un correo electrónico y una contraseña no válidos, parece que el sistema `json_login`devuelve un bonito JSON con una clave `error` establecida en "Credenciales no válidas". Si quisiéramos personalizar esto, podríamos crear una clase que implemente`AuthenticationFailureHandlerInterface`:

```php
class AppAuthFailureHandler implements AuthenticationFailureHandlerInterface
{
    public function onAuthenticationFailure($request, $exception)
    {
        return new JsonResponse(
            ['something' => 'went wrong'],
            401
        );
    }
}
```

Y luego establecer su ID de servicio en la opción `failure_handler` en `json_login`:

```yaml
json_login:
    failure_handler: App\Security\AppAuthFailureHandler
```

## Mostrar el error en el formulario

Pero, esto nos sirve de sobra. Así que vamos a utilizarlo en nuestro`/assets/vue/LoginForm.vue`. No profundizaremos demasiado en Vue, pero ya tengo un estado llamado `error`, y si lo configuramos, se mostrará en el formulario:

[[[ code('5669b565ef') ]]]

Después de hacer la petición, si la respuesta no está bien, ya estamos descodificando el JSON. Ahora digamos que `error.value = data.error`:

[[[ code('10d5e3295e') ]]]

Para ver si funciona, asegúrate de que tienes Webpack Encore ejecutándose en segundo plano para que recompile nuestro JavaScript. Actualiza. Y... puedes hacer clic en este pequeño enlace para hacer trampas e introducir un correo electrónico válido. Pero luego escribe una contraseña ridícula y... ¡Me encanta! ¡Vemos "Credenciales no válidas" en la parte superior con unos recuadros rojos!

## json_login Requires Content-Type: application/json

Así que la llamada AJAX funciona de maravilla. Sin embargo, hay un problema con el mecanismo de seguridad `json_login`: requiere que envíes una cabecera `Content-Type` configurada como`application/json`. Nosotros lo establecemos en nuestra llamada Ajax y tú también deberías hacerlo:

[[[ code('940652bd72') ]]]

Pero... si alguien se olvida, queremos asegurarnos de que las cosas no se vuelven completamente locas.

Comenta esa cabecera `Content-Type` para que podamos ver qué ocurre:

[[[ code('d553c86526') ]]]

Luego muévete, actualiza la página... escribe una contraseña ridícula y... ¿se borra el formulario? Mira la llamada a la Red. ¡La ruta devolvió un código de estado 200 con una clave `user` establecida en `null`!

Y... ¡eso tiene sentido! Como nos falta la cabecera, el mecanismo `json_login` no hizo nada. En su lugar, la petición continuó a nuestro `SecurityController`... excepto que esta vez el usuario no está conectado. Así que devolvemos `user: null`... con un código de estado 200.

Esto es un problema porque hace que parezca que la llamada Ajax ha tenido éxito. Para solucionarlo, si, por cualquier motivo, se omitió el mecanismo `json_login`... pero el usuario accede a nuestra ruta de inicio de sesión, devolvamos un código de estado 401 que diga:

> ¡Oye! ¡Necesitas iniciar sesión!

Entonces, si no es `$user`, entonces `return $this->json()`... y esto podría parecerse a cualquier cosa. Incluyamos una clave `error` que explique lo que probablemente salió mal: esto coincide con la clave`error` que `json_login` devuelve cuando fallan las credenciales, así que a nuestro JavaScript le gustará esto. Caramba. ¡Incluso corregiré mi errata!

[[[ code('19b29af00a') ]]]

Y lo más importante, para el segundo argumento, pasa un 401 para el código de estado.

A continuación, podemos simplificar... porque ahora sabemos que habrá un usuario:

[[[ code('01e3e58e03') ]]]

¡Hermoso! Gira y envía otra contraseña incorrecta. ¡Precioso! El código de estado 401 activa nuestro código de gestión de errores, que muestra el error en la parte superior. Maravilloso.

Vuelve a `LoginForm.vue` y pon de nuevo la cabecera `Content-Type`:

[[[ code('a833e97c59') ]]]

Siguiente: vamos a iniciar sesión con éxito y... ¡a averiguar qué queremos hacer cuando eso ocurra! También vamos a hablar de la sesión y de cómo autentica nuestras peticiones a la API.
