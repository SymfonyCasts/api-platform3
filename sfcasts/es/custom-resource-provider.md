# Proveedor de estado de recursos personalizado

Tenemos una nueva y reluciente clase de recurso API y... en su mayor parte, la utilizaremos como de costumbre.

## Personalizar las opciones de ApiResource

Por ejemplo, en lugar de `DailyQuests`, tal vez cambiemos`shortName` por sólo `Quest`. Cuando echamos un vistazo a los documentos, como era de esperar, el título cambia... junto con todas las URL.

[[[ code('81398c9919') ]]]

## Crear el proveedor de estado

Para poder cargar datos y que esta ruta de recolección no devuelva un 404, necesitamos un proveedor de estado. Y no se trata sólo de las rutas `GET`. La ruta `PUT` utiliza un proveedor de estado, al igual que `DELETE` y `PATCH`: todas ellas cargan primero el recurso, antes de editarlo o borrarlo.

Así que ¡hagamos un proveedor de estado! Ya lo hemos hecho antes. Ejecuta en tu terminal:

```terminal
./bin/console make:state-provider
```

Llámalo `DailyQuestStateProvider`. ¡Un nombre impresionante!

Vuelve a girar, abre el directorio `State/` y... ¡ahí está! Nuestro trabajo es sencillo: devolver el objeto u objetos `DailyQuest` para la operación actual.

[[[ code('291fc8cd46') ]]]

Empecemos de forma superbásica: devuelve un array con dos objetos `new DailyQuest()`codificados. Ambos están vacíos... porque esa clase no tiene propiedades.

[[[ code('8ee10b2a67') ]]]

Para decirle a API Platform que utilice el nuevo y brillante proveedor, en `DailyQuest`, añade `provider` establecido en `DailyQuestStateProvider::class`.

[[[ code('492ce3552c') ]]]

¡Vamos a probarlo! Vuelve a los documentos para "Ejecutar" la ruta de recogida y... ¡sí! ¡Se acabó el 404! Obtenemos un 200... ¡y devuelve 2 elementos! Lo único que tienen son los campos JSON-LD - `@id` y `@type` -, pero tiene sentido, ya que la clase no tiene ninguna otra propiedad.

## Añadir el identificador

Así que, ¡bien! Pero, antes de desbocarnos y añadir más propiedades, tenemos que hablar de por qué falta el punto final `GET` one. Tenemos la ruta `GET` colección, pero no `GET`-a-elemento-único. ¿Por qué?

Cada recurso de la API necesita un "identificador". Ahora mismo, nuestra clase no tiene un identificador... y eso hace que las dos rutas GET choquen. ¡Deja que te lo enseñe!

Gira y Ejecuta:

```terminal
php bin/console debug:router
```

Esto me encanta. API Platform crea una ruta real para cada operación de cada recurso API. Haré esto un poco más pequeño... mejor. Puedes ver todas las rutas para las búsquedas. Aquí está la de `_get_collection` y, encima, la de `_get_single`... ¡pero con la misma URL!

Normalmente, la URL sería `/api/quests/{id}`... donde `id` se conoce como el identificador. Pero... nuestro `DailyQuest` no tiene propiedades... así que API Platform no tiene ni idea de qué utilizar para el identificador.

Entonces, ¿cuál es la solución? La más sencilla es añadir una propiedad `$id`: `public int $id`... y, para simplificar, añadamos un constructor al que podamos pasar la propiedad `int $id`. Establece la propiedad dentro.

[[[ code('082c89c577') ]]]

En `DailyQuestStateProvider`, inventa unos cuantos identificadores: ¿qué tal `4` y `5`. 

[[[ code('00751c3309') ]]]

Genial, ahora vuelve a volcar las rutas:

```terminal-silent
php bin/console debug:router
```

¡Contempla! El único `GET` tiene una URL diferente con `{id}`. El `id`también faltaba en `put`, `patch`, y `delete`... y ahora también está. En los documentos, al actualizar... vemos lo mismo.

El identificador es importante porque se utiliza en las URL... y también para generar la cadena IRI `@id` de cada elemento. Aquí, puedes ver que `@id`apunta ahora a `/api/quests/4`.

## Un Identificador no tradicional con identificador: true

Pero espera, ¿cómo sabe la API Platform que el `id` es el "identificador" tan importante... y no una propiedad normal? Sinceramente... no estoy del todo seguro. Pero parece que el nombre `id` es especial... en algún lugar de API Platform. Si nombras una propiedad `id`, API Platform dice:

> ¡Oh, ése debe ser tu identificador!

Y... ¡normalmente no se equivoca! Pero, hay una forma más explícita de decir que una propiedad es un identificador. A continuación, en lugar de un identificador entero, veamos si podemos utilizar un identificador de fecha, de modo que tengamos URL como `/api/quests/2023-06-05`.
