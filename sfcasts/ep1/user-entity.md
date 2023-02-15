# Creating a User Entity

We won't talk about security in this tutorial. But even still, we *do* need the concept
of a user... because each treasure in the database will be *owned* by a user...
or really, by a dragon. Later, we'll use this to allow API users to see which
treasures belong to which user and a bunch more.

## make:user

So, let's create that `User` class. Find your terminal and run:

```terminal
php bin/console make:user
```

We could use `make:entity`, but `make:user` will set up a bit of the security stuff
that we'll need in a *future* tutorial. Let's call the class `User`, yes we *are*
going to store these in the database, and set `email` as the main identifier field.

Next it asks if we need to hash and check user passwords. If the hashed version of
user passwords will be stored in your system, say yes to this. If your users won't
have passwords - or some external system checks the passwords - answer no. I'll
say yes to this.

This didn't do much... in a good way! It gave us a `User` entity, the repository
class... and a small update to `config/packages/security.yaml`. Yup, it just sets
up the user provider: nothing special. And again, we'll talk about that in a
future tutorial.

## Adding a username Property

Ok, inside the `src/Entity/` directory, we have our new `User` entity class with
`id`, `email` and `password` properties... and getters and setters below. Nothing
fancy. This implements two interfaces that we need for security... but those aren't
important right now.

[[[ code('0d971142a0') ]]]

Oh, but I *do* want to add one more field to this class: a `username` that we can
show in the API.

So, spin back over to your terminal and this time run:

```terminal
php bin/console make:entity
```

Update the `User` class, add a `username` property, `255` length is good, not null...
and done. Hit enter one more time to exit.

Back over on the class... perfect! There's the new field. While we're here, add
`unique: true` to make this unique in the database.

[[[ code('fafceb175b') ]]]

Entity done! Let's make a migration for it. Back at the terminal run:

```terminal
symfony console make:migration
```

Then... spin over and open that new migration file. No surprises: it creates the
`user` table:

[[[ code('0ae474f637') ]]]

Close that up and run it with:

```terminal
symfony console doctrine:migrations:migrate
```

## Adding the Factory & Fixtures

Sweet! Though, I think our new entity deserves some juicy data fixtures. Let's
use Foundry like we did for `DragonTreasure`. Start by running

```terminal
php bin/console make:factory
```

to generate the factory for `User`.

Like before, in the `src/Factory/` directory, we have a new class - `UserFactory` -
which is really good at creating `User` objects. The main thing we need to tweak
is `getDefaults()` to make the data even better. I'm going to paste in new
contents for the entire class, which you can copy from the code block on this page.

[[[ code('af90bf3450') ]]]

This updates `getDefaults()` to have a little more pizazz and sets the `password`
to `password`. I know, creative. I'm also leveraging an `afterInstantiation` hook
to hash that password.

Finally, to actually create some fixtures, open up `AppFixtures`. Pretty simple
here: `UserFactory::createMany()` and let's create 10.

[[[ code('79b774e6f6') ]]]

Let's see if that worked! Spin over and run:

```terminal
symfony console doctrine:fixtures:load
```

No errors!

Status check: we have a `User` entity and we created a migration for it. Heck, we even
loaded some schweet data fixtures! But it is not, yet, part of our API. If you refresh
the documentation, there's still only `Treasure`.

Let's make this part of our API next.
