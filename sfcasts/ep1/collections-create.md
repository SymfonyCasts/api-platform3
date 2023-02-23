# Creating Embedded Objects

Is it possible to create a totally *new* `DragonTreasure` when we create a user?
Like... instead of sending the IRI of an *existing* treasure, we send an object?

Let's try it! First, I'll change this to a unique email and username. Then, for
`dragonTreasures`, clear those IRIs and, instead, pass a JSON object with the
fields that we know are required. Our new dragon user just scored a copy of
GoldenEye for N64! *Legendary*. Add a `description`... and a `value`.

In theory, this JSON body makes *sense*! But does it work? Hit "Execute" and...
nope! Well, not yet. But we know this error!

> Nested documents for attribute `dragonTreasures` are not allowed. Use IRIs instead.

## Making dragonTreasures Accept JSON Objects

Inside `User`, if we scroll *way* up, the `$dragonTreasures` property *is* writable
because it has `user:write`. 

[[[ code('e683c98090') ]]]

But we can't send an *object* for this property because we haven't added `user:write` 
to any of the fields *inside* of `DragonTreasure`. Let's fix that.

We want to be able to send `$name`, so add `user:write`... I'll skip `$description`
but do the same for `$value`. Now search for `setTextDescription()` which is
the *actual* description. Add `user:write` here too.

[[[ code('4a50c1c7c7') ]]]

Okay, *in theory*, we should *now* be able to send an embedded object. If we head
over and try it again... we upgraded to a 500 error!

> A new entity was found through the relationship `User#dragonTreasures`

## Cascading an Entity Relation Persist

This is great! We already know that when you send an embedded object, if you include
`@id`, the serializer will fetch that object first and *then* update it.
But if you *don't* have an `@id`, it will create a brand *new* object. Right now,
it *is* creating a new object,... but nothing told the entity manager to
*persist* it. *That's* why we're getting this error.

To solve this, we need to *cascade* persist this property. In `User`, on the
`OneToMany` for `$dragonTreasures`, add a `cascade` option set to `['persist']`.

[[[ code('5138b52b1c') ]]]

This means that if we're saving a `User` object, it should magically persist
any `$dragonTreasures` inside. And if we try it now... it works! That's awesome!
And apparently, our new treasure `id` is `43`.

Let's open up a new browser tab and navigate to that URL... plus `.json`... actually,
let's do `.jsonld`. Beautiful! We see that the `owner` is set to the new user that
we just created.

## How was owner Set? Again: The Smart Methods

But... hold your horses! We didn't *send* the `owner` field in the treasure
data... so how did that field get set? Well, first, it *does* make sense that we didn't
send an `owner` field for the new `DragonTreasure`... since the user that will
own it didn't even exist yet! Ok, then, but who *did* set the `owner`?

Behind the scenes, the serializer creates a new `User` object *first*. *Then*,
it creates a new `DragonTreasure` object. Finally, it sees that the new `DragonTreasure`
is *not* assigned to the `User` yet, and it calls `addDragonTreasure()`. When it
does that, the code down here sets the `owner`: just like we saw before. So our
well-written code is taking care of all of those details *for* us.

[[[ code('9d186c36cf') ]]]

## Adding the Valid Constraint

Anyways, you might remember from before that as soon as we allow a relation field
to send embedded data... we need to add *one* tiny thing. I won't do it, but if
we sent an empty `name` field, it *would* create a `DragonTreasure`... with an empty
`name`, even though, over here, if we scroll up to the `name` property, it's required!
Remember: when the system validates the `User` object, it will stop at
`$dragonTreasures`. It won't *also* validate those objects. If you *do* want to
validate them, add `#[Assert\Valid]`.

[[[ code('daf001e866') ]]]

Now that I have this, to prove that it's working, hit "Execute" and... awesome!
We get a 422 status code telling us that `name` shouldn't be empty. I'll
go put that back.

## Sending Embedded Objects and IRI Strings at the Same Time

We now know that we can send IRI strings *or* embedded objects for a relation
property - assuming we've setup the serialization groups to allow that. *And*, we
can even *mix* them.

Let's say that we want to create a new `DragonTreasure` object, but we're also going
to steal, *borrow*, a treasure from another dragon. This is *totally* allowed.
Watch! When we hit "Execute"... we get a 201 status code. This returns treasure
ids `44` (that's the new one) *and* `7`, which is the one we just stole.

Okay, we only have one more chapter about handling relationships. Let's see how we
can *remove* a treasure from a user to *delete* that treasure. That's *next*.
