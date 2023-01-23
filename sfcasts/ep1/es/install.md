# Instalación de la API Platform

Hola y bienvenidas, hermosas personas, a un tutorial que me es muy querido: cómo construir magníficos castillos con Legos. Sería increíble, ¿verdad? Pero en realidad, estamos aquí para hablar de la API Platform Versión 3, que prometo que es tan divertida como jugar con Legos. Pero no le digas a mi hijo que he dicho eso.

La API Platform es, sencillamente, una herramienta sobre Symfony que nos permite crear API potentes y disfrutar del proceso Existe desde hace años y, sinceramente, lo está petando. Tienen su propia conferencia dedicada y, realmente, se han superado a sí mismos con la última versión 3.

Si eres nuevo en la API Platform, no te culparía si dijeras:

> Vamos Ryan... crear una API no es tan difícil. Sólo se trata de devolver JSON: un
> ¡un montón de garabatos y corchetes!

Vale, eso es cierto (al menos para las primeras rutas). Pero hay un montón de pequeños detalles a los que prestar atención. Por ejemplo, si tienes una API que devuelve datos de productos, querrás que ese JSON del producto se devuelva de la misma manera, con los mismos campos, en todas las rutas. Ese proceso se llama serialización. Además, muchas API devuelven ahora campos adicionales que describen el significado de los datos. Vamos a ver y hablar de algo llamado "JSON-LD", que hace exactamente eso.

¿Qué más? ¿Qué hay de la documentación? Idealmente, documentación interactiva que se genere automáticamente... porque no queremos construir y mantener eso a mano. Incluso si estás construyendo una API sólo para ti, tener documentación es genial. Paginar colecciones también es superimportante, filtrar y buscar colecciones, validación y negociación del tipo de contenido, que es cuando ese mismo producto podría devolverse como JSON, CSV u otro formato. Así que sí, crear una ruta API es fácil. Pero crear una API rica es algo totalmente distinto. Y ése es el objetivo de la API Platform. Ah, y si estás familiarizado con la versión 2 de la API Platform, la versión 3 te resultará muy familiar. Simplemente es más limpia, más moderna y más potente. Así que saca tus Legos, ¡y hagámoslo!

## La distribución de la API Platform

Hay dos formas de instalar la API Platform. Si encuentras su sitio web y haces clic en la documentación, verás que hablan de la "Distribución" de la API Platform. ¡Esto está muy bien! Es un proyecto completamente prefabricado con Docker que te ofrece un lugar para construir tu API con Symfony, un área de administración React, andamiaje para crear un frontend Next.js y mucho más. Incluso te proporciona un servidor web listo para producción con herramientas adicionales como Mercure para actualizaciones en tiempo real. Es la forma más potente de utilizar la API Platform.

Pero... en este tutorial, no vamos a utilizarla. ¡Odio las cosas bonitas! No, empezaremos nuestro proyecto Lego desde cero: con una aplicación Symfony perfectamente normal y aburrida. ¿Por qué? Porque quiero que veas exactamente cómo funciona todo bajo el capó. Luego, si quieres utilizar esta Distribución más adelante, puedes hacerlo.

## Configuración del proyecto y nuestro proyecto

Bien, para ser un verdadero "Campeón de la Devolución JSON de la API Platform", ¡deberías codificar conmigo! Descarga el código fuente de esta página. Y tras descomprimirlo, encontrarás un directorio `start/` con el mismo código que ves aquí. Se trata de un nuevo proyecto Symfony 6.2 con... absolutamente nada en él. Abre este archivo`README.md` para ver todas las instrucciones de configuración. El último paso será abrir el proyecto en un terminal y utilizar el binario de Symfony para ejecutarlo:

```terminal
symfony serve -d
```

Esto inicia un servidor web local en `127.0.0.1:8000`. Haré trampas y haré clic en ese enlace para abrir... un proyecto Symfony 6.2 completamente vacío. Aquí no hay literalmente nada, excepto esta página de demostración.

¿Qué vamos a construir? Como todos sabemos, a Internet le falta algo terriblemente importante: ¡una aplicación para que los dragones presuman de sus tesoros robados! Porque si hay algo que le gusta más a un dragón que un tesoro, es presumir de él. Sí, crearemos una rica API que permita a los dragones expertos en tecnología publicar nuevos tesoros, buscar tesoros, buscar tesoros de otros dragones, etc. Y sí, acabo de terminar de leer El Hobbit.

## Instalación de la API Platform

Vamos a instalar la API Platform Vuelve a tu terminal y ejecuta:

```terminal
composer require api
```

Este es un alias de Symfony Flex. Aquí arriba, puedes ver que en realidad está instalando algo llamado `api-platform/api-pack`. Si no estás familiarizado, un "paquete" en Symfony es una especie de paquete falso, que te permite instalar fácilmente un conjunto de paquetes. Si te desplazas hacia abajo, instaló el propio `api-platform`, Doctrine, puesto que aún no lo tenía, y algunos otros paquetes. Al final... veamos... la receta`doctrine-bundle` nos pregunta si queremos incluir un archivo `docker-compose.yml`para ayudarnos a añadir una base de datos a nuestro proyecto. ¡Qué bien! Esto es opcional, pero voy a decir "p" por "Sí permanentemente". Y... ¡listo!

Lo primero que hay que ver es el archivo `composer.json`:

[[[ code('f8a66fd06b') ]]]

Como prometí, ese paquete API Platform añadió un montón de paquetes a nuestro proyecto.
Técnicamente, no todos son necesarios, pero esto nos va a dar una experiencia realmente rica construyendo nuestra API. Y si ejecutas

```terminal
git status
```

... ¡sí! Ha actualizado los archivos habituales... y también ha añadido un montón de archivos de configuración para los nuevos paquetes. Parece que hay mucho... pero las apariencias engañan. Todos estos directorios están vacíos... y los archivos de configuración son pequeños y sencillos. También tenemos algunos archivos `docker-compose` que utilizaremos en un minuto para poner en marcha una base de datos.

Así que... ahora que la API Platform está instalada... ¿ya nos ha dado algo? Sí, y es genial Vuelve al navegador y dirígete a `/api`. ¡Vaya! ¡Tenemos una página de documentación de la API! Está vacía porque, ya sabes, todavía no tenemos una API, pero pronto cobrará vida.

A continuación: Vamos a crear nuestra primera entidad Doctrine y a "exponerla" como Recurso API. Es hora de hacer algo de magia.