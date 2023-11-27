# React Admin

¡Vaya! ¡Cuidado! ¡Capítulo extra! Sabemos que nuestra API está completamente descrita mediante la especificación Open API. Incluso podemos verlo visitando `/api/docs.json`. Esto muestra todas nuestras rutas y sus campos. Obtiene esta deliciosa información leyendo nuestro código, PHPdoc y otras cosas. Y sabemos que esto se utiliza para alimentar la página de documentos Swagger UI. Nuestra API también se describe mediante JSON-LD e Hydra.

Y ambos tipos de documentación de la API pueden utilizarse para otras cosas.

Por ejemplo, busca "react admin" para encontrar un sistema de administración de código abierto basado en React. Esto es súper potente y genial... y existe desde hace mucho tiempo. Y la forma en que funciona es... increíble: lo apuntamos a nuestra documentación de la API y luego... ¡se construye solo! Creo que deberíamos probarlo.

Busca "api platform react admin" para encontrar la página de documentación de la API Platform que trata de esto. Tiene algo de información... pero lo que realmente buscamos está aquí. Haz clic en "Empezar". Esto nos guía a través de todos los detalles, incluyendo incluso la configuración CORS si tienes ese problema.

Así que... ¡hagámoslo!

## Configuración de Webpack Encore

Si utilizas la distribución Docker de API Platform, esta área de administración viene preinstalada, pero también es bastante fácil añadirla manualmente. Ahora mismo, nuestra aplicación no tiene JavaScript, así que tenemos que arrancarlo todo. Busca tu terminal y ejecuta:

```terminal
composer require encore
```

Esto instala WebpackEncoreBundle... y su receta nos da una configuración básica del frontend. Una vez hecho esto, instala los activos de Node con:

```terminal
npm install
```

Bien, vuelve a los documentos. API Platform tiene su propio paquete Node que ayuda a integrarse con el administrador. Así que vamos a instalarlo. Copia la línea`npm install` -también puedes usar `yarn` si quieres-, pégala en el terminal y añade un `-D` al final.

```terminal-silent
npm install @api-platform/admin -D
```

Ese `-D` no es super importante, pero yo suelo instalar mis activos como `devDependencies`.

## Configuración UX React

Para que todo esto funcione, en última instancia, vamos a renderizar un único componente React en una página. Para ayudarte con eso, voy a instalar un paquete UX que es... realmente bueno renderizando componentes React. Es opcional, pero bueno.

Ejecuta:

```terminal
composer require symfony/ux-react
```

Perfecto. Ahora, gira y busca "symfony ux react" para encontrar su documentación. Copia este código de instalación: tenemos que añadirlo a nuestro archivo `app.js`... aquí en`assets/`. Pégalo... y no necesitamos todos estos comentarios. También moveré este código debajo de las importaciones.

[[[ code('a2f819944d') ]]]

¡Increíble! Esto básicamente dice que buscará en un directorio `assets/react/controllers/`y hará que cada componente React que haya dentro sea súper fácil de renderizar en Twig. Así que, vamos a crearlo: en `assets/`, añade dos nuevos directorios:`react/controllers/`. Y luego crea un nuevo archivo llamado `ReactAdmin.jsx`.

Para el contenido, vuelve a los documentos de la API Platform... y nos da casi exactamente lo que necesitamos. Copia esto... y pégalo dentro de nuestro nuevo archivo. Pero antes, no lo parece, pero gracias a la sintaxis JSX, estamos utilizando React, así que necesitamos un `import React from 'react'`.

Y... asegurémonos de que lo tenemos instalado:

```terminal
npm install react -D
```

## Pasando un Prop al Componente React

En segundo lugar, echa un vistazo al prop `entrypoint`. Esto es genial. Pasamos la URL a nuestra página de inicio de la API... y luego React admin se encarga del resto. Para nosotros, esta URL sería algo como `https://localhost:8000/api`. Pero... Prefiero no codificar "localhost" en mi JavaScript.

En lugar de eso, vamos a pasarlo como una propiedad. Para permitirlo, añade un argumento `props`... y luego di `props.entrypoint`.

[[[ code('0b1181456c') ]]]

¿Cómo lo introducimos? Lo veremos en un minuto.

## Activar React en Encore

Muy bien, veamos si el sistema llega a construirse. Enciéndelo:

```terminal
npm run watch
```

