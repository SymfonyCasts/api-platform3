# ¿Token API? ¿Cookies de sesión?

Acompáñame mientras contamos una historia tan antigua como... la Internet moderna: La autenticación de API. Un tema de bombo y platillo, complejidad y héroes improbables. Los personajes incluyen sesiones, tokens de API, OAuth, ¡tokens web JSON! Pero, ¿qué necesitamos para nuestra situación?

Lo primero que quiero que te preguntes es

> ¿Quién va a utilizar mi API?

¿Es tu propio JavaScript, o necesitas permitir el acceso programático? ¿Como si alguien fuera a escribir un script que utilizara tu API?

Vamos a repasar estos dos casos de uso... y cada uno tiene algunas complejidades adicionales que discutiremos por el camino.

## ¡Todo es un Token!

Por cierto, cuando piensas en la autenticación de una API, sueles pensar en un token de API, ¡y es cierto! Pero resulta que... prácticamente toda la autenticación se realiza mediante algún tipo de token. Incluso la autenticación basada en sesión se realiza enviando una cookie... que contiene un único, lo has adivinado, "token". Es una cadena aleatoria que PHP utiliza para encontrar y cargar los datos de sesión relacionados en el servidor.

Así que el truco está en averiguar qué tipo de token necesitas en cada situación y cómo lo obtendrá el usuario final.

## Caso 1: Construir para tu propio JavaScript

Hablemos del primer caso de uso: el usuario de tu API es tu propio JavaScript.

Bien, antes de que nos sumerjamos en la seguridad, asegúrate de que tu frontend y tu API viven en el mismo dominio... exactamente en el mismo dominio, no sólo en un subdominio. ¿Por qué? Porque si viven en dos dominios o subdominios diferentes, tendrás que lidiar con CORS: Intercambio de Recursos entre Orígenes.

