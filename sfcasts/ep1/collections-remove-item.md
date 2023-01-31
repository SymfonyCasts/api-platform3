# Collections Remove Item

We just created a new user that has *two* treasures: IDs `7` and `44`. Let's update this user to see if we can make changes to those `$dragonTreasures`. We'll use the `PUT` endpoint, click "Try it out", and... let's see... the `id` we need is `14`, so I'll put `14` in here. I'll also remove all of the fields *except* for `dragonTreasures`. We know that this has *two* `dragonTreasures` currently - `/api/treasures/7` and `/api/treasures/44` - so if we sent this request, *in theory*, that should do *nothing*. And if we look down here... yeah, it makes no changes at all.

Suppose we want to be able to add a *new* `DragonTreasure` to this resource. To do that, we're going to list the two that it already has, along with `/api/treasures/8`. I'm totally just guessing that's a valid `id`. This dragon is going to steal that treasure from a *different* dragon, and when we hit "Execute"... that works *beautifully*. The serializer system noticed that it already had these first two, so it didn't do anything with those. It just added the new one - `id` `8`.

That's *cool*, but what I really want to talk about is *removing* a treasure. Let's say that our dragon left one of these treasures in their pants pocket and accidentally washed it in the laundry. I can't blame them. I lose my lip balm in there all the time. Since that treasure is soggy and useless now, we need to remove it from the list of treasures. Okay, no problem! We'll just mention the two our dragon still has and delete the other one. When we hit "Execute"... it *explodes* and we get a 500 error:

`An exception occurred while executing a query:
[...] Not null violation: 7 ERROR: null value in
column \"owner_id\"`

What happened? Well, our app set the `$owner` property for the `DragonTreasure` we just removed to `null` and is now trying to save it. *But* since we have it set to `nullable:false`, it's failing. Okay, let's back up a second. The serializer noticed that `7` and `8` were treasures we already had, but the *other* treasure - `44` - was *removed*. So, over on our `user` class, it called `removeDragonTreasure()`. What's really important about this is that it took that `DragonTreasure` and set the `owner` to `null` to break the relationship. Depending on your app, that might be *exactly* what you want. Maybe you'll allow `dragonTreasures` to have *no* current `owner` because they're still undiscovered and waiting for a dragon to find them. If *that's* the case, you'll just want to make sure that your relationship allows `null`. In that case, this would save just fine. But in *our* case, if a `DragonTreasure` no longer has an `owner`, we want to *delete* it *completely*.

We can do that in `User.php`, *way* up on our `DragonTreasure` property. After our `cascade`, let's add one more option here called `orphanRemoval: true`. This tells the serializer that if any of these `dragonTreasures` become orphaned, they should be *deleted*. With this change, if we hit "Execute" again... got it! It saves just fine. And if we try to look up the treasure with the `id` `44`, it's been deleted, so it's no longer in the system.

Next: Let's circle back to filters and how we can use them to search across related resources.
