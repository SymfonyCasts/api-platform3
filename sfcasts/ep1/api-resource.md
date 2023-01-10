# Creating your First ApiResource

We're about to build an API for the *very* important job of allowing dragons to show off their treasure. Right now, our project doesn't have a *single* database entity... but we're going to need one to store all that treasure.

## Generating our First Entity

Find your terminal and first run

```terminal
composer require maker --dev
```

to install Maker Bundle. *Then* run:

```terminal
./bin/console make:entity
```

Perfect! Let's call our entity `DragonTreasure`. Then it asks us a question that you maybe haven't seen before - `Mark this class as an API platform resource`? - because API Platform is installed. I'm going to say `no` because we're going to do this step *manually* in a moment so you can see it.

Okay, let's start adding some properties. Add `name` as a string, with a Length of the default 255, and make it *not* null in the database. Then, add `description` with a `text` type, and make *that* not null. We also need a `value`, like... how much the treasure is *worth*. That will be an `integer` and *also* not null. And we simply *have to* have a `coolFactor`, because dragons need to know just how awesome this treasure is. That'll be a number from 1 to 10, so make it an `integer` which will be *not* null. Then, a `createdAt` `datetime_immutable` that's not null... and *Ffnally*, add an `isPublished` property, which will be a `boolean` type, also not null... and hit "enter" to finish.

Phew! There's nothing very special here so far. This created two classes: `DragonTreasureRepository` (which we're not going to worry about), and the `DragonTreasure` entity itself with `$id`, `$name`, `$description`, `$value`, etc and the getters and setters below. Beautifuly boring. There *is* one little bug in this version of MakerBundle, though. You can see that it generated an `isIsPublished()` method. Let's change that to `getIsPublished()` instead.

## Setting up the Dtaabase

All right, so we have our entity. Now we need a migration for its table... but that might be a bit difficult since we don't have our database set up yet. I'm going to set up a database using Docker. The DoctrineBundle recipe gave us a nice `docker-compose.yml` file that boots up Postgres, so... let's use that! Spin over to your terminal and run:

```terminal
docker-compose up -d
```

If you don't want to use Docker, then feel free to start your own database engine and then, in `.env` or `.env.local`, configure  DATABASE_URL. But because I'm using Docker and also the `symfony` binary, I don't need to configure anything. The Symfony web server will automatically see the Docker database and set that `DATABASE_URL` environment variable *for* me.

Okay, to make the migration, run:

```terminal
symfony console make:migration
```

This `symfony console` is just like `./bin/console` except it injects the `DATABASE_URL` environment variable so that it can talk to the Docker database. Perfect! Spin over and check out the new migration file... to make sure it doesn't contain any weird surprises. And... looks good! So spin back over and run this with:

```terminal
symfony console doctrine:migrations:migrate
```

Done!

## Exposing our First API Resource

We now have an entity and a database table, but if you go and refresh the documentation... there's still nothing there. What we need to do is tell API Platform to expose our new `DragonTreasure` entity as an API resource. To do this, go above the class and add a new attribute called `ApiResource`. Hit "tab" to add that `use` statement.

That's it! As soon as we do that... and refresh... whoa! The documentation is alive! It now shows that we have *five* different endpoints: One to retrieve *all* of the `DragonTreasure` resources, one to retrieve an *individual* `DragonTreasure`, one to *create* a `DragonTreasure`, one to *replace* a `DragonTreasure`, one to update it, and one to delete it. And this is more than just documentation. These endpoints *work*.

Go over and click "Try it Out", then "Execute", and... it doesn't actually return anything because our database is empty, but we it *does* return with a 200 status code and an empty array. We'll talk about all of the other fancy keys in the response shortly.

Oh, but I do want to mention one thing. As we just saw, the easiest way to create a set of API endpoints is by adding this `ApiResource` above your entity class. But you can *also* add this `ApiResource` attribute above classes *any* class: *not* just entities. That's something we're going to talk about in a future tutorial: it can be a nice way to separate what your API looks like from what your entity looks like, especially in bigger APIs. but again, that's for *later*. Right now, using `ApiResource` on top of our entity is going to work great.

Next: Let's discover this cool, interactive documentation a bit more. Where did
this come from? How does our app magically have a bunch of new routes? Let's find out!
