# Creating your First ApiResource

We're about to build an API for the *very* important job of allowing dragons to show
off their treasure. Right now, our project doesn't have a *single* database entity...
but we're going to need one to store all that treasure.

## Generating our First Entity

Find your terminal and first run

```terminal
composer require maker --dev
```

to install Maker Bundle. *Then* run:

```terminal
php bin/console make:entity
```

Perfect! Let's call our entity `DragonTreasure`. Then it asks us a question that you
maybe haven't seen before - `Mark this class as an API platform resource`? It asks
because API Platform is installed. Say `no` because we're going to do this step
*manually* in a moment.

Okay, let's start adding properties. Start with `name` as a string, with a Length of
the default 255, and make it *not* nullable. Then, add `description` with a `text`
type, and make *that* not nullable. We also need a `value`, like... how much the
treasure is *worth*. That will be an `integer` not nullable. And we simply *must*
have a `coolFactor`: dragons need to specify just *how* awesome this treasure is.
That'll be a number from 1 to 10, so make it an `integer` and *not* nullable.
Then, `createdAt` `datetime_immutable` that's not nullable... and *Finally*, add
an `isPublished` property, which will be a `boolean` type, also not nullable. Hit "
enter" to finish.

Phew! There's nothing very special so far. This created two
classes: `DragonTreasureRepository` (which we're not going to worry about), and
the `DragonTreasure` entity itself with `$id`, `$name`, `$description`, `$value`, etc

[[[ code('a2d355a756') ]]]

along with the getter and setter methods. Beautifully boring. There *is* one little
bug in this version of MakerBundle, though. It generated an `isIsPublished()` method.
Let's change that to `getIsPublished()`.

[[[ code('05495189e0') ]]]

## Setting up the Database

All right, so we have our entity. Now we need a migration for its table... but that
might be a bit difficult since we don't have our database set up yet! I'm going to
use Docker for this. The DoctrineBundle recipe gave us a nice `docker-compose.yml`
file that boots up Postgres, so... let's use that! Spin over to your terminal and
run:

***TIP
In modern versions of Docker, run `docker compose up -d` instead of `docker-compose`.
***

```terminal
docker-compose up -d
```

If you don't want to use Docker, feel free to start your own database engine and
then, in `.env` or `.env.local`, configure DATABASE_URL. Because I'm using Docker as
well as the `symfony` binary, I don't need to configure anything. The Symfony web
server will automatically see the Docker database and set the `DATABASE_URL`
environment variable *for* me.

Okay, to make the migration, run:

```terminal
symfony console make:migration
```

This `symfony console` is just like `./bin/console` except it injects
the `DATABASE_URL` environment variable so that the command can talk to the Docker
database. Perfect! Spin over and check out the new migration file... just to make
sure it doesn't contain any weird surprises. 

[[[ code('0b595a7565') ]]]

Looks good! So spin back over and run this with:

```terminal
symfony console doctrine:migrations:migrate
```

Done!

## Exposing our First API Resource

We now have an entity and a database table. But if you go and refresh the
documentation... there's still nothing there. What we need to do is tell API Platform
to expose our `DragonTreasure` entity as an API resource. To do this, go above the
class and add a new attribute called `ApiResource`. Hit "tab" to add that `use`
statement.

[[[ code('57c556c560') ]]]

Done! As soon as we do that... and refresh... whoa! The documentation is alive! It
now shows that we have *six* different endpoints: One to retrieve *all* of
the `DragonTreasure` resources, one to retrieve an *individual* `DragonTreasure`, one
to *create* a `DragonTreasure`, two that edit a `DragonTreasure` plus one to delete
it. And this is more than just documentation. These endpoints *work*.

Go over and click "Try it Out", then "Execute". It doesn't actually *return* anything
because our database is empty, but it *does* gives us a 200 status code with some
empty JSON. We'll talk about all of the other fancy keys in the response shortly.

Oh, but I do want to mention one thing. As we just saw, the easiest way to create a
set of API endpoints is by adding this `ApiResource` attribute above your entity
class. But you can actually add this attribute above *any* class: *not* just
entities. That's something we're going to talk about in a future tutorial: it can be
a nice way to separate what your API looks like from what your entity looks like,
especially in bigger APIs. But again, that's for *later*. Right now,
using `ApiResource` on top of our entity is going to work great.

Let's discover this cool, interactive documentation a bit more. Where did
this come from? How does our app magically have a bunch of new routes? And do dragons
*really* love tacos? Let's find out next!
