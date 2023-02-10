# Operations / Endpoints

API Platform works by taking a class like `DragonTreasure` and saying that you want
to expose it as a *resource* in your API. We do that by adding the `ApiResource`
attribute:

[[[ code('79ebe0e721') ]]]

Right now, we're putting this above a Doctrine entity, though, in a future
tutorial, we'll learn that you can really put `ApiResource` above *any* class.

## Hello Operations

Out-of-the-box, every `ApiResource` includes 6 endpoints, which API Platform calls
operations. You can actually see these in the profiler. This is the profiler for
`GET /api/dragon_treasures.json`. Click on the "API Platform" section. On top,
we see metadata for this API resource. Below, we see the operations. This... is
more info than we need right now, but there's `Get`, `GetCollection`, `Post`,
`Put`, `Patch` and finally `Delete`. These are the same things we see on the
Swagger documentation.

Let's take a quick look at these. First, which operations *return* data? Actually,
*all* of them - except for `Delete`. This `Get`, the `Post`, `Put`
and `Patch` endpoints all return a *single* resource - so a single treasure. And
`GET /api/dragon_treasures` returns a *collection*.

Which endpoints do we *send* data to when we use them? That's `POST` to create,
and `PUT` and `PATCH` to update. We don't send any data for `DELETE` or either
`GET` operation.

## PUT vs PATCH

Most of the endpoints are pretty self-explanatory: get a collection of treasures,
a single treasure, create a treasure and delete a treasure. The only confusing ones
are put versus patch. `PUT` says "replaces" and `PATCH` says "updates". That...
sounds like two ways of saying the same thing!

***TIP
In API Platform 4, `PUT` will become a "replace": meaning if you *only* sent a
single field, all of the other fields in your resource will be set to null: your
object is completely "replaced" by the JSON you send. Starting in API Platform
3.1, you can "opt into" this new behavior by adding an `extraProperties` option
to every `ApiResource`:

```php
#[ApiResource(
    // ...

    extraProperties: [
        'standard_put' => true,
    ],
)]#
```
***

The topic of PUT versus PATCH in APIs can get spicy. But in API Platform, at
least today, PUT and PATCH work the same: they're both used to update a resource.
And we'll see them in action along the way.

## Customizing Operations

One of the things that you might want to do is customize or *remove* some of these
operations... or even add *more* operations. How could we do that? As we saw on
the profiler, each operation is backed by a *class*.

Back over above the `DragonTreasure` class, after `description`, add an `operations`
key. Notice that I'm getting auto-completion for the options because these are
*named* arguments to the constructor of the `ApiResource` class. I'll show you that
in a minute.

Set this to an array and then repeat *every* operation we currently have. So,
`new Get()`, hit tab to auto-complete that, `GetCollection`, `Post`, `Put`, `Patch`
and `Delete`.

[[[ code('aded800265') ]]]

Now, if we move over to the Swagger documentation and refresh... absolutely nothing
changes! That's what we wanted. We've just repeated *exactly* the *default*
configuration. But *now* we're free to customize things. For example, suppose we
don't want treasures to be deleted... because a dragon would never allow their
treasure to be stolen. Remove `Delete`.. and I'll even remove the `use` statement.

[[[ code('b63ac57c0a') ]]]

Now when we refresh, the `DELETE` operation is gone.

## ApiResource Options

Ok, so every attribute we use is *actually* a class. And knowing that is *powerful*.
Hold command or control and click on `ApiResource` to open it. This is *really* cool.
Every argument to the constructor is an *option* that we can pass to the attribute.
And almost all of these have a link to the documentation where you can read more.
We'll talk about the most important items, but this is a great resource to know about.

## Changing the shortName

One argument is called `shortName`. If you look over at Swagger,
our "model" is currently known as `DragonTreasure`, which obviously matches the
class. This is called the "short name". And by default, the URLs -
`/api/dragon_treasures` - are generated from that.

Let's say that we instead want to shorten all of this to just "treasure". No
problem: set `shortName` to `Treasure`.

[[[ code('d27e8de4f7') ]]]

As soon as we do that, watch the name and URLs. Nice. This resource is now known
as "Treasure" and the URLs updated to reflect that.

## Operation Options

Though, that's not the only way to configure the URLs. Just like with `ApiResource`,
each operation is *also* a class. Hold Command (or Ctrl) and Click to
open up the `Get` class. Once again, these constructor arguments are options...
and most have documentation.

One important argument is `uriTemplate`. Yup, we can control what the
URL looks like on an operation by operation basis.

Check it out. Remember, `Get` is how you fetch a *single* resource. Add
`uriTemplate` set to `/dragon-plunder/{id}` where that last part will be the placeholder
for the dynamic id. For `GetCollection`, let's *also* pass `uriTemplate`
set to `/dragon-plunder`.

[[[ code('7653e2ba56') ]]]

Ok! Let's go check the docs! Beautiful! The other operations keep the old URL,
but those use the *new* style. Later, when we talk about subresources, we'll go
deeper into `uriTemplate` and its sister option `uriVariables`.

Ok... since it's a bit silly to have two operations with weird URLs, let's remove
that customization.

[[[ code('be95536724') ]]]

Now that we know a bunch about `ApiResource` and these operations, it's time
to talk about the *heart* of API Platform: Symfony's serializer. That's next.
