# Entity -> DTO Item State Provider

What about the item endpoint? If we go to `/api/users/6.jsonld`... it *looks* like
it works... but it's a trap! It's just the collection *format*... with a single
item!

We know that there are *two* core providers: `CollectionProvider` and
an *item* provider, whose job is to return one item or null. Because we
set `provider` to `EntityToDtoStateProvider`, it's using this *one* `provider`
for *every* operation. And that's ok... as long as we make it smart enough to
handle both cases.

We saw how to do this earlier: `$operation` is the key. Add
`if ($operation instanceof CollectionOperationInterface)`. Now we can warp all of
this code up here. Lovely!

Below, this will be our item provider. `dd($uriVariables)`.

## Calling the Core Item Provider

When we try the item operation... nice! That's what we expect to see: the `id`
value, which is the dynamic part of the route.

Just like with the collection provider, we do *not* want to do the querying work
manually. Instead, we'll... "delegate" it the core Doctrine item provider. Add
a second argument... we can just copy the first... type-hinted with `ItemProvider`
(the one from Doctrine ORM), and called `$itemProvider`.

I like it! Back below, let *it* do the work with
`$entity = $this->itemProvider->provide()` passing `$operation`, `$uriVariables`
and `$context`.

This will give us an `$entity` object or null. If we *don't* have an `$entity` object,
`return null`. That will trigger a 404. But if we *do* have an `$entity` object,
we don't want to return that directly. Remember, the whole point of this class is
to take the `$entity` object and *transform* it into a `UserApi` DTO.

So instead, `return $this->mapEntityToDto($entity)`.

*That* feels good. And... the endpoint works *beautifully*. If we try an
*invalid* id, our provider returns null and API Platform takes care of the 404.

## Only Showing Published Dragon Treasures

Side note: if you follow some of these related treasures, they *may* 404 as well.
Let's see... we have 21 and 27. 21 works for me... and for 27... that *also*
works... of course. Anyway, the reason some *might* 404 is that, right now, if I go
back, the `dragonTreasures` property includes *all* the treasures related to this
user: even the *unpublished* ones. But in a previous tutorial, we created a query
extension that *prevented* unpublished treasures from being loaded.

Back when the `User` entity was our API resource, we *avoided* returning unpublished
treasures from this property. We created `getPublishedDragonTreasures()` and made
*that* the `dragonTreasures` property.

But in our state provider, we're setting *all* of them. This is an easy fix:
change to `getPublishedDragonTreasures()`. Actually, undo that... then refresh
the collection endpoint. Ok, we see treasures 16 and 40 down here... then after
using the new method... only 16! "40" is *unpublished*.

That was *easy*! And it highlights something cool. In order to have a
`dragonTreasures` field that returned something special when our `User` *entity*
was an ApiResource, we needed a dedicated method and a `SerializedName` attribute.
But with a custom class, we don't need any weirdness. We can do *whatever* we want
in the state provider. Our classes stay shiny and clean!

Next: Let's get our users *saving* with a state processor: a delicate dance that
involves handling new *and* existing users.
