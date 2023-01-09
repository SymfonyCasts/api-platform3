# Hydra: Describing API Classes, Operations & More

So we're looking at the JSON-LD documentation that describes our api. And right now
we know that we only have one API resource Dragon Treasure. But if you look down here
on this supported classes part, there's actually a bunch Dragon Treasure, one called
Entrypoint, another called ConstraintViolation Violation, and another called
ConstraintViolation Violation list. Those last year were going to come up when we
talked about validation errors later. But this Entrypoint is really interesting. It's
called the API Entrypoint. And what it's actually describing here is what the
homepage for our API looks like. You don't always think of your API having a
homepage, but it can and it should. So this is actually the HTML version of our API
homepage. And if you scroll down to the bottom, you can see other formats down here.
If you click Jsun ld, say hello to your homepage for your JSON-LD format. And what
you're literally seeing here is an API resource called Entrypoint, whose whole job is
basically to tell you where you can find the other API resources. Right now we have
one just called it Dragon Treasure, but that's a really cool, you can just discover
the entire API by going to this Entrypoint and getting more information from the
context which points to this document here. Anyways, the big point of JSON-LD is
basically to add those three extra

Fields to your API resources ID type in context. And by using that system, you can
use context to point to other documentation to kind of get more metadata or more
context about your data.

But at the top of the JSON-LD documentation, you can see that it, it points to
several other documents. This is a way to actually add more meaning to JSON-LD.
There's one really important one here called Hydra. Hydra in short adds more to
JSON-LD. It's in extension. If you think about it, if we want to describe our api, we
need to be able to communicate things like what classes we have, what properties
those have, where they're, they're readable or writeable in what operations each
class has. That's stuff that's actually done down here, and that's actually part of
Hydra. Hydra says that we should have these key called Hydra supported classes with
all of these sub keys under it to describe it.

So to say it a simpler way, APAP platform allows you to fetch your resources as
JSON-LD with additional Hydra fields. The end result of that is.json-LD and Hydra
fully describe our api. They describe the models we have, the operations, everything.
And yes, if this sounds very similar to OpenAPI, you're absolutely correct. They both
do the same thing. They describe our api. In fact, if you go to /api /Jo docs.json,
this is the OpenAPI description of our api. We add JSON-LD onto the end of it. This
is our JSON-LD Hydra description of the same api. Why do we have both? Well, Hydra is
a bit more powerful. There's certain things that it can describe that OpenAPI doesn't
have the ability to describe, but OpenAPI is a lot more common and has a lot of tools
built on top of it, like React admin. So API platform gives you both in case you need
them. All right. Next, let's talk about some serious debugging tools with API
platform and then dive back into building our api.
