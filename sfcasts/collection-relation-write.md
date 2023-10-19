# Writing to a Collection Relation

We are *so close* to completely re-implementing our API using these custom classes.
So excited!

Let's run *every* test to see where we stand.

```terminal
symfony php bin/phpunit
```

And... everything passes except *one*. This trouble-maker test is
`UserResourceTest::testTreasuresCannotBeStolen`. Let's go check it out!

Open `tests/Functional/UserResourceTest.php` and search for
`testTreasuresCannotBeStolen()`. Here it is.

Let's read the story. We update a user and attempt to change its `dragonTreasures`
property to contain a treasure owned by someone else. The test looks for a 422 status
code - because we want to prevent stealing treasures - but the test fails with
a 200.

But apart from the whole stealing thing, this is the first test that we've seen
that *writes* to a collection relation field. And *that* is an interesting topic
all on its own.

## Avoid Writable Collection Fields?

First, if you can, I'd recommend *against* allowing collection relationship fields
like this to be writable. I mean, you absolutely *can*... but it adds complexity.
For example, like this test shows, we need to worry about how setting the `dragonTreasures`
property changes the *owner* on that treasure. And there's already a *different*
way to do this: make a `patch()` request to this treasure and... change the
`owner`. Simple!

But, if you still want to allow your collection relation to be writable in
your DTO system, *fine*, here's how to do it. I'm kidding - it's not too bad.

## Testing the Collection Write

Start by duplicating this test. Rename it to `testTreasuresCanBeRemoved`.
I totally typo'ed that - mine says `cannot`, which is the *opposite* of what
I want to test - so make sure you get that right in your code.

Now we can dress this up a bit. Make the first `$dragonTreasure` owned by
`$user`. Then create a *second* `$dragonTreasure` *also* owned by `$user`, but we
won't need a variable for it... you'll see. Finally, add a *third* `$dragonTreasure`
called `$dragonTreasure3` that's owned by `$otherUser`.

So we have *three* `dragonTreasures`, *two*  owned by `$user`, and one by
`$otherUser`. Down here, we patch to modify `$user`. Remove `username` - we don't
care about that - then send *two* `dragonTreasures`: the *first* and the *third*:
`/api/treasures/` `$dragonTreasure3->getId()`.

We're going to test for *two* things. First, that the second treasure is
removed from this user. Think about it: `$user` *started* with these two treasures...
and the fact that this *second* treasure's IRI is *not* sent means that we want
it to be *removed* from `$user`.

Second, I added `$dragonTreasure3` *temporarily* to prove that treasures *can* be
stolen. This is currently owned by `$otherUser`, but we pass it to `dragonTreasures`...
and we're going to verify that the *owner* of `$dragonTreasure3` changes from
`$otherUser` to `$user`. That's not the end behavior we want, but it'll help us
get all the relation writing working. *Then* we'll worry about *preventing*
that.

Down here, `->assertStatus(200)` then extend the test by saying
`->get('/api/users/' . $user->getId())` and `->dump()`.

I want to see what the user looks like *after* the update. Finally, assert that
the `length` of the `dragonTreasures` field - I need quotes on that - is 2,
for treasures 1 and 3. Then assert that `dragonTreasures[0]` is equal to
`'/api/treasures/'.`, followed by `$dragonTreasure->getId()`. Copy that, paste,
and assert that the 1 key is `$dragonTreasure3`.

Lovely! That test took some work, but it'll be *super* useful. Let's... just
run it and see what happens! Copy the method name and, over at your terminal, run:

```terminal
symfony php bin/phpunit --filter=testTreasuresCanBeRemoved
```

And by "*cannot* be removed", I, of course, mean that it *can* be removed. That was
some good 'ol copy/paste madness right there. There we go. And... it *fails*,
on line 81. This means that the request was successful... but the
`dragonTreasures` are still the original two: `/api/treasures/2` instead of
`/api/treasures/3`. No changes were made to the treasures.

Why? Let's find out next and leverage the property accessor component to make
sure the changes save correctly.
