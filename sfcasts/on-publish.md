# Running Code "On Publish"

Oh, quick, minor thing about state processors. The `make:state-processor` command
created the `process()` method with a `void` return. And... that makes sense. API
Platform passes us the data and our job is just to save that... not return anything.

However, *technically* the `process()` method *can* return something. And, for consistency,
I *will* return something. Remove the `void` type and, at the bottom, return `$data`.

[[[ code('fae1d3f405') ]]]

I'll repeat this in `UserHashPasswordStateProcessor` for consistency.

[[[ code('6844996198') ]]]

Here's the deal: *if* you return something, *that* will be the "thing" that is
ultimately serialized and returned as JSON. If you do *not* return anything,
it will serialize `$data`. So, by returning `$data`... we're not changing any
behavior. But it's interesting to know that you *could* return something different.

## Detecting Changes: previous_data vs UnitOfWork

Ok, back to our goal. After we save, we need to detect if the `isPublished` field
*changed* from false to true, so we can run some custom code. But
by the time the state processor is called, the JSON from the user has already been
used to update the object. So `$data` will already have `isPublished` true.

In the last tutorial, we had a similar situation with a validator where we needed
to check if the *owner* of a `DragonTreasure` had changed. This logic lives in
`TreasureAllowToChangeValidator`. We start with `$value` - which is a collection
of `DragonTreasure` objects, loop over them, then use Doctrine's `UnitOfWork` to see
what each `DragonTreasure` looked like when it was *originally* loaded
from the database.

[[[ code('66546bf2f1') ]]]

Should we use that same trick here to see what the `isPublished` property
originally looked like? We *could*... but there's an easier way!

API Platform has a concept of
"previous data". When the request starts, API Platform *clones* the top-level object.
So, if we're editing a `DragonTreasure`, it grabs that from the database using
our state provider, clones it and, then keeps that "original" clone around in case
it comes in handy. We can use *that* to see if the value of `isPublished` changed.

But wait, why didn't we just this "previous data" thing in the last tutorial for
the validator? The reason is subtle. For the validator, the top-level object was
a `User` object. When PHP clones an object, it's a "shallow" clone: any string,
int or boolean properties are copied to the clone. But any *object* properties - like the
`DragonTreasure` objects - are *not* copied: the clone and the original `User`
objects both point to the *same* `DragonTreasure` objects in memory. So when the
`owner` of those treasures is updated... that affected both the main object *and*
the "previous object" clone. *That* is why we had to go deeper and use `UnitOfWork`.

But in *this* case, the `isPublished` property is a boring scalar boolean property.
So if we can get the previous data, that will have the correct, original, `isPublished`
value.

Great! So... how *do* we get the previous data? Notice we're passed an argument called
`$context`... which is full of useful info. Let's `dd()` that. 

[[[ code('f876c16baa') ]]]

Then copy the test name we're working on and... run it:

```terminal-silent
symfony php bin/phpunit --filter=testPublishTreasure
```

Oooo: a bunch of good stuff here. We have the current operation object...
and here it is: `previous_data`. Check out that beautiful `isPublished`
property: it's false!

Get rid of the `dd()`. At the bottom, say `$previousData = $context['previous_data']`.
And, if it's not there - which will happen for a `POST` request - set it to `null`.
I'll paste in the rest of the code that detects if `isPublished` changed from
false to true. Actually... this is not the *best* code I've ever
written - it's kinda confusing and won't let you publish *immediately* via
a `POST`... but it'll work for our purposes. Inside, add a dump.

[[[ code('a39c200601') ]]]

Let's do it! Run the test:

```terminal-silent
symfony php bin/phpunit --filter=testPublishTreasure
```

And... we hit the dump!

## Testing for and Creating Notifications

Our project has an unused `Notification` entity that I created before recording
*just* for this feature: it relates to a treasure and has a message. Nothing
fancy. Let's create one of these when we publish... by *first* testing for it.
TDD!

At the end of the test, say `NotificationFactory` - that's a Foundry factory that
I created, `::repository()` - to get a repository helper - then
`->assert()->count(1)`.

[[[ code('fe14151120') ]]]

With Foundry, our database is always empty at the start of a test: so checking for
1 row is perfect.

Back in the processor, remove the `dd()`... then check that the test fails our
new assertion:

```terminal-silent
symfony php bin/phpunit --filter=testPublishTreasure
```

Excellent! Back over, start by autowiring a private `EntityManagerInterface`
`$entityManager`. Then, below, I'll paste in some boring code that creates a
`Notification` and persists it.

[[[ code('5f5a2dbcb7') ]]]

Coolio. And the test says...

```terminal-silent
symfony php bin/phpunit --filter=testPublishTreasure
```

... that we rock! Next up: time to get crazy by creating a totally *custom* ApiResource
class that is *not* an entity.
