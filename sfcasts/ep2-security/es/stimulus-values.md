# Pasar valores a Stimulus

Establecer una variable global está bien. Pero si utilizas Stimulus, hay una forma mejor. Podemos pasar datos del servidor como un valor a un controlador Stimulus.

Por supuesto, esto es una aplicación Vue. Pero si miras en `templates/main/homepage.html.twig`, estamos utilizando el paquete `symfony/ux-vue` para renderizar esto:

[[[ code('483c00f1da') ]]]

Entre bastidores, eso activa un pequeño controlador Stimulus que inicia y renderiza el componente Vue. Cualquier argumento que pasemos aquí se envía al controlador Stimulus como un valor... y luego se reenvía como props a la aplicación Vue. Lo que vamos a hacer es "más o menos" específico de Vue, pero podrías utilizar esta estrategia para pasar valores a cualquier controlador Stimulus.

Primero, en el componente Vue, vamos a permitir que se pase una nueva prop llamada `user`:

[[[ code('d47e72ce52') ]]]

Si no utilizas Vue, no te preocupes demasiado por los detalles. Para asegurarnos de que llega aquí `console.log(props.user)`. E inicializa los datos en`props.user`:

[[[ code('b75e80d4a5') ]]]

A continuación, en `base.html.twig`, quita todo eso de `window.user`:

[[[ code('0e4de546ef') ]]]

Y en `homepage.html.twig`, pasa un nuevo `user` prop set a `app.user`:

[[[ code('e854a4d595') ]]]

Ahora, si te mueves y actualizas, ¿no funciona? Parece que estamos autenticados como... ¿nada?

## Serializar antes de pasar el valor

Si escarbas un poco, verás que estamos enviando el `user` a Stimulus como `{}` vacío. ¿Por qué? Porque cuando envías datos a Stimulus, éste no utiliza el serializador para transformarlos en JSON: sólo utiliza `json_encode()`. Y eso no es suficiente.

Así que tenemos que serializarlo nosotros mismos. Para ello, abre`src/Controller/MainController.php`. Aquí está el controlador que renderiza esa plantilla. Autoconecta un servicio llamado `NormalizerInterface` y luego pasa una variable a nuestra plantilla llamada `userData` ajustada a `$normalizer->normalize()`. Oh, ¡pero necesitamos al usuario! Añade otro argumento al controlador con el nuevo atributo`#[CurrentUser]`, type-hint `User`, digamos `$user`, y luego = `null` en caso de que no estemos autenticados. Más abajo, la normalización convertirá el objeto en una matriz. Así que pasa `$user` y luego el formato de la matriz, que es `jsonld`: queremos todos los campos JSON-LD. Por último, pasa el contexto de serialización con`'groups' => 'user:read'`:

[[[ code('e1a18a3026') ]]]

¡Último paso! En la plantilla, establece la propiedad `user` en `userData`:

[[[ code('30bf17bab4') ]]]

Ya que el sistema Stimulus ejecutará ese array a través de `json_encode()` que transformará ese array en JSON. Cuando pasemos y refresquemos .... ¡ya lo tienes! Puedes ver que todo el JSON se pasa al controlador Stimulus... y luego se pasa a Vue como prop.

Vuelve a girar y asegúrate de sacar ese `console.log()` de ahí:

[[[ code('04ce544505') ]]]

## Protección CSRF

Todavía no lo hemos visto, pero cuando empecemos a hacer peticiones a nuestra API, esas peticiones se autenticarán gracias a la sesión. Cuando utilices sesiones con tu API, puede que leas que necesitas protección CSRF. ¿Necesitamos tokens CSRF?

La respuesta rápida es: probablemente no. Mientras utilices algo llamado cookies SameSite - que son automáticas en Symfony - entonces tu API probablemente no necesite preocuparse por la protección CSRF. Pero ten en cuenta dos cosas. En primer lugar, asegúrate de que tus peticiones GET no tienen efectos secundarios. No hagas una tontería como permitir que el cliente de la API haga una petición GET... pero luego guardes algo en la base de datos. En segundo lugar, algunos navegadores antiguos -como IE 11- no admiten las cookies SameSite. Así que al renunciar a los tokens CSRF, podrías estar permitiendo que un pequeño porcentaje de tus usuarios sean susceptibles de sufrir ataques CSRF.

Si quieres saber más, nuestro tutorial sobre la API Platform 2 tiene un capítulo entero sobre [Cookies SameSite y tokens CSRF](https://symfonycasts.com/screencast/api-platform-security/samesite-csrf).

A continuación, pasemos al otro caso de uso de la autenticación: Los tokens API.
