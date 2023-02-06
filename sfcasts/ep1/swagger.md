# Swagger UI: Interactive Docs

The amazing interactive documentation that we've stumbled across is *not* something
from API platform! Nope, it's actually an open-source API documentation library
called Swagger UI. And the *really* cool thing about Swagger UI is that, if someone
create a file that *describes* any API, then that API can get all of this for free! I
love free stuff! *We* get Swagger UI because API platform *provides* that description
file out of the box. But more on that in a minute.

## Playing with our New API

Let's play around with this. Use the POST endpoint to create a new `DragonTreasure`.
We've recently plundered some "Gold coins"... which we got from "Scrooge McDuck".
He's mad. For our purposes, none of the other fields really matter. Down here, hit "
Execute" and... boom! When you scroll down, you can see that this made a POST request
to `/api/dragon_treasures` and sent all of that data as JSON! Then, our API returned
a "201" status code. A 201 status means that the request was successful and a
resource was *created*. Then it returned this JSON, which includes an `id` of `1`.
So, as I said, this isn't *just* documentation: we really *do* have a working API!
There are a few extra fields here too: `@context`, `@id`, and `@type` We'll talk
about those soon.

Now that we have a `DragonTreasure` to work with, open up this "GET" endpoint,
click "Try it Out", then "Execute". Oh, I love it. Swagger just made a `GET` request
to `/api/dragon_treasures` - this `?page=1` is optional. Our API returned information
inside something called `hydra:member`, which isn't particularly important yet. What
matters is that our API *did* return a list of all of the `DragonTreasures` we
currently have, which is just this one.

So in *just* a few minutes of work, we have a fully featured API for our Doctrine
entity. That is *cool*.

## Content Negotiation

Copy the URL to the API endpoint, open a new tab, and paste that in. Whoa! This...
returned HTML? But a second ago, Swagger said that it made a `GET` request to that
URL... and it returned *JSON*. What's going on?

One feature of API Platform is called "Content Negotiation". It means that our API
can return the same resource - like `DragonTreasure` - in *multiple* formats, like
JSON, or HTML... or even things like CSV. Oh, an ASCII format would be *awesome*.
Anyways, we *tell* API Platform which format we want by passing an `Accept` header in
the request. When we use the interactive docs, it passes this `Accept` header *for*
us set to `application/ld+json`. We'll talk about the `ld+json` part soon... but,
thanks to this, our API returns JSON!

And even though we don't *see* it here, when you go to a page in your browser, your
browser automatically sends an `Accept` header that says we want `text/html`. So,
this is API Platform showing us the "HTML representation" of our dragon treasures...,
which is just the documentation. Watch: when I open the endpoint this URL is for, it
automatically executed it.

The point is: if we want to see the JSON representation of our dragon treasures, we
need to pass this `Accept` header... which is super easy, for example, if you're
writing JavaScript.

*But* passing a custom `Accept` header isn't so easy in a browser... and it *would*
be nice to be able to see the JSON version of this. Fortunately, API Platform gives
us a way to cheat. Remove the `?page=1` to simplify things. Then, at the end of any
endpoint, you can add `.` followed by the *extension* of the format you want:
like `.jsonld`.

*Now* we see the `DragonTreasure` resource in that format. API Platform also supports
normal JSON out of the box, so we can see the same thing, but in pure, standard JSON.

## Where do the new Routes Come From?

The fact that *all* of this works means that... we apparently have a new route
for `/api` as well as a bunch of other new routes for each operation -
like `GET /api/dragon_treasures`. But... where did these come from? How are they
being dynamically added to our app?

To answer that, spin over to your terminal and run:

```terminal
./bin/console debug:router
```

I'll make this a bit smaller so we can see everything. Yup! Each endpoint is
represented by a normal, traditional route. *How* are these being added? When we
installed API Platform, its recipe added a `config/routes/api_platform.yaml` file.

[[[ code('f0f315d391') ]]]

This is actually a route *import*. It looks a little weird, but it activates API
Platform when the routing system is loading. API Platform then finds all of the API
resources in our app and generates a route for every endpoint.

The point is that all *we* need to focus on is creating these beautiful PHP classes
and decorating them with `ApiResource`. API Platform takes care of all the heavy
lifting of hooking up those endpoints. Of course, we'll need to tweak the
configuration and talk about more advanced things, but hey! That's the point of this
tutorial. And we're *already* off to an epic start.

Next: I want to talk about the secret behind how this Swagger UI documentation is
generated. It's called *OpenAPI*.
