# FiltroPropiedades: Conjuntos de campos dispersos

Como a los dragones les encantan los tesoros caros, añadamos una forma de filtrar en función del valor, como dentro de un rango. Hay un filtro incorporado para eso llamado`RangeFilter`. Busca la propiedad `$value` y, como hicimos antes, utiliza`#[ApiFilter()]` y dentro `RangeFilter` (la del ORM) `::class`:

[[[ code('94173682b5') ]]]

Éste no necesita ninguna otra opción, así que... ¡hemos terminado! Joder... ha sido fácil. Cuando actualicemos... ábrelo, y dale a "Probar".... ¡mira qué bien! Tenemos un montón de filtros nuevos: `value[between]`, `value[gt]` (o "mayor que"),`value[gte]`, etc. Probemos `value[gt]`... con un número aleatorio... tal vez `500000`. Cuando pulsemos "Ejecutar"... ¡sí! Aquí se actualizó la URL. No es... la URL más bonita del mundo -debido a la codificación-, pero funciona a las mil maravillas. Y abajo en los resultados... ¡aparentemente hay 18 tesoros que valen más que eso!

## FiltroPropiedad

El último filtro que quiero mostrarte... en realidad no es un filtro en absoluto. Es una forma de que nuestros clientes de la API elijan qué campos quieren que se les devuelvan... en lugar de qué resultados.

Para mostrarlo, busca el método `getDescription()`. Imagina que queremos devolver una versión más corta y truncada de la descripción. Para ello, copia el método`getDescription()`, pégalo a continuación y cámbiale el nombre a `getShortDescription()`:

[[[ code('b23723799d') ]]]

Para truncarlo, podemos utilizar la función `u()` de Symfony. Escribe `u` y asegúrate de pulsar "tab" para que se autocomplete. Esta es una función rara que nos da Symfony... y al darle a "tab" se añadió una declaración `use` para ella:

[[[ code('b36939414f') ]]]

Esto crea un objeto con todo tipo de cosas relacionadas con las cadenas, incluyendo`truncate()`. Pasa 40 para truncar en `40` caracteres seguidos de `...`.

¡Método terminado! Para exponer esto a nuestra API, arriba, añade el atributo `Groups` con`treasure:read`:

[[[ code('dd0b92e22a') ]]]

¡Precioso! Bien, vuelve a la documentación y actualízala. Abre la ruta `GET`, pulsa "Probar", "Ejecutar" y... bonito. ¡Aquí está nuestra descripción truncada!

Aunque... es raro que ahora devolvamos dos descripciones: una corta y la normal. Si nuestro cliente de la API quiere la descripción corta, puede que no quiera que le devolvamos también la descripción completa... por el bien del ancho de banda.

¿Qué podemos hacer? Presentamos: ¡el `PropertyFilter`! Vuelve a `DragonTreasure`. A diferencia de los demás, este filtro debe ir por encima de la clase. Así que aquí, digamos`ApiFilter`, y luego `PropertyFilter` (en este caso, sólo hay uno)`::class`. Hay algunas opciones que puedes pasar a esto - que puedes encontrar en los docs - pero no necesitamos ninguna de ellas:

[[[ code('29aa3955b8') ]]]

Entonces... ¿qué hace eso? Vuelve atrás, actualiza la documentación, abre la ruta de recolección GET y pulsa "Probar". ¡Woh! Ahora vemos una caja `properties[]`y podemos añadirle elementos. ¡Vamos a probarlo! Añade una nueva cadena llamada `name`y otra llamada `description`.

Momento de la verdad. Pulsa "Ejecutar", y... ¡ahí está! Las ha añadido a la URL de forma normal. Pero mira la respuesta: sólo contiene los campos `name` y `description`. Bueno... también contiene los campos JSON-LD, pero los datos reales son sólo esos dos campos.

Si elimináramos las cadenas `properties`, obtendríamos la respuesta normal y completa. Así que, por defecto, obtienes todos los campos. Pero ahora los usuarios pueden elegir menos campos si lo desean.

## ¿Qué pasa con Vulcain?

Todo esto funciona bastante bien. Pero si echas un vistazo a la documentación de la API Platform para `PropertyFilter`, en realidad recomiendan una solución diferente: algo llamado "Vulcain". No, no es el planeta natal de Spock. Estamos hablando de un protocolo que añade funciones a tu servidor web. Fue creado por el equipo de API Platform, y si nos desplazamos un poco hacia abajo, tienen un ejemplo realmente bueno.

Imagina que tenemos la siguiente API. Si hacemos una petición a `/books`, obtendremos de vuelta estos dos libros. Bastante sencillo. Entonces, si queremos obtener más información sobre el primer libro, hacemos una petición a esa URL: `/books/1`. Pero... ahora queremos información sobre el autor, así que hacemos una petición a`/authors/1`.

Así que, para obtener toda la información sobre el libro y sobre el autor, al final tuvimos que hacer cuatro peticiones: la original y 3 más. Eso no es bueno para el rendimiento.

Lo que Vulcain te permite es hacer sólo esta primera petición... pero decirle al servidor que te envíe los datos de las otras peticiones.

Podemos ver esto mejor en JavaScript, y hay un pequeño ejemplo aquí abajo. En este caso, imagina que estamos haciendo una petición a `/books/1` pero sabemos que también necesitamos la información del autor. Así que, cuando hacemos la petición, incluimos una cabecera especial `Preload`. Esto le dice al servidor:

> Después de devolver los datos del libro, utiliza un push del servidor para enviarme la información
> encontrada siguiendo el IRI `author`.

Lo realmente genial es que tu JavaScript no cambia realmente. Sigues utilizando `fetch()` para hacer una segunda petición a la URL `bookJSON.author`... sólo que ésta volverá instantáneamente porque el navegador ya tiene los datos.

No voy a entrar en todos los detalles, pero el `Preload` del primer ejemplo es aún más impresionante: `/member/*/author`. Eso le dice al servidor que envíe todos los datos como si también hubiéramos solicitado cada una de las claves `member` -por tanto, todos los libros- y las URL de sus autores.

La cuestión es: si utilizas Vulcain, los usuarios de tu API pueden hacer cambios minúsculos para disfrutar de enormes ventajas de rendimiento... sin que nosotros tengamos que añadir mucha fantasía a nuestra API.

A continuación: Hablemos de formatos. Sabemos que nuestra API puede devolver representaciones JSON-LD, JSON e incluso HTML de nuestros recursos. Vamos a añadir dos nuevos formatos, incluido un formato CSV, que será la función de exportación CSV más rápida que jamás hayas creado.
