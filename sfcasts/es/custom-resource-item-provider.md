# Proveedor de elementos de recursos personalizados

Intentemos obtener un único elemento. Cambiaré la fecha, pulsaré "Ejecutar", y... código de estado 200. Espera un momento... está devolviendo una colección: ¡exactamente los mismos datos que nuestra ruta de colección!

## Operaciones de colección frente a operaciones de elemento

Vale, cada operación puede tener su propio proveedor. Pero cuando ponemos `provider` directamente bajo `#[ApiResource]`, éste se convierte en el proveedor de cada operación. Eso está muy bien... siempre que no olvides que algunas operaciones obtienen una colección de recursos, mientras que otras obtienen un único elemento.

Dentro de nuestro proveedor, el `$operation` nos ayuda a conocer la diferencia. `dd()` que...

[[[ code('8f939b320f    ') ]]]

Luego, por aquí, copia la URL, pégala en una pestaña nueva y añade `.jsonld` al final. ¡Ya está! Se trata de una operación `Get`. Si intentamos obtener la colección, es`GetCollection`.

De vuelta en el proveedor, `if ($operation instanceof CollectionOperationInterface)`,`return $this->createQuests()`.

[[[ code('5ca226a5c3') ]]]

A continuación, sabemos que se trata de una operación "elemento".

## Variables URI

Así que con esto ya funciona la operación colección. Ahora, necesitamos una forma de extraer la cadena de fecha de la URL para poder encontrar la búsqueda que coincida. ¿Cómo podemos conseguirlo? `dd($uriVariables)`.

[[[ code('c8274c01f4') ]]]

Cuando actualizamos... he aquí: ¡hay un `dayString` dentro! Observa que, en `DailyQuest`, nunca configuramos el aspecto que debe tener la URL. Puedes hacerlo, pero por defecto, API Platform calcula automáticamente cómo deben ser la ruta y la URL. Ejecuta:

```terminal
php bin/console debug:router
```

Para los puntos finales de los elementos, es `/api/quests/{dayString}`: el `dayString` es un comodín en la ruta. En el proveedor, `$uriVariables` contendrá todas las partes variables de la URI, así que `dayString` en nuestro caso. Esto nos pone en peligro.

## Devolver un único elemento

Aquí abajo, necesitamos devolver un único `DailyQuest` o null. Digamos`$quests = $this->createQuests()`, luego`return $quests[$uriVariables['dayString']]` o `null` si no está configurado.

[[[ code('1a461433ba') ]]]

Recuerda: esto funciona porque el array utiliza `dayString` para cada clave. En una app real, querríamos hacer esto de forma más eficiente: no tiene sentido cargar cada búsqueda... sólo para devolver una. Pero para nuestra aplicación de prueba, esto funcionará bien.

Vale, prueba esa ruta. Ya está Un resultado. Y si probamos con una fecha aleatoria que no existe... como "2013"... obtenemos un 404. API Platform ve que hemos devuelto`null` y gestiona el 404 por nosotros.

¡Ahora somos los orgullosos padres de un proveedor de estado totalmente funcional! Aunque pronto hablaremos más de esto, incluyendo temas como la paginación. Pero a continuación: vamos a centrarnos en crear un procesador de estado para nuestro recurso personalizado.
