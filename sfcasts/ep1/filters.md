# Filters: Searching Results

Some of our dragon treasures are currently published and some are unpublished. That's
thanks to `DragonTreasureFactory`, where we randomly publish some but not
others.

Right now, the API is returning *every* last dragon treasure. In the future, we're
going to make it so that our API automatically returns only *published* treasures.
But to start, let's at least make it possible for our API clients to filter out
unpublished results if they want to.

## Hello ApiFilter

How? By Leveraging *filters*. API Platform comes with a *bunch* of built-in filters
that allow you to filter the collections of results by text, booleans, dates and
much more.

Here's how it works: above your class, add an attribute called `ApiFilter`.

There are typically two ingredients that you need to pass to this. The first is which
filter *class* you want to use. And if you look at the documentation, there's a bunch
of them, like one called `BooleanFilter` that we'll use now and another called
`SearchFilter` that we'll use in a few minutes.

Pass this `BooleanFilter` - the one from `ORM`, since we're using the Doctrine ORM -
because we want to allow the user to filter on a boolean field.

The second thing you need top pass is `properties` set to an array of which fields
or properties you want to use this filter on. Set this to `isPublished`:

[[[ code('88d9757f2c') ]]]

## Using the Filter in the Request

All right! Go back to the documentation and check out the GET collection
endpoint. When we try this... there's a new `isPublished` field! First, just
hit "Execute" without setting that. When we scroll all the way down, there we go!
`hydra:totalItems: 40`. Now set `isPublished` to `true` and try it again.

Yes! We have `hydra:totalItems: 16`. It's alive! And check out *how* the filtering
happens. It's dead simple via a query parameter: `isPublished=true`. Oh, and it
gets cooler. Look at the response: we have `hydra:view`, which shows the pagination
and now we *also* have a new `hydra:search`. Yea, API Platform actually *documents*
this new way of searching right in the response. It's saying:

> Hey, if you want, you can add a `?isPublished=true` query parameter to filter
> these results.

Pretty stinking cool.

## Adding Filters Directly Above Properties

Now, when you read about filters inside of the API Platform docs, they pretty
much always show it *above* the class, like we have. But you can *also* put the
filter above the *property* it relates to.

Watch: copy the `ApiFilter` line, remove it, and go down to `$isPublished`. Paste
this above. And now, we don't need the `properties` option anymore... API Platform
figures that out on its own:

[[[ code('3bd0cf4bce') ]]]

The result? The same as before. I won't try it, but if you peek at the collection
endpoint, it still has the `isPublished` filter field.

## SearchFilter: Filter by Text

What else can we do? Another really handy filter is `SearchFilter`. Let's make it
possible to search by text on the `name` property. This looks *almost* the same:
above `$name`, add `ApiFilter`. In this case we want `SearchFilter`: again, get
the one for the ORM. This filter *also* accepts an option. You can see here that,
in addition to `properties`, `ApiFilter` has an argument called `strategy`. That
doesn't apply to all filters, but it *does* apply to this one. Set `strategy`
to `partial`:

[[[ code('bf1c077bbd') ]]]

This will allow us to search on the `name` property for a *partial* match. It's
a "fuzzy" search. Other strategies include `exact`, `start` and more.

Let's give it a shot! Refresh the docs page. And... now the collection endpoint has
*another* filter box. Search for `rare` and hit Execute. Let's see, down here...
yes! Apparently 15 of the results have `rare` somewhere in the `name`.

And again, this works by adding a simple `?name=rare` to the URL.

Oh, let's also make the `description` field searchable:

[[[ code('f9800671f7') ]]]

And now... that shows up in the API too!

The `SearchFilter` is easy to set up... but it's a fairly simple fuzzy search.
If you want something more complex - like ElasticSearch - API Platform *does*
support that. You can even create your *own* custom filters, which we'll do in a
future tutorial.

Alrighty: next, let's see two more filters: one simple and one weird... A filter
that, instead of hiding *results*, allows the API user to hide certain *fields* in
the response.
