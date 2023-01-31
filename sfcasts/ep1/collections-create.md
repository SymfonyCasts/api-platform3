# Creating Embedded Objects

Is is possible to create a totally *new* `DragonTreasure` when we create a user?
Like... instead of sending the IRI of an *existing* treasure, we can send an object?

Let's try it! First, I'll change this to a unique email and username. Then, for
`dragonTreasures`, clear those IRIs and, instead, pass a JSON object with the
fields that we know are required. Recently, this new dragon user acquired a copy
of GoldenEye for the N64. *Classic*. Add a `description`... and a `value`.

In theory, this JSON body makes *sense*! But does it work? Hit "Execute" and...
nope! Well, not yet. But we know this error:

> Nested documents for attribute `dragonTreasures` are not allowed. Use IRIs instead.

## Making dragonTreasures Accept JSON Objects

Inside `User`, if we scroll *way* up, the `$dragonTreasures` property *is* writable
because it has `user:write`. But we can't send an *object* for this property because
we haven't added `user:write` to any of the fields *inside* of `DragonTreasure`.
Let's fix that.

We want to be able to send `$name`, so add `user:write`... I'll skip `$description`
but do the same thing for `$value`. Now search for `setTextDescription()` which is
our *actual* description. Add `user:write` here too.

Okay, *in theory*, we should *now* be able to send an embedded object. If we head
over and try it again... we *upgraded*... to a 500 error! This one's familiar too:

> A new entity was found through the relationship `User#dragonTreasures`

## Cascading an Entity Relation Persist

This is good! Earlier, we learned that when you send an embedded object, if you
include an `@id`, the serializer will fetch that object first and *then* update it.
But if you *don't* have an `@id`, it will create a brand *new* object. Right now,
it *is* creating a brand new object,... but nothing told the entity manager to
*persist* it. *That's* why we're getting this error.

To solve this, we need to *cascade* persist this. In `User`, on the `OneToMany` for
`$dragonTreasures`, add a `cascade` option set to `['persist']`.

This means that if we're saving a `User` object, it should automatically persist
any `$dragonTreasures` inside. And if we try it now... it works! That's awesome!
And apparently, our new treasure `id` is `43`.

Let's open up a new browser tab and navigate to that URL... plus `.json`... actually,
let's do `.jsonld`. Beautiful! We see that the `owner` is set to the new user that
we just created.

## How was owner Set? Again: The Smart Methods

But... wait a second. We didn't *send* the `owner` field inside of the treasure
data... so how is it set? Well, first, it *does* make that we didn't set the
`owner` inside for the new `DragonTreasure`... since the user didn't even exist
yet! But then, who *did* set the `owner`?

Behind the scenes, the serializer creates a new `User` object *first*. *Then*,
creates a new `DragonTreasure` object. Finally, it sees that this new `DragonTreasure`
isn't assigned to the `User` yet, and it calls `addDragonTreasure()`. When it
does that, the code down here sets the `owner` - just like we saw before. So our
code being well-written is taking care of all of those details *for* us.

## Adding the Valid Constraint

Anyways, you might remember from before that as soon as we allow a relation field
to send embedded data... we need to add *one* tiny thing. I won't do it, but if
we sent an empty `name` field, it *would* create a `DragonTreasure`... with an empty
`name`, even though, over here, if we scroll up to the `name` property, it's required!
Remember: when the system validates the `User` object, it will stop at
`$dragonTreasures`. It won't *also* validate those objects. If you *do* want to
validate them, add `#[Assert\Valid]`.

Now that I have this, to prove that it's working, I'll hit "Execute" and... awesome!
We get a 422 status code telling us that `name` shouldn't be empty. Awesome. I'll
go put that back.

## Sending Embedded Objects and IRI Strings at the Same Time

We now know that we can send IRI strings *or* embedded objects for a relationship
property - assuming we've setup the serialization groups to allow that. *And*, we
can even *mix* them.

Let's say that we want to create a new `DragonTreasure` object, but we're also going
to *steal* a treasure from another dragon. This is *totally* allowed. Watch! When
we hit "Execute"... we get a 201 status code. This returns treasure ids `44` (that's
the new one) *and* `7`, which is the one we just stole from another dragon.

Okay, we only have one more chapter about handling relationships. Let's see how we
*remove* a treasure from a user to *delete* that treasure. That's *next*.
