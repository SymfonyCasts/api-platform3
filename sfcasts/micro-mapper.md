# MicroMapper: Central DTO Mapping

Doing the data transformation, from `UserApi` to the `User` entity, or the `User`
entity to `UserApi`, is the *only* part of our provider and processor that
*isn't* generic and reusable. Rats! If it wasn't for that code, we could
create a `DragonTreasureApi` class and do this whole thing over again with, like
almost no work! *Fortunately*, this is a well-known problem called "data mapping".

For this tutorial, I tried a few data mapping libraries, most notably
`jane-php/automapper-bundle`, which is super-fast, advanced, *and* fun to use.
However, it isn't *quite* as flexible as I needed... and extending it looked complex.
Honestly... I got stuck in a few places... though I know that work *is* being done
to make this package even friendlier.

The point is, we're not going to use that library. *Instead*, to handle the mapping,
I created a small package of my own. It's easy to understand, and gives us *full*
control... even if it's not *quite* as cool as jane's automapper.

## Installing micro-mapper

So let's get it installed! Run:

```terminal
composer require symfonycasts/micro-mapper
```

That kind of sounds like a superhero. Now that we have this in our app, we have
one new micromapper service that's good at converting data from *one* object
to another. Let's start by using it in our *processor*.

## Using the MicroMapper Service

Up on top, autowire a `private MicroMapperInterface $microMapper`. And
down here, for all the mapping stuff, copy the existing logic, because we'll
need it in a minute. Replace it with `return $this->microMapper->map()`.
This has two main arguments: The `$from` object, which will be `$dto` and the
*toClass*, so `User::class`.

Done! Well... not *quite*, but let's try running `testPostToCreateUser` anyway.

```terminal-silent
symfony php bin/phpunit --filter=testPostToCreateUser
```

And... it *fails* with a *500* error. The interesting thing is *what* that 500
error says. Let's "View Page Source" so we can read this even better. It says

> No mapper found for `App\UserResource\UserApi` -> `App\Entity\User`

And this comes from `MicroMapper`. This basically says:

> Hey, I don't know *how* to convert a `UserApi` object to a `User` object! Halp!

## Creating a Mapper

MicroMapper *isn't* magic... it's really the opposite. To teach micro mapper how
to do this conversion, we need to create a class that *explains* what we want.
That's called a *mapper class*. And these are fun!

Let me start by closing a few things... and then creating a new `Mapper/` directory
in `src/`. Inside of *that*, add a new PHP class called... how about
`UserApiToEntityMapper`, because we're going from `UserApi` to the `User` entity.

This class needs 2 things. First, to implement `MapperInterface`. And second, above
the class, to describe what it's mapping *to* and *from*, we need an `#[AsMapper()]`
attribute with `from: UserApi::class` and `to: User::class`.

To help the interface, go to "Code Generate" (or "command" + "N"
on a Mac) and generate the two *methods* it needs: `load()` and `populate()`. For
starters, let's `dd($from, $toClass)`.

Now, *just* by creating this and giving it `#[AsMapper]`, when we use MicroMapper
to do this transformation, it *should* call our `load()` method. Let's see if it
does!

Run the test:

```terminal-silent
symfony php bin/phpunit --filter=testPostToCreateUser
```

And... got it! *There's* the `UserApi` object we're passing, and *it's* passing
*us* the `User` class. The purpose of `load()` is to *load* the `$toClass` object
and return it, like by querying for a `User` entity or creating a new one.

To do the query, on top, add `public function __construct()` and inject the normal
`UserRepository $userRepository`. Down here, this will hold the same code that we
saw earlier. I like to say `$dto = $from` and `assert($dto instanceof UserApi)`.
That helps my brain *and* my editor.

Next, *if* our `$dto` has an `id`, then call `$this->userRepository->find($dto->id)`.
*Else*, create a brand `new User()` object.

