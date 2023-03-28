# 404 On Unpublished Items

We no longer return unpublished treasures from the treasure collection endpoint,
but we *are* still allowing them to be returned from the GET one endpoint. That's
because these `QueryCollectionExtensionInterface` classes are only called when
we are fetching a *collection* of items: not when we're selecting a *single* item.

To prove this, go into our test. Duplicate the the collection test, paste and
call it `testGetOneUnpublishedTreasure404s`. Inside, create just one `DragonTreasure`
that's unpublished... and make a `->get()` request to `/api/treasures/`... oh!
I need a `$dragonTreasure`. That's better. Now add the `$dragonTreasure->getId()`.

At the bottom, assert that status is 404... and we don't need any of these assertions,
or this `$json` variable.

A very simple test. Copy that method name and, you know the drill. Run *just*
that test:

```terminal-silent
symfony php bin/phpunit --filter=testGetOneUnpublishedTreasure404s
```

And... yep! It currently returns a 200 status code.

## Hello Query Item Extensions

How do we fix this? Well... just like how there's a
`QueryCollectionExtensionInterface` for the collection endpoint, there's also a
`QueryItemExtensionInterface` that's used whenever API Platform queries for a
*single* item.

You can create a totally separate class for this, but you can also combine it with
the collection extension. Add a second interface for `QueryItemExtensionInterface`.
Then, at the bottom, go to Code -> Generate - or command + N on a Mac - to add
the one method we need: `applyToItem()`.

Yea, it's almost identical to the collection method.... it works the same way...
and we'll even need the same logic! So, copy the code we need, then go to the
Refactor menu and say "Refactor this", which is also control + T on a Mac. Select
to extract this to a method... and call it `addIsPublishedWhere()`.

Awesome! I'll clean things up... and, you know what? I should have added this
`if` statement inside there too. So let's move that... which means we'll need a
`string $resourceClass` argument. Above, pass `$resourceClass` to the method.

Perfect! Now, in `applyToItem()`, we can do the *exact* same thing.

Ok, we're ready! Try the test now

```terminal-silent
symfony php bin/phpunit --filter=testGetOneUnpublishedTreasure404s
```

And.. it passes!

## Fixing our Test Suite

And since we've just made *so* many changes to our code, let's run *all* the tests:

```terminal
symfony php bin/phpunit
```

And... whoops! 3 failures - all coming from `DragonTreasureResourceTest`. The
problem is that, when we created treasures in our tests, we weren't explicit about
whether we wanted a published or unpublished treasure... and that value is set
randomly in our factory.

To fix this, we could always be explicit by controlling the `isPublished` field
in all cases. Or... we can be lazier and, in `DragonTreasureFactory`, set
`isPublished` to true by default.

Now, keep our fixture data interesting, when we create the 40 dragon treasures, we
can override `isPublished` and manually add some randomness: if a random number
from 0 to 10 is greater than 3, then make it published.

That *should* fix most of our tests. Though search for `unpublished`. Ah yea,
we're testing that an admin can `PATCH` to edit a treasure. We created an *unpublished*
`DragonTreasure`... just so we could assert that this was in the response and it
was false. Let's change this to `true` in both places.

There's one other similar test: change `isPublished` to `true` here as well.

*Now* try the tests:

```terminal-silent
symfony php bin/phpunit
```

## Allowing Updates to an Unpublished Item

They're happy! I'm happy! Well, *mostly*. We still have one problem. Find the first
`PATCH` test. We're creating a *published* `DragonTreasure`, updating it... and it
works just fine. Copy this entire test... paste it.. but delete the bottom part:
we only need the top. Call this method `testPatchUnpublishedWorks()`... then
make sure the `DragonTreasure` is *unpublished*.

Think about it: if we have a `DragonTreasure` and it has `isPublished` `false`,
I *should* be able to update it, right? This is my treasure, I created it and
I'm still working on it. We want this to work.

Will it? You can probably guess:

```terminal-silent
symfony php bin/phpunit --filter=testPatchUnpublishedWorks
```

Unfortunately... we get a 404! This both a feature... and little "gotcha". when you
create a `QueryCollectionExtensionInterface`, that's only used for this *one*
collection endpoint. But when you create an `ItemExtensionInterface` that's used
*whenever* we fetch a single treasure: *including* for the `Delete`, `Patch` and
`Put`. So, when an owner tries to `Patch` their own `DragonTreasure`, thanks to our
query extension, it can't be found.

There are two solutions for this. First, in `applyToItem()`, API Platform passes
us the `$operation`. So you could use this to determine if is this a `Get`,
`Patch` or `Delete` operation and only apply the logic for *some* of those.

And... this might make sense. Because if you're *allowed* to edit or delete a
treasure... that means you've already passed a security check... so we don't
necessarily need to lock things down via this query extension.

The other solution is, inside of this class, inject the `Security` service then
change the query to allow owners to see *their* treasures. The cool thing about this
solution is that it will also allow unpublished treasures to be returned from the
collection endpoint if the current user is the owner of that treasure.

Let's try this. Add the `public function __construct()`... and autowire the
wonderful `Security` service.

Below... life gets a bit trickier. Start with `$user = $this->security->getUser()`.
*If* we have a user, we're going to modify the `QueryBuilder` in a similar...
but slightly different way. Oh, actually, let me bring the `$rootAlias` up above
my if statement. Now, if the user is logged in, add an `OR %s.owner = :owner`...
then pass in one more `rootAlias`... followed by `setParameter('owner', $user)`.

Else, if there is no user, use the original query. And we need the `isPublished`
parameter in both cases... so keep that at the bottom.

I think I like that! Let's see what the test thinks:

```terminal-silent
symfony php bin/phpunit --filter=testPatchUnpublishedWorks
```

It like it too! In fact, *all* of our tests seem happy.

Ok team: final topic. When we fetch a `User` resource, we return its dragon treasures.
Does that collection *also* include *unpublished* treasures? Ah... yep it does! Let's
talk about why and how to fix it next.
