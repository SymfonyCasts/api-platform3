# Configuración y formas de ampliar la API Platform

¡Abróchense las escamas, entusiastas de los dragones! Es hora de sumergirnos en el tercer episodio de nuestra fascinante saga API Platform: el episodio en el que las cosas se ponen... digamos: más avanzadas e interesantes.

El episodio 1 fue nuestra introducción, y cubrimos muchas cosas: paginación, filtrado y mucho sobre serialización: cómo nuestros objetos de recursos API se convierten en JSON y cómo el JSON enviado por el usuario se convierte de nuevo en esos mismos objetos.

El Episodio 2 trataba sobre la seguridad e incluía cosas como procesadores de estado -la clave para ejecutar código antes o después de guardarlo-, campos personalizados, validación, votantes y mucho más.

## ¿Clases Api personalizadas?

Todo eso está muy bien. Pero, hasta ahora, todas nuestras clases `#[ApiResource]` han sido entidades Doctrine. Y eso está muy bien Pero a medida que tu API empieza a ser diferente de tus entidades, hacer que funcione añade complejidad: grupos de serialización, ampliación de normalizadores, etc. En algún momento, resulta más fácil y claro dejar de utilizar tu entidad directamente para tu API y, en su lugar, crear una clase dedicada. Ese es el mayor enfoque de este tutorial... y nos adentrará en el concepto de proveedores y procesadores de estado... que son básicamente el núcleo de todo.

## Configuración del proyecto

Muy bien gente, ¡hagámoslo! Te recomiendo que te registres y codifiques conmigo: es más divertido y sacarás más partido de esto. Descarga el código del curso desde esta página y, cuando lo descomprimas, encontrarás un directorio `start/` con el mismo código que tengo aquí, incluido el importantísimo archivo `README.md`, que contiene todos los detalles para poner en marcha este tutorial.

El último paso es girar, abrir un terminal en el proyecto y ejecutar

```terminal
symfony serve -d
```

para iniciar el servidor web incorporado en https://127.0.0.1:8000. Saluda a: ¡Treasure Connect! Esta es la misma aplicación que construimos en los episodios uno y dos. He hecho algunos pequeños cambios -incluida la corrección de algunas imprecisiones-, pero nada importante.

La página más importante es `/api`, donde podemos ver nuestros dos recursos API: Tesoro y Usuario. ¡Y los hemos hecho bastante complejos! Tenemos sub-recursos, campos personalizados, seguridad compleja, etc. Pero, de nuevo, tanto para `DragonTreasure` como para`User`, el atributo `#[ApiResource]` está por encima de una clase de entidad. Dentro de un rato, volveremos a crear esta misma configuración de la API, pero con clases dedicadas.

[[[ code('84df11c956') ]]]

## ¿Controladores personalizados? ¿Escuchadores de eventos?

Antes de entrar en materia, voy a buscar "API platform extending" para encontrar una de mis páginas favoritas de la documentación de la API Platform. Responde a una pregunta sencilla pero poderosa: ¿cuáles son las distintas formas en que puedo extender la API Platform? Por ejemplo, los procesadores de estado son la mejor forma de ejecutar código antes o después de guardar algo: un tema del que hablamos en el último tutorial.

Por tanto, esta página es genial y quiero que la conozcas. Pero también estoy aquí para mencionar un par de cosas de las que no vamos a hablar. En primer lugar, no vamos a hablar de construir operaciones con controladores personalizados. Diablos, ¡eso ni siquiera está en esta lista! La razón: siempre hay una forma mejor -un punto de extensión diferente- de hacerlo. Por ejemplo, puedes crear una operación personalizada o incluso una clase ApiResource personalizada con un procesador de estado que te permita hacer cualquier trabajo extraño que necesite tu operación personalizada.

Tampoco vamos a hablar de los escuchadores de eventos: estos eventos del núcleo. Es por la misma razón: hay diferentes puntos de extensión que podemos utilizar. Estos eventos también sólo funcionan para REST: no funcionarán para GraphQL. Y... parece que la próxima versión de API Platform -la versión 3.2- podría incluso eliminar estos eventos en favor de un nuevo sistema interno que aproveche aún más los proveedores de estado y los procesadores de estado.

Bien, equipo: es hora de ponerse a trabajar. A continuación, vamos a utilizar un proveedor de estado para añadir un campo totalmente personalizado a uno de nuestros recursos API. Pero a diferencia de cuando lo hicimos en el tutorial anterior, este campo estará debidamente documentado en nuestra API.
