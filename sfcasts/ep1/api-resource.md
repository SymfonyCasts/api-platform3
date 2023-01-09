# Creating your First ApiResource

We're about to build an API that will allow dragons to show off their treasure. Right now, our project doesn't have a *single* database entity, and we're going to need one to store the individual treasures. To create it, find your terminal and, first, run

```terminal
composer require maker --dev
```

to install Maker Bundle. *Then* run:

```terminal
./bin/console make:entity
```

Perfect! Let's call our entity `DragonTreasure`. Then it's going to ask us a question that you may have never seen before - `Mark this class as an API platform resource`? - because API Platform is installed. I'm going to say `no` for now, because we're going to do this step *manually* in a moment so you can see it.

Okay, let's start adding some properties. I'll add `name` as a string, with a Field Length of the default 255, and make it *not* null in the database. Then, I'll add `description` with a `text` type, and make *that* not null the database as well. We should also add a `value`, like... how much the treasure is *worth*. That will be an `integer` and *also* not null. We simply *have to* have a `coolFactor` too, because other dragons need to know just how cool this treaure is. We'll rank that from 1 to 10, so let's make this an `integer` which will be *not* null. Then, we'll need a `createdAt` `datetime_immutable` that's not null. *Finally*, let's add an `isPublished` property, which will be a `boolean` type, also not null, and hit "enter" to finish.

There's nothing very special here so far. This created two classes: The `DragonTreasureRepository` (which we're not going to worry about), and the `DragonTreasure` entity itself with `$id`, `$name`, `$description`, `$value`, as well as some other properties and the getters and setters below. So... pretty boring at the moment. There *is* one little bug in this version of MakerBundle, though. You can see here that it generated and `isIsPublished()` method, so let's change that to `getIsPublished()` instead. It's trying to be a little *too* clever.

All right, so we have our entity. Now we need a migration for it, but that might be a little difficult since we don't have our database set up yet. I'm actually going to set up our database using Docker. The Doctrine bundle gave us a nice `docker-compose.yml` file that boots up Postgres, so we're going to use that. Spin over to your terminal and run:

```terminal
docker-compose up -d
```

If you don't want to use Docker for some reason, then you can start your own database engine and then, in `.env` or `.env.local`, just configure your database URL correctly. Because I'm using Docker and also the Symfony binary, I don't need to configure anything. The Symfony web server will automatically set that database URL environment variable for me.

Okay, to make the migration, run:

```terminal
symfony console make:migration
```

This `symfony console` is just like `./bin/console` except, because we're running it through Symfony, it's going to inject that environment variable so that it talks to the Docker database. Perfect! And, as usual, we're going to spin over and check out the new migration to make sure it doesn't contain any surprises. And... looks good! The table `dragon_treasure` is currently grayed out. So spin back over and run:

```terminal
symfony console doctrine:migrations:migrate
```

Done!

We now have an entity and a database table, but if you go and refresh the documentation... there's still nothing there. What we need to do is tell API Platform to expose our new `DragonTreasure` entity as an API resource. To do this, go above the class and add a new attribute called `ApiResource` and hit "tab" to add that `use` statement. That's it! As soon as we do that... and refresh... whoa! The documentation grew! It now shows that we have *five* different endpoints: One to retrieve *all* of the `DragonTreasure` resources, one to retrive an *individual* `DragonTreasure`, one to *create* a `DragonTreasure`, one to *replace* a `DragonTreasure`, one to update it, and one to delete it. And this is more than just documentation. These end points actually *exist*. Go over and click "Try it Out", then "Execute", and... it doesn't actually return anything because our database is currently empty, but we *do* have an empty array set here, so this *is* working. And we'll talk about all of the other fancy keys here shortly. They're *super* important.

As you just saw, the easiest way to create an API is by adding this `ApiResource` above your entity. One thing I want to mention is that you can *also* add this `ApiResource` attribute to classes that are *not* entities, and that's something we're going to talk about in a future tutorial. This can be a nice way to separate what your API looks like from what your entity looks like, especially in bigger APIs. We'll discuss the details behind that later. For now, using `ApiResource` on top of our entity is going to work out great.

Next: We need to talk about something *important* called "OpenAPI". This is the *key* to how these interactive docs are being generated.
