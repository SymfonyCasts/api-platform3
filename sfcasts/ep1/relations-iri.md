# Relations & Iris

When we tried to create a `Dragontreasure` with this `owner`, we set the field to
the owner's database id. And we found out that API Platform did *not* like that.
It said: "expected IRI". But what *is* an `IRI`?

We mentioned this term *one* time earlier in the tutorial. Go back down to the
GET `/api/users` collection endpoint. We know that every resource has an `@id` field
set to the `URL` to where you can fetch that resource. This is the `IRI` or
"International Resource Identifier". It's meant to be a unique identifier across
your *entire* API - like across *all* resources.

Think about it: the number "1" is *not* a unique identifier - we might have a
`DragonTreasure` with that id *and* a `User`. But this URL *is* unique. And, a URL
is also just a heck of a lot more handy than the integer id anyways.

So when we want to *set* a relation property, we need to use the IRI, like
`/api/users/1`.

When we hit execute, it works! A `201` status code. In the returned JSON, no
surprise, the `owner` field comes back *also* as IRI.

The takeaway from all of this is delightfully simple. Relations are just normal
properties, but we get and *set* them via their IRI string. I think this is such
a beautiful and clean way to handle this.

## Adding a Collection dragonTreasures Relation Field

Ok, let's talk about the *other* side of this relationship. Refresh the whole page
and go to the `GET` one user endpoint. Try this with a real user id - like 1 for
me. And... there's the basic data.

So the question I have *now* is: could we add a `dragonTreasure` field that shows
*all* the treasures that this user owns?

Well, let's think about it. We know that the serializer works serializing accessible
properties on an object. And... we *do* have a `dragonTreasures` property on `user`.

So... it *should* just work! To expose the field to our API, add it to the
serialization group `user:read`. Later, we'll talk about how we can *write* to a
collection field... but for now, just make it readable.

All right. Refresh... and look at the same `GET` endpoint. Down here, cool! It
shows a new `dragonTreasures` field in the example response. Let's try this: use the
same id, hit "Execute" and... oh, gorgeous It returns an array of IRI strings!
I love that! And, of course, if we need more information about these, we can make
a request to any of these URLs to get all the shiny details.

And if you get *really* fancy and use something like Vulcain, you could even "preload"
those relations so that the server pushes the data directly to the client.

But as cool as this is, this *does* lead me to a question: what if needing the
`DragonTreasure` data for a user is *so* common that, to avoid extra requests,
we want to embed the data right here - like JSON objects instead of IRI strings?

Can we do that? Absolutely. Let's find out how next.
