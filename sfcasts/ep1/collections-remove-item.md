# Removing Items from a Collection

Our brand-new user is the proud owner of *two* treasures with IDs `7` and `44`.
Let's update this user to see if we can make some changes to `$dragonTreasures`.
Use the `PUT` endpoint, click "Try it out", and... let's see... the `id` we need
is `14`... so I'll enter that. I'll also remove every field *except* for
`dragonTreasures` so we can focus.

We know that this currently has *two* dazzling treasures - `/api/treasures/7` and
`/api/treasures/44`. So if we send this request, *in theory*, that should do...
*nothing*! And if we look down here... yeah: it made no changes at all.

Suppose we want to add a *new* `DragonTreasure` to this resource. To do
that, we list the two that it already has, along with `/api/treasures/8`. I'm totally
guessing that's a valid `id`. When we hit "Execute"... that works *beautifully*. The
serializer system noticed that it already had these first two, so it didn't do
anything with those. It just added the new one with id `8`.

## Removing an Item from a Collection

That's *cool*, but what I really want to talk about is *removing* a treasure. Let's
say that our dragon left one of these treasures in their pants pocket and
accidentally washed it in the laundry. I can't blame them. I lose my lip balm in
there all the time. Since the treasure is soggy and useless now, we need to remove
it from the list of treasures. No problem! We'll just mention the two our dragon
*still* has and remove the other one. When we hit "Execute"... it *explodes*!

> An exception occurred while executing a query: [...] Not null violation: 7.
> null value in column "owner_id"

What happened? Well, our app set the `$owner` property for the `DragonTreasure` we
just removed to `null`... and is now trying to save it. *But* since we have it set to
`nullable: false`, it's failing.

[[[ code('15cff49a62') ]]]

But... let's take a step back and look at the *whole* picture. First, the serializer
noticed that treasures `7` and `8` are *already* owned by the `User`... so it did
nothing with those. But *then* it noticed that the treasure with id 44 - which
*was* owned by this `User` - is missing!

Because of that, over on our `User` class, the serializer called
`removeDragonTreasure()`. What's really important is that it takes that
`DragonTreasure` and set the `owner` to `null` to break the relationship. Depending
on your app, that might be *exactly* what you want. Maybe you allow
`dragonTreasures` to have *no* `owner`... like... they're still undiscovered and
waiting for a dragon to find them. If *that's* the case, you'll just want to make
sure that your relationship allows `null`... and everything will save just fine.

But in *our* case, if a `DragonTreasure` no longer has an `owner`, we want to *delete*
it *completely*. We can do that in `User`... *way* up on the `dragonTreasures`
property. After `cascade`, add one more option here: `orphanRemoval: true`.

[[[ code('aa791b1eab') ]]]

This tells Doctrine that if any of these `dragonTreasures` become "orphaned" - meaning
they no longer have *any* owner - they should be *deleted*.

Let's try it. When we hit "Execute" again... got it! It saves just fine.

Next: Let's circle back to filters and see how we can use them to search across
related resources.
