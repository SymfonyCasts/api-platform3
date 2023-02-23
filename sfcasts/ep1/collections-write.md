# Adding Items to a Collection Property

Let's fetch a single user in our API: I know one exists with ID 2. And cool!

As we learned earlier, exposing a *collection* relation property is just like any
other field: simply make sure that it's in the correct serialization group. And then
you can go *further* with serialization groups to choose between making it return
as an array of IRI strings or as an array of embedded objects, like we have now.

New question: could we also *modify* the `dragonTreasures` that a user owns from
one of the user operations? The answer is, of course, yea! And we're going to do
this in increasingly crazy ways.

## Making the Collection Field Writable

Look at the POST endpoint. We don't see a `dragonTreasures` field right now because...
the field simply isn't writable: it's not in the correct group. To remedy that, we
know what to do: add `user:write`.

[[[ code('961aab3c0d') ]]]

Easy peasy! When we refresh the docs, and check that endpoint... there we go:
`dragonTreasures`. And it says that this field should be an array of strings: an
array of IRI strings.

Let's try crafting a new user. Fill in the `email` and `username`. Then, let's
assign the new user to a few *existing* treasures. Let's sneak up to the GET collection
endpoint for treasures... and awesome. We have ids 2, 3 and 4.

Back down here, assign `owner` to an array with `/api/treasures/2`, `/api/treasures/3`
and `/api/treasures/4`.

Makes sense, right? If the API can return `dragonTreasures` as an array of IRI strings,
why can't we *send* an array of IRI strings? When we hit Execute... indeed! It
worked perfectly!

And since each treasure can have only one owner... it means that we kinda stole
those treasures from someone else! Sorry!

## The adder & remover Methods for Collections

But... wait a second, how did that work? We know that when we send fields like
`email`, `password`, and `username`, because those are private properties, the
serializer calls the setter methods. When we pass `username`, it calls
`setUsername()`.

[[[ code('fbc23eb8f2') ]]]

So when we pass `dragonTreasures`, it must call `setDragonTreasures`, right?

Well guess what? We don't *have* a `setDragonTreasures()` method! But we *do* have
an `addDragonTreasure()` method and a `removeDragonTreasure()` method.

[[[ code('598a552058') ]]]

The serializer is really smart. It sees that the new `User` object has no
`dragonTreasures`. So it recognizes that each of these three objects are *new*
to this user and so it calls `addDragonTreasure()` once for each.

And the way that MakerBundle generated these methods is *critical*. It takes the
new `DragonTreasure` and sets the `owner` to be *this* object. That's important
because of how Doctrine handles relationships: setting the owner sets what's called
the "owning" side of the relationship. Basically, without this, Doctrine wouldn't
save this change to the database.

The takeaway is that, thanks to `addDragonTreasure()` and its magical powers,
the `owner` of the `DragonTreasure` is changed from its old owner to the new `User`,
and everything saves exactly like we want.

Next, let's get more complex by allowing treasures to be *created* when we're creating
a new `User`. We're also going to allow treasures to be *removed* from a `User`...
for the unlikely event that the dwarves take back the mountain. As if.
