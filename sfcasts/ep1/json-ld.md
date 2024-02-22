# JSON-LD: Giving Meaning to your Data

I've just used the `GET` collection endpoint to fetch *all* of my resources... which
shows that we have a treasure with `id=1`. I'll close up this operation... and use
this other `GET` endpoint. Click "Try it out", put "1" in for the ID, and click
"Execute".

## What does our Data Mean?

Beautiful! But... I have some questions. Specifically: what is the *significance* of
these fields? What do `name` or `description` or `value` actually *mean*? Is the
description plain text? HTML? Is `name` a short name for the item or a *proper*
name? Is this value in dollars? Euros? French fries? What the heck is `coolFactor`?
And why am I asking *you* all of these unfair questions?

If you're a human (you are... right?), then you can probably figure out a lot of
the "meaning" of these fields on your own. But *machines* - okay, maybe *minus*
futuristic AIs - well, they *can't* figure this out. They don't know what these keys
*mean*. So... how *can* we give context and meaning to our data?

## RDF: Resource Description Framework

First, there's this thing called "RDF" or "Resource Description Framework", which
is a set of rules about how we describe the meaning of data so that computers can
understand. It's... *boring* and abstract, but basically a guide on how you
can define that one piece of data has a certain *type*, or one resource is a subclass
of some *other* type. In HTML, you can add attributes to your elements to add this
RDF metadata. You could say that this `<div>` describes a "person", and that this
person's name and telephone are these other pieces of data. This makes the random
HTML in your site *understandable* by machines. It's even better if two different
sites use the exact same definition of "person", which is why the types are URLs...
and sites try to reuse existing types rather than invent new ones.

## Hello JSON-LD

Why are we talking about this? Because JSON-LD attempts to do the same thing for
our API. Our API endpoints are returning JSON. But the `content-type` header in
the response says that this is `application/ld+json`.

When you see `application/ld+json`, it means that the data *is* JSON... but with
extra fields that have special meaning according to a giant JSON-LD spec document.
So, quite literally, JSON-LD is JSON... with extra goodies.

## The @id Field

For example, every resource, like `DragonTreasure`, has three `@` fields. The most
important is probably `@id`. This is the unique identifier to the resource. It's
basically the same as `id`, but it's even *better* because it's a URL. So instead
of just saying `"id": 1`, you have `@id` `/api/dragon_treasures/1`. That means
that, first, the string will be unique across all of our API resource classes and
second, this URL is handy! You can pop this into your browser, and, if you have the
`accept` header or add `.jsonld` to the end... whoops... let me get rid of
my extra `/`... yeah! You can *see* that resource. So `@id` is just like `id`...
but better.

## The @type and @context Fields

Another special field is `@type`. This describes the *type* of resource, like
what fields it has. And if we see two different resources that *both* have `@type`
`DragonTreasure`, we know that they represent the *same* thing.

You can think of `@type` almost like a class, which we can use to find out
what fields it has and the *type* of each field. Though... where *can* we actually
see that info?

This is where `@context` comes in handy. Copy the context URL, paste it into your
browser, and... beautiful! We get this very simple document that says that
`DragonTreasure` has `name`, `description`, `value`, `coolFactor`, `createdAt`, and
`isPublished` fields. If we want even *more* information about what those
mean, we can follow the `@vocab` link... to get to *another* page of info.

Here, we can see *all* the classes in our API - like `DragonTreasure` - and
*all* of its properties, like `name`. We can also see things like
`required: false`, `readable: true`, `writeable: true` and also that it's a `string`.
And we have this info for *every* field. Look: down at `value`. We can see that
this is an `integer`. This `xmls:integer` refers to *another* document, up
on top, which, if we followed it, would describe `xmls:integer` in more detail.

At this point, you might be saying:

> Hey! This seems a lot like the OpenAPI spec doc!

And you're *right*. We'll talk more about that in a few minutes.

You also might be thinking:

> Um... I kind of get what you're saying... but this is confusing.

And you would *also* be right! It's hard, as a mere human, to follow all of these links
to find the fields and their types. But imagine what this would look like to a
*machine*. It's an information *gold mine*!

Oh, and I want to mention that, if you look under `value`... `hydra:description`...
it picked up the PHP documentation that we added to that field earlier.

## Adding Extra Info

We can also add extra information above the class to describe this *model*.
We *could* do this via PHP documentation like normal, but `ApiResource` also
has some options we can pass. One is `description`. Let's *describe* this
as `A rare and valuable treasure.`

[[[ code('da2a8477b5') ]]]

Now, when we refresh the page... and search for "rare" (I'll close a few things
here...), yup! It added the description to the `DragonTreasure` type. And, not
surprisingly, this data also shows up over here inside Swagger, because it was
*also* added to the OpenAPI spec doc.

The point is, thanks to JSON-LD, we have extra fields in every response that
give each resource a unique id *and* a way to discover *exactly* what that "type"
looks like.

Next: we need to discuss one last piece of *theory*: what these `hydra` things
mean.
