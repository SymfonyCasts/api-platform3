# Filtering Relation Collection

Hey, we've made a pretty fancy API! We've got a few sub-resources and embedded
relation data, which is readable and writable. This is all super awesome... but
it sure does crank up the complexity of our API, especially when it comes to security.

For example, we can no longer see unpublished treasures from the GET collection
or GET single endpoints. But we *can* still see unpublished treasures if you fetch
a user and read its `dragonTreasures` field.

## Writing the Test

Let's whip up a test real quick to expose this problem. Open our `UserResourceTest`.
At the bottom, add a public function `testUnpublishedTreasuresNotReturned()`.
Inside that, create a user with `UserFactory::createOne()`. Then use `DragonTreasureFactory`
to create a treasure that's `isPublished` false and has its `owner` set to the
`$user`... just so we know *who* the owner is.

For the action, say `$this->browser()`... and we *do* need to log in to use the
endpoint... but we don't care *who* we're logged in as... so say `actingAs()`
`UserFactory::createOne()` to log in as someone else.

Then `->get()` `/api/users/` `$user->getId()`. Finish with `assertJsonMatches()`
that the `length()` of `dragonTreasures` is zero - using a cool `length()` function
from that JMESPath syntax:

[[[ code('1c405c7deb') ]]]

Let's try it! Copy the method... and run it with `--filter=` that name:

```terminal-silent
symfony php bin/phpunit --filter=testUnpublishedTreasuresNotReturned
```

Ok! It expected 1 to be the same as 0 because we *are* returning the unpublished
treasure... but we don't want to!

## How Relations are Loaded

First... why *is* this unpublished `DragonTreasure` being returned? Didn't we
build query extension classes to prevent *exactly* this?

Well.... an important thing to understand is that these query extension classes
are used for the *main* query on an endpoint only. For example, if we use the
GET collection endpoint for treasures, the "main" query is for those treasures
and the query collection extension *is* called.

But when we make a call to a *user* endpoint - like to GET a single `User` - 
API Platform is *not* making a query for any treasures: it's making a query for
that *one* `User`. Once it has that `User`, to get this `dragonTreasures` field,
it does *not* make another query for those, at least not directly. Instead, 
if you open the `User` entity, API Platform - via the serializer - simply calls
`getDragonTreasures()`.

So it queries for the `User`, calls `->getDragonTreasures()`... and whatever *that*
returns is set onto the `dragonTreasures` field. And since this returns *all*
related treasures, that's what we get: including the unpublished ones.

## Adding a Filtered Getter Method

How can we fix this? By adding a *new* method that only returns the *published*
treasures. Say `public function getPublishedDragonTreasures()`, which returns a
`Collection`. Inside, we can get fancy: return `$this->dragonTreasures->filter()`
passing that a callback with a `DragonTreasure $treasure` argument. *Then*, return
`$treasure->getIsPublished()`:

[[[ code('5a89ba2e38') ]]]

That's a nifty trick for looping through all the treasures and getting a shiny
*new* collection with just the *published* ones.

Side note: one downside to this approach is that if a user has 100 treasures...
but only 10 of them are published, internally, Doctrine will first query for all
100... even though we'll only return 10. If you have *large* collections,
this can be a performance problem. In our Doctrine tutorial, we talk about fixing
this with something called the [Criteria system](https://symfonycasts.com/screencast/doctrine-relations/collection-criteria).
But with both approaches, the result is the same: a method that returns a subset
of the collection.

## Swapping the Getter into our API

At this point, the new method will work, but it's not *yet* part of our API.
Scroll up to the `dragonTreasures` property. It's currently readable and writable
in our API. Make the property only writable:

[[[ code('e01049a325') ]]]

Then, down on the new method, add `#[Groups('user:read')]` to make this part of
our API and `#[SerializedName('dragonTreasures')]` to give it the original name:

[[[ code('1f1b8dce19') ]]]

Drumroll! Try the test:

```terminal-silent
symfony php bin/phpunit --filter=testUnpublishedTreasuresNotReturned
```

It explodes! Because... I have a syntax error. Try it again. All green!

And... we're done! You did it! Thank you *so* much for joining me on this gigantic,
cool, challenging journey into API Platform and security. Parts of this tutorial
were pretty complex... because I want you to be able to solve *real*, tough security
problems.

In the next tutorial, we're going to look at even *more* custom and powerful things
that you can do with API Platform, including how to use classes for API resources
that are *not* entities.

In the meantime, let us know what you're building and, as always, we're here for
you in the comments section. Alright friends, see ya next time!