Y... ¡error de sintaxis! Ve esta sintaxis `.jsx` y... ¡no sabe qué hacer con ella! Eso es porque aún no hemos activado React dentro de WebpackEncore. Pulsa Ctrl+C para detenerlo... luego gira y abre `webpack.config.js`. Busca un comentario que diga `.enableReactPreset()`. Ahí lo tienes. Descomenta eso.

[[[ code('3f9e76f9e6') ]]]

Ahora, cuando volvamos a ejecutar

```terminal
npm run watch
```

de nuevo... ¡seguirá sin funcionar! Pero nos da el comando que necesitamos para instalar el único paquete que falta para que React sea compatible Cópialo y ejecútalo:

```terminal-silent
npm install @babel/react-preset@^7.0.0 --save-dev
```

Y ahora cuando probemos

```terminal
npm run watch
```

... ¡funciona! Es hora de renderizar ese componente React.

## Renderizando el componente ReactAdmin

¿Cómo lo hacemos? Esta es la parte fácil. En `src/Controller/`, crea una nueva clase PHP llamada `AdminController`. Probablemente será el controlador más aburrido que jamás hayas creado. Haz que extienda `AbstractController`, y luego añade un `public function` llamado `dashboard()`, que devolverá un `Response`, aunque eso es opcional. Encima de esto, añade un `Route()` para `/admin`.

Todo lo que necesitamos dentro es `return $this->render()` y luego una plantilla: `admin/dashboard.html.twig`.

[[[ code('167094b0fc') ]]]

¡Genial! Abajo, en el directorio `templates/`, crea ese directorio `admin/`... y dentro, un nuevo archivo llamado `dashboard.html.twig`. De nuevo, ésta es probablemente una de las plantillas más aburridas que harás nunca, al menos al principio. Amplía`base.html.twig` y añade `block body` y `endblock`.

Ahora, ¿cómo renderizamos el componente React? Gracias a ese paquete UX React, es superfácil. Crea el elemento en el que debe renderizarse y añade`react_component()` seguido del nombre del componente. Como el archivo se llama`ReactAdmin.jsx` en el directorio `react/controllers/`, su nombre será `ReactAdmin`.

[[[ code('9b44e70388') ]]]

Y aquí es donde pasamos los accesorios. Recuerda: tenemos uno llamado `entrypoint`. Ah, pero déjame arreglar mi sangría... y recuerda añadir el `</div>`. No necesitamos nada dentro del div... porque ahí es donde aparecerá mágicamente el área de administración de React, como un conejo salido de una chistera.

Pasa el prop set `entrypoint` a la función normal `path()`. Ahora, sólo tenemos que averiguar el nombre de ruta que API Platform utiliza para la página de inicio de la API. Esta pestaña está ejecutando npm... así que abriré una nueva pestaña de terminal y la ejecutaré:

```terminal
php bin/console debug:router
```

¡Woh! Demasiado grande. Así está mejor. Desplázate un poco hacia arriba y... aquí está. Queremos:`api_entrypoint`. Vuelve y pásalo.

[[[ code('84b7e26758') ]]]

¡Momento de la verdad! Busca tu navegador, cambia la dirección a `/admin`, y... ¡hola ReactAdmin! ¡Woh! Entre bastidores, eso hizo una petición a nuestro punto de entrada de la API, vio todos los diferentes recursos de la API que tenemos, ¡y creó este admin! Lo sé, ¿no es una locura?

No profundizaremos demasiado en esto, aunque puedes personalizarlo y casi seguro que necesitarás personalizarlo. No es perfecto: parece un poco confuso por nuestro `dragonTreasures` incrustado, pero ya es muy potente. ¡Incluso la validación funciona! Observa: cuando envío, lee la validación del lado del servidor devuelta por nuestra API y asigna cada error al campo correcto. Y los tesoros conocen nuestros filtros. ¡Todo está aquí!

Si esto te parece interesante, no dudes en seguir investigando.

¡Muy bien, equipo! ¡Lo habéis conseguido! Has superado el primer tutorial sobre la API Platform, que es fundamental para todo. Ahora entiendes cómo se serializan los recursos, cómo se relacionan los recursos con otros recursos, los IRI, etc. Todas estas cosas te van a servir para cualquier API que estés construyendo. En el próximo tutorial, hablaremos de usuarios, seguridad, validación personalizada, campos específicos de usuario y otras cosas extravagantes. Cuéntanos qué estás construyendo y, si tienes alguna pregunta, estamos a tu disposición en la sección de comentarios.

¡Muy bien, amigos! ¡Hasta la próxima!
