# Filtrar relaciones

Antes hemos añadido un montón de filtros a `DragonTreasure`. Vamos a añadir unos cuantos más -empezando por `User` - para que podamos mostrar algunos superpoderes de filtrado en las relaciones.

## Utilizar PropertyFilter en relaciones

Empieza como de costumbre: `ApiFilter` y utilicemos primero `PropertyFilter::class`. Recuerda: se trata de una especie de filtro falso que permite a nuestro cliente de la API seleccionar los campos que desee. Y todo esto es bastante familiar hasta ahora.

[[[ code('794f814755') ]]]

Cuando nos dirigimos, actualizamos y vamos a la ruta de recolección `GET`... vemos un nuevo campo`properties[]`. Podríamos elegir devolver sólo `username`... o `username`y `dragonTreasures`.

Cuando pulsamos "Ejecutar"... ¡perfecto! Vemos los dos campos... donde `dragonTreasures`es una matriz de objetos, cada uno de los cuales contiene los campos que elegimos incrustar.

De nuevo, esto es super duper normal. Así que vamos a intentar algo más interesante. De hecho, lo que vamos a intentar no está soportado directamente en los documentos interactivos.

Así que, copia esta URL... pégala y añade `.jsonld` al final.

Éste es el objetivo: quiero devolver el campo `username` y después sólo el campo `name`de cada tesoro dragón. La sintaxis es un poco fea: es `[dragonTreasures]`, seguido de `[]=name`.

Y así... ¡sólo muestra `name`! Así que, directamente,`PropertyFilter` nos permite llegar a través de las relaciones.

## Buscar campos de relación

Hagamos otra cosa. Volvamos a `DragonTreasure`. Podría ser útil filtrar por `$owner`: podríamos obtener rápidamente una lista de todos los tesoros de un usuario concreto.

¡No te preocupes! Sólo tienes que añadir `ApiFilter` por encima de la propiedad `$owner`, pasando el fiel `SearchFilter::class` seguido de `strategy: 'exact'`.

[[[ code('950f478cdd') ]]]

Volviendo a los documentos, si abrimos la ruta de la colección de tesoros `GET` y le damos una vuelta... veamos... aquí está: "propietario". Introduce algo como `/api/users/4`... suponiendo que se trate de un usuario real en nuestra base de datos, y... ¡sí! ¡Aquí están los cinco tesoros propiedad de ese usuario!

Pero quiero volverme más loco: quiero encontrar todos los tesoros que sean propiedad de un usuario que coincida con un nombre de usuario concreto. Así que en lugar de filtrar por`owner`, tenemos que filtrar por `owner.username`.

¿Cómo? Bueno, cuando queremos filtrar simplemente por `owner`, podemos poner el `ApiFilter`justo encima de esa propiedad. Pero como queremos filtrar por `owner.username`, no podemos ponerlo encima de una propiedad... porque `owner.username` no es una propiedad. Éste es uno de los casos en los que necesitamos poner el filtro encima de la clase. Y... eso también significa que tenemos que añadir una opción `properties` establecida en una matriz. Dentro, digamos `'owner.username'` y establecerla en la estrategia `partial`.

[[[ code('986925a0c3') ]]]

¡Vale! Vuelve y actualiza. Sabemos que tenemos un propietario cuyo nombre de usuario es "Smaug"... así que volvamos a la ruta de la colección `GET` y... aquí en `owner.username`, busquemos "maug"... y pulsemos "Ejecutar".

Veamos... ¡Ha funcionado! Esto muestra todos los tesoros propiedad de cualquier usuario cuyo nombre de usuario contenga `maug`. ¡Genial!

Bien, escuadrón: prepárate para la gran final: los Subrecursos. Éstos han cambiado mucho en API Platform 3. Vamos a sumergirnos en ellos.
