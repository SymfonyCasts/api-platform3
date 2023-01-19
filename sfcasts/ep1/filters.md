# Filters: Searching Results

Some of our dragon treasures are currently published and some are unpublished. That's
thanks to our `DragonTreasureFactory`, where we randomly publish some but not
others.

Right now, *all* of them are being returned by our API. In the future, we're
going to make it so that our API automatically returns only *published* treasures.
But to start, let's at least make it possible for our API clients to filter out
unpublished results if they want to.

## Hello ApiFilter

How? By Leveraging *filters*. API Platform comes with a *bunch* of built-in filters
that allow you to filter the collections of results by text, booleans, dates and
much more.

Here's how it works: above your class add an attribute called `ApiFilter`.

There are typically two things that you need to pass to this. The first is which
filter *class* you want to use. And if you look at the documentation, there's a bunch
of them, like one called `BooleanFilter` that we'll use now and another called
`SearchFilter` that we'll use in a few minutes.

Pass this `BooleanFilter` - the one from `ORM`, since we're using the Doctrine ORM -
because we want to allow the user to filter on a boolean field.

The second thing you need top ass is `properties` set to an array of which fields
or properties you want to search on. Set this to `isPublished`.

----> PROOFED THROUGH HERE

All right, let's
go back to our documentation and check up the GET collection endpoint. When we tried
it out, there's a new `isPublished` field here, so let's try it empty. First, I'll
just hit `execute` and if I scroll all the way down, there we go. Hydra `totalItems`
- 40. Now if we say `isPublished` `true` and hit `execute`,

We have `hydra:totalItems=16`, it's alive. And check out how the filtering happens.
It's really simple, it's just a query parameter - `isPublished=true`. And this is
really cool down here. If you look at the response, we have the `Hydra:view`, which
shows the Pagination. We also have a new `Hydra:search`. Hydra actually documents
this
new way of searching through our content. This basically says, "Hey, if you want to,
you can add a `?isPublished=true` query parameter to filter these results."
Pretty cool. All right. Now when you read about API filters inside of the API
Platform documentation, they pretty much always show it above the class, but you can
also put it above the property. You can put filters above the properties that they
relate to. So I'm going to copy this API filter, remove it, and let's go down to the
`isPublished`
field and I'll add up there. And no surprise when you do this, you don't need to pass
the `properties` option anymore. That's going to be built in. So the result is the
same. I won't
try it, but if you look at our collection endpoint, it still has `isPublished` on
there.

All right, what? What else can we do? Well, there's another really handy filter
called a `SearchFilter`. So let's allow somebody to search on the `title` property.
So
I'll go above the `isPublished` property, add `ApiFilter`. In this case we
want a `SearchFilter`. And again, get the one from the `Symfony` bundle and do
`::class`. Now
this one does take an extra option. So you can see here that there is, in addition to
that `properties` argument, there's an argument here called `strategy`. This doesn't
apply to all filters, but it does apply to this one. We'll set `strategy` to
`partial`. So what this means is it's going to allow us to search the `title`
property and
it's going to be a fuzzy match. We'll be able to put any text, we'll be able to enter
something and if that matches any part of the `title`, it will return. There's also
`exact` and other strategies as well that you can read about in the documentation.
All
right, so I'm going to refresh the docs page. Now the collection endpoint has
another box. So let's search for `rare`. Hit Execute. And let's see, down at
the bottom. Awesome. 15 of our results have `rare` somewhere in the `title`.

And again, it works by just adding `?name=Rare` on the URL. All right, so let's also
make the description field searchable as well. And that now shows up inside of our
API. So this is still a fairly simple fuzzy search. If you want something, um, more
complex like ElasticSearch, you can hook API Platform filters with ElasticSearch as
well. And you can even create your own custom filters, which we'll do in a future
tutorial. Like for example, maybe you just want to have a little question mark--
`?search=` on the URL that searches across many fields. All right, next, let's see
one more filter, a second filter that's a bit different. Instead of hiding certain
results, that filter allows the API user to hide or show certain fields in the
response.

