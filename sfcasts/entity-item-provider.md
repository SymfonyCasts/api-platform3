# Entity -> DTO Item State Provider

What about the item endpoint? If we go to `/api/users/6.jsonld`... it *looks* like
it works... but this is just the collection endpoint returning a single item!

We know that there are actually *two* core providers: `CollectionProvider` and
an *item* provider, whose job is to return one item or null. Right now, because we've
set `provider` to `EntityToDtoStateProvider`, it's using this *one* `provider`
for *every* operation. And that's ok... as long as we make it smart enough to handle
both cases.

We saw how to do this earlier: `$operation` is the key. Add
`if ($operation instanceof CollectionOperationInterface)`. Now we can warp all of
this code up here and... lovely!

Down here, this will be our item provider. `dd($uriVariables)`.

## Calling the Core Item Provider

When we try the item operation... nice! That's what we expect to see: the `id`
value, which is the dynamic part of the route.

Ok, just like with the collection provider, we do *not* want to do the querying work
manually. Instead, we'll... "delegate" it the core Doctrine item provider. Add
a second argument... we can just copy the first... type-hinted with `ItemProvider`
(the one from Doctrine ORM), and called `$itemProvider`.

I like it! Back below, let it do the work with
`$entity = $this->itemProvider->provide()` passing `$operation`, `$uriVariables`
and `$context`.

This will give us an `$entity` object or null. If we *don't* have an `$entity` object,
`return null`. That will trigger a 404. But if we *do* have an `$entity` object,
we don't want to return that directly. Remember, the whole point of this class is
to take the `$entity` object and *transform* it into a `UserApi` DTO.

So instead, `return $this->mapEntityToDto($entity)`.

*That* feels good. And.. the endpoint works *beautifully*. If we try an
*invalid* id, our provider returns null and API Platform takes care of the 404.

## Only Showing Published Dragon Treasures

Side note: if you follow some of these related treasures, they *may* 404 as well.
Let's see... we have 21 and 27. 21 works for me, but how about 27? That 27 *also*
works for me, of course. The reason they *might* 404 is that, right now, if I go
back, these `dragonTreasures` include *all* of the treasures related to this user:
even the *unpublished* ones. But in a previous tutorial, we created a query extension
that *prevented* unpublished treasures from being loaded.

Originally, when the `User` entity was our API resource, we didn't return all of
the treasures on that endpoint. We created `getPublishedDragonTreasures()` and
made *that* the `dragonTreasures` property.

But in our state provider, we're returning *all* of them. This is an easy fix:
change this to `getPublishedDragonTreasures()`. Actually, undo that... then refresh
the collection endpoint. Ok, we see treasures 16 and 40 down here... then after
using the new method... only 16!  "40" is *unpublished*.

That was *easy*! And it also highlights something pretty cool. In order to have a
`dragonTreasures` field that returned something special when our `User` *entity*
was an ApiResource, we needed a dedicated method and a `SerializedName` attribute.
But with a custom class, we don't need any weirdness. We can do *whatever* we want
in the state provider. So shiny and clean!

Next: Let's get our users *saving* with a state processor... but come on, we all
know by this point that we're going to make something *else* do most of the work.
