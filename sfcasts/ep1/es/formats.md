# Más formatos: HAL Y CSV

API Platform admite múltiples formatos de entrada y salida. Podemos ir a`/api/treasures.json` para ver JSON, a `.jsonld` para ver JSON-LD o incluso a `.html` para ver el formato de salida HTML.

## Aceptar cabecera y negociación de contenido

Pero añadir esta extensión al final de la URL es sólo un truco que permite API Platform. Para elegir el formato que queremos que devuelva la API, se supone que debemos enviar una cabecera`Accept`. Y podemos verlo cuando utilizamos los documentos interactivos. Esto hace una petición con una cabecera `Accept` establecida en `application/ld+json`. Establecer esta cabecera es fácil de hacer en JavaScript, y si no la estableces, JSON-LD es el formato por defecto.

API Platform utiliza tres formatos por defecto. Puedes verlos aquí abajo, en la parte inferior de la página de documentación. Pero, ¿qué dice en nuestra aplicación que queremos utilizar específicamente estos tres formatos? Para responder a eso, dirígete a tu terminal y ejecuta:

```terminal
./bin/console debug:config api_platform
```

Dentro de la configuración, comprueba esta clave `formats`... que, por defecto, está configurada para esos tres formatos. Esto básicamente dice que si la cabecera `Accept` es`application/ld+json`, utiliza el formato JSON-LD. Internamente, significa que cuando Symfony serialice nuestros datos, lo hará en JSON-LD o JSON.

## Añadir un nuevo formato

Como reto, vamos a añadir un cuarto formato. Para ello, sólo tenemos que añadir un nuevo elemento a esta configuración... pero sin reemplazar completamente los formatos existentes. Copia estos, y luego abre el directorio `/config/packages/`. Aún no tenemos un archivo`api_platform.yaml`, así que vamos a crear uno. Dentro de él, di `api_platform`y pega los de abajo. Y aunque no es necesario, voy a cambiar esto para utilizar una versión más corta y atractiva de esta configuración:

[[[ code('d89758fd53') ]]]

¡Listo!

Si ahora vamos y actualizamos, todo funciona igual. Tenemos los mismos formatos abajo... porque simplemente hemos repetido la configuración por defecto.

El nuevo formato que vamos a añadir es otro tipo de JSON llamado HAL. Esto es lo que ocurre. Todos entendemos el formato JSON. Pero luego, para añadir más significado a JSON -como ciertas claves que debe tener tu JSON y su significado-, algunas personas sacan estándares que amplían JSON. JSON-LD es un ejemplo y HAL es un estándar competidor. No suelo utilizar HAL... así que hacemos esto sobre todo para ver un ejemplo de cómo es añadir un formato.

Ah, y se supone que el `Content-Type` de HAL es `application/hal+json`:

[[[ code('f970655b10') ]]]

En cuanto lo hacemos, al actualizar... ¿no aparece nada? Estoy bastante seguro de que Symfony no ha visto mi nuevo archivo de configuración. Salta aquí y limpia la caché con:

```terminal
./bin/console cache:clear
```

Actualizar de nuevo y... ¡ya está! ¡Ahora vemos `jsonhal`! Y si hacemos clic, ¡nos lleva a la versión `jsonhal` de nuestra página de inicio de la API!

Probemos una ruta con este formato. Haz clic en la petición `GET`, "Pruébalo", y, aquí abajo, podemos seleccionar qué "tipo de medio" solicitar. Selecciona`application/hal+json`, pulsa "Ejecutar", y... ¡ahí está!

Puedes ver que es JSON... y tiene los mismos resultados, pero parece un poco diferente. Tiene cosas como `_embedded` y `_links`... que forman parte del estándar HAL... y de las que no merece la pena hablar ahora.

Por cierto, la razón por la que este nuevo formato funcionó simplemente añadiendo un poquito de configuración es que el serializador ya entiende el formato `jsonhal`. Así que cuando hacemos una petición con esta cabecera `Accept`, API Platform pide al serializador que serialice en el formato `jsonhal`... y sabe cómo hacerlo.

## Añadir un formato CSV

Bien, hagamos algo que sea un poco más práctico. ¿Y si nuestros usuarios dragón necesitan devolver los tesoros en formato CSV... para poder importarlos a Quickbooks con fines fiscales?

Bueno, CSV es un formato que el Serializador de Symfony entiende sin más. Sabemos que podríamos añadir CSV directamente en este archivo de configuración. Pero como reto añadido, en lugar de habilitar el CSV para cada recurso API de nuestro sistema, vamos a añadirlo sólo a `DragonTreasure`.

Busca el atributo `ApiResource` y, en la parte inferior, añade `formats`. Al igual que con la configuración, si simplemente ponemos `csv` aquí, eso eliminará los demás formatos. Para hacerlo bien, tenemos que enumerarlos todos: `jsonld`, `json`, `html`, y`jsonhal`. Cada uno de ellos leerá la configuración para saber qué tipo de contenido debe utilizar. Al final, añade `csv`. Pero como `csv` no existe en la configuración, tenemos que decirle qué tipo de contenido lo activará. Así que ponlo en `text/csv`.

[[[ code('5583ab3b8a') ]]]

Oh, ¡pero mi editor está loco! Dice:

> El orden de los argumentos con nombre no coincide con el orden de los parámetros

Sabemos que cada atributo PHP es una clase... y cuando pasamos argumentos al atributo, en realidad estamos pasando argumentos con nombre al constructor de esa clase. Y, con argumentos con nombre, el orden de los argumentos no importa. En realidad no creo que PhpStorm deba señalar esto como un problema... pero si te molesta como a mí, puedes darle a "Ordenar argumentos" y... ya está. Ha movido `formats` un poco más arriba, está contento, y no tendremos que mirar ese subrayado amarillo.

Muy bien, dirígete, actualiza, abre nuestra ruta de recolección y pulsa "Probar". Esta vez, aquí abajo, selecciona `text/csv` y luego... ¡"Activar"! Hola CSV. ¡Demasiado fácil!

Una vez más, esto funciona porque el serializador de Symfony entiende el formato CSV, así que hace todo el trabajo.

De hecho, abre el perfilador de esa petición... y baja a la sección del serializador. ¡Sí! Podemos ver que está utilizando el formato `csv`... que activa un `CsvEncoder`. Por eso obtenemos nuestros bonitos resultados. Si necesitaras devolver tus resultados en un formato personalizado no admitido por el serializador, podrías añadir tu propio codificador al sistema para gestionarlo. Es superflexible

Siguiente: ¡Hablemos de validación!
