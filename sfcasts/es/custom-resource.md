# Recurso totalmente personalizado

Hasta ahora, tenemos dos clases de recursos API: `DragonTreasure` y `User`. Y ambas son clases de entidad. Pero tener tu atributo `#[ApiResource]` encima de una clase de entidad no es un requisito. Puedes crear cualquier clase PHP normal y aburrida que quieras, espolvorear este atributo `#[ApiResource]` encima, y ¡zas! se convierte en parte de tu API. Bueno, queda algo de trabajo, pero eso lo veremos en un momento.

¿Por qué querrías crear una clase personalizada para tu API en lugar de utilizar una entidad? Por dos razones principales. Primero: porque los datos que estás sirviendo no proceden de la base de datos... o proceden de una mezcla de diferentes tablas de la base de datos. O segundo: los datos que estás obteniendo proceden de la base de datos... pero como tu API tiene un aspecto lo bastante diferente de tu entidad, quieres limpiar las cosas teniendo una clase para tu API separada de tu clase de entidad. Vamos a jugar con ambos casos, empezando por el primero: cuando tus datos proceden de un lugar distinto a una base de datos.

## Creación de la clase

La situación es la siguiente: cada día publicamos una búsqueda única para que la completen nuestros dragones. Queremos exponer estas búsquedas como un nuevo recurso de la API. Podrán listar todas las búsquedas anteriores, buscar una búsqueda individual por fecha o actualizar el estado de una búsqueda si la completan. Eso es bastante fácil. Pero no vamos a almacenar estos datos en la base de datos. Vamos a fingir que los datos proceden de otro sitio.

Así que, en lugar de crear una entidad, vamos a crear una clase nueva y la pondremos en este directorio `ApiResource/`. Este directorio fue añadido por la receta de la API Platform cuando la instalamos originalmente... y está destinado a ser el hogar de tus clases de recursos API. Añade una nueva clase PHP... y llamémosla `DailyQuest`.

Para que forme parte de tu API, sólo tienes que añadir `#[ApiResource]` encima de la clase.

[[[ code('5a3c9f2cf6') ]]]

Y ya está Pásate por la documentación y... ¡tachán! ¡Ya está en la documentación de nuestra API! Aunque tiene un aspecto un poco raro: falta la operación única `GET`. Normalmente, veríamos algo como `/api/daily_quests/{id}`. Descubriremos el misterio de por qué falta en un minuto.

## Directorios de clase ApiResource

Ah, y por cierto: para encontrar todas nuestras clases de recursos API, API Platform explora sólo dos directorios en busca de este atributo: `src/Entity/` y `src/ApiResource`. Aunque esto puede modificarse en `/config/packages/api_platform.yaml` con una configuración de rutas de mapeo.

Vale, entonces... ¿cómo es posible que esto ya forme parte de nuestra API? Es sólo una clase. Diablos, ¡ni siquiera tiene propiedades! Prueba el punto final de la colección `GET`. Pulsa "Ejecutar" y... obtenemos un 404. Así que... en realidad no funciona. Si probamos la ruta`POST` -sólo estamos enviando datos vacíos- devuelve un código de estado 201 como si hubiera tenido éxito... pero entre bastidores, no ha pasado absolutamente nada. No se ha creado ni guardado ningún dato.

Volvamos a nuestra página favorita de "actualización" de la documentación: la que habla de proveedores y procesadores. Si añadimos el atributo `#[ApiResource]` sobre una clase de entidad, obtendremos gratuitamente estos procesadores y proveedores. Resulta que... ésta es realmente la única diferencia entre añadir `#[ApiResource]` encima de una clase cualquiera y añadirlo encima de una entidad. Cuando utilizas `#[ApiResource]` sobre una entidad, API Platform te proporciona automáticamente procesadores y proveedores. Cuando creas una clase personalizada, empiezas sin proveedores ni procesadores. Esto significa que la API Platform no tiene ni idea de cómo cargar los datos cuando haces una petición a `GET`... ni de cómo procesar los datos al final de una petición a `POST` o `PATCH`.

¡Añadir esas piezas que faltan es nuestro trabajo! Empecemos con eso a continuación.
