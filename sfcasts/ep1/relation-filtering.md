# Filtering on Relations

Earlier, we added a bunch of nice filters to `DragonTreasure`. Let's add a few
more - starting with `User` - so we can show off some filtering *superpowers* for
relations.

## Using PropertyFilter Across Relations

Start like normal: `ApiFilter` and let's first use `PropertyFilter::class`. Remember:
this is kind of a fake filter that allows our API client to select which *fields*
they want. And this is all pretty familiar so far.

[[[ code('794f814755') ]]]

When we head over, refresh, and go to the `GET` collection endpoint... we see a new
`properties[]` field. We could choose to return just `username`... or `username`
*and* `dragonTreasures`.

When we hit "Execute"... perfect! We see the two fields... where `dragonTreasures`
is an array of objects, each containing the fields we chose to embedded.

Again, this is super duper normal. So let's try something more interesting. In
fact, what we're going to try isn't supported directly in the interactive docs.

So, copy this URL... paste and add `.jsonld` to the end.

Here's the goal: I want to return the `username` field and then *only* the `name`
field of each dragon treasure. The syntax is a bit ugly: it's `[dragonTreasures]`,
followed by `[]=name`.

And just like that... it only shows `name`! So right out of the box,
`PropertyFilter` allows us to reach across relationships.

## Searching Relation Fields

Let's do something *else*. Head back to `DragonTreasure`. It might be
handy to filter by the `$owner`: we could quickly get a list of
all treasures for a specific user.

No sweat! Just add `ApiFilter` above the `$owner` property, passing in the trusty `SearchFilter::class` followed by `strategy: 'exact'`.

[[[ code('950f478cdd') ]]]

Back over on the docs, if we open up the `GET` collection endpoint for treasures
and give it a whirl... let's see... here we go - "owner". Enter something
like `/api/users/4`... assuming that's actually a real user in our database,
and... yes! Here are the *five* treasures owned by that user!

But I want to get crazier: I want to find all treasures
that are owned by a user matching a specific *username*. So instead of filtering on
`owner`, we need to filter on `owner.username`.

How? Well, when we want to filter simply by `owner`, we can put the `ApiFilter`
right above that property. But since we want to filter on `owner.username`, we can't
put that above a property... because `owner.username` *isn't* a property.
This is one of the cases where we need to put the filter above the
*class*. And... that also means we need to add a `properties` option set to an array.
Inside, say `'owner.username'` and set that to the `partial` strategy.

[[[ code('986925a0c3') ]]]

Ok! Head back over and refresh. We know we have an owner whose username is "Smaug"...
so let's go back to the `GET` collection endpoint and... here in `owner.username`,
search for "maug"... and hit "Execute".

Let's see... That worked! This shows all treasures owned by any user whose username
contains `maug`. Pretty cool!

Ok squad: get ready for the grand finale - *Subresources*. These have *seriously*
changed in API Platform 3. Let's dive into them next.