It's *that* simple. And if, for some reason, we don't have a `$userEntity`,
`throw new \Exception('User not found')`, similar to what we did before. Down here,
`return $userEntity`.

So we've initialized our `$to` object and returned it. And that's the point of
`load()`: to do the *least* amount of work to get the `$to` object... but *without*
populating the data.

Internally, after calling `load()`, micro mapper will *then* call `populate()`
and pass us the `User` entity object that we just returned. To see this, let's
`dd($from, $to)`.

Run that test:

```terminal-silent
symfony php bin/phpunit --filter=testPostToCreateUser
```

Perfect! Here's our "from" `UserApi` object, and the *new* `User` entity.

Now... you might be wondering why we have both a `load()` method *and* a `populate()`
method... when it seems like these could just be *one* method. And you'd mostly
be right! But there's a technical reason why they're separated, and it'll
come in handy later when we talk about relationships. But for now, you can
imagine these two methods are really just one, continuous process: `load()` is
called, then `populate()`.

And no surprise, *this* is where we will take the data from the `$from` object and
put it onto the `$to` object. Once again, to keep me sane, I'll say `$dto = $from`
and `assert($dto instanceof UserApi)`... then
`$entity = $to` and `assert($entity instanceof User)`.

The code down here is going to be really boring... so I'll paste it.
At the bottom, `return $entity`.

We're using `$this->userPasswordHasher` here... so we *also* need to make sure, at
the top, to add `private UserPasswordHasherInterface $userPasswordHasher`.

So this is basically the same code we had before... but in a different spot.

Let's see what the test thinks!

```terminal-silent
symfony php bin/phpunit --filter=testPostToCreateUser
```

It *passes*! This is *huge*! We've offloaded this work to our mapper... which
means our processor is almost completely generic. Now we can remove the
`UserPasswordHasher` that we don't need anymore... and the `UserRepository` up here.
We can even remove those `use` statements.

We still *do* need to write the mapping code, but now it lives in a nice, central
location.

## Mapping the Other Direction

Ready to *repeat* this for the provider. Close the processor... and open it up.
This time, we're going from the `User` entity to `UserApi`. Copy all of this code,
*delete* it and, just like before, autowire `MicroMapperInterface $microMapper`.
Down here, this simplifies to `return $this->microMapper->map()` going from our
`$entity` to `UserApi::class`.

Sweet! If we tried this now, we'd get a 500 error because we don't have a mapper
for it. Back in `src/Mapper/`, create a new class called `UserEntityToApiMapper`...
implement `MapperInterface`... and above the class, add `#[AsMapper()]`. In this
case, we're going `from: User::class`, `to: UserApi::class`.

Implement both of the methods we need... and we start pretty much the same way as
before, with `$entity = $from` and `assert($entity instanceof User)`.

Down here, to create the DTO, we don't need to do any queries. We're *always*
going to instantiate a fresh new `UserApi()`. Set the ID onto it with
`$dto->id = $entity->getId()`... then `return $dto`.

Ok, the job of the `load()` method is *really* to create the `$to` object and...
*at least* make sure it has its identifier if there is one.

Everything else we need to do is down here in `populate()`. Start our usual way:
`$entity = $from`, `$dto = $to` and two asserts: `assert($entity instanceof User)`
and `assert($dto instanceof UserApi)`. Below that, use the exact code we had before.
We're just transferring the data. At the bottom, `return $dto`.

Phew! Let's try this! Head over to your browser, refresh this page, and... *oh*...

> Full authentication is required to access this resource.

*Of course*. That's because we added security! Head back over to the homepage,
click this username and password shortcut... *boop*... and *now* try to refresh
that page. It *works*! We *are* missing some of the data, though, which is
my fault.

I said `$dto = new UserApi()`. So instead of *modifying* the `$to` object I'm being
passed, I created a *new* one... and the original wasn't modified. There we go. If
I try it again... *much* better.

So this is *huge* people! Our provider and processor are now generic!
Let's finish the process of making them work for *any* API resource class *next*
