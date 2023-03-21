# Formulario de inicio de sesión API con json_login

En la página de inicio, que está construida en Vue, tenemos un formulario de inicio de sesión. El objetivo es que, cuando lo enviemos, envíe una petición AJAX con el correo electrónico y la contraseña a una ruta que lo validará.

El formulario en sí está construido aquí en `assets/vue/LoginForm.vue`:

[[[ code('551206aaca') ]]]

Si no estás familiarizado con Vue, no te preocupes. Haremos algo de codificación ligera en él, pero lo estoy utilizando principalmente como ejemplo para hacer algunas peticiones a la API.

En la parte inferior, al enviar, hacemos una petición POST a `/login` enviando los datos`email` y `password` como JSON. Así que nuestro primer objetivo es crear esta ruta:

[[[ code('6504d10ca8') ]]]

## Crear el controlador de inicio de sesión

Afortunadamente, Symfony tiene un mecanismo incorporado justo para esto. Para empezar, aunque no servirá de mucho, ¡necesitamos un nuevo controlador! En `src/Controller/`, crea una nueva clase PHP. Llamémosla `SecurityController`. Parecerá muy tradicional: extiende`AbstractController`, luego añade un `public function login()` que devolverá un`Response`, el de `HttpFoundation`:

[[[ code('4b4fc6f3ba') ]]]

Arriba, dale un `Route` con una URL de `/login` para que coincida con la que está enviando nuestro JavaScript. Nombra la ruta `app_login`. Ah, y en realidad no necesitamos hacer esto, pero también podemos añadir `methods: ['POST']`:

[[[ code('1f98df3a19') ]]]

No habrá una página `/login` en nuestro sitio a la que hagamos una petición GET: sólo haremos POST a esta URL.

## Devolución del ID de usuario actual

Como verás en un minuto, no vamos a procesar `email` y `password`en este controlador... pero esto se ejecutará después de un inicio de sesión correcto. Entonces... ¿qué deberíamos devolver después de un inicio de sesión correcto? No lo sé Y, sinceramente, depende sobre todo de lo que sería útil en nuestro JavaScript. Aún no he pensado mucho en ello, pero quizá... ¿el identificador de usuario? Empecemos por ahí.

Si la autenticación se ha realizado correctamente, entonces, en este punto, el usuario habrá iniciado sesión normalmente. Para obtener el usuario autenticado actualmente, voy a aprovechar una nueva función de Symfony. Añade un argumento con un atributo PHP llamado`#[CurrentUser]`. Entonces podemos utilizar el tipo-hint normal `User`, llamarlo `$user` y por defecto `null`, en caso de que no estemos logueados por alguna razón:

[[[ code('51b3da5f48') ]]]

Hablaremos de cómo es posible en un minuto.

A continuación, devuelve `$this->json()` con una clave `user` establecida en `$user->getId()`:

[[[ code('c5022fb549') ]]]

¡Genial! Y eso es todo lo que necesitamos que haga nuestro controlador.

## Activar json_login

Para activar el sistema que hará el verdadero trabajo de leer el correo electrónico y la contraseña, dirígete a `config/packages/security.yaml`. Debajo del cortafuegos, añade `json_login` y debajo `check_path`... que debería estar configurado con el nombre de la ruta que acabamos de crear. Así, `app_login`:

[[[ code('7601e1339d') ]]]

Esto activa una escucha de seguridad: es un trozo de código que ahora vigilará cada petición para ver si es una petición POST a esta ruta. Por tanto, un POST a `/login`. Si lo es, descodificará el JSON de esa petición, leerá las claves `email` y `password`de ese JSON, validará la contraseña y nos conectará.

Sin embargo, tenemos que decirle qué claves del JSON estamos utilizando. Nuestro JavaScript está enviando `email` y `password`: super creativo. Así que debajo de esto, pon`username_path` a `email` y `password_path` a `password`:

[[[ code('b26c7e3239') ]]]

## El proveedor de usuario

¡Listo! Pero, ¡espera! Si enviamos un POST `email` y `password` a esta ruta... ¿cómo demonios sabe el sistema cómo encontrar a ese usuario? ¿Cómo se supone que sabe que debe consultar la tabla `user` `WHERE email = ` el correo electrónico de la petición?

¡Excelente pregunta! En el episodio 1, ejecutamos:

```terminal
php ./bin/console make:user
```

Esto creó una entidad `User` con las cosas básicas de seguridad que necesitamos:

[[[ code('587807fe97') ]]]

En `security.yaml`, también creó un proveedor de usuario:

[[[ code('35f896a9c1') ]]]

Se trata de un proveedor de entidad: indica al sistema de seguridad que busque usuarios en la base de datos consultando por la propiedad `email`. Esto significa que nuestro sistema descodificará el JSON, obtendrá la clave `email`, buscará un `User` con un correo electrónico que coincida y, a continuación, validará la contraseña. En otras palabras... ¡estamos listos!

Volviendo a `LoginForm.vue`, el JavaScript también está listo: `handleSubmit()`
se llamará cuando enviemos el formulario... y realiza la llamada AJAX:

[[[ code('d3b718ece0') ]]]

¡Así que vamos a probarlo! Muévete y actualiza para estar seguro. Pruébalo primero con un correo electrónico y una contraseña falsos. Envías y... ¿no pasa nada? Abre el inspector de tu navegador y ve a la consola. ¡Sí! Ves un código de estado 401 y arroja este error: credenciales no válidas. Eso viene de aquí mismo, de nuestro JavaScript: una vez finalizada la petición, si la respuesta es "no está bien" -lo que significa que había un código de estado 4XX o 5XX-, descodificamos el JSON y lo registramos.

Aparentemente, cuando fallamos la autenticación con `json_login`, devuelve un pequeño trozo de JSON con "Credenciales no válidas".

A continuación: convirtamos este error en algo que podamos ver en el formulario, gestionemos otro caso de error y luego pensemos qué hacer cuando la autenticación tenga éxito.