# Hydra: Describing API Classes, Operations & More

We're looking at the JSON-LD documentation that describes our API. Right now, we
know that we only have one API resource: `DragonTreasure`. But if you look down at
the `supportedClass` section, there are actually a *bunch* of supported classes.
There's one called `Entrypoint`, another called `ConstraintViolation`, and another
called `ConstraintViolationList`. Those last two are going to come up later when we
talk about validation errors.

## Entrypoint: Your API Homepage

But this `Entrypoint` is really interesting. It's called "The API entrypoint", and
it's actually describing what the *homepage* for our API *looks like*. We don't always
think about our APIs having a homepage, but they *can* and they *should*.

This is the HTML version of our API homepage. And if you scroll down to the bottom,
you can see other formats. Click "JSON-LD"... say "hello" to the API homepage
in the JSON-LD format! This returns an API resource called `Entrypoint`, whose whole
job is to basically tell us where we can find info about the *other* API resources.
It's like links on a homepage! You can *discover* the API by going to this
`Entrypoint` and following the `@context` link... which points to this.

## Hello Hydra

Anyways, the *point* of JSON-LD is to add those three extra fields to your API
resources: `@id`, `@type`, and `@context`. And then we can leverage `@context` to
point to *other* documentation to get more metadata or *more* context about the data.
For example, at the top of the JSON-LD documentation, it points to several *other*
documents as well that add more *meaning* to JSON-LD.

And, there's one really important one here called `hydra`. Hydra is, in short an
*extension* to JSON-LD: it describes even *more* fields that you can add to your
JSON-LD and what they mean.

Think about it. If we want to *totally* describe our API, we need to be able to
communicate things like what classes we have, their properties, whether each is
*readable* or *writeable*, and what *operations* each class supports. That
communication is done down here... and it's actually part of *Hydra*. Yup, if you
use JSON-LD by itself... it doesn't have a predefined way to advertise what your
models look like. But then Hydra says:

> What if we allow the API classes to be described with a key called
> `hydra:supportedClasses`?

Here's the big picture: API Platform allows us to fetch JSON-LD API documentation
that contains extra `hydra` fields. The end result is a system that *fully describes*
our API. They describe the models we have, the operations... *everything*.

## Why Hydra *and* OpenAPI?

And *yes*, if this sounds *very* similar to the point of OpenAPI, you're *absolutely*
correct. Both of them do the same thing: describe our API. In fact, if you go to
`/api/docs.json`, *this* is the OpenAPI description of our API. If we replace the
`.json` with  `.jsonld`, this is our *JSON-LD Hydra* description of the *same* API.
Why do we have both? Hydra is a bit more powerful: there are certain things it can
describe that OpenAPI can't. But OpenAPI is a lot more common and has more tools
built on top of it, like React Admin. API platform provides *both* in case you need
them.

Next: Let's talk about some serious debugging tools with API Platform, and then dive
back into *building* our API.
