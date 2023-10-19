# Writing to a Collection Relation

We are *so close* to completely re-implementing our API using these custom classes.
So excited!

Let's run *every* test to see where we stand.

```terminal
symfony php bin/phpunit
```

And... everything passes except *one*. This problematic test is
`UserResourceTest::testTreasuresCannotBeStolen`. Let's go check that out.

Open `tests/Functional/UserResourceTest.php` and search for
`testTreasuresCannotBeStolen()`. Here it is.

Let's read the story. We update a user and attempt to change its `dragonTreasures`
property to contain a treasure owned by someone else. The test looks for a 422 status
code - because we want to prevent stealing treasures - but the test failed with
a 200.

But actually, the more important thing right now is that this tests *writes* to a
collection relation field. And right now, our `dragonTreasures` field is *not*
writable at all.

## Avoid Writable Collection Fields?

First, I'd recommend *against* allowing collection relationship fields like this
to be writable. I mean, you absolutely *can*... but it adds complexity. For example,
like this test shows, we need to worry about how setting the `dragonTreasures`
property changes the *owner* on that treasure. And there's already a *different*
way to do this: make a `patch()` request to this specific treasure and change
the `owner`. Simple.

But, if you still want to allow your collection relation to be writable within
your DTO system, here's how to do it.

## Testing the Collection Write

Start by duplicating this test... then rename it to `testTreasuresCanBeRemoved`.
I totally misnamed that - mine says `cannot`, which is the *opposite* of what
I want to test - so make sure you get that right in your code.

Now we can dress this test up a bit. Make the first `$dragonTreasure` owned by
`$user`. Then make a *second* `$dragonTreasure` *also* owned by `$user`, but we
won't need a variable this time... you'll see. Finally add *third* `$dragonTreasure`
called `$dragonTreasure3` that's owned by `$otherUser`.

Ok: we have *three* `dragonTreasures`, *Two*  owned by `$user`, and one by
`$otherUser`. Down here, we patch to modify `$user`. Remove `username` - we don't
care about that - then send *two* `dragonTreasures`: the *first* and the *third*:
`/api/treasures/` `$dragonTreasure3->getId()`.

We're going to test for *two* thing. First, that the second `DragonTreasure` is
removed from this user. Think about it: `$user` *started* with these two treasures...
and the fact that this *second* treasure's IRI is *not* sent means that we want
that to be *removed* from the `$user`.

Second, I added `$dragonTreasure3` *temporarily* to prove that treasures *can* be
stolen. This is currently owned by `$otherUser`, but we pass it to `dragonTreasures`
and we're going to verify that the *owner* of `$dragonTreasure3` changes from
`$otherUser` to `$user`. That's not the end behavior we want, but it'll help us
get all of the relation writing working. *Then* we'll worry about *preventing*
that.

Down here, `->assertStatus(200)` then extend the test by saying
`->get('/api/users/' . $user->getId())` then `->dump()`.

I want to see what the user looks like *after* the update. Finally, assert that
the `length` of the `dragonTreasures` field - I need quotes on that - is 2,
for treasures 1 and 3. Then assert that `dragonTreasures[0]` is equal to
`'/api/treasures/'.`, followed by `$dragonTreasure->getId()`. Copy that, paste,
and assert that the 1 key is `$dragonTreasure3`.

Lovely! That test took some work, but it's doing to be *super* useful. The
`dragonTreasures` field still isn't writable but, pff, let's try it anyway.
Copy the method name and, over at your terminal, run:

```terminal
symfony php bin/phpunit --filter=testTreasuresCanBeRemoved
```

And by "*cannot* be removed", I, of course, mean that it *can* be removed. That was
some good 'ol copy/paste madness right there. There we go. And... it *fails*,
on line 81. This means that the request was successful... but the two
`$dragonTreasures` are still the original two: `/api/treasures/2` instead of
`/api/treasures/3`. No changes were made to the treasures.

Next: let's make this field writable and see how we can leverage the property
accessor component to make sure the changes save correctly.
