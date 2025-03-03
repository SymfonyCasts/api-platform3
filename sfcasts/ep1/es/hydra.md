# Hydra: Descripción de clases, operaciones y más de la API

Estamos examinando la documentación JSON-LD que describe nuestra API. Ahora mismo, sabemos que sólo tenemos un recurso API: `DragonTreasure`. Pero si te fijas en la sección `supportedClasses`, en realidad hay un montón de clases compatibles. Hay una llamada `Entrypoint`, otra llamada `ConstraintViolation`, y otra llamada `ConstraintViolationList`. Estas dos últimas aparecerán más adelante, cuando hablemos de los errores de validación.

## Punto de entrada: la página de inicio de tu API

Pero este `Entrypoint` es realmente interesante. Se llama "El punto de entrada de la API", y en realidad describe el aspecto de la página de inicio de nuestra API. No siempre pensamos en que nuestras API tengan una página de inicio, pero pueden y deben tenerla.

Y, ¡bienvenidos a nuestra página de inicio de la API - estilo HTML! Si te desplazas hasta la parte inferior, podrás ver otros formatos. Haz clic en "JSON-LD" y... ¡saluda a la página de inicio de la API en formato JSON-LD! Esto devuelve un recurso de la API llamado `Entrypoint`, cuya función es indicarnos dónde podemos encontrar información sobre los demás recursos de la API ¡Es como los enlaces de una página de inicio! Puedes descubrir la API yendo a este`Entrypoint` y siguiendo el enlace `@context`... que apunta a esto.

## Hola Hydra

De todos modos, el propósito de JSON-LD es añadir esos tres campos adicionales a tus recursos API: `@id`, `@type`, y `@context`. Luego podemos aprovechar `@context` para apuntar a otra documentación para obtener más metadatos o más contexto. Por ejemplo, en la parte superior de la documentación de JSON-LD, se apunta a varios otros documentos que añaden más significado a JSON-LD.

Y aquí hay uno realmente importante llamado `hydra`. Hydra es, en pocas palabras, una extensión de JSON-LD: describe aún más campos que puedes añadir a JSON-LD y lo que significan.

Piénsalo: si queremos describir totalmente nuestra API, necesitamos poder comunicar cosas como qué clases tenemos, sus propiedades, si cada una es legible o escribible, y qué operaciones admite cada clase. Esa comunicación se hace aquí abajo... y en realidad forma parte de Hydra. Sí, si utilizas JSON-LD por sí mismo... no tiene una forma predefinida de anunciar cómo son tus modelos. Pero entonces Hydra dice:

> ¿Y si permitimos que las clases de la API se describan con una clave llamada
> `hydra:supportedClasses`?

Este es el panorama general: API Platform nos permite obtener documentación JSON-LD de la API que contenga campos adicionales `hydra`. El resultado final es un sistema que describe completamente nuestra API. Describen los modelos que tenemos, las operaciones... todo.

## ¿Por qué Hydra y OpenAPI?

Y sí, si esto suena muy parecido a lo que pretende OpenAPI, tienes toda la razón. Ambos hacen lo mismo: describir nuestra API. De hecho, si entras en`/api/docs.json`, ésta es la descripción OpenAPI de nuestra API.

***TIP
En la API Platform 3.2 y posteriores, la URL cambió a `/api/docs.jsonopenapi`
***

Si sustituimos`.json` por `.jsonld`, ésta es la descripción JSON-LD Hydra de la misma API. ¿Por qué tenemos las dos? Hydra es un poco más potente: hay ciertas cosas que puede describir que OpenAPI no puede. Pero OpenAPI es mucho más común y tiene más herramientas construidas sobre ella. API Platform proporciona ambas... ¡por si las necesitas!

A continuación: Vamos a añadir algunas herramientas de depuración serias a nuestra configuración de la API Platform.