CORS no sólo añade complejidad a tu configuración, sino que también perjudica al rendimiento. Kévin Dunglas -el desarrollador principal de API Platform- tiene una [entrada de blog](https://dunglas.dev/2022/01/preventing-cors-preflight-requests-using-content-negotiation/) sobre esto. Incluso muestra una estrategia en la que tu frontend y tu backend pueden vivir en directorios o repositorios totalmente distintos, pero seguir viviendo en el mismo dominio gracias a algunos trucos del servidor web.

Si, por alguna razón, decides poner tu API y tu frontend en subdominios diferentes, entonces tendrás que preocuparte de las cabeceras CORS y puedes solucionarlo con NelmioCorsBundle. Pero no te lo recomiendo.

## El caso de las Sesiones

De todos modos, volvamos a la seguridad. Si estás llamando a tu API desde tu propio JavaScript, es probable que el usuario se esté registrando a través de un formulario de acceso con un correo electrónico y una contraseña. No importa si se trata de un formulario de inicio de sesión tradicional o de uno creado con un sofisticado framework JavaScript que se envía mediante AJAX.

Y, sinceramente, una forma muy sencilla de gestionar este caso de uso no es con tokens de API, sino con la autenticación básica HTTP de toda la vida. Es decir, pasando literalmente el correo electrónico y la contraseña a cada ruta. Por ejemplo, el usuario introduce su correo electrónico y contraseña, tú haces una petición API a algún punto final sólo para asegurarte de que es válido, luego almacenas ese correo electrónico y contraseña en JavaScript y lo envías en cada petición API que se realice en adelante. Tu correo electrónico y contraseña funcionan básicamente como un token API.

Sin embargo, esto tiene algunos retos prácticos, como la cuestión de dónde almacenas de forma segura el correo electrónico y la contraseña en JavaScript para poder utilizarlos continuamente. En realidad, éste es un problema en general con JavaScript y las "credenciales", incluidos los tokens de API: tienes que tener mucho cuidado con dónde los almacenas para que otro JavaScript de tu página no pueda leerlos. Hay soluciones: https://bit.ly/auth0-token-storage - pero añade una complejidad que muy probablemente no necesites.

Así que en su lugar, para tu propio JavaScript, puedes utilizar una sesión. Cuando inicias una sesión en Symfony, devuelve una cookie "sólo HTTP"... y esa cookie contiene el id de sesión. Aunque, el contenido de la cookie no es realmente importante: puede ser el id de sesión o algún tipo de token que hayas inventado y estés leyendo en Symfony. Lo realmente importante es que, como la cookie es "sólo HTTP", no puede ser leída por JavaScript: ni por tu JavaScript ni por el de nadie. Pero siempre que realices una petición a la API de tu dominio, esa cookie vendrá con ella... y tu aplicación la utilizará para iniciar la sesión del usuario.

Así que el token de la API en esta situación es simplemente el "identificador de sesión", que se almacena de forma segura en una cookie sólo HTTP. Mmmm. Vamos a codificar este caso de uso.

Ah, y por cierto, un caso extremo en esta situación es si tienes una situación de Inicio de Sesión Único - un SSO. En ese caso, te autenticarás con tu SSO como una aplicación web normal. Cuando termines, tendrás un token, que puedes utilizar para autenticar al usuario con una sesión normal... o puedes utilizar ese token directamente desde tu JavaScript. Se trata de un caso de uso más avanzado que no trataremos en este tutorial... aunque sí hablaremos de cómo leer y validar los tokens de la API, independientemente de su procedencia.

## Caso de uso 2: Acceso programático y tokens de API

El segundo gran caso de uso de la autenticación es el acceso programático. Algún código hablará con tu API... además de JavaScript desde dentro del navegador.

En este caso, los clientes de la API enviarán absolutamente algún tipo de cadena de token de la API, por lo que tienes que hacer que tu API pueda leer un token que se envía en cada petición, normalmente en una cabecera `Authorization`:

```php
$response = $thhpClient->request(
    'GET',
    '/api/treasures',
    [
        'Authorization' => 'Bearer '.$apiToken,
    ],
);
```

Cómo obtiene el usuario este token depende: hay dos casos principales. El primero es el caso del "token de acceso personal a GitHub". En este caso, un usuario puede ir a una página de tu sitio y hacer clic para crear un nuevo token de acceso. Luego puede copiarlo y utilizarlo en algún código.

El segundo gran caso es OAuth, que no es más que una forma elegante y segura de obtener un token de acceso. Es especialmente importante cuando el "código" que realiza las peticiones a la API lo hace en "nombre" de algún usuario de tu sistema.

Por ejemplo, imagina un sitio -R ReplyToAllCommentsWithHearts.com- que te permite conectarte con GitHub. Una vez lo hayas hecho, ese sitio puede hacer peticiones de API a GitHub para tu cuenta, como hacer comentarios como tu usuario. O imagina una aplicación para iPhone en la que, para iniciar sesión, muestres al usuario el formulario de inicio de sesión de tu sitio. Entonces, a través de un flujo OAuth, esa aplicación móvil recibirá un token de acceso que podrá utilizar para hablar con tu API en nombre de ese usuario.

En este tutorial vamos a hablar del método del token de acceso personal, incluyendo cómo leer y validar los tokens de la API, vengan de donde vengan. No hablaremos del flujo OAuth... y en parte es porque es una bestia aparte. Sí, si tienes un caso de uso en el que necesitas permitir que terceros obtengan tokens de API para diferentes usuarios de tu sitio, necesitarás algún tipo de servidor OAuth, tanto si lo construyes tú mismo como si utilizas alguna otra solución. Pero una vez que el servidor OAuth ha hecho su trabajo, el cliente que hablará con tu API recibe... ¡un token! Y luego utilizarán ese token para hablar con tu API. Así que tu API tendrá que leer, validar y entender ese token, pero no le importa cómo lo obtuvo el cliente de la API.

Bien, dejemos atrás toda esta teoría y empecemos a repasar a continuación el primer caso de uso: permitir que nuestro JavaScript inicie sesión enviando una petición AJAX.
