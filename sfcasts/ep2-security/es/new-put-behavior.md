# Nuevo comportamiento PUT

Busca tu terminal y borra manualmente el directorio de caché:

```terminal-silent
rm -rf var/cache/*
```

Lo hago para que, cuando ejecutemos todas nuestras pruebas

```terminal-silent
symfony php bin/phpunit
```

veamos una advertencia de desaprobación, que es fascinante. Dice

> Desde API Platform 3.1: en API Platform 4, `PUT` siempre sustituirá los datos.
> Establece `extraProperties["standard_put"]` en `true` en cada operación para evitar romper
> el comportamiento de PUT. Utiliza `PATCH` para el comportamiento antiguo.

Vale... ¿qué significa eso? Ahora mismo, significa que nada ha cambiado: nuestra operación `PUT`se comporta como siempre lo ha hecho. Pero, en la API Platform 4, el comportamiento de `PUT`cambiará radicalmente. Y, en algún momento entre ahora y entonces, tenemos que optar por ese nuevo comportamiento para que no se rompa de repente cuando actualicemos a la versión 4 en el futuro.

## Qué cambia en PUT

¿Qué cambia exactamente? Ve a los documentos de la API y actualízalos. Utiliza la ruta `GET`collection endpoint... y pulsa "Ejecutar", para que podamos obtener un ID válido.

Genial: tenemos un tesoro con el ID 1.

Ahora mismo, si enviamos una petición a `PUT` con este ID, podemos enviar sólo un campo para actualizar sólo esa cosa. Por ejemplo, podemos enviar `description`para cambiar sólo eso.

Ah, pero antes de Ejecutar esto, necesitamos haber iniciado sesión. En mi otra pestaña, rellenaré el formulario de inicio de sesión. Perfecto. Ahora ejecuta la operación `PUT`.

Sí: pasamos sólo el campo `description`, y sólo actualiza el campo `description`: todos los demás campos permanecen igual.

Vaya, resulta que no es así como se supone que funciona `PUT` según la especificación HTTP. `PUT` se supone que es un "reemplazo". Lo que quiero decir es que, si enviamos sólo un campo, se supone que la operación `PUT` toma ese nuevo recurso -que es sólo el único campo- y sustituye al recurso existente. Es una forma complicada de decir que, al utilizar PUT, tienes que enviar todos los campos, incluso los que no cambian. De lo contrario, se establecerán en `null`.

Si te parece una locura, estoy de acuerdo, pero hay razones técnicas válidas para que sea así. La cuestión es que: así es como se supone que funciona `PUT` y en la API Platform 4, así es como funcionará `PUT`.

Sinceramente, hace que `PUT` sea menos útil. Así que te darás cuenta de que en adelante utilizaré casi exclusivamente `PATCH`.

## Pasar al nuevo comportamiento PUT

Nos guste o no, en algún momento entre ahora y la API Platform 4, tenemos que decirle a la API Platform que está bien que cambie el comportamiento de `PUT` al "nuevo" modo. Hagámoslo ahora añadiendo algo de configuración extra a cada atributo `ApiResource` de nuestra aplicación.

Abre `src/Entity/DragonTreasure.php`... y añade una nueva opción llamada `extraProperties`ajustada a una matriz con `standard_put` ajustada a `true`:

[[[ code('ea885696ed') ]]]

¡Ya está! Cópialo... porque vamos a necesitarlo aquí abajo en este`ApiResource`... aunque no tenga una operación `PUT`:

[[[ code('9e203f8e8e') ]]]

Luego, en `User`, añádelo también a los dos puntos de `ApiResource`:

[[[ code('d2d3f33582') ]]]

Ahora, cuando ejecutemos nuestras pruebas, ¡la desaprobación habrá desaparecido! No estamos utilizando la operación `PUT`en ninguna prueba, así que todo sigue pasando.

## Ver el nuevo comportamiento

Para ver el nuevo comportamiento, prueba de nuevo la ruta `PUT`: sigue enviando un solo campo. Esta vez... ¡fíjate! ¡Un error de validación 422! Todos los campos que no incluimos se establecieron como nulos... y eso provocó el fallo de validación.

Así que... esto hace que `PUT` sea un poco menos útil... y nos apoyaremos mucho más en `PATCH`. Si ya no quieres tener una operación `PUT`, tiene mucho sentido. Una cosa única del nuevo comportamiento `PUT` es que podrías utilizarlo para crear nuevos objetos... lo que podría ser útil en algunos casos extremos... o una absoluta pesadilla desde el punto de vista de la seguridad, ya que ahora tenemos que preocuparnos de que se editen o creen objetos mediante la misma operación `PUT`. Por eso, a medida que avancemos, me verás eliminar la operación `PUT` en algunos casos.

A continuación: vamos a complicar la seguridad asegurándonos de que un `DragonTreasure`sólo pueda ser editado por su propietario.
