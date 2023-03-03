# Subresources

We have *two* different ways to get the dragon treasures for a user. First, we could
fetch the `User` and read its `dragonTreasures` property. The second is via the filter
that we added a moment ago. In the API, that looks like `owner=/api/users/4` on
the `GET` collection operation for treasures.

This is *my* go-to way of getting the data... because if I want to fetch treasures,
it make sense to use a `treasures` endpoint. Besides, if a user owns a *lot*
of treasures, that'll give us pagination!

But you may sometimes choose to add a *special* way to fetch a resource
or collection of resources... almost like a vanity URL. For example, imagine that,
to get this same collection, we want the user to be able to go to
`/api/users/4/treasures.jsonld`. That, of course, doesn't work. But it *can* be
done. This is called a *subresource*, and subresources are *much* nicer in API
platform 3.

## Adding a Subresource via Another ApiResource

Okay, let's *think*. This endpoint will return treasures. So to add this
*subresource*, we need to update the `DragonTreasure` class.

How? By adding a *second* `ApiResource` attribute. We already have this main one,
so now add a *new* one. But this time, control the URL with a `uriTemplate` option
set to exactly what we want: `/users/{user_id}` for the wildcard part (we'll see
how that's used in a moment) then `/treasures`.

That's it! Well... also add `.{_format}`. This is *optional*, but that's the magic
that lets us "cheat" and add this `.jsonld` to the end of the URL.

[[[ code('dc8d25efc7') ]]]

Next, add `operations`... because we don't need *all six*... we really need just
*one*. So, say `[new GetCollection()]` because we will return a *collection*
of treasures.

[[[ code('e69163ede0') ]]]

Ok, let's see what this did! Head back to the documentation and refresh. Suddenly
we have... *three* resources and this one has the correct URL!

Oh, and we have *three* resources because, if you recall, we *customized* the
`shortName`. Copy that and paste it onto the new `ApiResource` so they match.
And to make PhpStorm happy, I'll put them in *order*.

[[[ code('81664bdefe') ]]]

Now when we refresh... perfect! *That's* what we want!

## Understanding uriVariables

We now have a new operation for fetching treasures. But does it *work*? It *says*
that it will retrieve a collection of treasure resources, so that's good. But...
we have a *problem*. It thinks that we need to pass the `id` of a `DragonTreasure`...
but it should be the id of a `User`! And even if we pass something, like `4`...
and hit "Execute"... look at the URL! It didn't even use the `4`: it still has
`{user_id}` in the URL! So *of course* it comes back with a 404 error.

The problem is that we need to help API Platform understand what `{user_id}` *means*.
We need to tell it that this is the id of the *user* and that it should use that
to query `WHERE owner_id` equals the value.

To do that, add a new option called `uriVariables`. This is where we describe any
"wildcards" in your URL. Pass `user_id` set to a `new Link()` object. There are
multiple... we want the one from `ApiPlatform\Metadata`.

[[[ code('7b22cfd797') ]]]

This object needs *two* things. First, point to the *class* that the `{user_id}`
is referring to. Do that by passing a `fromClass` option set to `User::class`.

[[[ code('0110937d99') ]]]

*Second*, we need to define which *property* on `User` *points* to `DragonTreasure`
so that it can figure out how to structure the query. To do *this*, set `fromProperty`
to `treasures`. So, inside `User`, we're saying that this property describes the
relationship. Oh, but I totally messed that up: the property is `dragonTreasures`.

[[[ code('7598afa4de') ]]]

Ok, cruise back over and refresh. Under the endpoint... yea! It says "User identifier".
Let's put `4` in there again, hit "Execute" and... *got it*. There are the *five*
treasures for this user!

And in the other browser tab... if we refresh... it *works*!

## How the Query is Made

Behind the scenes, thanks to the `Link`, API Platform basically makes the following
query:

>  SELECT * FROM dragon_treasure WHERE owner_id =

whatever we pass for `{user_id}`. It knows how to make that query by looking
at the Doctrine relationship and figuring out which column to use. It's *super*
smart.

We can actually see this in the profiler. Go to `/_profiler`, click on our request...
and, down here, we see 2 queries... which are basically the same: the 2nd
is used for the "total items" for pagination.

If you click "View formatted query" on the main query... it's even more complex
than I expected! It has an `INNER JOIN`... but it's basically selecting all the
dragon treasures data where `owner_id` = the ID of that user.

## What about toProperty?

By the way, if you look at the documentation, there's also a way to set all of this
up via the *other* side of the relationship: by saying `toProperty: 'owner'`.

This still works... and works exactly the same. But I recommend sticking with
`fromProperty`, which is consistent and, I think, more clear. The `toProperty` is
needed only if you didn't map the *inverse* side of a relationship... like if there
was *no* `dragonTreasures` property on `User`. Unless you have that situation,
stick with `fromProperty`.

## Don't Forget normalizationContext!

This is all working nicely except for one small problem. If you look back at the
data, it shows the wrong *fields*! It's returning *everything*, like `id` and
`isPublished`.

Those aren't supposed to be included thanks of our normalization groups. But since
we haven't *specified* any normalization groups on the new `ApiResource`, the serializer
returns everything.

To fix this, copy the `normalizationContext` and paste it down here. We don't need
to worry about `denormalizationContext` because we don't have any operations that
do any denormalizing.

[[[ code('b5e1afb5bb') ]]]

If we refresh now... got it!

## A Single Subresource Endpoint

Let's add *one* more subresource to see a slightly different case. I'll show you
the URL I want first. We have a treasure with ID `11`. This means we can go
to `/api/treasures/11.jsonld` to see that. *Now* I want to be able to
add `/owner` to the end to get the *user* that owns this treasure. Right now, that
doesn't work.... so let's *get* to work!

Because the resource that will be returned is a `User`, *that's* the class that
needs the new API Resource.

Above it, add `#[ApiResource()]` with `uriTemplate` set to
`/treasures/{treasure_id}` for the wildcard (though this can be called anything),
followed by `/owner.{_format}`.

[[[ code('afc3ca23c9') ]]]

Next pass `uriVariables` with `treasure_id` set to a `new Link()` - the one from
`ApiPlatform\Metadata`. Inside, set `fromClass` to `DragonTreasure::class`. And
since the *property* inside `DragonTreasure` that refers to this relationship is
`owner`, add `fromProperty: 'owner'`.

[[[ code('6e2a5a0e44') ]]]

We also know that we're going to need the `normalizationContext`... so copy that...
and paste it here. Finally, we only want *one* operation: a `GET` operation to
return a single `User`. So, add `operations` set to `[new Get()]`.

[[[ code('faf3b7ba68') ]]]

That should do it! Move back over to the documentation, refresh, and take a look
under "User". Yep! We have a new operation! And it even sees that the wildcard
is a "DragonTreasure identifier".

If we go refresh the other tab... it works!

Ok team, I lied about this being the last topic because... it's bonus topic time!
Next: let's create a React-based admin area automatically from our API docs. Woh.
