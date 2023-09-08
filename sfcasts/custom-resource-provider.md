# Custom Resource State Provider

We have a shiny new API resource class and... for the most part, we'll use it
like normal.

## Customizing ApiResource Options

For example, instead of `DailyQuests`, maybe we change the
`shortName` to just `Quest`. When we peek at the docs, as expected, the title changes...
along with all the URLs.

## Making the State Provider

To be able to load data and have this collection endpoint *not* return a 404, we
need a *state provider*. And it's not *just* the `GET` endpoints. The `PUT` endpoint
uses a state provider, as well as `DELETE` and `PATCH`: these all first *load*
the resource, before editing or deleting it.

So let's make a state provider! We've done this before. At your terminal, run:

```terminal
./bin/console make:state-provider
```

Call it `DailyQuestStateProvider`. *Awesome* name!

Spin back over, open the `State/` directory and... there it is! Our job is simple:
to return the `DailyQuest` object or objects for the current operation.

Let's start *super* basic: return an array with two hard-coded `new DailyQuest()`
objects. They're both empty... because that class doesn't have any properties.

To tell API Platform to *use* the shiny new provider, in `DailyQuest`,
add `provider` set to `DailyQuestStateProvider::class`.

Let's give this a whirl! Dash back over to the docs to "Execute" the collection endpoint.
And... *yes*! No more 404! We get a 200... and it returned 2 items! All they
have are the JSON-LD fields -  `@id` and `@type` - but that makes sense since the
class doesn't have any other properties.

## Adding the Identifier

So, yay! *But*, before we run wild and add more properties, we need to talk about
why the `GET` *one* endpoint is missing. We *have* the `GET` *collection* endpoint,
but no `GET`-a-single-item endpoint. Why?

Every API resource needs an "identifier". Right now, our class does *not* have
an identifier... and that causes the two GET routes to collide. Let me show you!

Spin over and run:

```terminal
php bin/console debug:router
```

I love this. API Platform creates an actual route for *every* operation of every
API resource. I'll make this a little smaller... better. You can see all the
routes for the quests. Here's the one for `_get_collection` and, above it, the
one for `_get_single`... but with the *same* URL!

*Usually*, the URL would be `/api/quests/{id}`... where `id` is known as the
identifier. But... our `DailyQuest` doesn't have *any* properties... so API
Platform has *no* idea what to use for the identifier.

So what's the solution? The easiest is to add an `$id` property: `public int $id`...
and, for simplicity, let's add a constructor where we can pass the `int $id`. Set
the property inside.

Over in `DailyQuestStateProvider`, invent a few IDs: how about `4` and `5`. Cool,
*now* dump the routes again:

```terminal-silent
php bin/console debug:router
```

Behold! The single `GET` has a *different* URL with `{id}`. The `id`
was also missing from `put`, `patch`, and `delete`... and it's there now too. Over
on the docs, when we refresh... we see the *same* thing.

The identifier is important because it's used in the URLs... and so it's also
used to generate the `@id` IRI string for each item. Here, you can see the `@id`
is now pointing to `/api/quests/4`.

## A non-traditional Identifier with identifier: true

But wait, *how* did API Platform know that the `id` is the all-important "identifier"...
and not just some normal property? I'm... *honestly*... not entirely sure. But it
seems that the *name* `id` is special... *somewhere* in API platform. If you name
a property `id`, API Platform says:

> Oh, that must be your identifier!

And... it's usually not wrong! *But*, there *is* a more explicit way to say that
a property is an identifier. Next, instead of an integer identifier, let's see if
we can use a *date* identifier, so we have URLs like `/api/quests/2023-06-05`.
